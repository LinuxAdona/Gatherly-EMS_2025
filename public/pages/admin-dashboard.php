<?php
session_start();

// Check if user is logged in and is an administrator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'administrator') {
    header("Location: signin.php");
    exit();
}

require_once '../../src/services/dbconnect.php';

$first_name = $_SESSION['first_name'] ?? 'Admin';

// Fetch statistics
$stats = [
    'total_users' => 0,
    'total_venues' => 0,
    'total_events' => 0,
    'total_revenue' => 0
];

$result = $conn->query("SELECT COUNT(*) as count FROM users");
$stats['total_users'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM venues");
$stats['total_venues'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM events");
$stats['total_events'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT SUM(total_cost) as total FROM events WHERE status = 'completed'");
$stats['total_revenue'] = $result->fetch_assoc()['total'] ?? 0;

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Gatherly</title>
    <link rel="icon" type="image/x-icon" href="../assets/images/logo.png">
    <link rel="stylesheet" href="../../src/output.css?v=<?php echo filemtime(__DIR__ . '/../../src/output.css'); ?>">
    <script src="https://kit.fontawesome.com/2a99de0fa5.js" crossorigin="anonymous"></script>
</head>

<body class="bg-linear-to-br from-slate-50 via-white to-blue-50 font-['Montserrat']">
    <!-- Navbar -->
    <nav class="sticky top-0 z-50 bg-white shadow-md">
        <div class="container px-4 mx-auto sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-12 sm:h-16">
                <div class="flex items-center h-full">
                    <a href="home.php" class="flex items-center group">
                        <img class="w-8 h-8 mr-2 transition-transform sm:w-10 sm:h-10 group-hover:scale-110"
                            src="../assets/images/logo.png" alt="Gatherly Logo">
                        <span class="text-lg font-bold text-gray-800 sm:text-xl">Gatherly</span>
                    </a>
                </div>
                <div class="items-center hidden gap-6 md:flex">
                    <a href="admin-dashboard.php"
                        class="font-semibold text-indigo-600 transition-colors hover:text-indigo-700">Dashboard</a>
                    <a href="manage-users.php" class="text-gray-700 transition-colors hover:text-indigo-600">Users</a>
                    <a href="manage-venues.php" class="text-gray-700 transition-colors hover:text-indigo-600">Venues</a>
                    <a href="manage-events.php" class="text-gray-700 transition-colors hover:text-indigo-600">Events</a>
                    <a href="reports.php" class="text-gray-700 transition-colors hover:text-indigo-600">Reports</a>
                    <div class="relative">
                        <button id="profile-dropdown-btn"
                            class="flex items-center gap-2 text-gray-700 transition-colors hover:text-indigo-600">
                            <i class="text-2xl fas fa-user-shield"></i>
                            <span><?php echo htmlspecialchars($first_name); ?></span>
                            <i class="text-xs fas fa-chevron-down"></i>
                        </button>
                        <div id="profile-dropdown"
                            class="absolute right-0 hidden w-48 py-2 mt-2 bg-white rounded-lg shadow-lg">
                            <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50">Profile</a>
                            <a href="settings.php" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50">Settings</a>
                            <a href="../../src/services/signout-handler.php"
                                class="block px-4 py-2 text-red-600 hover:bg-red-50">Sign Out</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container px-4 py-8 mx-auto sm:px-6 lg:px-8">
        <!-- Welcome Section -->
        <div class="mb-8">
            <h1 class="mb-2 text-3xl font-bold text-gray-800 sm:text-4xl">Administrator Dashboard</h1>
            <p class="text-gray-600">System overview and management tools</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 gap-4 mb-8 sm:grid-cols-2 lg:grid-cols-4">
            <div class="p-6 bg-white border-l-4 border-blue-500 shadow-md rounded-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="mb-1 text-sm text-gray-600">Total Users</p>
                        <h3 class="text-3xl font-bold text-gray-800"><?php echo number_format($stats['total_users']); ?>
                        </h3>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-blue-100 rounded-lg">
                        <i class="text-2xl text-blue-600 fas fa-users"></i>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-white border-l-4 border-green-500 shadow-md rounded-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="mb-1 text-sm text-gray-600">Total Venues</p>
                        <h3 class="text-3xl font-bold text-gray-800">
                            <?php echo number_format($stats['total_venues']); ?></h3>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-green-100 rounded-lg">
                        <i class="text-2xl text-green-600 fas fa-building"></i>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-white border-l-4 border-purple-500 shadow-md rounded-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="mb-1 text-sm text-gray-600">Total Events</p>
                        <h3 class="text-3xl font-bold text-gray-800">
                            <?php echo number_format($stats['total_events']); ?></h3>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-purple-100 rounded-lg">
                        <i class="text-2xl text-purple-600 fas fa-calendar"></i>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-white border-l-4 border-yellow-500 shadow-md rounded-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="mb-1 text-sm text-gray-600">Total Revenue</p>
                        <h3 class="text-3xl font-bold text-gray-800">
                            ₱<?php echo number_format($stats['total_revenue'], 2); ?></h3>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-yellow-100 rounded-lg">
                        <i class="text-2xl text-yellow-600 fas fa-peso-sign"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Management Tools -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="p-6 bg-white shadow-md rounded-xl">
                <h2 class="mb-4 text-xl font-bold text-gray-800">
                    <i class="mr-2 text-indigo-600 fas fa-tools"></i>
                    Management Tools
                </h2>
                <div class="space-y-3">
                    <a href="manage-users.php"
                        class="flex items-center justify-between p-4 transition-all border border-gray-200 rounded-lg hover:border-indigo-200 hover:bg-indigo-50">
                        <div class="flex items-center gap-3">
                            <i class="text-xl text-indigo-600 fas fa-users-cog"></i>
                            <span class="font-semibold text-gray-700">Manage Users</span>
                        </div>
                        <i class="text-gray-400 fas fa-arrow-right"></i>
                    </a>
                    <a href="manage-venues.php"
                        class="flex items-center justify-between p-4 transition-all border border-gray-200 rounded-lg hover:border-green-200 hover:bg-green-50">
                        <div class="flex items-center gap-3">
                            <i class="text-xl text-green-600 fas fa-map-marked-alt"></i>
                            <span class="font-semibold text-gray-700">Manage Venues</span>
                        </div>
                        <i class="text-gray-400 fas fa-arrow-right"></i>
                    </a>
                    <a href="manage-events.php"
                        class="flex items-center justify-between p-4 transition-all border border-gray-200 rounded-lg hover:border-purple-200 hover:bg-purple-50">
                        <div class="flex items-center gap-3">
                            <i class="text-xl text-purple-600 fas fa-calendar-check"></i>
                            <span class="font-semibold text-gray-700">Manage Events</span>
                        </div>
                        <i class="text-gray-400 fas fa-arrow-right"></i>
                    </a>
                    <a href="system-settings.php"
                        class="flex items-center justify-between p-4 transition-all border border-gray-200 rounded-lg hover:border-gray-300 hover:bg-gray-50">
                        <div class="flex items-center gap-3">
                            <i class="text-xl text-gray-600 fas fa-cog"></i>
                            <span class="font-semibold text-gray-700">System Settings</span>
                        </div>
                        <i class="text-gray-400 fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="p-6 bg-white shadow-md rounded-xl">
                <h2 class="mb-4 text-xl font-bold text-gray-800">
                    <i class="mr-2 text-green-600 fas fa-chart-line"></i>
                    Quick Reports
                </h2>
                <div class="space-y-3">
                    <a href="reports.php?type=revenue"
                        class="flex items-center justify-between p-4 transition-all border border-gray-200 rounded-lg hover:border-green-200 hover:bg-green-50">
                        <div class="flex items-center gap-3">
                            <i class="text-xl text-green-600 fas fa-money-bill-wave"></i>
                            <span class="font-semibold text-gray-700">Revenue Reports</span>
                        </div>
                        <i class="text-gray-400 fas fa-arrow-right"></i>
                    </a>
                    <a href="reports.php?type=bookings"
                        class="flex items-center justify-between p-4 transition-all border border-gray-200 rounded-lg hover:border-blue-200 hover:bg-blue-50">
                        <div class="flex items-center gap-3">
                            <i class="text-xl text-blue-600 fas fa-chart-bar"></i>
                            <span class="font-semibold text-gray-700">Booking Analytics</span>
                        </div>
                        <i class="text-gray-400 fas fa-arrow-right"></i>
                    </a>
                    <a href="reports.php?type=venues"
                        class="flex items-center justify-between p-4 transition-all border border-gray-200 rounded-lg hover:border-purple-200 hover:bg-purple-50">
                        <div class="flex items-center gap-3">
                            <i class="text-xl text-purple-600 fas fa-chart-pie"></i>
                            <span class="font-semibold text-gray-700">Venue Performance</span>
                        </div>
                        <i class="text-gray-400 fas fa-arrow-right"></i>
                    </a>
                    <a href="reports.php?type=users"
                        class="flex items-center justify-between p-4 transition-all border border-gray-200 rounded-lg hover:border-orange-200 hover:bg-orange-50">
                        <div class="flex items-center gap-3">
                            <i class="text-xl text-orange-600 fas fa-person-running"></i>
                            <span class="font-semibold text-gray-700">User Activity</span>
                        </div>
                        <i class="text-gray-400 fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../src/components/Footer.php'; ?>

    <script>
        // Profile dropdown toggle
        const profileBtn = document.getElementById('profile-dropdown-btn');
        const profileDropdown = document.getElementById('profile-dropdown');

        profileBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            profileDropdown.classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
                profileDropdown.classList.add('hidden');
            }
        });
    </script>
</body>

</html>