<?php
session_start();

// Check if user is logged in and is a venue manager
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'venue_manager') {
    header("Location: signin.php");
    exit();
}

require_once '../../src/services/dbconnect.php';

$first_name = $_SESSION['first_name'] ?? 'Manager';
$user_id = $_SESSION['user_id'];

// Fetch venue manager's statistics
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM venues WHERE manager_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$total_venues = $result->fetch_assoc()['count'] ?? 0;
$stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venue Manager Dashboard | Gatherly</title>
    <link rel="icon" type="image/x-icon" href="../assets/images/logo.png">
    <link rel="stylesheet" href="../../src/output.css?v=<?php echo filemtime(__DIR__ . '/../../src/output.css'); ?>">
    <script src="https://kit.fontawesome.com/2a99de0fa5.js" crossorigin="anonymous"></script>
</head>

<body class="bg-gradient-to-br from-orange-50 via-white to-red-50 font-['Montserrat']">
    <!-- Navbar -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-20">
                <div class="flex items-center gap-3">
                    <img src="../assets/images/logo.png" alt="Gatherly Logo" class="w-10 h-10 sm:w-12 sm:h-12">
                    <span class="text-xl sm:text-2xl font-bold text-gray-800">Venue Manager</span>
                </div>
                <div class="hidden md:flex items-center gap-6">
                    <a href="venue-manager-dashboard.php" class="text-orange-600 font-semibold hover:text-orange-700 transition-colors">Dashboard</a>
                    <a href="my-venues.php" class="text-gray-700 hover:text-orange-600 transition-colors">My Venues</a>
                    <a href="bookings-manager.php" class="text-gray-700 hover:text-orange-600 transition-colors">Bookings</a>
                    <a href="pricing-manager.php" class="text-gray-700 hover:text-orange-600 transition-colors">Pricing</a>
                    <a href="analytics-manager.php" class="text-gray-700 hover:text-orange-600 transition-colors">Analytics</a>
                    <div class="relative">
                        <button id="profile-dropdown-btn" class="flex items-center gap-2 text-gray-700 hover:text-orange-600 transition-colors">
                            <i class="fas fa-user-cog text-2xl"></i>
                            <span><?php echo htmlspecialchars($first_name); ?></span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        <div id="profile-dropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 hidden">
                            <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-orange-50">Profile</a>
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
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-800 mb-2">Welcome, <?php echo htmlspecialchars($first_name); ?>! 🏢</h1>
            <p class="text-gray-600">Manage your venues and optimize your bookings</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-orange-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">My Venues</p>
                        <h3 class="text-3xl font-bold text-gray-800"><?php echo $total_venues; ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-building text-2xl text-orange-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Total Bookings</p>
                        <h3 class="text-3xl font-bold text-gray-800">0</h3>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-check text-2xl text-green-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Occupancy Rate</p>
                        <h3 class="text-3xl font-bold text-gray-800">0%</h3>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-pie text-2xl text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Revenue (MTD)</p>
                        <h3 class="text-3xl font-bold text-gray-800">₱0</h3>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-peso-sign text-2xl text-purple-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Tools -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-tools text-orange-600 mr-2"></i>
                    Venue Management
                </h2>
                <div class="space-y-3">
                    <a href="add-venue.php" class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:border-orange-200 hover:bg-orange-50 transition-all">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-plus-circle text-xl text-orange-600"></i>
                            <span class="font-semibold text-gray-700">Add New Venue</span>
                        </div>
                        <i class="fas fa-arrow-right text-gray-400"></i>
                    </a>
                    <a href="my-venues.php" class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:border-blue-200 hover:bg-blue-50 transition-all">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-building text-xl text-blue-600"></i>
                            <span class="font-semibold text-gray-700">Manage Venues</span>
                        </div>
                        <i class="fas fa-arrow-right text-gray-400"></i>
                    </a>
                    <a href="pricing-manager.php" class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:border-green-200 hover:bg-green-50 transition-all">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-tags text-xl text-green-600"></i>
                            <span class="font-semibold text-gray-700">Dynamic Pricing</span>
                        </div>
                        <i class="fas fa-arrow-right text-gray-400"></i>
                    </a>
                    <a href="availability-calendar.php" class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:border-purple-200 hover:bg-purple-50 transition-all">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-calendar-alt text-xl text-purple-600"></i>
                            <span class="font-semibold text-gray-700">Availability Calendar</span>
                        </div>
                        <i class="fas fa-arrow-right text-gray-400"></i>
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-chart-line text-green-600 mr-2"></i>
                    Analytics & Insights
                </h2>
                <div class="space-y-3">
                    <a href="analytics-manager.php" class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:border-blue-200 hover:bg-blue-50 transition-all">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-chart-bar text-xl text-blue-600"></i>
                            <span class="font-semibold text-gray-700">Booking Analytics</span>
                        </div>
                        <i class="fas fa-arrow-right text-gray-400"></i>
                    </a>
                    <a href="revenue-forecast.php" class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:border-green-200 hover:bg-green-50 transition-all">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-chart-line text-xl text-green-600"></i>
                            <span class="font-semibold text-gray-700">Revenue Forecast</span>
                        </div>
                        <i class="fas fa-arrow-right text-gray-400"></i>
                    </a>
                    <a href="occupancy-trends.php" class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:border-purple-200 hover:bg-purple-50 transition-all">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-chart-area text-xl text-purple-600"></i>
                            <span class="font-semibold text-gray-700">Occupancy Trends</span>
                        </div>
                        <i class="fas fa-arrow-right text-gray-400"></i>
                    </a>
                    <a href="booking-requests.php" class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:border-orange-200 hover:bg-orange-50 transition-all">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-inbox text-xl text-orange-600"></i>
                            <span class="font-semibold text-gray-700">Booking Requests</span>
                        </div>
                        <i class="fas fa-arrow-right text-gray-400"></i>
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