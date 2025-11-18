<?php
session_start();

// Check if user is logged in and is an organizer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
    header("Location: ../signin.php");
    exit();
}

$first_name = $_SESSION['first_name'] ?? 'Organizer';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Event Planner | Gatherly</title>
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
</head>

<body class="<?php echo $nav_layout === 'sidebar' ? 'bg-gray-100' : 'bg-linear-to-br from-indigo-50 via-white to-pink-50'; ?> font-['Montserrat'] min-h-screen">
    <?php include '../../../src/components/OrganizerSidebar.php'; ?>

    <!-- Main Content -->
    <div class="<?php echo $nav_layout === 'sidebar' ? 'lg:ml-64' : 'container mx-auto'; ?> <?php echo $nav_layout === 'sidebar' ? '' : 'px-4 sm:px-6 lg:px-8'; ?> min-h-screen">
        <?php if ($nav_layout === 'sidebar'): ?>
            <!-- Top Bar for Sidebar Layout -->
            <div class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-20 px-4 sm:px-6 lg:px-8 py-4 mb-8">
                <h1 class="text-2xl font-bold text-gray-800">AI Event Planner</h1>
                <p class="text-sm text-gray-600">Your intelligent assistant for perfect event planning</p>
            </div>
            <div class="px-4 sm:px-6 lg:px-8">
            <?php else: ?>
                <!-- Header for Navbar Layout -->
                <div class="mb-8">
                    <h1 class="mb-2 text-3xl font-bold text-gray-800 sm:text-4xl">AI Event Planner</h1>
                    <p class="text-gray-600">Your intelligent assistant for perfect event planning</p>
                </div>
            <?php endif; ?>
            <!-- Header Section -->
            <div class="mb-8 text-center">
                <div class="flex items-center justify-center gap-4 mb-4">
                    <div class="flex items-center justify-center w-16 h-16 bg-indigo-600 rounded-full shadow-lg">
                        <i class="text-3xl text-white fas fa-robot"></i>
                    </div>
                    <div class="text-left">
                        <h1 class="text-4xl font-bold text-gray-800">AI Event Planner</h1>
                        <p class="text-gray-600">Your intelligent assistant for perfect event planning</p>
                    </div>
                </div>

                <div class="flex flex-wrap justify-center gap-3 mt-4">
                    <span class="px-4 py-2 text-sm font-semibold text-indigo-700 bg-indigo-200 rounded-full">
                        <i class="mr-1 fas fa-brain"></i> Smart Venue Matching
                    </span>
                    <span class="px-4 py-2 text-sm font-semibold rounded-full text-cyan-700 bg-cyan-200">
                        <i class="mr-1 fas fa-users"></i> Supplier Recommendations
                    </span>
                    <span class="px-4 py-2 text-sm font-semibold text-indigo-700 bg-indigo-200 rounded-full">
                        <i class="mr-1 fas fa-chart-line"></i> Budget Optimization
                    </span>
                    <span class="px-4 py-2 text-sm font-semibold text-blue-700 bg-blue-200 rounded-full">
                        <i class="mr-1 fas fa-comments"></i> Conversational Interface
                    </span>
                </div>
            </div>

            <!-- Chat Interface -->
            <div class="max-w-5xl mx-auto">
                <div class="overflow-hidden bg-white shadow-2xl rounded-2xl">
                    <!-- Chat Header -->
                    <div
                        class="flex items-center justify-between p-6 text-white bg-linear-to-r from-indigo-600 to-cyan-600">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-12 h-12 bg-white rounded-full">
                                <i class="text-2xl text-indigo-600 fas fa-robot"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold">AI Assistant</h3>
                                <p class="text-sm opacity-90">Online • Ready to help</p>
                            </div>
                        </div>
                        <button id="clearChat"
                            class="px-4 py-2 transition-colors bg-white rounded-lg bg-opacity-20 text-cyan-600 hover:bg-opacity-30">
                            <i class="mr-2 fas fa-redo"></i>
                            Start Over
                        </button>
                    </div>

                    <!-- Chat Messages -->
                    <div id="chatMessages" class="p-6 overflow-y-auto bg-gray-50" style="height: 550px;">
                        <!-- Messages will be added here dynamically -->
                    </div>

                    <!-- Chat Input -->
                    <div class="p-6 bg-white border-t border-gray-200">
                        <form id="chatForm" class="flex gap-3">
                            <input type="text" id="chatInput"
                                class="flex-1 px-5 py-3 text-lg border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="Type your message here..." autocomplete="off" autofocus>
                            <button type="submit"
                                class="px-8 py-3 font-semibold text-white transition-all transform bg-indigo-600 shadow-lg rounded-xl hover:bg-indigo-700 hover:scale-105">
                                <i class="mr-2 fas fa-paper-plane"></i>
                                Send
                            </button>
                        </form>

                        <!-- Quick Action Buttons -->
                        <div class="flex flex-wrap gap-2 mt-4">
                            <button
                                class="quick-action px-3 py-1.5 text-sm bg-indigo-100 text-indigo-700 rounded-lg hover:bg-indigo-200 transition-colors">
                                Wedding for 150 guests
                            </button>
                            <button
                                class="quick-action px-3 py-1.5 text-sm bg-pink-100 text-pink-700 rounded-lg hover:bg-pink-200 transition-colors">
                                Corporate event for 100 people
                            </button>
                            <button
                                class="quick-action px-3 py-1.5 text-sm bg-indigo-100 text-indigo-700 rounded-lg hover:bg-indigo-200 transition-colors">
                                Birthday party for 80 guests
                            </button>
                            <button
                                class="quick-action px-3 py-1.5 text-sm bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors">
                                Need all services
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Info Cards -->
                <div class="grid grid-cols-1 gap-4 mt-6 md:grid-cols-3">
                    <div class="p-4 bg-white border border-indigo-200 shadow-sm rounded-xl">
                        <div class="flex items-center gap-3 mb-2">
                            <i class="text-2xl text-indigo-600 fas fa-lightbulb"></i>
                            <h4 class="font-bold text-gray-800">How it works</h4>
                        </div>
                        <p class="text-sm text-gray-600">Answer a few simple questions about your event, and I'll provide
                            personalized recommendations.</p>
                    </div>

                    <div class="p-4 bg-white border border-pink-200 shadow-sm rounded-xl">
                        <div class="flex items-center gap-3 mb-2">
                            <i class="text-2xl text-pink-600 fas fa-shield-alt"></i>
                            <h4 class="font-bold text-gray-800">Smart & Secure</h4>
                        </div>
                        <p class="text-sm text-gray-600">Powered by machine learning algorithms to ensure the best matches
                            for your event needs.</p>
                    </div>

                    <div class="p-4 bg-white border border-indigo-200 shadow-sm rounded-xl">
                        <div class="flex items-center gap-3 mb-2">
                            <i class="text-2xl text-indigo-600 fas fa-clock"></i>
                            <h4 class="font-bold text-gray-800">Save Time</h4>
                        </div>
                        <p class="text-sm text-gray-600">Get instant recommendations instead of spending hours searching and
                            comparing options.</p>
                    </div>
                </div>
            </div>
            <?php if ($nav_layout === 'sidebar'): ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="../../assets/js/ai-planner.js"></script>
</body>

</html>