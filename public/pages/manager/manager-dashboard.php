<?php
session_start();

// Check if user is logged in and is a manager (venue owner)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: ../signin.php");
    exit();
}

require_once '../../../src/services/dbconnect.php';

$first_name = $_SESSION['first_name'] ?? 'Manager';
$user_id = $_SESSION['user_id'];

// Fetch manager's statistics
$stats = [
    'total_venues' => 0,
    'total_bookings' => 0,
    'pending_bookings' => 0,
    'total_revenue' => 0
];

// Get total venues (assuming managers can own/manage venues)
// Note: You might need to add a manager_id field to venues table
$result = $conn->query("SELECT COUNT(*) as count FROM venues");
$stats['total_venues'] = $result->fetch_assoc()['count'];

// Get total bookings/events
$result = $conn->query("SELECT COUNT(*) as count FROM events WHERE status IN ('confirmed', 'completed')");
$stats['total_bookings'] = $result->fetch_assoc()['count'];

// Get pending bookings
$result = $conn->query("SELECT COUNT(*) as count FROM events WHERE status = 'pending'");
$stats['pending_bookings'] = $result->fetch_assoc()['count'];

// Get total revenue
$result = $conn->query("SELECT SUM(total_cost) as total FROM events WHERE status IN ('confirmed', 'completed')");
$stats['total_revenue'] = $result->fetch_assoc()['total'] ?? 0;

// Get recent bookings
$recent_bookings_query = "SELECT e.event_id, e.event_name, e.event_date, e.status, e.total_cost, 
                          v.venue_name, u.first_name, u.last_name 
                          FROM events e 
                          LEFT JOIN venues v ON e.venue_id = v.venue_id 
                          LEFT JOIN users u ON e.client_id = u.user_id 
                          ORDER BY e.created_at DESC 
                          LIMIT 5";
$recent_bookings = $conn->query($recent_bookings_query);

// Get venue performance data
$venue_performance_query = "SELECT v.venue_name, COUNT(e.event_id) as booking_count, 
                            SUM(e.total_cost) as revenue 
                            FROM venues v 
                            LEFT JOIN events e ON v.venue_id = e.venue_id 
                            WHERE e.status IN ('confirmed', 'completed')
                            GROUP BY v.venue_id 
                            ORDER BY booking_count DESC 
                            LIMIT 5";
$venue_performance = $conn->query($venue_performance_query);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venue Manager Dashboard | Gatherly</title>
    <link rel="icon" type="image/x-icon" href="../../assets/images/logo.png">
    <link rel="stylesheet"
        href="../../../src/output.css?v=<?php echo filemtime(__DIR__ . '/../../../src/output.css'); ?>">
    <script src="https://kit.fontawesome.com/2a99de0fa5.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>

<body class="bg-linear-to-br from-green-50 via-white to-teal-50 font-['Montserrat']">
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
                    <a href="manager-dashboard.php"
                        class="font-semibold text-green-600 transition-colors hover:text-green-700">Dashboard</a>
                    <a href="my-venues.php" class="text-gray-700 transition-colors hover:text-green-600">My Venues</a>
                    <a href="bookings.php" class="text-gray-700 transition-colors hover:text-green-600">Bookings</a>
                    <a href="pricing.php" class="text-gray-700 transition-colors hover:text-green-600">Pricing</a>
                    <a href="analytics.php" class="text-gray-700 transition-colors hover:text-green-600">Analytics</a>
                    <div class="relative">
                        <button id="profile-dropdown-btn"
                            class="flex items-center gap-2 text-gray-700 transition-colors hover:text-green-600">
                            <i class="text-2xl fas fa-user-tie"></i>
                            <span><?php echo htmlspecialchars($first_name); ?></span>
                            <i class="text-xs fas fa-chevron-down"></i>
                        </button>
                        <div id="profile-dropdown"
                            class="absolute right-0 hidden w-48 py-2 mt-2 bg-white rounded-lg shadow-lg">
                            <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-green-50">Profile</a>
                            <a href="settings.php" class="block px-4 py-2 text-gray-700 hover:bg-green-50">Settings</a>
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
        <!-- Welcome Section -->
        <div class="mb-8">
            <h1 class="mb-2 text-3xl font-bold text-gray-800 sm:text-4xl">Welcome back,
                <?php echo htmlspecialchars($first_name); ?>! 🏢</h1>
            <p class="text-gray-600">Manage your venues and optimize your business</p>
        </div>

        <!-- Dynamic Pricing Tool Highlight Banner -->
        <div class="p-6 mb-8 border-2 border-green-300 shadow-lg bg-linear-to-r from-green-100 to-teal-100 rounded-xl">
            <div class="flex flex-col items-start justify-between lg:flex-row lg:items-center">
                <div class="mb-4 lg:mb-0">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="flex items-center justify-center w-12 h-12 bg-green-600 rounded-full">
                            <i class="text-2xl text-white fas fa-chart-line"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-green-900">Dynamic Pricing & Analytics</h2>
                    </div>
                    <p class="text-gray-700">Optimize your venue pricing with AI-powered demand forecasting and
                        competitive analysis!</p>
                    <div class="flex flex-wrap gap-2 mt-3">
                        <span class="px-3 py-1 text-xs font-semibold text-green-700 bg-green-200 rounded-full">
                            <i class="mr-1 fas fa-brain"></i> Smart Pricing
                        </span>
                        <span class="px-3 py-1 text-xs font-semibold text-teal-700 bg-teal-200 rounded-full">
                            <i class="mr-1 fas fa-calendar-alt"></i> Demand Forecasting
                        </span>
                        <span class="px-3 py-1 text-xs font-semibold text-blue-700 bg-blue-200 rounded-full">
                            <i class="mr-1 fas fa-chart-bar"></i> Revenue Optimization
                        </span>
                    </div>
                </div>
                <button id="openPricingTool"
                    class="px-6 py-3 font-semibold text-white transition-all transform bg-green-600 shadow-lg rounded-xl hover:bg-green-700 hover:scale-105 hover:shadow-xl">
                    <i class="mr-2 fas fa-dollar-sign"></i>
                    Optimize Pricing
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 gap-4 mb-8 sm:grid-cols-2 lg:grid-cols-4">
            <div class="p-6 bg-white border-l-4 border-green-500 shadow-md rounded-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="mb-1 text-sm text-gray-600">My Venues</p>
                        <h3 class="text-3xl font-bold text-gray-800">
                            <?php echo number_format($stats['total_venues']); ?></h3>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-green-100 rounded-lg">
                        <i class="text-2xl text-green-600 fas fa-building"></i>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-white border-l-4 border-blue-500 shadow-md rounded-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="mb-1 text-sm text-gray-600">Total Bookings</p>
                        <h3 class="text-3xl font-bold text-gray-800">
                            <?php echo number_format($stats['total_bookings']); ?></h3>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-blue-100 rounded-lg">
                        <i class="text-2xl text-blue-600 fas fa-calendar-check"></i>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-white border-l-4 border-yellow-500 shadow-md rounded-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="mb-1 text-sm text-gray-600">Pending</p>
                        <h3 class="text-3xl font-bold text-gray-800">
                            <?php echo number_format($stats['pending_bookings']); ?></h3>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-yellow-100 rounded-lg">
                        <i class="text-2xl text-yellow-600 fas fa-clock"></i>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-white border-l-4 border-purple-500 shadow-md rounded-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="mb-1 text-sm text-gray-600">Total Revenue</p>
                        <h3 class="text-3xl font-bold text-gray-800">
                            ₱<?php echo number_format($stats['total_revenue'], 2); ?></h3>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-purple-100 rounded-lg">
                        <i class="text-2xl text-purple-600 fas fa-peso-sign"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Recent Bookings -->
        <div class="grid grid-cols-1 gap-6 mb-8 lg:grid-cols-2">
            <!-- Quick Actions -->
            <div class="p-6 bg-white shadow-md rounded-xl">
                <h2 class="mb-4 text-xl font-bold text-gray-800">
                    <i class="mr-2 text-green-600 fas fa-bolt"></i>
                    Quick Actions
                </h2>
                <div class="space-y-3">
                    <a href="add-venue.php"
                        class="flex items-center justify-between p-4 transition-all border border-gray-200 rounded-lg hover:border-green-200 hover:bg-green-50 group">
                        <div class="flex items-center gap-3">
                            <i class="text-xl text-green-600 fas fa-plus-circle"></i>
                            <span class="font-semibold text-gray-700">Add New Venue</span>
                        </div>
                        <i class="text-gray-400 transition-transform group-hover:translate-x-1 fas fa-arrow-right"></i>
                    </a>
                    <a href="my-venues.php"
                        class="flex items-center justify-between p-4 transition-all border border-gray-200 rounded-lg hover:border-blue-200 hover:bg-blue-50 group">
                        <div class="flex items-center gap-3">
                            <i class="text-xl text-blue-600 fas fa-building"></i>
                            <span class="font-semibold text-gray-700">Manage Venues</span>
                        </div>
                        <i class="text-gray-400 transition-transform group-hover:translate-x-1 fas fa-arrow-right"></i>
                    </a>
                    <a href="bookings.php"
                        class="flex items-center justify-between p-4 transition-all border border-gray-200 rounded-lg hover:border-yellow-200 hover:bg-yellow-50 group">
                        <div class="flex items-center gap-3">
                            <i class="text-xl text-yellow-600 fas fa-calendar-alt"></i>
                            <span class="font-semibold text-gray-700">View Bookings</span>
                        </div>
                        <i class="text-gray-400 transition-transform group-hover:translate-x-1 fas fa-arrow-right"></i>
                    </a>
                    <a href="pricing.php"
                        class="flex items-center justify-between p-4 transition-all border border-gray-200 rounded-lg hover:border-purple-200 hover:bg-purple-50 group">
                        <div class="flex items-center gap-3">
                            <i class="text-xl text-purple-600 fas fa-tags"></i>
                            <span class="font-semibold text-gray-700">Set Pricing Rules</span>
                        </div>
                        <i class="text-gray-400 transition-transform group-hover:translate-x-1 fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <!-- Recent Bookings -->
            <div class="p-6 bg-white shadow-md rounded-xl">
                <h2 class="mb-4 text-xl font-bold text-gray-800">
                    <i class="mr-2 text-green-600 fas fa-history"></i>
                    Recent Bookings
                </h2>
                <div class="space-y-3">
                    <?php if ($recent_bookings && $recent_bookings->num_rows > 0): ?>
                        <?php while ($booking = $recent_bookings->fetch_assoc()): ?>
                            <div
                                class="p-4 transition-all border border-gray-200 rounded-lg hover:border-green-200 hover:bg-green-50">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h3 class="mb-1 font-semibold text-gray-800">
                                            <?php echo htmlspecialchars($booking['event_name']); ?></h3>
                                        <p class="text-sm text-gray-600">
                                            <i class="mr-1 fas fa-user"></i>
                                            <?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?>
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            <i class="mr-1 fas fa-map-marker-alt"></i>
                                            <?php echo htmlspecialchars($booking['venue_name'] ?? 'No venue assigned'); ?>
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            <i class="mr-1 fas fa-calendar"></i>
                                            <?php echo date('M d, Y', strtotime($booking['event_date'])); ?>
                                        </p>
                                        <p class="text-sm font-semibold text-green-600">
                                            <i class="mr-1 fas fa-peso-sign"></i>
                                            ₱<?php echo number_format($booking['total_cost'], 2); ?>
                                        </p>
                                    </div>
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full
                                        <?php
                                        echo $booking['status'] == 'confirmed' ? 'bg-green-100 text-green-700' : ($booking['status'] == 'pending' ? 'bg-yellow-100 text-yellow-700' : ($booking['status'] == 'completed' ? 'bg-blue-100 text-blue-700' : 'bg-red-100 text-red-700'));
                                        ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="flex flex-col items-center justify-center py-8 text-center text-gray-500">
                            <i class="mb-3 text-4xl fas fa-calendar-times"></i>
                            <p class="mb-2 font-semibold">No bookings yet</p>
                            <p class="text-sm">Bookings will appear here once clients start reserving your venues</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Venue Performance Chart -->
        <div class="p-6 mb-8 bg-white shadow-md rounded-xl">
            <h2 class="mb-4 text-xl font-bold text-gray-800">
                <i class="mr-2 text-green-600 fas fa-chart-bar"></i>
                Venue Performance
            </h2>
            <div class="relative" style="height: 300px;">
                <canvas id="venuePerformanceChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Dynamic Pricing Tool Modal -->
    <div id="pricingModal" class="fixed inset-0 z-50 items-center justify-center hidden bg-black bg-opacity-50">
        <div class="w-full max-w-4xl m-4 overflow-hidden bg-white shadow-2xl rounded-2xl">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-4 text-white bg-linear-to-r from-green-600 to-teal-600">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 bg-white rounded-full">
                        <i class="text-xl text-green-600 fas fa-chart-line"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold">Dynamic Pricing Tool</h3>
                        <p class="text-sm opacity-90">AI-powered pricing optimization</p>
                    </div>
                </div>
                <button id="closePricingTool" class="text-white transition-colors hover:text-gray-200">
                    <i class="text-2xl fas fa-times"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto bg-gray-50" style="max-height: 500px;">
                <div class="mb-6">
                    <h4 class="mb-4 text-lg font-bold text-gray-800">Pricing Recommendations</h4>

                    <!-- Pricing Factors -->
                    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-3">
                        <div class="p-4 bg-white border-l-4 border-green-500 rounded-lg">
                            <p class="mb-1 text-xs font-semibold text-gray-600 uppercase">Peak Season</p>
                            <p class="text-2xl font-bold text-green-600">+20%</p>
                            <p class="text-sm text-gray-600">Recommended increase</p>
                        </div>
                        <div class="p-4 bg-white border-l-4 border-blue-500 rounded-lg">
                            <p class="mb-1 text-xs font-semibold text-gray-600 uppercase">Weekend Rate</p>
                            <p class="text-2xl font-bold text-blue-600">+15%</p>
                            <p class="text-sm text-gray-600">Standard adjustment</p>
                        </div>
                        <div class="p-4 bg-white border-l-4 border-purple-500 rounded-lg">
                            <p class="mb-1 text-xs font-semibold text-gray-600 uppercase">Demand Score</p>
                            <p class="text-2xl font-bold text-purple-600">High</p>
                            <p class="text-sm text-gray-600">Current demand level</p>
                        </div>
                    </div>

                    <!-- Pricing Calculator -->
                    <div class="p-6 bg-white rounded-lg shadow-sm">
                        <h5 class="mb-4 font-bold text-gray-800">Calculate Optimal Price</h5>
                        <form id="pricingForm" class="space-y-4">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label class="block mb-2 text-sm font-semibold text-gray-700">Base Price (₱)</label>
                                    <input type="number" id="basePrice"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                        placeholder="50000" value="50000">
                                </div>
                                <div>
                                    <label class="block mb-2 text-sm font-semibold text-gray-700">Season</label>
                                    <select id="season"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                                        <option value="1.0">Off-Peak (0%)</option>
                                        <option value="1.1">Regular (+10%)</option>
                                        <option value="1.2" selected>Peak (+20%)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block mb-2 text-sm font-semibold text-gray-700">Day Type</label>
                                    <select id="dayType"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                                        <option value="1.0">Weekday (0%)</option>
                                        <option value="1.15" selected>Weekend (+15%)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block mb-2 text-sm font-semibold text-gray-700">Demand</label>
                                    <select id="demand"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                                        <option value="0.9">Low (-10%)</option>
                                        <option value="1.0">Normal (0%)</option>
                                        <option value="1.1" selected>High (+10%)</option>
                                    </select>
                                </div>
                            </div>
                            <button type="button" id="calculatePrice"
                                class="w-full px-6 py-3 font-semibold text-white transition-colors bg-green-600 rounded-lg hover:bg-green-700">
                                Calculate Optimal Price
                            </button>
                        </form>

                        <!-- Result -->
                        <div id="pricingResult"
                            class="hidden p-4 mt-4 border-l-4 border-green-500 rounded-lg bg-green-50">
                            <p class="mb-1 text-sm font-semibold text-gray-600">Recommended Price</p>
                            <p class="text-3xl font-bold text-green-600" id="optimalPrice">₱60,000.00</p>
                            <p class="mt-2 text-sm text-gray-600">This price optimizes for maximum revenue while
                                remaining competitive</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../../src/components/footer.php'; ?>

    <script src="../../assets/js/manager.js"></script>
</body>

</html>