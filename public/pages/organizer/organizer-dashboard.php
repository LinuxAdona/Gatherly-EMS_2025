<?php
session_start();

// Check if user is logged in and is an organizer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
    header("Location: ../signin.php");
    exit();
}

require_once '../../../src/services/dbconnect.php';

$first_name = $_SESSION['first_name'] ?? 'Organizer';
$user_id = $_SESSION['user_id'];

// Fetch organizer's statistics
$stats = [
    'my_events' => 0,
    'pending_events' => 0,
    'confirmed_events' => 0,
    'total_spent' => 0
];

// Get organizer's events count
$result = $conn->query("SELECT COUNT(*) as count FROM events WHERE client_id = $user_id");
$stats['my_events'] = $result->fetch_assoc()['count'];

// Get pending events
$result = $conn->query("SELECT COUNT(*) as count FROM events WHERE client_id = $user_id AND status = 'pending'");
$stats['pending_events'] = $result->fetch_assoc()['count'];

// Get confirmed events
$result = $conn->query("SELECT COUNT(*) as count FROM events WHERE client_id = $user_id AND status = 'confirmed'");
$stats['confirmed_events'] = $result->fetch_assoc()['count'];

// Get total spent
$result = $conn->query("SELECT SUM(total_cost) as total FROM events WHERE client_id = $user_id AND status IN ('confirmed', 'completed')");
$stats['total_spent'] = $result->fetch_assoc()['total'] ?? 0;

// Get recent events
$recent_events_query = "SELECT e.event_id, e.event_name, e.event_date, e.status, e.total_cost, v.venue_name 
                        FROM events e 
                        LEFT JOIN venues v ON e.venue_id = v.venue_id 
                        WHERE e.client_id = $user_id 
                        ORDER BY e.event_date DESC 
                        LIMIT 5";
$recent_events = $conn->query($recent_events_query);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organizer Dashboard | Gatherly</title>
    <link rel="icon" type="image/x-icon" href="../../assets/images/logo.png">
    <link rel="stylesheet"
        href="../../../src/output.css?v=<?php echo filemtime(__DIR__ . '/../../../src/output.css'); ?>">
    <script src="https://kit.fontawesome.com/2a99de0fa5.js" crossorigin="anonymous"></script>
</head>

<body class="bg-linear-to-br from-indigo-50 via-white to-cyan-50 font-['Montserrat']">
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
                    <a href="organizer-dashboard.php"
                        class="font-semibold text-indigo-600 transition-colors hover:text-indigo-700">Dashboard</a>
                    <a href="my-events.php" class="text-gray-700 transition-colors hover:text-indigo-600">My Events</a>
                    <a href="find-venues.php" class="text-gray-700 transition-colors hover:text-indigo-600">Find
                        Venues</a>
                    <a href="ai-planner.php" class="text-gray-700 transition-colors hover:text-indigo-600">AI
                        Planner</a>
                    <div class="relative">
                        <button id="profile-dropdown-btn"
                            class="flex items-center gap-2 text-gray-700 transition-colors hover:text-indigo-600">
                            <i class="text-2xl fas fa-user-circle"></i>
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
    <div class="container px-4 py-8 mx-auto sm:px-6 lg:px-8">
        <!-- Welcome Section -->
        <div class="mb-8">
            <h1 class="mb-2 text-3xl font-bold text-gray-800 sm:text-4xl">Welcome back,
                <?php echo htmlspecialchars($first_name); ?>! 👋</h1>
            <p class="text-gray-600">Plan your events with intelligent venue recommendations</p>
        </div>

        <!-- AI Chatbot Highlight Banner -->
        <div
            class="p-6 mb-8 border-2 border-indigo-300 shadow-lg bg-linear-to-r from-indigo-100 to-cyan-100 rounded-xl">
            <div class="flex flex-col items-start justify-between lg:flex-row lg:items-center">
                <div class="mb-4 lg:mb-0">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="flex items-center justify-center w-12 h-12 bg-indigo-600 rounded-full">
                            <i class="text-2xl text-white fas fa-robot"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-indigo-900">AI Event Planner Assistant</h2>
                    </div>
                    <p class="text-gray-700">Get personalized venue and supplier recommendations powered by AI. Tell us
                        about your event and we'll create the perfect plan!</p>
                    <div class="flex flex-wrap gap-2 mt-3">
                        <span class="px-3 py-1 text-xs font-semibold text-indigo-700 bg-indigo-200 rounded-full">
                            <i class="mr-1 fas fa-brain"></i> Smart Matching
                        </span>
                        <span class="px-3 py-1 text-xs font-semibold text-cyan-700 bg-cyan-200 rounded-full">
                            <i class="mr-1 fas fa-chart-line"></i> Budget Optimization
                        </span>
                        <span class="px-3 py-1 text-xs font-semibold text-indigo-700 bg-indigo-200 rounded-full">
                            <i class="mr-1 fas fa-users"></i> Supplier Recommendations
                        </span>
                    </div>
                </div>
                <a href="ai-planner.php"
                    class="px-6 py-3 font-semibold text-white transition-all transform bg-indigo-600 shadow-lg rounded-xl hover:bg-indigo-700 hover:scale-105 hover:shadow-xl">
                    <i class="mr-2 fas fa-comments"></i>
                    Open AI Planner
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 gap-4 mb-8 sm:grid-cols-2 lg:grid-cols-4">
            <div class="p-6 bg-white border-l-4 border-indigo-500 shadow-md rounded-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="mb-1 text-sm text-gray-600">My Events</p>
                        <h3 class="text-3xl font-bold text-gray-800"><?php echo number_format($stats['my_events']); ?>
                        </h3>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-indigo-100 rounded-lg">
                        <i class="text-2xl text-indigo-600 fas fa-calendar-alt"></i>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-white border-l-4 border-yellow-500 shadow-md rounded-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="mb-1 text-sm text-gray-600">Pending</p>
                        <h3 class="text-3xl font-bold text-gray-800">
                            <?php echo number_format($stats['pending_events']); ?></h3>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-yellow-100 rounded-lg">
                        <i class="text-2xl text-yellow-600 fas fa-clock"></i>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-white border-l-4 border-green-500 shadow-md rounded-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="mb-1 text-sm text-gray-600">Confirmed</p>
                        <h3 class="text-3xl font-bold text-gray-800">
                            <?php echo number_format($stats['confirmed_events']); ?></h3>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-green-100 rounded-lg">
                        <i class="text-2xl text-green-600 fas fa-check-circle"></i>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-white border-l-4 border-blue-500 shadow-md rounded-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="mb-1 text-sm text-gray-600">Total Spent</p>
                        <h3 class="text-3xl font-bold text-gray-800">
                            ₱<?php echo number_format($stats['total_spent'], 2); ?></h3>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-blue-100 rounded-lg">
                        <i class="text-2xl text-blue-600 fas fa-peso-sign"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Recent Events -->
        <div class="grid grid-cols-1 gap-6 mb-8 lg:grid-cols-2">
            <!-- Quick Actions -->
            <div class="p-6 bg-white shadow-md rounded-xl">
                <h2 class="mb-4 text-xl font-bold text-gray-800">
                    <i class="mr-2 text-indigo-600 fas fa-bolt"></i>
                    Quick Actions
                </h2>
                <div class="space-y-3">
                    <a href="find-venues.php"
                        class="flex items-center justify-between p-4 transition-all border border-gray-200 rounded-lg hover:border-indigo-200 hover:bg-indigo-50 group">
                        <div class="flex items-center gap-3">
                            <i class="text-xl text-indigo-600 fas fa-search"></i>
                            <span class="font-semibold text-gray-700">Search Venues</span>
                        </div>
                        <i class="text-gray-400 transition-transform group-hover:translate-x-1 fas fa-arrow-right"></i>
                    </a>
                    <a href="create-event.php"
                        class="flex items-center justify-between p-4 transition-all border border-gray-200 rounded-lg hover:border-green-200 hover:bg-green-50 group">
                        <div class="flex items-center gap-3">
                            <i class="text-xl text-green-600 fas fa-plus-circle"></i>
                            <span class="font-semibold text-gray-700">Create New Event</span>
                        </div>
                        <i class="text-gray-400 transition-transform group-hover:translate-x-1 fas fa-arrow-right"></i>
                    </a>
                    <a href="my-events.php"
                        class="flex items-center justify-between p-4 transition-all border border-gray-200 rounded-lg hover:border-blue-200 hover:bg-blue-50 group">
                        <div class="flex items-center gap-3">
                            <i class="text-xl text-blue-600 fas fa-list"></i>
                            <span class="font-semibold text-gray-700">View All Events</span>
                        </div>
                        <i class="text-gray-400 transition-transform group-hover:translate-x-1 fas fa-arrow-right"></i>
                    </a>
                    <a href="bookings.php"
                        class="flex items-center justify-between p-4 transition-all border border-gray-200 rounded-lg hover:border-orange-200 hover:bg-orange-50 group">
                        <div class="flex items-center gap-3">
                            <i class="text-xl text-orange-600 fas fa-ticket-alt"></i>
                            <span class="font-semibold text-gray-700">Manage Bookings</span>
                        </div>
                        <i class="text-gray-400 transition-transform group-hover:translate-x-1 fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <!-- Recent Events -->
            <div class="p-6 bg-white shadow-md rounded-xl">
                <h2 class="mb-4 text-xl font-bold text-gray-800">
                    <i class="mr-2 text-indigo-600 fas fa-history"></i>
                    Recent Events
                </h2>
                <div class="space-y-3">
                    <?php if ($recent_events && $recent_events->num_rows > 0): ?>
                    <?php while ($event = $recent_events->fetch_assoc()): ?>
                    <div
                        class="p-4 transition-all border border-gray-200 rounded-lg hover:border-indigo-200 hover:bg-indigo-50">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="mb-1 font-semibold text-gray-800">
                                    <?php echo htmlspecialchars($event['event_name']); ?></h3>
                                <p class="text-sm text-gray-600">
                                    <i class="mr-1 fas fa-map-marker-alt"></i>
                                    <?php echo htmlspecialchars($event['venue_name'] ?? 'No venue assigned'); ?>
                                </p>
                                <p class="text-sm text-gray-600">
                                    <i class="mr-1 fas fa-calendar"></i>
                                    <?php echo date('M d, Y', strtotime($event['event_date'])); ?>
                                </p>
                            </div>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full
                                        <?php
                                        echo $event['status'] == 'confirmed' ? 'bg-green-100 text-green-700' : ($event['status'] == 'pending' ? 'bg-yellow-100 text-yellow-700' : ($event['status'] == 'completed' ? 'bg-blue-100 text-blue-700' : 'bg-red-100 text-red-700'));
                                        ?>">
                                <?php echo ucfirst($event['status']); ?>
                            </span>
                        </div>
                    </div>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <div class="flex flex-col items-center justify-center py-8 text-center text-gray-500">
                        <i class="mb-3 text-4xl fas fa-calendar-times"></i>
                        <p class="mb-2 font-semibold">No events yet</p>
                        <p class="text-sm">Create your first event to get started!</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/organizer.js"></script>
</body>

</html>