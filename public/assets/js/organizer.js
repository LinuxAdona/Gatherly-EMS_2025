// Organizer Dashboard JavaScript

// Profile dropdown toggle
const profileBtn = document.getElementById('profile-dropdown-btn');
const profileDropdown = document.getElementById('profile-dropdown');

if (profileBtn && profileDropdown) {
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
}

// AI Chatbot Modal Management
const openChatbotBtn = document.getElementById('openChatbot');
const closeChatbotBtn = document.getElementById('closeChatbot');
const chatbotModal = document.getElementById('chatbotModal');
const chatForm = document.getElementById('chatForm');
const chatInput = document.getElementById('chatInput');
const chatMessages = document.getElementById('chatMessages');

// Open chatbot modal
if (openChatbotBtn) {
    openChatbotBtn.addEventListener('click', () => {
        chatbotModal.classList.remove('hidden');
        chatInput.focus();
    });
}

// Close chatbot modal
if (closeChatbotBtn) {
    closeChatbotBtn.addEventListener('click', () => {
        chatbotModal.classList.add('hidden');
    });
}

// Close modal when clicking outside
if (chatbotModal) {
    chatbotModal.addEventListener('click', (e) => {
        if (e.target === chatbotModal) {
            chatbotModal.classList.add('hidden');
        }
    });
}

// Handle chat form submission
if (chatForm) {
    chatForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const message = chatInput.value.trim();
        if (!message) return;

        // Add user message to chat
        addUserMessage(message);
        chatInput.value = '';

        // Show typing indicator
        showTypingIndicator();

        try {
            // Send message to AI recommendation API
            const response = await fetch('../../../src/services/ai-recommendation.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    message: message,
                    context: 'venue_recommendation'
                })
            });

            const data = await response.json();
            
            // Remove typing indicator
            removeTypingIndicator();

            if (data.success) {
                // Add AI response to chat
                addBotMessage(data.response, data.venues);
            } else {
                addBotMessage('Sorry, I encountered an error. Please try again.');
            }
        } catch (error) {
            console.error('Chat error:', error);
            removeTypingIndicator();
            addBotMessage('Sorry, I\'m having trouble connecting. Please try again later.');
        }
    });
}

// Add user message to chat
function addUserMessage(message) {
    const messageDiv = document.createElement('div');
    messageDiv.className = 'flex items-start gap-3 mb-4 justify-end';
    messageDiv.innerHTML = `
        <div class="max-w-md p-4 bg-purple-600 text-white rounded-lg shadow-sm">
            <p>${escapeHtml(message)}</p>
        </div>
        <div class="flex items-center justify-center flex-shrink-0 w-8 h-8 bg-gray-300 rounded-full">
            <i class="text-gray-600 fas fa-user"></i>
        </div>
    `;
    chatMessages.appendChild(messageDiv);
    scrollToBottom();
}

// Add bot message to chat
function addBotMessage(message, venues = null) {
    const messageDiv = document.createElement('div');
    messageDiv.className = 'flex items-start gap-3 mb-4';
    
    let venueHTML = '';
    if (venues && venues.length > 0) {
        venueHTML = '<div class="mt-3 space-y-2">';
        venues.forEach(venue => {
            venueHTML += `
                <div class="p-3 border border-purple-200 rounded-lg bg-purple-50">
                    <h4 class="font-semibold text-purple-900">${escapeHtml(venue.name)}</h4>
                    <p class="text-sm text-gray-700">
                        <i class="mr-1 fas fa-users"></i> Capacity: ${venue.capacity}
                    </p>
                    <p class="text-sm text-gray-700">
                        <i class="mr-1 fas fa-peso-sign"></i> Price: ₱${formatNumber(venue.price)}
                    </p>
                    <p class="text-sm text-gray-700">
                        <i class="mr-1 fas fa-star"></i> Match Score: ${venue.score}%
                    </p>
                    <a href="venue-details.php?id=${venue.id}" class="inline-block mt-2 text-sm font-semibold text-purple-600 hover:text-purple-700">
                        View Details <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            `;
        });
        venueHTML += '</div>';
    }
    
    messageDiv.innerHTML = `
        <div class="flex items-center justify-center flex-shrink-0 w-8 h-8 bg-purple-600 rounded-full">
            <i class="text-white fas fa-robot"></i>
        </div>
        <div class="max-w-md p-4 bg-white rounded-lg shadow-sm">
            <p class="text-gray-800">${escapeHtml(message)}</p>
            ${venueHTML}
        </div>
    `;
    chatMessages.appendChild(messageDiv);
    scrollToBottom();
}

// Show typing indicator
function showTypingIndicator() {
    const typingDiv = document.createElement('div');
    typingDiv.id = 'typingIndicator';
    typingDiv.className = 'flex items-start gap-3 mb-4';
    typingDiv.innerHTML = `
        <div class="flex items-center justify-center flex-shrink-0 w-8 h-8 bg-purple-600 rounded-full">
            <i class="text-white fas fa-robot"></i>
        </div>
        <div class="p-4 bg-white rounded-lg shadow-sm">
            <div class="flex gap-1">
                <div class="w-2 h-2 bg-purple-600 rounded-full animate-bounce" style="animation-delay: 0ms;"></div>
                <div class="w-2 h-2 bg-purple-600 rounded-full animate-bounce" style="animation-delay: 150ms;"></div>
                <div class="w-2 h-2 bg-purple-600 rounded-full animate-bounce" style="animation-delay: 300ms;"></div>
            </div>
        </div>
    `;
    chatMessages.appendChild(typingDiv);
    scrollToBottom();
}

// Remove typing indicator
function removeTypingIndicator() {
    const typingIndicator = document.getElementById('typingIndicator');
    if (typingIndicator) {
        typingIndicator.remove();
    }
}

// Scroll chat to bottom
function scrollToBottom() {
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Format number with commas
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Close modal with Escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && chatbotModal && !chatbotModal.classList.contains('hidden')) {
        chatbotModal.classList.add('hidden');
    }
});
