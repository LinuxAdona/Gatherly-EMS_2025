<?php
session_start();

// Check if user is logged in and is a coordinator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'coordinator') {
    header("Location: signin.php");
    exit();
}

require_once '../../src/services/dbconnect.php';

$first_name = $_SESSION['first_name'] ?? 'Coordinator';
$user_id = $_SESSION['user_id'];

// Fetch coordinator's events
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM events WHERE coordinator_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_events = $stmt->fetch()['count'] ?? 0;
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM events WHERE coordinator_id = ? AND status = 'pending'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pending_events = $stmt->fetch()['count'] ?? 0;
$stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coordinator Dashboard | Gatherly</title>
    <link rel="icon" type="image/x-icon" href="../assets/images/logo.png">
    <link rel="stylesheet" href="../../src/output.css?v=<?php echo filemtime(__DIR__ . '/../../src/output.css'); ?>">
    <script src="https://kit.fontawesome.com/2a99de0fa5.js" crossorigin="anonymous"></script>
</head>

<body class="bg-gradient-to-br from-green-50 via-white to-teal-50 font-['Montserrat']">
    <!-- Navbar -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-20">
                <div class="flex items-center gap-3">
                    <img src="../assets/images/logo.png" alt="Gatherly Logo" class="w-10 h-10 sm:w-12 sm:h-12">
                    <span class="text-xl sm:text-2xl font-bold text-gray-800">Gatherly Coordinator</span>
                </div>
                <div class="hidden md:flex items-center gap-6">
                    <a href="coordinator-dashboard.php" class="text-green-600 font-semibold hover:text-green-700 transition-colors">Dashboard</a>
                    <a href="my-events-coordinator.php" class="text-gray-700 hover:text-green-600 transition-colors">My Events</a>
                    <a href="venue/search.php" class="text-gray-700 hover:text-green-600 transition-colors">Search Venues</a>
                    <a href="chat/ai-chat.php" class="text-gray-700 hover:text-green-600 transition-colors">AI Assistant</a>
                    <a href="clients-list.php" class="text-gray-700 hover:text-green-600 transition-colors">Clients</a>
                    <div class="relative group">
                        <button class="flex items-center gap-2 text-gray-700 hover:text-green-600 transition-colors">
                            <i class="fas fa-user-tie text-2xl"></i>
                            <span><?php echo htmlspecialchars($first_name); ?></span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 hidden group-hover:block">
                            <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-green-50">Profile</a>
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
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-800 mb-2">Welcome, <?php echo htmlspecialchars($first_name); ?>! 🎯</h1>
            <p class="text-gray-600">Manage your events and coordinate with clients</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Total Events</p>
                        <h3 class="text-3xl font-bold text-gray-800"><?php echo $total_events; ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-2xl text-green-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Pending Events</p>
                        <h3 class="text-3xl font-bold text-gray-800"><?php echo $pending_events; ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-2xl text-yellow-600"></i>
                    </div>
                </div>
            </div>

            <a href="venue/search.php" class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all hover:-translate-y-1 border border-transparent hover:border-green-200">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-search text-2xl text-blue-600"></i>
                    </div>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-1">Search Venues</h3>
                <p class="text-sm text-gray-600">Find perfect venues for your clients</p>
            </a>

            <a href="chat/ai-chat.php" class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all hover:-translate-y-1 border border-transparent hover:border-purple-200">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-robot text-2xl text-purple-600"></i>
                    </div>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-1">AI Assistant</h3>
                <p class="text-sm text-gray-600">Get AI-powered recommendations</p>
            </a>
        </div>

        <!-- Quick Actions & Events -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-tasks text-green-600 mr-2"></i>
                    Quick Actions
                </h2>
                <div class="space-y-3">
                    <a href="create-event.php" class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:border-green-200 hover:bg-green-50 transition-all">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-plus-circle text-xl text-green-600"></i>
                            <span class="font-semibold text-gray-700">Create New Event</span>
                        </div>
                        <i class="fas fa-arrow-right text-gray-400"></i>
                    </a>
                    <a href="my-events-coordinator.php" class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:border-blue-200 hover:bg-blue-50 transition-all">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-calendar-check text-xl text-blue-600"></i>
                            <span class="font-semibold text-gray-700">Manage Events</span>
                        </div>
                        <i class="fas fa-arrow-right text-gray-400"></i>
                    </a>
                    <a href="clients-list.php" class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:border-purple-200 hover:bg-purple-50 transition-all">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-users text-xl text-purple-600"></i>
                            <span class="font-semibold text-gray-700">View Clients</span>
                        </div>
                        <i class="fas fa-arrow-right text-gray-400"></i>
                    </a>
                    <a href="reports-coordinator.php" class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:border-orange-200 hover:bg-orange-50 transition-all">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-chart-line text-xl text-orange-600"></i>
                            <span class="font-semibold text-gray-700">View Reports</span>
                        </div>
                        <i class="fas fa-arrow-right text-gray-400"></i>
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-800">Upcoming Events</h2>
                    <a href="my-events-coordinator.php" class="text-sm text-green-600 hover:text-green-700 font-semibold">View All</a>
                </div>
                <div class="space-y-3">
                    <div class="p-4 border border-gray-200 rounded-lg">
                        <p class="text-sm text-gray-500">No upcoming events</p>
                        <p class="text-xs text-gray-400 mt-1">Create your first event to get started</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../src/components/Footer.php'; ?>
</body>

</html>