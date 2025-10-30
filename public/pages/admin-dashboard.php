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

<body class="bg-gradient-to-br from-slate-50 via-white to-blue-50 font-['Montserrat']">
    <!-- Navbar -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-20">
                <div class="flex items-center gap-3">
                    <img src="../assets/images/logo.png" alt="Gatherly Logo" class="w-10 h-10 sm:w-12 sm:h-12">
                    <span class="text-xl sm:text-2xl font-bold text-gray-800">Gatherly Admin</span>
                </div>
                <div class="hidden md:flex items-center gap-6">
                    <a href="admin-dashboard.php" class="text-indigo-600 font-semibold hover:text-indigo-700 transition-colors">Dashboard</a>
                    <a href="manage-users.php" class="text-gray-700 hover:text-indigo-600 transition-colors">Users</a>
                    <a href="manage-venues.php" class="text-gray-700 hover:text-indigo-600 transition-colors">Venues</a>
                    <a href="manage-events.php" class="text-gray-700 hover:text-indigo-600 transition-colors">Events</a>
                    <a href="reports.php" class="text-gray-700 hover:text-indigo-600 transition-colors">Reports</a>
                    <div class="relative group">
                        <button class="flex items-center gap-2 text-gray-700 hover:text-indigo-600 transition-colors">
                            <i class="fas fa-user-shield text-2xl"></i>
                            <span><?php echo htmlspecialchars($first_name); ?></span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 hidden group-hover:block">
                            <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50">Profile</a>
                            <a href="settings.php" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50">Settings</a>
                            <a href="../../src/services/signout-handler.php" class="block px-4 py-2 text-red-600 hover:bg-red-50">Sign Out</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Welcome Section -->
        <div class="mb-8">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-800 mb-2">Administrator Dashboard</h1>
            <p class="text-gray-600">System overview and management tools</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Total Users</p>
                        <h3 class="text-3xl font-bold text-gray-800"><?php echo number_format($stats['total_users']); ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-2xl text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Total Venues</p>
                        <h3 class="text-3xl font-bold text-gray-800"><?php echo number_format($stats['total_venues']); ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-building text-2xl text-green-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Total Events</p>
                        <h3 class="text-3xl font-bold text-gray-800"><?php echo number_format($stats['total_events']); ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar text-2xl text-purple-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Total Revenue</p>
                        <h3 class="text-3xl font-bold text-gray-800">₱<?php echo number_format($stats['total_revenue'], 2); ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-peso-sign text-2xl text-yellow-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Management Tools -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-tools text-indigo-600 mr-2"></i>
                    Management Tools
                </h2>
                <div class="space-y-3">
                    <a href="manage-users.php" class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:border-indigo-200 hover:bg-indigo-50 transition-all">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-users-cog text-xl text-indigo-600"></i>
                            <span class="font-semibold text-gray-700">Manage Users</span>
                        </div>
                        <i class="fas fa-arrow-right text-gray-400"></i>
                    </a>
                    <a href="manage-venues.php" class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:border-green-200 hover:bg-green-50 transition-all">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-map-marked-alt text-xl text-green-600"></i>
                            <span class="font-semibold text-gray-700">Manage Venues</span>
                        </div>
                        <i class="fas fa-arrow-right text-gray-400"></i>
                    </a>
                    <a href="manage-events.php" class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:border-purple-200 hover:bg-purple-50 transition-all">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-calendar-check text-xl text-purple-600"></i>
                            <span class="font-semibold text-gray-700">Manage Events</span>
                        </div>
                        <i class="fas fa-arrow-right text-gray-400"></i>
                    </a>
                    <a href="system-settings.php" class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:border-gray-300 hover:bg-gray-50 transition-all">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-cog text-xl text-gray-600"></i>
                            <span class="font-semibold text-gray-700">System Settings</span>
                        </div>
                        <i class="fas fa-arrow-right text-gray-400"></i>
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-chart-line text-green-600 mr-2"></i>
                    Quick Reports
                </h2>
                <div class="space-y-3">
                    <a href="reports.php?type=revenue" class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:border-green-200 hover:bg-green-50 transition-all">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-money-bill-wave text-xl text-green-600"></i>
                            <span class="font-semibold text-gray-700">Revenue Reports</span>
                        </div>
                        <i class="fas fa-arrow-right text-gray-400"></i>
                    </a>
                    <a href="reports.php?type=bookings" class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:border-blue-200 hover:bg-blue-50 transition-all">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-chart-bar text-xl text-blue-600"></i>
                            <span class="font-semibold text-gray-700">Booking Analytics</span>
                        </div>
                        <i class="fas fa-arrow-right text-gray-400"></i>
                    </a>
                    <a href="reports.php?type=venues" class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:border-purple-200 hover:bg-purple-50 transition-all">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-chart-pie text-xl text-purple-600"></i>
                            <span class="font-semibold text-gray-700">Venue Performance</span>
                        </div>
                        <i class="fas fa-arrow-right text-gray-400"></i>
                    </a>
                    <a href="reports.php?type=users" class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:border-orange-200 hover:bg-orange-50 transition-all">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-user-chart text-xl text-orange-600"></i>
                            <span class="font-semibold text-gray-700">User Activity</span>
                        </div>
                        <i class="fas fa-arrow-right text-gray-400"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../src/components/Footer.php'; ?>
</body>

</html>