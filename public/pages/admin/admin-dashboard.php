<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/opt/lampp/htdocs/Gatherly-EMS_2025/error.log');

session_start();

// Check if user is logged in and is an administrator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'administrator') {
    header("Location: ../signin.php");
    exit();
}

try {
    require_once '../../../src/services/dbconnect.php';
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

$first_name = $_SESSION['first_name'] ?? 'Admin';

// Fetch statistics with error handling
$stats = [
    'total_users' => 0,
    'total_venues' => 0,
    'total_events' => 0,
    'total_revenue' => 0
];

try {
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    if ($result) {
        $stats['total_users'] = $result->fetch_assoc()['count'];
    } else {
        error_log("Query failed - users count: " . $conn->error);
    }

    $result = $conn->query("SELECT COUNT(*) as count FROM venues");
    if ($result) {
        $stats['total_venues'] = $result->fetch_assoc()['count'];
    } else {
        error_log("Query failed - venues count: " . $conn->error);
    }

    $result = $conn->query("SELECT COUNT(*) as count FROM events");
    if ($result) {
        $stats['total_events'] = $result->fetch_assoc()['count'];
    } else {
        error_log("Query failed - events count: " . $conn->error);
    }

    $result = $conn->query("SELECT SUM(total_cost) as total FROM events WHERE status = 'completed'");
    if ($result) {
        $stats['total_revenue'] = $result->fetch_assoc()['total'] ?? 0;
    } else {
        error_log("Query failed - revenue sum: " . $conn->error);
    }
} catch (Exception $e) {
    error_log("Error fetching statistics: " . $e->getMessage());
    die("Error loading dashboard data. Check error.log for details.");
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Gatherly</title>
    <link rel="icon" type="image/x-icon" href="../../assets/images/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Almarai:wght@300;400;700;800&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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
                <h1 class="text-2xl font-bold text-gray-800">Administrator Dashboard</h1>
                <p class="text-sm text-gray-600">System overview and management tools</p>
            </div>
            <div class="px-4 sm:px-6 lg:px-8">
            <?php else: ?>
                <!-- Header for Navbar Layout -->
                <div class="mb-8">
                    <h1 class="mb-2 text-3xl font-bold text-gray-800 sm:text-4xl">Administrator Dashboard</h1>
                    <p class="text-gray-600">System overview and management tools</p>
                </div>
            <?php endif; ?>
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4 mb-6 md:mb-8">
                <div class="bg-white p-3 md:p-4 rounded-lg shadow-sm border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-600 mb-1">Total Users</p>
                            <p class="text-lg md:text-xl font-bold text-gray-900">
                                <?php echo number_format($stats['total_users']); ?></p>
                        </div>
                        <div class="p-2 md:p-3 bg-blue-100 rounded-lg">
                            <i class="fas fa-users text-lg md:text-xl text-blue-600"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-3 md:p-4 rounded-lg shadow-sm border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-600 mb-1">Total Venues</p>
                            <p class="text-lg md:text-xl font-bold text-gray-900">
                                <?php echo number_format($stats['total_venues']); ?></p>
                        </div>
                        <div class="p-2 md:p-3 bg-green-100 rounded-lg">
                            <i class="fas fa-building text-lg md:text-xl text-green-600"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-3 md:p-4 rounded-lg shadow-sm border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-600 mb-1">Total Events</p>
                            <p class="text-lg md:text-xl font-bold text-gray-900">
                                <?php echo number_format($stats['total_events']); ?></p>
                        </div>
                        <div class="p-2 md:p-3 bg-purple-100 rounded-lg">
                            <i class="fas fa-calendar text-lg md:text-xl text-purple-600"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-3 md:p-4 rounded-lg shadow-sm border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-600 mb-1">Total Revenue</p>
                            <p class="text-lg md:text-xl font-bold text-gray-900">
                                ₱<?php echo number_format($stats['total_revenue'], 2); ?></p>
                        </div>
                        <div class="p-2 md:p-3 bg-yellow-100 rounded-lg">
                            <i class="fas fa-money-bill-wave text-lg md:text-xl text-yellow-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue Analytics Chart -->
            <div class="p-6 mb-8 bg-white shadow-md rounded-xl">
                <div class="flex flex-col items-start justify-between mb-6 sm:flex-row sm:items-center">
                    <div>
                        <h2 class="mb-1 text-xl font-bold text-gray-800">
                            <i class="mr-2 text-yellow-600 fas fa-chart-line"></i>
                            Revenue Analytics
                        </h2>
                        <p class="text-sm text-gray-600">Track revenue performance over time</p>
                    </div>
                    <div class="flex flex-wrap gap-2 mt-4 sm:mt-0">
                        <select id="yearSelect"
                            class="px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Loading years...</option>
                        </select>
                        <select id="monthSelect"
                            class="px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">All Months</option>
                            <option value="1">January</option>
                            <option value="2">February</option>
                            <option value="3">March</option>
                            <option value="4">April</option>
                            <option value="5">May</option>
                            <option value="6">June</option>
                            <option value="7">July</option>
                            <option value="8">August</option>
                            <option value="9">September</option>
                            <option value="10">October</option>
                            <option value="11">November</option>
                            <option value="12">December</option>
                        </select>
                    </div>
                </div>

                <!-- Summary Stats -->
                <div class="grid grid-cols-1 gap-4 mb-6 sm:grid-cols-3">
                    <div class="p-4 border-l-4 border-yellow-500 rounded-lg bg-yellow-50">
                        <p class="mb-1 text-xs font-semibold text-gray-600 uppercase">Period</p>
                        <p id="periodLabel" class="text-lg font-bold text-gray-800">Loading...</p>
                    </div>
                    <div class="p-4 border-l-4 border-green-500 rounded-lg bg-green-50">
                        <p class="mb-1 text-xs font-semibold text-gray-600 uppercase">Total Revenue</p>
                        <p id="totalRevenue" class="text-lg font-bold text-gray-800">₱0.00</p>
                    </div>
                    <div class="p-4 border-l-4 border-blue-500 rounded-lg bg-blue-50">
                        <p class="mb-1 text-xs font-semibold text-gray-600 uppercase">Total Events</p>
                        <p id="totalEvents" class="text-lg font-bold text-gray-800">0</p>
                    </div>
                </div>

                <!-- Chart Container -->
                <div class="relative" style="height: 400px;">
                    <canvas id="revenueChart"></canvas>
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

            <?php if ($nav_layout === 'sidebar'): ?>
    </div> <!-- Close sidebar inner wrapper -->
<?php endif; ?>
</div> <!-- Close main content -->

<script src="../../assets/js/admin.js"></script>
</body>

</html>