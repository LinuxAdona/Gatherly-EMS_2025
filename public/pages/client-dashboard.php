<?php
session_start();

// Check if user is logged in and is a client
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: signin.php");
    exit();
}

$first_name = $_SESSION['first_name'] ?? 'Guest';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard | Gatherly</title>
    <link rel="icon" type="image/x-icon" href="../assets/images/logo.png">
    <link rel="stylesheet" href="../../src/output.css?v=<?php echo filemtime(__DIR__ . '/../../src/output.css'); ?>">
    <script src="https://kit.fontawesome.com/2a99de0fa5.js" crossorigin="anonymous"></script>
</head>

<body class="bg-linear-to-br from-indigo-50 via-white to-purple-50 font-['Montserrat']">
    <!-- Navbar -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-20">
                <div class="flex items-center gap-3">
                    <img src="../assets/images/logo.png" alt="Gatherly Logo" class="w-10 h-10 sm:w-12 sm:h-12">
                    <span class="text-xl sm:text-2xl font-bold text-gray-800">Gatherly</span>
                </div>
                <div class="hidden md:flex items-center gap-6">
                    <a href="client-dashboard.php"
                        class="text-indigo-600 font-semibold hover:text-indigo-700 transition-colors">Dashboard</a>
                    <a href="venue/search.php" class="text-gray-700 hover:text-indigo-600 transition-colors">Search
                        Venues</a>
                    <a href="chat/ai-chat.php" class="text-gray-700 hover:text-indigo-600 transition-colors">AI
                        Assistant</a>
                    <a href="suppliers.php" class="text-gray-700 hover:text-indigo-600 transition-colors">Suppliers</a>
                    <a href="my-events.php" class="text-gray-700 hover:text-indigo-600 transition-colors">My Events</a>
                    <div class="relative">
                        <button id="profile-dropdown-btn"
                            class="flex items-center gap-2 text-gray-700 hover:text-indigo-600 transition-colors">
                            <i class="fas fa-user-circle text-2xl"></i>
                            <span><?php echo htmlspecialchars($first_name); ?></span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        <div id="profile-dropdown"
                            class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 hidden">
                            <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50">Profile</a>
                            <a href="../../src/services/signout-handler.php"
                                class="block px-4 py-2 text-red-600 hover:bg-red-50">Sign Out</a>
                        </div>
                    </div>
                </div>
                <!-- Mobile menu button -->
                <button class="md:hidden text-gray-700" id="mobile-menu-btn">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
        </div>
        <!-- Mobile menu -->
        <div class="md:hidden hidden bg-white border-t" id="mobile-menu">
            <div class="px-4 py-3 space-y-3">
                <a href="client-dashboard.php" class="block text-indigo-600 font-semibold">Dashboard</a>
                <a href="venue/search.php" class="block text-gray-700">Search Venues</a>
                <a href="chat/ai-chat.php" class="block text-gray-700">AI Assistant</a>
                <a href="suppliers.php" class="block text-gray-700">Suppliers</a>
                <a href="my-events.php" class="block text-gray-700">My Events</a>
                <a href="profile.php" class="block text-gray-700">Profile</a>
                <a href="../../src/services/signout-handler.php" class="block text-red-600">Sign Out</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Welcome Section -->
        <div class="mb-8">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-800 mb-2">Welcome back,
                <?php echo htmlspecialchars($first_name); ?>! 👋</h1>
            <p class="text-gray-600">Let's plan your perfect event together</p>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <a href="venue/search.php"
                class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all hover:-translate-y-1 border border-transparent hover:border-indigo-200">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-search text-2xl text-indigo-600"></i>
                    </div>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-1">Find Venues</h3>
                <p class="text-sm text-gray-600">Search and compare venues with AI recommendations</p>
            </a>

            <a href="chat/ai-chat.php"
                class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all hover:-translate-y-1 border border-transparent hover:border-purple-200">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-robot text-2xl text-purple-600"></i>
                    </div>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-1">AI Assistant</h3>
                <p class="text-sm text-gray-600">Chat with our AI to get venue recommendations</p>
            </a>

            <a href="suppliers.php"
                class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all hover:-translate-y-1 border border-transparent hover:border-green-200">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-store text-2xl text-green-600"></i>
                    </div>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-1">Suppliers</h3>
                <p class="text-sm text-gray-600">Browse event services and suppliers</p>
            </a>

            <a href="coordinators.php"
                class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all hover:-translate-y-1 border border-transparent hover:border-orange-200">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-2xl text-orange-600"></i>
                    </div>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-1">Coordinators</h3>
                <p class="text-sm text-gray-600">Find professional event coordinators</p>
            </a>
        </div>

        <!-- Recent Events & Recommendations -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- My Upcoming Events -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-800">My Upcoming Events</h2>
                    <a href="my-events.php" class="text-sm text-indigo-600 hover:text-indigo-700 font-semibold">View
                        All</a>
                </div>
                <div class="space-y-3">
                    <div class="p-4 border border-gray-200 rounded-lg hover:border-indigo-200 transition-colors">
                        <p class="text-sm text-gray-500 mb-1">No upcoming events</p>
                        <p class="text-xs text-gray-400">Start planning your event today!</p>
                    </div>
                </div>
            </div>

            <!-- AI Recommendations -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-800">
                        <i class="fas fa-sparkles text-purple-500 mr-2"></i>
                        AI Recommendations
                    </h2>
                </div>
                <div class="space-y-3">
                    <div class="p-4 bg-linear-to-r from-purple-50 to-indigo-50 rounded-lg border border-purple-200">
                        <p class="text-sm text-gray-700 mb-2">
                            <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
                            <strong>Pro Tip:</strong> Use our AI chatbot to find the perfect venue based on your
                            specific needs!
                        </p>
                        <a href="chat/ai-chat.php"
                            class="text-sm text-indigo-600 hover:text-indigo-700 font-semibold">Try AI Chat →</a>
                    </div>
                    <div class="p-4 bg-linear-to-r from-blue-50 to-cyan-50 rounded-lg border border-blue-200">
                        <p class="text-sm text-gray-700 mb-2">
                            <i class="fas fa-calendar-check text-blue-500 mr-2"></i>
                            <strong>Planning Tips:</strong> Book venues 3-6 months in advance for popular dates
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../src/components/Footer.php'; ?>

    <script>
        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');

        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });

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