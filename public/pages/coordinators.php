<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

require_once '../../src/services/dbconnect.php';

$first_name = $_SESSION['first_name'] ?? 'User';
$role = $_SESSION['role'];

// Fetch all coordinators
$coordinators_query = "SELECT u.user_id, u.first_name, u.last_name, u.email, u.phone,
                       (SELECT COUNT(*) FROM events WHERE coordinator_id = u.user_id) as total_events,
                       (SELECT COUNT(*) FROM events WHERE coordinator_id = u.user_id AND status = 'completed') as completed_events
                       FROM users u 
                       WHERE u.role = 'coordinator'
                       ORDER BY total_events DESC, u.last_name";
$result = $conn->query($coordinators_query);
$coordinators = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Coordinators | Gatherly</title>
    <link rel="icon" type="image/x-icon" href="../assets/images/logo.png">
    <link rel="stylesheet" href="../../src/output.css?v=<?php echo filemtime(__DIR__ . '/../../src/output.css'); ?>">
    <script src="https://kit.fontawesome.com/2a99de0fa5.js" crossorigin="anonymous"></script>
</head>

<body class="bg-gradient-to-br from-indigo-50 via-white to-purple-50 font-['Montserrat']">
    <!-- Navbar -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-20">
                <div class="flex items-center gap-3">
                    <img src="../assets/images/logo.png" alt="Gatherly Logo" class="w-10 h-10 sm:w-12 sm:h-12">
                    <span class="text-xl sm:text-2xl font-bold text-gray-800">Gatherly</span>
                </div>
                <div class="hidden md:flex items-center gap-6">
                    <a href="<?php echo $role === 'client' ? 'client-dashboard.php' : 'home.php'; ?>" class="text-gray-700 hover:text-indigo-600 transition-colors">Dashboard</a>
                    <a href="venue/search.php" class="text-gray-700 hover:text-indigo-600 transition-colors">Search Venues</a>
                    <a href="coordinators.php" class="text-indigo-600 font-semibold">Coordinators</a>
                    <a href="../../src/services/signout-handler.php" class="text-gray-700 hover:text-red-600 transition-colors">Sign Out</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-800 mb-2">Professional Event Coordinators</h1>
            <p class="text-gray-600">Find experienced coordinators to help plan your perfect event</p>
        </div>

        <!-- Why Hire a Coordinator -->
        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl shadow-lg p-6 mb-8 text-white">
            <h2 class="text-2xl font-bold mb-4">Why Hire a Coordinator?</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="flex items-start gap-3">
                    <i class="fas fa-check-circle text-2xl mt-1"></i>
                    <div>
                        <h3 class="font-bold mb-1">Expert Planning</h3>
                        <p class="text-sm opacity-90">Professional guidance from start to finish</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <i class="fas fa-clock text-2xl mt-1"></i>
                    <div>
                        <h3 class="font-bold mb-1">Save Time</h3>
                        <p class="text-sm opacity-90">Let experts handle the details</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <i class="fas fa-handshake text-2xl mt-1"></i>
                    <div>
                        <h3 class="font-bold mb-1">Vendor Network</h3>
                        <p class="text-sm opacity-90">Access to trusted suppliers</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Coordinators List -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Available Coordinators</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($coordinators as $coordinator):
                    $success_rate = $coordinator['total_events'] > 0
                        ? round(($coordinator['completed_events'] / $coordinator['total_events']) * 100)
                        : 0;
                ?>
                    <div class="border border-gray-200 rounded-lg p-5 hover:border-indigo-300 hover:shadow-lg transition-all">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-16 h-16 bg-gradient-to-br from-indigo-400 to-purple-500 rounded-full flex items-center justify-center text-white text-2xl font-bold">
                                <?php echo strtoupper(substr($coordinator['first_name'], 0, 1) . substr($coordinator['last_name'], 0, 1)); ?>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-800">
                                    <?php echo htmlspecialchars($coordinator['first_name'] . ' ' . $coordinator['last_name']); ?>
                                </h3>
                                <span class="text-sm text-gray-600">Event Coordinator</span>
                            </div>
                        </div>

                        <div class="space-y-2 text-sm text-gray-600 mb-4">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-envelope w-4"></i>
                                <span class="truncate"><?php echo htmlspecialchars($coordinator['email']); ?></span>
                            </div>
                            <?php if (!empty($coordinator['phone'])): ?>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-phone w-4"></i>
                                    <span><?php echo htmlspecialchars($coordinator['phone']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Statistics -->
                        <div class="grid grid-cols-2 gap-3 mb-4 p-3 bg-gray-50 rounded-lg">
                            <div class="text-center">
                                <p class="text-2xl font-bold text-indigo-600"><?php echo $coordinator['total_events']; ?></p>
                                <p class="text-xs text-gray-600">Total Events</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-green-600"><?php echo $success_rate; ?>%</p>
                                <p class="text-xs text-gray-600">Success Rate</p>
                            </div>
                        </div>

                        <button class="w-full px-4 py-2 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition-colors">
                            <i class="fas fa-user-check mr-2"></i>Contact Coordinator
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (empty($coordinators)): ?>
                <div class="text-center py-12">
                    <i class="fas fa-user-friends text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500">No coordinators available at the moment</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../../src/components/Footer.php'; ?>
</body>

</html>