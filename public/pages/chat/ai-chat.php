<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../signin.php');
    exit();
}

require_once '../../../src/services/dbconnect.php';
require_once '../../../src/models/ChatbotEngine.php';
require_once '../../../src/models/RecommendationEngine.php';
require_once '../../../src/models/DynamicPricingEngine.php';

$userId = $_SESSION['user_id'];

// Initialize engines
$recommendationEngine = new RecommendationEngine($conn);
$pricingEngine = new DynamicPricingEngine($conn);
$chatbot = new ChatbotEngine($conn, $recommendationEngine, $pricingEngine);

// Handle AJAX message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    header('Content-Type: application/json');
    $message = $_POST['message'];
    $response = $chatbot->processMessage($userId, $message);
    echo json_encode($response);
    exit();
}

// Get conversation history
$history = $chatbot->getConversationHistory($userId, 20);

// Get user info
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Venue Assistant | Gatherly</title>
    <link rel="icon" type="image/x-icon" href="../../assets/images/logo.png">
    <link rel="stylesheet" href="../../../src/output.css">
    <script src="https://kit.fontawesome.com/2a99de0fa5.js" crossorigin="anonymous"></script>
    <style>
        .chat-container {
            height: calc(100vh - 200px);
            display: flex;
            flex-direction: column;
        }

        .messages-container {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }

        .message {
            margin-bottom: 16px;
            display: flex;
        }

        .message.user {
            justify-content: flex-end;
        }

        .message.bot {
            justify-content: flex-start;
        }

        .message-bubble {
            max-width: 70%;
            padding: 12px 16px;
            border-radius: 16px;
            position: relative;
        }

        .message.user .message-bubble {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-bottom-right-radius: 4px;
        }

        .message.bot .message-bubble {
            background: #f3f4f6;
            color: #1f2937;
            border-bottom-left-radius: 4px;
        }

        .typing-indicator {
            display: none;
            padding: 12px;
        }

        .typing-indicator span {
            height: 8px;
            width: 8px;
            background: #9ca3af;
            border-radius: 50%;
            display: inline-block;
            margin: 0 2px;
            animation: typing 1.4s infinite;
        }

        .typing-indicator span:nth-child(2) {
            animation-delay: 0.2s;
        }

        .typing-indicator span:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes typing {

            0%,
            60%,
            100% {
                transform: translateY(0);
            }

            30% {
                transform: translateY(-10px);
            }
        }

        .suggestion-chip {
            display: inline-block;
            padding: 8px 16px;
            margin: 4px;
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 14px;
        }

        .suggestion-chip:hover {
            background: #f3f4f6;
            border-color: #667eea;
        }
    </style>
</head>

<body class="bg-gray-100 font-['Montserrat']">
    <!-- Navigation -->
    <nav class="sticky top-0 z-50 w-full shadow-md bg-white border-b border-gray-200">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="../home.php" class="flex items-center group">
                    <img class="w-8 h-8 mr-2 transition-transform group-hover:scale-110" src="../../assets/images/logo.png" alt="Logo">
                    <span class="text-xl font-bold text-gray-800">Gatherly</span>
                </a>
                <div class="flex items-center gap-4">
                    <a href="../venue/search.php" class="text-sm font-semibold text-gray-700 hover:text-indigo-600">Search Venues</a>
                    <a href="../dashboard.php" class="text-sm font-semibold text-gray-700 hover:text-indigo-600">Dashboard</a>
                    <span class="text-sm text-gray-600">Welcome, <?= htmlspecialchars($user['first_name']) ?></span>
                    <a href="../../services/logout.php" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-5xl mx-auto px-4 py-6">
        <!-- Header -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-robot text-white text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">AI Venue Assistant</h1>
                    <p class="text-gray-600">Ask me anything about venues! I'm here to help you find the perfect match.</p>
                </div>
            </div>
        </div>

        <!-- Chat Container -->
        <div class="bg-white rounded-xl shadow-lg chat-container">
            <!-- Messages -->
            <div class="messages-container" id="messagesContainer">
                <!-- Welcome Message -->
                <div class="message bot">
                    <div class="message-bubble">
                        <p class="font-semibold mb-2">👋 Hello <?= htmlspecialchars($user['first_name']) ?>!</p>
                        <p>I'm your AI venue assistant. I can help you:</p>
                        <ul class="list-disc ml-5 mt-2 space-y-1">
                            <li>Find venues matching your requirements</li>
                            <li>Compare prices and features</li>
                            <li>Check availability</li>
                            <li>Get recommendations based on your event</li>
                        </ul>
                        <p class="mt-3">Try asking me something like:</p>
                        <div class="mt-2">
                            <span class="suggestion-chip" onclick="sendSuggestion(this)">Find a wedding venue for 200 guests</span>
                            <span class="suggestion-chip" onclick="sendSuggestion(this)">Show me venues under 100K</span>
                            <span class="suggestion-chip" onclick="sendSuggestion(this)">Which venues have parking?</span>
                        </div>
                    </div>
                </div>

                <!-- Previous Conversation -->
                <?php foreach ($history as $chat): ?>
                    <?php if ($chat['message']): ?>
                        <div class="message user">
                            <div class="message-bubble">
                                <?= nl2br(htmlspecialchars($chat['message'])) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($chat['response']): ?>
                        <div class="message bot">
                            <div class="message-bubble">
                                <?= nl2br(htmlspecialchars($chat['response'])) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <!-- Typing Indicator -->
                <div class="message bot typing-indicator" id="typingIndicator">
                    <div class="message-bubble">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </div>

            <!-- Input Area -->
            <div class="border-t border-gray-200 p-4">
                <form id="chatForm" class="flex gap-2">
                    <input
                        type="text"
                        id="messageInput"
                        placeholder="Ask me anything... (e.g., 'Find a birthday venue for 150 guests under 120K')"
                        class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        autocomplete="off">
                    <button
                        type="submit"
                        class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-semibold">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
                <p class="text-xs text-gray-500 mt-2">
                    💡 Tip: Be specific! Include event type, guest count, and budget for best results.
                </p>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
            <a href="../venue/search.php" class="p-4 bg-white rounded-lg shadow hover:shadow-lg transition-shadow text-center">
                <i class="fas fa-search text-indigo-600 text-2xl mb-2"></i>
                <p class="font-semibold text-gray-800">Advanced Search</p>
                <p class="text-sm text-gray-600">Use filters and see map</p>
            </a>
            <a href="../dashboard.php" class="p-4 bg-white rounded-lg shadow hover:shadow-lg transition-shadow text-center">
                <i class="fas fa-chart-bar text-indigo-600 text-2xl mb-2"></i>
                <p class="font-semibold text-gray-800">View Analytics</p>
                <p class="text-sm text-gray-600">See booking trends</p>
            </a>
            <a href="venue-chat.php" class="p-4 bg-white rounded-lg shadow hover:shadow-lg transition-shadow text-center">
                <i class="fas fa-comments text-indigo-600 text-2xl mb-2"></i>
                <p class="font-semibold text-gray-800">Chat with Venues</p>
                <p class="text-sm text-gray-600">Talk to venue managers</p>
            </a>
        </div>
    </div>

    <script>
        const messagesContainer = document.getElementById('messagesContainer');
        const chatForm = document.getElementById('chatForm');
        const messageInput = document.getElementById('messageInput');
        const typingIndicator = document.getElementById('typingIndicator');

        // Auto-scroll to bottom
        function scrollToBottom() {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        // Send message
        chatForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const message = messageInput.value.trim();
            if (!message) return;

            // Add user message to chat
            addMessage(message, 'user');
            messageInput.value = '';

            // Show typing indicator
            typingIndicator.style.display = 'flex';
            scrollToBottom();

            try {
                // Send to server
                const formData = new FormData();
                formData.append('message', message);

                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                // Hide typing indicator
                typingIndicator.style.display = 'none';

                // Add bot response
                addMessage(data.text, 'bot', data);
                scrollToBottom();

            } catch (error) {
                typingIndicator.style.display = 'none';
                addMessage('Sorry, I encountered an error. Please try again.', 'bot');
                console.error('Error:', error);
            }
        });

        // Add message to chat
        function addMessage(text, sender, data = null) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${sender}`;

            const bubbleDiv = document.createElement('div');
            bubbleDiv.className = 'message-bubble';
            bubbleDiv.innerHTML = formatMessage(text);

            // Add suggestions if available
            if (data && data.suggestions) {
                const suggestionsDiv = document.createElement('div');
                suggestionsDiv.className = 'mt-2';
                data.suggestions.forEach(suggestion => {
                    const chip = document.createElement('span');
                    chip.className = 'suggestion-chip';
                    chip.textContent = suggestion;
                    chip.onclick = () => sendSuggestion(chip);
                    suggestionsDiv.appendChild(chip);
                });
                bubbleDiv.appendChild(suggestionsDiv);
            }

            messageDiv.appendChild(bubbleDiv);
            messagesContainer.insertBefore(messageDiv, typingIndicator);
        }

        // Format message (convert markdown-like syntax)
        function formatMessage(text) {
            text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            text = text.replace(/\n/g, '<br>');
            return text;
        }

        // Send suggestion
        function sendSuggestion(element) {
            messageInput.value = element.textContent;
            messageInput.focus();
        }

        // Initialize
        scrollToBottom();

        // Auto-focus input
        messageInput.focus();
    </script>
</body>

</html>