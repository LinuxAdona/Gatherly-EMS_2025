<?php
session_start();

// Check if user is logged in and is an administrator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'administrator') {
    header("Location: ../signin.php");
    exit();
}

require_once '../../../src/services/dbconnect.php';

$first_name = $_SESSION['first_name'] ?? 'Admin';

// Handle venue actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $venue_id = $_POST['venue_id'] ?? null;
    $action = $_POST['action'];

    if ($venue_id) {
        switch ($action) {
            case 'activate':
                $conn->query("UPDATE venues SET status = 'active' WHERE venue_id = $venue_id");
                break;
            case 'deactivate':
                $conn->query("UPDATE venues SET status = 'inactive' WHERE venue_id = $venue_id");
                break;
            case 'delete':
                $conn->query("DELETE FROM venues WHERE venue_id = $venue_id");
                break;
        }
    }
    header("Location: manage-venues.php");
    exit();
}

// Fetch venues with filters
$location_filter = $_GET['location'] ?? '';
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

$query = "SELECT v.*, m.first_name, m.last_name FROM venues v 
          LEFT JOIN users m ON v.manager_id = m.user_id 
          WHERE 1=1";
if ($location_filter) {
    $query .= " AND v.location LIKE '%" . $conn->real_escape_string($location_filter) . "%'";
}
if ($status_filter) {
    $query .= " AND v.status = '" . $conn->real_escape_string($status_filter) . "'";
}
if ($search) {
    $search_term = $conn->real_escape_string($search);
    $query .= " AND (v.venue_name LIKE '%$search_term%' OR v.location LIKE '%$search_term%')";
}
$query .= " ORDER BY v.created_at DESC";

$venues_result = $conn->query($query);

// Get statistics
$stats = [];
$stats['total'] = $conn->query("SELECT COUNT(*) as count FROM venues")->fetch_assoc()['count'];
$stats['active'] = $conn->query("SELECT COUNT(*) as count FROM venues WHERE status = 'active'")->fetch_assoc()['count'];
$stats['inactive'] = $conn->query("SELECT COUNT(*) as count FROM venues WHERE status = 'inactive'")->fetch_assoc()['count'];
$stats['total_capacity'] = $conn->query("SELECT SUM(capacity) as total FROM venues")->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Venues | Gatherly</title>
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

<body class="bg-linear-to-br from-slate-50 via-white to-blue-50 font-['Montserrat'] flex flex-col min-h-screen">
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
                    <a href="admin-dashboard.php"
                        class="text-gray-700 transition-colors hover:text-indigo-600">Dashboard</a>
                    <a href="manage-users.php" class="text-gray-700 transition-colors hover:text-indigo-600">Users</a>
                    <a href="manage-venues.php"
                        class="font-semibold text-indigo-600 transition-colors hover:text-indigo-700">Venues</a>
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
                            <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50">Profile</a>
                            <a href="settings.php" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50">Settings</a>
                            <a href="../../../src/services/signout-handler.php"
                                class="block px-4 py-2 text-red-600 hover:bg-red-50">Sign Out</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container px-4 py-8 mx-auto sm:px-6 lg:px-8 grow">
        <!-- Header and Statistics -->
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6 mb-8">
            <!-- Header -->
            <div class="lg:shrink-0">
                <h1 class="mb-2 text-3xl font-bold text-gray-800 sm:text-4xl">
                    <i class="mr-2 text-green-600 fas fa-map-marked-alt"></i>
                    Venue Management
                </h1>
                <p class="text-gray-600">Manage event venues and their availability</p>
            </div>

            <!-- Statistics Cards -->
            <div class="p-4 bg-white shadow-sm rounded-lg lg:shrink-0">
                <div class="flex items-center gap-6">
                    <div class="flex items-center gap-2 relative group cursor-help">
                        <i class="fas fa-building text-blue-500"></i>
                        <span class="text-xl font-bold text-gray-800"><?php echo number_format($stats['total']); ?></span>
                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-1 bg-gray-900 text-white text-xs rounded opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 whitespace-nowrap">
                            Total Venues
                        </div>
                    </div>
                    <div class="w-px h-8 bg-gray-200"></div>
                    <div class="flex items-center gap-2 relative group cursor-help">
                        <i class="fas fa-check-circle text-green-500"></i>
                        <span class="text-xl font-bold text-gray-800"><?php echo number_format($stats['active']); ?></span>
                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-1 bg-gray-900 text-white text-xs rounded opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 whitespace-nowrap">
                            Active Venues
                        </div>
                    </div>
                    <div class="w-px h-8 bg-gray-200"></div>
                    <div class="flex items-center gap-2 relative group cursor-help">
                        <i class="fas fa-times-circle text-red-500"></i>
                        <span class="text-xl font-bold text-gray-800"><?php echo number_format($stats['inactive']); ?></span>
                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-1 bg-gray-900 text-white text-xs rounded opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 whitespace-nowrap">
                            Inactive Venues
                        </div>
                    </div>
                    <div class="w-px h-8 bg-gray-200"></div>
                    <div class="flex items-center gap-2 relative group cursor-help">
                        <i class="fas fa-users text-purple-500"></i>
                        <span class="text-xl font-bold text-gray-800"><?php echo number_format($stats['total_capacity']); ?></span>
                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-1 bg-gray-900 text-white text-xs rounded opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 whitespace-nowrap">
                            Total Capacity
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="p-6 mb-6 bg-white shadow-md rounded-xl">
            <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div>
                    <label class="block mb-2 text-sm font-semibold text-gray-700">Search</label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                        placeholder="Venue name or location..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block mb-2 text-sm font-semibold text-gray-700">Location</label>
                    <input type="text" name="location" value="<?php echo htmlspecialchars($location_filter); ?>"
                        placeholder="Filter by location..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block mb-2 text-sm font-semibold text-gray-700">Status</label>
                    <select name="status"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Status</option>
                        <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active
                        </option>
                        <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive
                        </option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit"
                        class="flex-1 px-6 py-2 text-white transition-colors bg-indigo-600 rounded-lg hover:bg-indigo-700">
                        <i class="mr-2 fas fa-search"></i>Filter
                    </button>
                    <a href="manage-venues.php"
                        class="px-4 py-2 text-gray-600 transition-colors bg-gray-200 rounded-lg hover:bg-gray-300">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Venues Grid -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            <?php if ($venues_result->num_rows > 0): ?>
                <?php while ($venue = $venues_result->fetch_assoc()): ?>
                    <div class="overflow-hidden transition-shadow bg-white shadow-md rounded-xl hover:shadow-xl">
                        <div class="h-48 bg-linear-to-br from-indigo-400 to-purple-500">
                            <div class="flex items-center justify-center h-full">
                                <i class="text-6xl text-white fas fa-building"></i>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-3">
                                <h3 class="text-xl font-bold text-gray-800">
                                    <?php echo htmlspecialchars($venue['venue_name']); ?></h3>
                                <?php if ($venue['status'] === 'active'): ?>
                                    <span
                                        class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Active</span>
                                <?php else: ?>
                                    <span
                                        class="px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">Inactive</span>
                                <?php endif; ?>
                            </div>
                            <div class="space-y-2 text-sm text-gray-600">
                                <p><i
                                        class="mr-2 fas fa-map-marker-alt text-indigo-600"></i><?php echo htmlspecialchars($venue['location']); ?>
                                </p>
                                <p><i class="mr-2 fas fa-users text-indigo-600"></i>Capacity:
                                    <?php echo number_format($venue['capacity']); ?></p>
                                <p><i
                                        class="mr-2 fas fa-dollar-sign text-indigo-600"></i>₱<?php echo number_format($venue['base_price'], 2); ?>
                                </p>
                                <?php if ($venue['first_name']): ?>
                                    <p><i class="mr-2 fas fa-user text-indigo-600"></i>Manager:
                                        <?php echo htmlspecialchars($venue['first_name'] . ' ' . $venue['last_name']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="flex gap-2 mt-4">
                                <?php if ($venue['status'] === 'active'): ?>
                                    <form method="POST" class="flex-1" onsubmit="return confirm('Deactivate this venue?');">
                                        <input type="hidden" name="venue_id" value="<?php echo $venue['venue_id']; ?>">
                                        <input type="hidden" name="action" value="deactivate">
                                        <button type="submit"
                                            class="w-full px-4 py-2 text-sm text-white transition-colors bg-orange-500 rounded-lg hover:bg-orange-600">
                                            <i class="mr-2 fas fa-ban"></i>Deactivate
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" class="flex-1">
                                        <input type="hidden" name="venue_id" value="<?php echo $venue['venue_id']; ?>">
                                        <input type="hidden" name="action" value="activate">
                                        <button type="submit"
                                            class="w-full px-4 py-2 text-sm text-white transition-colors bg-green-500 rounded-lg hover:bg-green-600">
                                            <i class="mr-2 fas fa-check"></i>Activate
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <form method="POST" class="flex-1" onsubmit="return confirm('Delete this venue permanently?');">
                                    <input type="hidden" name="venue_id" value="<?php echo $venue['venue_id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit"
                                        class="w-full px-4 py-2 text-sm text-white transition-colors bg-red-500 rounded-lg hover:bg-red-600">
                                        <i class="mr-2 fas fa-trash"></i>Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-3 py-12 text-center">
                    <i class="mb-4 text-6xl text-gray-400 fas fa-building"></i>
                    <p class="text-xl text-gray-500">No venues found</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../../../src/components/Footer.php'; ?>

    <script>
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