<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Messages - Gatherly</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="../../src/output.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/chats.css">
</head>

<body class="bg-gray-50 dark:bg-gray-900 min-h-screen flex flex-col transition-colors duration-300">

    <!-- Navbar -->
    <nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 h-15">
        <div class="flex items-center justify-between px-10 py-3">
            <!-- Left Section -->
            <div class="flex items-center gap-10">
                <!-- Logo -->
                <div class="flex items-center gap-2 text-blue-600 dark:text-blue-400 font-bold text-lg">
                    <i class="fa-solid fa-gem text-xl"></i>
                    Gatherly
                </div>

                <!-- Nav Links -->
                <div class="flex items-center gap-6">
                    <a href="#"
                        class="flex items-center gap-2 px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition">
                        <i class="fa-solid fa-house"></i> Home
                    </a>
                    <a href="#"
                        class="flex items-center gap-2 px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition">
                        <i class="fa-solid fa-building-columns"></i> Venues
                    </a>
                    <a href="#"
                        class="flex items-center gap-2 px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition">
                        <i class="fa-solid fa-chart-line"></i> Analytics
                    </a>
                    <a href="#"
                        class="flex items-center gap-2 px-3 py-1.5 text-sm bg-blue-600 dark:bg-blue-500 text-white rounded transition">
                        <i class="fa-solid fa-message"></i> Messages
                    </a>
                </div>
            </div>

            <!-- Right Section -->
            <div class="flex items-center gap-2.5">
                <button id="toggleMode"
                    class="text-gray-600 dark:text-gray-300 text-xl hover:text-blue-600 dark:hover:text-blue-400 transition">
                    <i class="fa-solid fa-sun"></i>
                </button>
                <button
                    class="flex items-center gap-2 bg-blue-600 dark:bg-blue-500 text-white px-4 py-1.5 rounded text-sm hover:bg-blue-700 dark:hover:bg-blue-600 transition">
                    <i class="fa-solid fa-right-to-bracket"></i> Sign In
                </button>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="flex-grow px-16 py-10">
        <h2 class="text-3xl font-bold mb-6 text-gray-900 dark:text-gray-100">Messages</h2>

        <div class="flex gap-5 h-[calc(100vh-160px)]">

            <!-- Sidebar - Conversations List -->
            <div
                class="bg-white dark:bg-gray-800 w-80 border border-gray-200 dark:border-gray-700 rounded-lg p-5 flex flex-col">
                <h3 class="text-sm text-gray-600 dark:text-gray-400 mb-5">Conversations</h3>

                <!-- Conversation 1 -->
                <div class="conversation active flex items-center justify-between p-2.5 rounded-md cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                    data-chat="ballroom">
                    <div class="flex items-center gap-2.5">
                        <div
                            class="w-9 h-9 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-sm font-bold text-gray-600 dark:text-gray-300">
                            SA
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-900 dark:text-gray-100">Grand Ballroom</p>
                            <span class="text-xs text-gray-500 dark:text-gray-400">Yes, we have several dates available
                                in March.</span>
                        </div>
                    </div>
                    <span
                        class="badge bg-blue-600 dark:bg-blue-500 text-white text-xs rounded-full px-2 py-0.5">2</span>
                </div>

                <!-- Conversation 2 -->
                <div class="conversation flex items-center justify-between p-2.5 rounded-md cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                    data-chat="garden">
                    <div class="flex items-center gap-2.5">
                        <div
                            class="w-9 h-9 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-sm font-bold text-gray-600 dark:text-gray-300">
                            MI
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-900 dark:text-gray-100">Garden Paradise</p>
                            <span class="text-xs text-gray-500 dark:text-gray-400">The catering package
                                includes...</span>
                        </div>
                    </div>
                </div>

                <!-- Conversation 3 -->
                <div class="conversation flex items-center justify-between p-2.5 rounded-md cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                    data-chat="skyline">
                    <div class="flex items-center gap-2.5">
                        <div
                            class="w-9 h-9 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-sm font-bold text-gray-600 dark:text-gray-300">
                            EM
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-900 dark:text-gray-100">Skyline Rooftop</p>
                            <span class="text-xs text-gray-500 dark:text-gray-400">I can send you the contract
                                today.</span>
                        </div>
                    </div>
                    <span
                        class="badge bg-blue-600 dark:bg-blue-500 text-white text-xs rounded-full px-2 py-0.5">1</span>
                </div>
            </div>

            <!-- Chat Area -->
            <div id="chatArea"
                class="bg-white dark:bg-gray-800 flex-1 border border-gray-200 dark:border-gray-700 rounded-lg flex flex-col h-full">

                <!-- Chat Header -->
                <div class="chat-header px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h4 class="text-base font-bold text-gray-900 dark:text-gray-100 mb-1">Grand Ballroom Manager</h4>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Usually replies within an hour</span>
                </div>

                <!-- Chat Messages -->
                <div id="chatMessages" class="flex-grow px-5 py-5 flex flex-col gap-4 overflow-y-auto">
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
                <div
                    class="chat-input border-t border-gray-200 dark:border-gray-700 flex items-center px-4 py-2.5 gap-2.5">
                    <i id="attachFile"
                        class="fa-solid fa-paperclip text-lg text-gray-600 dark:text-gray-400 cursor-pointer hover:text-blue-600 dark:hover:text-blue-400 transition"></i>
                    <input type="file" id="fileInput" class="hidden" />
                    <input type="text" placeholder="Type your message..."
                        class="flex-1 border-none outline-none px-2.5 py-2 text-sm rounded-md bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                    <button
                        class="bg-blue-600 dark:bg-blue-500 text-white px-3.5 py-2 rounded-md text-base flex items-center justify-center hover:bg-blue-700 dark:hover:bg-blue-600 transition">
                        <i class="fa-solid fa-paper-plane text-white"></i>
                    </button>
                </div>

            </div>
        </div>
    </div>

    <script src="../../assets/js/chat.js"></script>
</body>

</html>