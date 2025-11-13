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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Messages | Gatherly</title>
    <link rel="icon" type="image/x-icon" href="../../assets/images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="../../../src/output.css?v=<?php echo filemtime(__DIR__ . '/../../../src/output.css'); ?>"
        rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Almarai:wght@300;400;700;800&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        /* Custom message bubble styles */
        .message {
            display: flex;
            flex-direction: column;
            max-width: 70%;
            animation: messageSlide 0.3s ease-out;
        }

        @keyframes messageSlide {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message.received {
            align-self: flex-start;
        }

        .message.sent {
            align-self: flex-end;
            text-align: right;
        }

        .message p {
            background: #f3f4f6;
            padding: 12px 16px;
            border-radius: 16px;
            font-size: 14px;
            color: #1f2937;
            line-height: 1.5;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            word-wrap: break-word;
        }

        .message.received p {
            border-bottom-left-radius: 4px;
        }

        .message.sent p {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            border-bottom-right-radius: 4px;
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
        }

        .timestamp {
            font-size: 11px;
            color: #9ca3af;
            margin-top: 4px;
            font-weight: 500;
        }

        .chat-image {
            max-width: 250px;
            max-height: 250px;
            border-radius: 12px;
            margin-bottom: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: transform 0.2s;
        }

        .chat-image:hover {
            transform: scale(1.02);
        }

        .conversation.active {
            background: linear-gradient(135deg, #eef2ff 0%, #f5f3ff 100%);
            border-left: 3px solid #6366f1;
        }

        #chatMessages::-webkit-scrollbar {
            width: 6px;
        }

        #chatMessages::-webkit-scrollbar-track {
            background: #f3f4f6;
        }

        #chatMessages::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 3px;
        }

        #chatMessages::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }
    </style>
</head>

<body class="bg-linear-to-br from-indigo-50 via-white to-purple-50 font-['Montserrat'] min-h-screen flex flex-col">
    <!-- Navbar -->
    <nav class="sticky top-0 z-50 bg-white shadow-md">
        <div class="container px-4 mx-auto sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <a href="../../../index.php" class="flex items-center group">
                        <img class="w-10 h-10 mr-2 transition-transform group-hover:scale-110"
                            src="../../assets/images/logo.png" alt="Gatherly Logo">
                        <span class="text-xl font-bold text-gray-800">Gatherly</span>
                    </a>
                </div>
                <div class="items-center hidden gap-6 md:flex">
                    <a href="organizer-dashboard.php"
                        class="text-gray-700 transition-colors hover:text-indigo-600">Dashboard</a>
                    <a href="my-events.php" class="text-gray-700 transition-colors hover:text-indigo-600">My Events</a>
                    <a href="find-venues.php" class="text-gray-700 transition-colors hover:text-indigo-600">Find
                        Venues</a>
                    <a href="ai-planner.php" class="text-gray-700 transition-colors hover:text-indigo-600">AI
                        Planner</a>
                    <a href="chats.php" class="font-semibold text-indigo-600">Chat</a>
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
    <div class="container px-4 py-8 mx-auto sm:px-6 lg:px-8 grow">
        <!-- Page Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <button id="toggleSidebar"
                        class="p-2 text-gray-600 transition-colors rounded-lg md:hidden hover:bg-gray-100">
                        <i class="text-xl fas fa-bars"></i>
                    </button>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">
                            <i class="mr-2 text-indigo-600 fas fa-comments"></i>Messages
                        </h1>
                        <p class="mt-1 text-gray-600">Connect with venue managers and suppliers</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button
                        class="flex items-center gap-2 px-4 py-2 text-gray-700 transition-colors bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50">
                        <i class="fas fa-search"></i>
                        <span class="hidden sm:inline">Search</span>
                    </button>
                    <button
                        class="flex items-center gap-2 px-4 py-2 text-white transition-colors bg-indigo-600 rounded-lg shadow-sm hover:bg-indigo-700">
                        <i class="fas fa-plus"></i>
                        <span class="hidden sm:inline">New Message</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="relative flex gap-5 h-[calc(100vh-250px)]">
            <!-- Sidebar - Conversations List -->
            <div id="chatSidebar"
                class="fixed inset-y-0 left-0 z-50 flex flex-col overflow-hidden transition-transform duration-300 transform -translate-x-full bg-white border border-gray-200 shadow-lg w-80 md:relative md:translate-x-0 md:w-80 rounded-xl md:rounded-xl">
                <!-- Close button for mobile -->
                <button id="closeSidebar"
                    class="absolute z-10 p-2 text-gray-600 transition-colors rounded-lg top-4 right-4 md:hidden hover:bg-gray-100">
                    <i class="fas fa-times"></i>
                </button>
                <div class="p-5 border-b border-gray-200 shrink-0 bg-linear-to-r from-indigo-50 to-purple-50">
                    <h3 class="text-sm font-semibold text-gray-700">All Conversations</h3>
                    <p class="text-xs text-gray-500">3 active chats</p>
                </div>

                <div class="flex-1 overflow-x-hidden overflow-y-auto">
                    <!-- Conversation 1 -->
                    <div class="flex items-center justify-between p-4 transition border-b border-gray-100 cursor-pointer conversation active hover:bg-indigo-50"
                        data-chat="ballroom">
                        <div class="flex items-center gap-3">
                            <div class="relative">
                                <div
                                    class="flex items-center justify-center w-12 h-12 text-sm font-bold text-white bg-indigo-600 rounded-full shadow-md">
                                    SA
                                </div>
                                <span
                                    class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white rounded-full"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-gray-900 truncate">Grand Ballroom</p>
                                <span class="text-xs text-gray-500 truncate">Yes, we have several dates available in
                                    March.</span>
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-1">
                            <span
                                class="badge bg-indigo-600 text-white text-xs rounded-full px-2 py-0.5 font-semibold">2</span>
                            <span class="text-xs text-gray-400">10:32 AM</span>
                        </div>
                    </div>

                    <!-- Conversation 2 -->
                    <div class="flex items-center justify-between p-4 transition border-b border-gray-100 cursor-pointer conversation hover:bg-indigo-50"
                        data-chat="garden">
                        <div class="flex items-center gap-3">
                            <div class="relative">
                                <div
                                    class="flex items-center justify-center w-12 h-12 text-sm font-bold text-white bg-purple-600 rounded-full shadow-md">
                                    MI
                                </div>
                                <span
                                    class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white rounded-full"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-gray-900 truncate">Garden Paradise</p>
                                <span class="text-xs text-gray-500 truncate">The catering package includes...</span>
                            </div>
                        </div>
                        <div class="flex flex-col items-end">
                            <span class="text-xs text-gray-400">Yesterday</span>
                        </div>
                    </div>

                    <!-- Conversation 3 -->
                    <div class="flex items-center justify-between p-4 transition border-b border-gray-100 cursor-pointer conversation hover:bg-indigo-50"
                        data-chat="skyline">
                        <div class="flex items-center gap-3">
                            <div class="relative">
                                <div
                                    class="flex items-center justify-center w-12 h-12 text-sm font-bold text-white rounded-full shadow-md bg-cyan-600">
                                    EM
                                </div>
                                <span
                                    class="absolute bottom-0 right-0 w-3 h-3 bg-gray-400 border-2 border-white rounded-full"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-gray-900 truncate">Skyline Rooftop</p>
                                <span class="text-xs text-gray-500 truncate">I can send you the contract today.</span>
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-1">
                            <span
                                class="badge bg-indigo-600 text-white text-xs rounded-full px-2 py-0.5 font-semibold">1</span>
                            <span class="text-xs text-gray-400">2 days ago</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Overlay for mobile -->
            <div id="sidebarOverlay" class="fixed inset-0 z-40 hidden bg-black bg-opacity-50 md:hidden"></div>

            <!-- Chat Area -->
            <div id="chatArea" class="flex-1 overflow-hidden bg-white border border-gray-200 shadow-lg rounded-xl">

                <!-- Chat Header -->
                <div
                    class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-linear-to-r from-indigo-50 to-purple-50">
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <div
                                class="flex items-center justify-center w-10 h-10 text-sm font-bold text-white bg-indigo-600 rounded-full">
                                SA
                            </div>
                            <span
                                class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white rounded-full"></span>
                        </div>
                        <div>
                            <h4 class="text-base font-bold text-gray-900">Grand Ballroom Manager</h4>
                            <span class="text-sm text-green-600"><i class="mr-1 fas fa-circle text-[8px]"></i>Online -
                                Usually replies within an hour</span>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button
                            class="p-2 text-gray-600 transition-colors rounded-lg hover:bg-white hover:text-indigo-600">
                            <i class="fas fa-phone"></i>
                        </button>
                        <button
                            class="p-2 text-gray-600 transition-colors rounded-lg hover:bg-white hover:text-indigo-600">
                            <i class="fas fa-video"></i>
                        </button>
                        <button
                            class="p-2 text-gray-600 transition-colors rounded-lg hover:bg-white hover:text-indigo-600">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                    </div>
                </div>

                <!-- Chat Messages -->
                <div id="chatMessages" class="flex flex-col gap-4 px-6 py-5 overflow-y-auto bg-gray-50"
                    style="height: calc(100vh - 450px);">
                    <!-- Date Divider -->
                    <div class="flex items-center justify-center my-2">
                        <div
                            class="px-4 py-1 text-xs font-semibold text-gray-600 bg-white border border-gray-200 rounded-full shadow-sm">
                            Today
                        </div>
                    </div>

                    <div class="message received">
                        <p>Yes, we have several dates available in March</p>
                        <div class="timestamp">09:49 AM</div>
                    </div>

                    <div class="message sent">
                        <p>Hi! I'm interested in booking for a corporate event in March.</p>
                        <div class="timestamp">09:59 AM</div>
                    </div>
                </div>

                <!-- Chat Input -->
                <div class="flex items-center gap-3 px-5 py-4 bg-white border-t border-gray-200">
                    <button id="attachFile"
                        class="p-2 text-lg text-gray-600 transition-colors rounded-lg hover:bg-gray-100 hover:text-indigo-600">
                        <i class="fas fa-paperclip"></i>
                    </button>
                    <input type="file" id="fileInput" class="hidden" />
                    <div
                        class="flex-1 flex items-center gap-2 px-4 py-2.5 bg-gray-100 rounded-lg border border-gray-200 focus-within:border-indigo-600 focus-within:ring-2 focus-within:ring-indigo-200">
                        <input type="text" placeholder="Type your message..."
                            class="flex-1 text-sm text-gray-900 placeholder-gray-500 bg-transparent border-none outline-none" />
                        <button class="p-1 text-gray-500 transition-colors hover:text-indigo-600">
                            <i class="text-lg far fa-smile"></i>
                        </button>
                    </div>
                    <button
                        class="flex items-center justify-center w-10 h-10 text-white transition-all bg-indigo-600 rounded-lg shadow-md hover:bg-indigo-700 hover:shadow-lg">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../../src/components/Footer.php'; ?>

    <script src="../../assets/js/chats.js"></script>
    <script>
        // Profile dropdown toggle - Fixed version
        document.addEventListener('DOMContentLoaded', function() {
            const profileBtn = document.getElementById('profile-dropdown-btn');
            const profileDropdown = document.getElementById('profile-dropdown');

            if (profileBtn && profileDropdown) {
                profileBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    profileDropdown.classList.toggle('hidden');
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', (e) => {
                    if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
                        profileDropdown.classList.add('hidden');
                    }
                });

                // Prevent dropdown from closing when clicking inside it
                profileDropdown.addEventListener('click', (e) => {
                    e.stopPropagation();
                });
            }

            // Mobile sidebar toggle
            const toggleSidebarBtn = document.getElementById('toggleSidebar');
            const closeSidebarBtn = document.getElementById('closeSidebar');
            const chatSidebar = document.getElementById('chatSidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

            function openSidebar() {
                chatSidebar.classList.remove('-translate-x-full');
                sidebarOverlay.classList.remove('hidden');
            }

            function closeSidebar() {
                chatSidebar.classList.add('-translate-x-full');
                sidebarOverlay.classList.add('hidden');
            }

            if (toggleSidebarBtn) {
                toggleSidebarBtn.addEventListener('click', openSidebar);
            }

            if (closeSidebarBtn) {
                closeSidebarBtn.addEventListener('click', closeSidebar);
            }

            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', closeSidebar);
            }

            // Close sidebar when conversation is selected on mobile
            const conversations = document.querySelectorAll('.conversation');
            conversations.forEach(conv => {
                conv.addEventListener('click', () => {
                    if (window.innerWidth < 768) {
                        closeSidebar();
                    }
                });
            });
        });
    </script>
</body>

</html>