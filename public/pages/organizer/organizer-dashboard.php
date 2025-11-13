<?php
// TEMPORARY: Enable error display for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);

session_start();

// Initialize debug info array
$debug_info = [];
$has_error = false;
$error_message = '';

// Check if user is logged in and is an organizer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
    header("Location: ../signin.php");
    exit();
}

$debug_info[] = "User ID: " . $_SESSION['user_id'];
$debug_info[] = "User Role: " . $_SESSION['role'];
$debug_info[] = "Current directory: " . __DIR__;

try {
    $debug_info[] = "Loading dbconnect.php...";
    require_once __DIR__ . '/../../../src/services/dbconnect.php';
    $debug_info[] = "dbconnect.php loaded successfully";
    
    if (!isset($conn)) {
        throw new Exception("Database connection variable \$conn is not set");
    }
    
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    $debug_info[] = "Database connection verified";
} catch (Exception $e) {
    $has_error = true;
    $error_message = $e->getMessage();
    $debug_info[] = "ERROR: " . $e->getMessage();
    error_log("ERROR in organizer-dashboard.php: " . $e->getMessage());
}

$first_name = $_SESSION['first_name'] ?? 'Organizer';
$user_id = $_SESSION['user_id'];

// Fetch organizer's statistics
$stats = [
    'my_events' => 0,
    'pending_events' => 0,
    'confirmed_events' => 0,
    'total_spent' => 0
];

$recent_events = null;

// Only proceed with queries if database connection is successful
if (!$has_error && isset($conn)) {
    try {
        $debug_info[] = "Fetching organizer statistics...";
        
        // Get organizer's events count
        $result = $conn->query("SELECT COUNT(*) as count FROM events WHERE client_id = $user_id");
        if ($result === false) {
            throw new Exception("Failed to fetch events count: " . $conn->error);
        }
        $stats['my_events'] = $result->fetch_assoc()['count'];
        $debug_info[] = "My events count: " . $stats['my_events'];

        // Get pending events
        $result = $conn->query("SELECT COUNT(*) as count FROM events WHERE client_id = $user_id AND status = 'pending'");
        if ($result === false) {
            throw new Exception("Failed to fetch pending events: " . $conn->error);
        }
        $stats['pending_events'] = $result->fetch_assoc()['count'];
        $debug_info[] = "Pending events count: " . $stats['pending_events'];

        // Get confirmed events
        $result = $conn->query("SELECT COUNT(*) as count FROM events WHERE client_id = $user_id AND status = 'confirmed'");
        if ($result === false) {
            throw new Exception("Failed to fetch confirmed events: " . $conn->error);
        }
        $stats['confirmed_events'] = $result->fetch_assoc()['count'];
        $debug_info[] = "Confirmed events count: " . $stats['confirmed_events'];

        // Get total spent
        $result = $conn->query("SELECT SUM(total_cost) as total FROM events WHERE client_id = $user_id AND status IN ('confirmed', 'completed')");
        if ($result === false) {
            throw new Exception("Failed to fetch total spent: " . $conn->error);
        }
        $stats['total_spent'] = $result->fetch_assoc()['total'] ?? 0;
        $debug_info[] = "Total spent: ₱" . number_format($stats['total_spent'], 2);

        // Get recent events
        $debug_info[] = "Fetching recent events...";
        $recent_events_query = "SELECT e.event_id, e.event_name, e.event_date, e.status, e.total_cost, v.venue_name 
                                FROM events e 
                                LEFT JOIN venues v ON e.venue_id = v.venue_id 
                                WHERE e.client_id = $user_id 
                                ORDER BY e.event_date DESC 
                                LIMIT 5";
        $recent_events = $conn->query($recent_events_query);
        if ($recent_events === false) {
            throw new Exception("Failed to fetch recent events: " . $conn->error);
        }
        $debug_info[] = "Recent events fetched: " . $recent_events->num_rows . " rows";
        
        $debug_info[] = "All queries completed successfully";
    } catch (Exception $e) {
        $has_error = true;
        $error_message = $e->getMessage();
        $debug_info[] = "ERROR: " . $e->getMessage();
        error_log("ERROR in organizer-dashboard.php queries: " . $e->getMessage());
    }
    
    if (isset($conn)) {
        $conn->close();
        $debug_info[] = "Database connection closed";
    }
}
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

<body class="bg-linear-to-br from-indigo-50 via-white to-cyan-50 font-['Montserrat'] min-h-screen flex flex-col">
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
                    <a href="chats.php" class="text-gray-700 transition-colors hover:text-indigo-600">Chat</a>
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

    <!-- Debug Information Section -->
    <?php if (!empty($debug_info)): ?>
        <div class="container px-4 py-4 mx-auto sm:px-6 lg:px-8">
            <div class="p-4 border rounded-lg <?php echo $has_error ? 'bg-red-50 border-red-300' : 'bg-blue-50 border-blue-300'; ?>">
                <div class="flex items-start gap-3">
                    <i class="mt-1 text-xl <?php echo $has_error ? 'fas fa-exclamation-circle text-red-600' : 'fas fa-info-circle text-blue-600'; ?>"></i>
                    <div class="flex-1">
                        <h3 class="mb-2 text-lg font-bold <?php echo $has_error ? 'text-red-900' : 'text-blue-900'; ?>">
                            <?php echo $has_error ? 'Error Detected' : 'Debug Information'; ?>
                        </h3>
                        <?php if ($has_error && !empty($error_message)): ?>
                            <div class="p-3 mb-3 font-semibold text-red-900 bg-red-100 border border-red-200 rounded">
                                <?php echo htmlspecialchars($error_message); ?>
                            </div>
                        <?php endif; ?>
                        <div class="space-y-1 text-sm font-mono <?php echo $has_error ? 'text-red-800' : 'text-blue-800'; ?>">
                            <?php foreach ($debug_info as $info): ?>
                                <div class="flex items-start gap-2">
                                    <span class="mt-1 text-xs">•</span>
                                    <span class="flex-1"><?php echo htmlspecialchars($info); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <button onclick="this.closest('.p-4').remove()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <div class="container px-4 py-8 mx-auto sm:px-6 lg:px-8 grow">
        <div class="flex flex-col gap-6 mb-8 lg:flex-row lg:items-start lg:justify-between">
            <!-- Welcome Section -->
            <div class="mb-8">
                <h1 class="mb-2 text-3xl font-bold text-gray-800 sm:text-4xl">Welcome back,
                    <?php echo htmlspecialchars($first_name); ?>! 👋</h1>
                <p class="text-gray-600">Plan your events with intelligent venue recommendations</p>
            </div>
            <!-- Statistics Cards -->
            <div class="p-4 bg-white rounded-lg shadow-sm lg:shrink-0">
                <div class="flex items-center gap-6">
                    <div class="relative flex items-center gap-2 group cursor-help">
                        <i class="text-indigo-500 fas fa-calendar-alt"></i>
                        <span
                            class="text-xl font-bold text-gray-800"><?php echo number_format($stats['my_events']); ?></span>
                        <div
                            class="absolute invisible px-3 py-1 mb-2 text-xs text-white transition-all duration-200 -translate-x-1/2 bg-gray-900 rounded opacity-0 bottom-full left-1/2 group-hover:opacity-100 group-hover:visible whitespace-nowrap">
                            My Events</div>
                    </div>
                    <div class="w-px h-8 bg-gray-200"></div>
                    <div class="relative flex items-center gap-2 group cursor-help">
                        <i class="text-yellow-500 fas fa-clock"></i>
                        <span
                            class="text-xl font-bold text-gray-800"><?php echo number_format($stats['pending_events']); ?></span>
                        <div
                            class="absolute invisible px-3 py-1 mb-2 text-xs text-white transition-all duration-200 -translate-x-1/2 bg-gray-900 rounded opacity-0 bottom-full left-1/2 group-hover:opacity-100 group-hover:visible whitespace-nowrap">
                            Pending Events</div>
                    </div>
                    <div class="w-px h-8 bg-gray-200"></div>
                    <div class="relative flex items-center gap-2 group cursor-help">
                        <i class="text-green-500 fas fa-check-circle"></i>
                        <span
                            class="text-xl font-bold text-gray-800"><?php echo number_format($stats['confirmed_events']); ?></span>
                        <div
                            class="absolute invisible px-3 py-1 mb-2 text-xs text-white transition-all duration-200 -translate-x-1/2 bg-gray-900 rounded opacity-0 bottom-full left-1/2 group-hover:opacity-100 group-hover:visible whitespace-nowrap">
                            Confirmed Events</div>
                    </div>
                    <div class="w-px h-8 bg-gray-200"></div>
                    <div class="relative flex items-center gap-2 group cursor-help">
                        <i class="text-blue-500 fas fa-peso-sign"></i>
                        <span
                            class="text-xl font-bold text-gray-800">₱<?php echo number_format($stats['total_spent'], 2); ?></span>
                        <div
                            class="absolute invisible px-3 py-1 mb-2 text-xs text-white transition-all duration-200 -translate-x-1/2 bg-gray-900 rounded opacity-0 bottom-full left-1/2 group-hover:opacity-100 group-hover:visible whitespace-nowrap">
                            Total Spent</div>
                    </div>
                </div>
            </div>
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
                        <span class="px-3 py-1 text-xs font-semibold rounded-full text-cyan-700 bg-cyan-200">
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
                    Chat with AI
                </a>
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

    <?php include '../../../src/components/Footer.php' ?>

    <script src="../../assets/js/organizer.js"></script>
</body>

</html>