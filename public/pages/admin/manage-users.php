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
$sort_filter = $_GET['sort'] ?? 'latest';
$search = $_GET['search'] ?? '';

$query = "SELECT * FROM users WHERE 1=1";
if ($search) {
    $search_term = $conn->real_escape_string($search);
    $query .= " AND (first_name LIKE '%$search_term%' OR last_name LIKE '%$search_term%' OR email LIKE '%$search_term%')";
}
if ($role_filter) {
    $query .= " AND role = '" . $conn->real_escape_string($role_filter) . "'";
}
if ($status_filter) {
    $query .= " AND status = '" . $conn->real_escape_string($status_filter) . "'";
}
if ($sort_filter === 'oldest') {
    $query .= " ORDER BY created_at ASC";
} else {
    $query .= " ORDER BY created_at DESC";
}

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
    <link rel="stylesheet"
        href="../../../src/output.css?v=<?php echo filemtime(__DIR__ . '/../../../src/output.css'); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Almarai:wght@300;400;700;800&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="<?php
                $nav_layout = $_SESSION['nav_layout'] ?? 'sidebar';
                echo $nav_layout === 'sidebar' ? 'bg-gray-100' : 'bg-linear-to-br from-slate-50 via-white to-blue-50';
                ?> font-['Montserrat']">
    <?php include '../../../src/components/AdminSidebar.php'; ?>

    <!-- Main Content -->
    <div
        class="<?php echo $nav_layout === 'sidebar' ? 'lg:ml-64' : 'container mx-auto'; ?> <?php echo $nav_layout === 'sidebar' ? '' : 'px-4 sm:px-6 lg:px-8'; ?> min-h-screen">
        <?php if ($nav_layout === 'sidebar'): ?>
            <!-- Top Bar for Sidebar Layout -->
            <div class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-20 px-4 sm:px-6 lg:px-8 py-4 mb-8">
                <h1 class="text-2xl font-bold text-gray-800">
                    <!-- <i class="mr-2 text-indigo-600 fas fa-users-cog"></i> -->
                    User Management
                </h1>
                <p class="text-sm text-gray-600">Manage system users and their access permissions</p>
            </div>
            <div class="px-4 sm:px-6 lg:px-8">
            <?php else: ?>
                <!-- Header for Navbar Layout -->
                <div class="mb-8">
                    <h1 class="mb-2 text-3xl font-bold text-gray-800 sm:text-4xl">
                        <!-- <i class="mr-2 text-indigo-600 fas fa-users-cog"></i> -->
                        User Management
                    </h1>
                    <p class="text-gray-600">Manage system users and their access permissions</p>
                </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 md:gap-4 mb-6 md:mb-8">
                <div class="p-3 md:p-4 bg-white shadow-sm rounded-lg border border-gray-200">
                    <div class="flex flex-col">
                        <div class="flex items-center gap-2 mb-1 md:mb-2">
                            <i class="fas fa-users text-blue-500 text-sm md:text-base"></i>
                            <span
                                class="text-lg md:text-xl font-bold text-gray-800"><?php echo number_format($stats['total']); ?></span>
                        </div>
                        <p class="text-xs text-gray-600">Total Users</p>
                    </div>
                </div>
                <div class="p-3 md:p-4 bg-white shadow-sm rounded-lg border border-gray-200">
                    <div class="flex flex-col">
                        <div class="flex items-center gap-2 mb-1 md:mb-2">
                            <i class="fas fa-check-circle text-green-500 text-sm md:text-base"></i>
                            <span
                                class="text-lg md:text-xl font-bold text-gray-800"><?php echo number_format($stats['active']); ?></span>
                        </div>
                        <p class="text-xs text-gray-600">Active</p>
                    </div>
                </div>
                <div class="p-3 md:p-4 bg-white shadow-sm rounded-lg border border-gray-200">
                    <div class="flex flex-col">
                        <div class="flex items-center gap-2 mb-1 md:mb-2">
                            <i class="fas fa-times-circle text-red-500 text-sm md:text-base"></i>
                            <span
                                class="text-lg md:text-xl font-bold text-gray-800"><?php echo number_format($stats['inactive']); ?></span>
                        </div>
                        <p class="text-xs text-gray-600">Inactive</p>
                    </div>
                </div>
                <div class="p-3 md:p-4 bg-white shadow-sm rounded-lg border border-gray-200">
                    <div class="flex flex-col">
                        <div class="flex items-center gap-2 mb-1 md:mb-2">
                            <i class="fas fa-calendar-alt text-purple-500 text-sm md:text-base"></i>
                            <span
                                class="text-lg md:text-xl font-bold text-gray-800"><?php echo number_format($stats['organizers']); ?></span>
                        </div>
                        <p class="text-xs text-gray-600">Organizers</p>
                    </div>
                </div>
                <div class="p-3 md:p-4 bg-white shadow-sm rounded-lg border border-gray-200">
                    <div class="flex flex-col">
                        <div class="flex items-center gap-2 mb-1 md:mb-2">
                            <i class="fas fa-building text-yellow-500 text-sm md:text-base"></i>
                            <span
                                class="text-lg md:text-xl font-bold text-gray-800"><?php echo number_format($stats['managers']); ?></span>
                        </div>
                        <p class="text-xs text-gray-600">Managers</p>
                    </div>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="p-4 md:p-6 mb-6 bg-white shadow-md rounded-xl">
                <form method="GET" class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Search</label>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                            placeholder="Name or email..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                        <div>
                            <label class="block mb-2 text-sm font-semibold text-gray-700">Role</label>
                            <select name="role"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">All Roles</option>
                                <option value="organizer" <?php echo $role_filter === 'organizer' ? 'selected' : ''; ?>>
                                    Organizer</option>
                                <option value="manager" <?php echo $role_filter === 'manager' ? 'selected' : ''; ?>>
                                    Manager
                                </option>
                                <option value="administrator"
                                    <?php echo $role_filter === 'administrator' ? 'selected' : ''; ?>>
                                    Administrator</option>
                            </select>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-semibold text-gray-700">Status</label>
                            <select name="status"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">All Status</option>
                                <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>
                                    Active
                                </option>
                                <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>
                                    Inactive
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-semibold text-gray-700">Sort</label>
                            <select name="sort"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="latest" <?php echo $sort_filter === 'latest' ? 'selected' : ''; ?>>Latest
                                </option>
                                <option value="oldest" <?php echo $sort_filter === 'oldest' ? 'selected' : ''; ?>>Oldest
                                </option>
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit"
                                class="cursor-pointer flex-1 px-4 md:px-6 py-2 text-sm md:text-base text-white transition-colors bg-indigo-600 rounded-lg hover:bg-indigo-700">
                                <i class="mr-1 md:mr-2 fas fa-search"></i><span
                                    class="hidden sm:inline">Filter</span><span class="sm:hidden">Go</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Users Table -->
            <div class="overflow-hidden bg-white shadow-md rounded-xl mb-6">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-xs font-semibold tracking-wider text-left text-gray-700 uppercase">
                                    User</th>
                                <th
                                    class="px-6 py-3 text-xs font-semibold tracking-wider text-left text-gray-700 uppercase">
                                    Email</th>
                                <th
                                    class="px-6 py-3 text-xs font-semibold tracking-wider text-left text-gray-700 uppercase">
                                    Role</th>
                                <th
                                    class="px-6 py-3 text-xs font-semibold tracking-wider text-left text-gray-700 uppercase">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-xs font-semibold tracking-wider text-left text-gray-700 uppercase">
                                    Joined</th>
                                <th
                                    class="px-6 py-3 text-xs font-semibold tracking-wider text-center text-gray-700 uppercase">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if ($users_result->num_rows > 0): ?>
                                <?php while ($user = $users_result->fetch_assoc()): ?>
                                    <tr class="transition-colors hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div
                                                    class="flex items-center justify-center w-10 h-10 text-white bg-indigo-500 rounded-full">
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
                                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($user['email']); ?>
                                            </div>
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
                                                <span
                                                    class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">
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
                                                        <button
                                                            onclick="openDeactivateModal(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>')"
                                                            class="cursor-pointer px-3 py-1 text-xs text-white transition-colors bg-orange-500 rounded hover:bg-orange-600"
                                                            title="Deactivate User">
                                                            <i class="fas fa-ban"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <button
                                                            onclick="openActivateModal(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>')"
                                                            class="cursor-pointer px-3 py-1 text-xs text-white transition-colors bg-green-500 rounded hover:bg-green-600"
                                                            title="Activate User">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <button
                                                        onclick="openDeleteModal(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>')"
                                                        class="cursor-pointer px-3 py-1 text-xs text-white transition-colors bg-red-500 rounded hover:bg-red-600"
                                                        title="Delete User">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
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

            <!-- Activate User Modal -->
            <div id="activateModal" class="fixed inset-0 z-50 hidden"
                onclick="if(event.target === this) closeActivateModal()">
                <div class="absolute inset-0 bg-black opacity-50"></div>
                <div class="relative flex items-center justify-center min-h-screen p-4">
                    <div class="max-w-md p-6 mx-4 bg-white rounded-lg shadow-xl">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-bold text-gray-800">
                                <i class="mr-2 text-green-600 fas fa-check-circle"></i>
                                Activate User
                            </h3>
                            <button onclick="closeActivateModal()" class="text-gray-500 hover:text-gray-700">
                                <i class="text-xl fas fa-times"></i>
                            </button>
                        </div>
                        <p class="mb-6 text-gray-600">
                            Are you sure you want to activate <span id="activateUserName"
                                class="font-semibold text-gray-800"></span>?
                            This will restore their access to the system.
                        </p>
                        <form method="POST" class="flex justify-end gap-3">
                            <input type="hidden" name="user_id" id="activateUserId">
                            <input type="hidden" name="action" value="activate">
                            <button type="button" onclick="closeActivateModal()"
                                class="px-4 py-2 text-gray-700 transition-colors bg-gray-200 rounded-lg hover:bg-gray-300">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-white transition-colors bg-green-600 rounded-lg hover:bg-green-700">
                                <i class="mr-2 fas fa-check"></i>Activate
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Deactivate User Modal -->
            <div id="deactivateModal" class="fixed inset-0 z-50 hidden"
                onclick="if(event.target === this) closeDeactivateModal()">
                <div class="absolute inset-0 bg-black opacity-50"></div>
                <div class="relative flex items-center justify-center min-h-screen p-4">
                    <div class="max-w-md p-6 mx-4 bg-white rounded-lg shadow-xl">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-bold text-gray-800">
                                <i class="mr-2 text-orange-600 fas fa-ban"></i>
                                Deactivate User
                            </h3>
                            <button onclick="closeDeactivateModal()" class="text-gray-500 hover:text-gray-700">
                                <i class="text-xl fas fa-times"></i>
                            </button>
                        </div>
                        <p class="mb-6 text-gray-600">
                            Are you sure you want to deactivate <span id="deactivateUserName"
                                class="font-semibold text-gray-800"></span>?
                            They will lose access to the system but their data will be preserved.
                        </p>
                        <form method="POST" class="flex justify-end gap-3">
                            <input type="hidden" name="user_id" id="deactivateUserId">
                            <input type="hidden" name="action" value="deactivate">
                            <button type="button" onclick="closeDeactivateModal()"
                                class="px-4 py-2 text-gray-700 transition-colors bg-gray-200 rounded-lg hover:bg-gray-300">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-white transition-colors bg-orange-600 rounded-lg hover:bg-orange-700">
                                <i class="mr-2 fas fa-ban"></i>Deactivate
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Delete User Modal -->
            <div id="deleteModal" class="fixed inset-0 z-50 hidden" onclick="if(event.target === this) closeDeleteModal()">
                <div class="absolute inset-0 bg-black opacity-50"></div>
                <div class="relative flex items-center justify-center min-h-screen p-4">
                    <div class="max-w-md p-6 mx-4 bg-white rounded-lg shadow-xl">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-bold text-gray-800">
                                <i class="mr-2 text-red-600 fas fa-exclamation-triangle"></i>
                                Delete User
                            </h3>
                            <button onclick="closeDeleteModal()" class="text-gray-500 hover:text-gray-700">
                                <i class="text-xl fas fa-times"></i>
                            </button>
                        </div>
                        <div class="p-4 mb-4 border-l-4 border-red-500 bg-red-50">
                            <p class="font-semibold text-red-800">Warning: This action cannot be undone!</p>
                        </div>
                        <p class="mb-6 text-gray-600">
                            Are you sure you want to permanently delete <span id="deleteUserName"
                                class="font-semibold text-gray-800"></span>?
                            All their data and associated records will be removed from the system.
                        </p>
                        <form method="POST" class="flex justify-end gap-3">
                            <input type="hidden" name="user_id" id="deleteUserId">
                            <input type="hidden" name="action" value="delete">
                            <button type="button" onclick="closeDeleteModal()"
                                class="px-4 py-2 text-gray-700 transition-colors bg-gray-200 rounded-lg hover:bg-gray-300">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-white transition-colors bg-red-600 rounded-lg hover:bg-red-700">
                                <i class="mr-2 fas fa-trash"></i>Delete Permanently
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <?php if ($nav_layout === 'sidebar'): ?>
    </div> <!-- Close sidebar inner wrapper -->
<?php endif; ?>
</div> <!-- Close main content -->

<script>
    // Sidebar toggle for mobile
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('admin-sidebar');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('-translate-x-full');
        });
    }

    // Modal functions
    function openActivateModal(userId, userName) {
        document.getElementById('activateUserId').value = userId;
        document.getElementById('activateUserName').textContent = userName;
        const modal = document.getElementById('activateModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeActivateModal() {
        const modal = document.getElementById('activateModal');
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    function openDeactivateModal(userId, userName) {
        document.getElementById('deactivateUserId').value = userId;
        document.getElementById('deactivateUserName').textContent = userName;
        const modal = document.getElementById('deactivateModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeDeactivateModal() {
        const modal = document.getElementById('deactivateModal');
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    function openDeleteModal(userId, userName) {
        document.getElementById('deleteUserId').value = userId;
        document.getElementById('deleteUserName').textContent = userName;
        const modal = document.getElementById('deleteModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeDeleteModal() {
        const modal = document.getElementById('deleteModal');
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    // Close modals on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeActivateModal();
            closeDeactivateModal();
            closeDeleteModal();
        }
    });
</script>
</body>

</html>
<?php $conn->close(); ?>