<?php
session_start();

// Check if user is logged in and is an administrator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'administrator') {
    header("Location: ../signin.php");
    exit();
}

require_once '../../../src/services/dbconnect.php';

$first_name = $_SESSION['first_name'] ?? 'Admin';

// Handle user actions (activate, deactivate, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $user_id = $_POST['user_id'] ?? null;
    $action = $_POST['action'];

    if ($user_id) {
        switch ($action) {
            case 'activate':
                $conn->query("UPDATE users SET status = 'active' WHERE user_id = $user_id");
                break;
            case 'deactivate':
                $conn->query("UPDATE users SET status = 'inactive' WHERE user_id = $user_id");
                break;
            case 'delete':
                $conn->query("DELETE FROM users WHERE user_id = $user_id");
                break;
        }
    }
    header("Location: manage-users.php");
    exit();
}

// Fetch users with filters
$role_filter = $_GET['role'] ?? '';
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

$query = "SELECT * FROM users WHERE 1=1";
if ($role_filter) {
    $query .= " AND role = '" . $conn->real_escape_string($role_filter) . "'";
}
if ($status_filter) {
    $query .= " AND status = '" . $conn->real_escape_string($status_filter) . "'";
}
if ($search) {
    $search_term = $conn->real_escape_string($search);
    $query .= " AND (first_name LIKE '%$search_term%' OR last_name LIKE '%$search_term%' OR email LIKE '%$search_term%')";
}
$query .= " ORDER BY created_at DESC";

$users_result = $conn->query($query);

// Get statistics
$stats = [];
$stats['total'] = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$stats['active'] = $conn->query("SELECT COUNT(*) as count FROM users WHERE status = 'active'")->fetch_assoc()['count'];
$stats['inactive'] = $conn->query("SELECT COUNT(*) as count FROM users WHERE status = 'inactive'")->fetch_assoc()['count'];
$stats['organizers'] = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'organizer'")->fetch_assoc()['count'];
$stats['managers'] = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'manager'")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | Gatherly</title>
    <link rel="icon" type="image/x-icon" href="../../assets/images/logo.png">
    <link rel="stylesheet" href="../../../src/output.css?v=<?php echo filemtime(__DIR__ . '/../../../src/output.css'); ?>">
    <script src="https://kit.fontawesome.com/2a99de0fa5.js" crossorigin="anonymous"></script>
</head>

<body class="bg-gradient-to-br from-slate-50 via-white to-blue-50 font-['Montserrat']">
    <!-- Navbar -->
    <nav class="sticky top-0 z-50 bg-white shadow-md">
        <div class="container px-4 mx-auto sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-12 sm:h-16">
                <div class="flex items-center h-full">
                    <a href="../home.php" class="flex items-center group">
                        <img class="w-8 h-8 mr-2 transition-transform sm:w-10 sm:h-10 group-hover:scale-110"
                            src="../../assets/images/logo.png" alt="Gatherly Logo">
                        <span class="text-lg font-bold text-gray-800 sm:text-xl">Gatherly</span>
                    </a>
                </div>
                <div class="items-center hidden gap-6 md:flex">
                    <a href="admin-dashboard.php" class="text-gray-700 transition-colors hover:text-indigo-600">Dashboard</a>
                    <a href="manage-users.php" class="font-semibold text-indigo-600 transition-colors hover:text-indigo-700">Users</a>
                    <a href="manage-venues.php" class="text-gray-700 transition-colors hover:text-indigo-600">Venues</a>
                    <a href="manage-events.php" class="text-gray-700 transition-colors hover:text-indigo-600">Events</a>
                    <a href="reports.php" class="text-gray-700 transition-colors hover:text-indigo-600">Reports</a>
                    <div class="relative">
                        <button id="profile-dropdown-btn"
                            class="flex items-center gap-2 text-gray-700 transition-colors cursor-pointer hover:text-indigo-600">
                            <i class="text-2xl fas fa-user-shield"></i>
                            <span><?php echo htmlspecialchars($first_name); ?></span>
                            <i class="text-xs fas fa-chevron-down"></i>
                        </button>
                        <div id="profile-dropdown"
                            class="absolute right-0 hidden w-48 py-2 mt-2 bg-white rounded-lg shadow-lg">
                            <a href="../../../src/services/signout-handler.php"
                                class="block px-4 py-2 text-red-600 hover:bg-red-50">Sign Out</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container px-4 py-8 mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="mb-2 text-3xl font-bold text-gray-800 sm:text-4xl">
                <i class="mr-2 text-indigo-600 fas fa-users-cog"></i>
                User Management
            </h1>
            <p class="text-gray-600">Manage system users and their access permissions</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 gap-4 mb-8 sm:grid-cols-2 lg:grid-cols-5">
            <div class="p-4 bg-white border-l-4 border-blue-500 shadow-md rounded-xl">
                <p class="mb-1 text-xs text-gray-600 uppercase">Total Users</p>
                <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['total']); ?></p>
            </div>
            <div class="p-4 bg-white border-l-4 border-green-500 shadow-md rounded-xl">
                <p class="mb-1 text-xs text-gray-600 uppercase">Active</p>
                <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['active']); ?></p>
            </div>
            <div class="p-4 bg-white border-l-4 border-red-500 shadow-md rounded-xl">
                <p class="mb-1 text-xs text-gray-600 uppercase">Inactive</p>
                <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['inactive']); ?></p>
            </div>
            <div class="p-4 bg-white border-l-4 border-purple-500 shadow-md rounded-xl">
                <p class="mb-1 text-xs text-gray-600 uppercase">Organizers</p>
                <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['organizers']); ?></p>
            </div>
            <div class="p-4 bg-white border-l-4 border-yellow-500 shadow-md rounded-xl">
                <p class="mb-1 text-xs text-gray-600 uppercase">Managers</p>
                <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['managers']); ?></p>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="p-6 mb-6 bg-white shadow-md rounded-xl">
            <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div>
                    <label class="block mb-2 text-sm font-semibold text-gray-700">Search</label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                        placeholder="Name or email..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block mb-2 text-sm font-semibold text-gray-700">Role</label>
                    <select name="role" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Roles</option>
                        <option value="organizer" <?php echo $role_filter === 'organizer' ? 'selected' : ''; ?>>Organizer</option>
                        <option value="manager" <?php echo $role_filter === 'manager' ? 'selected' : ''; ?>>Manager</option>
                        <option value="administrator" <?php echo $role_filter === 'administrator' ? 'selected' : ''; ?>>Administrator</option>
                    </select>
                </div>
                <div>
                    <label class="block mb-2 text-sm font-semibold text-gray-700">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Status</option>
                        <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 px-6 py-2 text-white transition-colors bg-indigo-600 rounded-lg hover:bg-indigo-700">
                        <i class="mr-2 fas fa-search"></i>Filter
                    </button>
                    <a href="manage-users.php" class="px-4 py-2 text-gray-600 transition-colors bg-gray-200 rounded-lg hover:bg-gray-300">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Users Table -->
        <div class="overflow-hidden bg-white shadow-md rounded-xl">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-xs font-semibold tracking-wider text-left text-gray-700 uppercase">User</th>
                            <th class="px-6 py-3 text-xs font-semibold tracking-wider text-left text-gray-700 uppercase">Email</th>
                            <th class="px-6 py-3 text-xs font-semibold tracking-wider text-left text-gray-700 uppercase">Role</th>
                            <th class="px-6 py-3 text-xs font-semibold tracking-wider text-left text-gray-700 uppercase">Status</th>
                            <th class="px-6 py-3 text-xs font-semibold tracking-wider text-left text-gray-700 uppercase">Joined</th>
                            <th class="px-6 py-3 text-xs font-semibold tracking-wider text-center text-gray-700 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if ($users_result->num_rows > 0): ?>
                            <?php while ($user = $users_result->fetch_assoc()): ?>
                                <tr class="transition-colors hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex items-center justify-center w-10 h-10 text-white bg-indigo-500 rounded-full">
                                                <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-semibold text-gray-900">
                                                    <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php echo htmlspecialchars($user['email']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        $role_colors = [
                                            'administrator' => 'bg-red-100 text-red-800',
                                            'manager' => 'bg-yellow-100 text-yellow-800',
                                            'organizer' => 'bg-blue-100 text-blue-800'
                                        ];
                                        $color = $role_colors[$user['role']] ?? 'bg-gray-100 text-gray-800';
                                        ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $color; ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($user['status'] === 'active'): ?>
                                            <span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">
                                                <i class="mr-1 fas fa-check-circle"></i>Active
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">
                                                <i class="mr-1 fas fa-times-circle"></i>Inactive
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                        <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <div class="flex justify-center gap-2">
                                            <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                                <?php if ($user['status'] === 'active'): ?>
                                                    <form method="POST" class="inline" onsubmit="return confirm('Deactivate this user?');">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                        <input type="hidden" name="action" value="deactivate">
                                                        <button type="submit" class="px-3 py-1 text-xs text-white transition-colors bg-orange-500 rounded hover:bg-orange-600">
                                                            <i class="fas fa-ban"></i>
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <form method="POST" class="inline">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                        <input type="hidden" name="action" value="activate">
                                                        <button type="submit" class="px-3 py-1 text-xs text-white transition-colors bg-green-500 rounded hover:bg-green-600">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                <form method="POST" class="inline" onsubmit="return confirm('Delete this user permanently?');">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <button type="submit" class="px-3 py-1 text-xs text-white transition-colors bg-red-500 rounded hover:bg-red-600">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="px-3 py-1 text-xs text-gray-500 bg-gray-200 rounded">You</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    <i class="mb-2 text-4xl fas fa-users"></i>
                                    <p>No users found</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php include '../../../src/components/Footer.php'; ?>

    <script>
        // Profile dropdown toggle
        document.getElementById('profile-dropdown-btn')?.addEventListener('click', function(e) {
            e.stopPropagation();
            document.getElementById('profile-dropdown').classList.toggle('hidden');
        });

        document.addEventListener('click', function() {
            document.getElementById('profile-dropdown')?.classList.add('hidden');
        });
    </script>
</body>

</html>
<?php $conn->close(); ?>