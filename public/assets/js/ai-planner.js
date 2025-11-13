// AI Planner Page JavaScript

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

// Chat functionality
const chatForm = document.getElementById('chatForm');
const chatInput = document.getElementById('chatInput');
const chatMessages = document.getElementById('chatMessages');
const clearChatBtn = document.getElementById('clearChat');

// Conversation state for multi-turn dialogue
let conversationState = {};

// Initialize with welcome message
window.addEventListener('DOMContentLoaded', () => {
    addBotMessage("Hello! 👋 I'm your AI event planning assistant. I'll help you find the perfect venue and suppliers for your event by asking you a few questions.\n\nLet's get started!\n\nWhat type of event are you planning? (e.g., wedding, corporate event, birthday party, concert)");
});

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
            // Send message to AI conversational planner API
            console.log('Sending message:', message);
            console.log('Conversation state:', conversationState);
            
            const response = await fetch('/home2/c2lkis/public_html/src/services/ai/ai-conversation.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    message: message,
                    conversation_state: conversationState
                })
            });

            console.log('Response status:', response.status);
            console.log('Response ok:', response.ok);
            
            const data = await response.json();
            console.log('Response data:', data);
            
            // Remove typing indicator
            removeTypingIndicator();

            if (data.success) {
                // Update conversation state with the returned state
                if (data.conversation_state) {
                    conversationState = data.conversation_state;
                    console.log('Updated conversation state:', conversationState);
                }
                
                // Add AI response to chat
                if (data.needs_more_info) {
                    // Still gathering information
                    addBotMessage(data.response);
                } else {
                    // Final recommendations
                    console.log('Generating final recommendations with:', {
                        venues: data.venues,
                        suppliers: data.suppliers
                    });
                    addBotMessage(data.response, data.venues, data.suppliers);
                }
            } else {
                // Show detailed error message
                let errorMsg = 'Sorry, I encountered an error: ' + (data.error || 'Unknown error');
                if (data.debug) {
                    errorMsg += '\n\nDebug info: ' + data.debug;
                }
                addBotMessage(errorMsg);
                console.error('API Error:', data.error);
                console.error('Full response:', data);
                if (data.debug) {
                    console.error('Debug info:', data.debug);
                }
            }
        } catch (error) {
            console.error('Chat error:', error);
            removeTypingIndicator();
            addBotMessage('Sorry, I\'m having trouble connecting. Please try again later.');
        }
    });
}

// Quick action buttons
const quickActionBtns = document.querySelectorAll('.quick-action');
quickActionBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        chatInput.value = btn.textContent.trim();
        chatInput.focus();
    });
});

// Clear chat button
if (clearChatBtn) {
    clearChatBtn.addEventListener('click', () => {
        if (confirm('Are you sure you want to start a new conversation? This will clear all messages.')) {
            chatMessages.innerHTML = '';
            conversationState = {};
            addBotMessage("Hello! 👋 I'm your AI event planning assistant. I'll help you find the perfect venue and suppliers for your event by asking you a few questions.\n\nLet's get started!\n\nWhat type of event are you planning? (e.g., wedding, corporate event, birthday party, concert)");
        }
    });
}

// Add user message to chat
function addUserMessage(message) {
    const messageDiv = document.createElement('div');
    messageDiv.className = 'flex items-start justify-end gap-3 mb-4';
    messageDiv.innerHTML = `
        <div class="max-w-lg p-4 bg-indigo-600 text-white rounded-2xl shadow-md">
            <p class="leading-relaxed">${escapeHtml(message)}</p>
        </div>
        <div class="flex items-center justify-center shrink-0 w-10 h-10 bg-gray-300 rounded-full">
            <i class="text-gray-600 fas fa-user"></i>
        </div>
    `;
    chatMessages.appendChild(messageDiv);
    scrollToBottom();
}

// Add bot message to chat
function addBotMessage(message, venues = null, suppliers = null) {
    const messageDiv = document.createElement('div');
    messageDiv.className = 'flex items-start gap-3 mb-4';
    
    let contentHTML = '';
    
    // Venue recommendations
    if (venues && venues.length > 0) {
        contentHTML += '<div class="mt-4"><h4 class="font-bold text-indigo-900 mb-3 text-lg">🏛️ Venue Recommendations:</h4><div class="space-y-3">';
        venues.forEach(venue => {
            contentHTML += `
                <div class="p-4 border-2 border-indigo-300 rounded-xl bg-indigo-50 hover:bg-indigo-100 transition-all shadow-sm">
                    <div class="flex items-start justify-between mb-2">
                        <h5 class="font-bold text-indigo-900 text-lg">${escapeHtml(venue.name)}</h5>
                        <span class="px-3 py-1 text-sm font-bold text-green-700 bg-green-100 rounded-full">
                            <i class="mr-1 fas fa-star"></i> ${venue.score}% Match
                        </span>
                    </div>
                    <p class="text-sm text-gray-700 mb-2">
                        <i class="mr-2 fas fa-users text-indigo-600"></i> Capacity: <strong>${venue.capacity}</strong> guests
                    </p>
                    <p class="text-sm text-gray-700 mb-2">
                        <i class="mr-2 fas fa-peso-sign text-indigo-600"></i> Base Price: <strong>₱${formatNumber(venue.price)}</strong>
                    </p>
                    <p class="text-sm text-gray-700 mb-2">
                        <i class="mr-2 fas fa-map-marker-alt text-indigo-600"></i> ${escapeHtml(venue.location)}
                    </p>
                    <p class="text-sm text-gray-600 mb-3 leading-relaxed">${escapeHtml(venue.description)}</p>
                    ${venue.amenities ? `<p class="text-xs text-indigo-700 mb-2"><i class="mr-1 fas fa-check-circle"></i> ${escapeHtml(venue.amenities)}</p>` : ''}
                    <a href="venue-details.php?id=${venue.id}" class="inline-block px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">
                        View Details <i class="ml-1 fas fa-arrow-right"></i>
                    </a>
                </div>
            `;
        });
        contentHTML += '</div></div>';
    }
    
    // Supplier recommendations
    if (suppliers && Object.keys(suppliers).length > 0) {
        contentHTML += '<div class="mt-5"><h4 class="font-bold text-indigo-900 mb-3 text-lg">👥 Recommended Suppliers:</h4>';
        
        for (const [category, services] of Object.entries(suppliers)) {
            if (services && services.length > 0) {
                // Get category icon
                const icons = {
                    'Catering': '🍽️',
                    'Lights and Sounds': '🎵',
                    'Photography': '📸',
                    'Videography': '🎥',
                    'Host/Emcee': '🎤',
                    'Styling and Flowers': '💐',
                    'Equipment Rental': '🪑'
                };
                const icon = icons[category] || '📋';
                
                contentHTML += `<div class="mb-4"><h5 class="font-semibold text-base text-gray-800 mb-2 flex items-center gap-2"><span>${icon}</span> ${category}</h5><div class="space-y-2">`;
                
                services.forEach(service => {
                    contentHTML += `
                        <div class="p-3 border-2 border-blue-200 rounded-lg bg-blue-50 hover:bg-blue-100 transition-colors shadow-sm">
                            <div class="flex justify-between items-start gap-3">
                                <div class="flex-1">
                                    <h6 class="font-bold text-blue-900 mb-1">${escapeHtml(service.service_name)}</h6>
                                    <p class="text-sm text-gray-700 mb-1">
                                        <i class="mr-1 fas fa-store text-blue-600"></i> ${escapeHtml(service.supplier_name)}
                                    </p>
                                    <p class="text-xs text-gray-600 mb-2 leading-relaxed">${escapeHtml(service.description)}</p>
                                    <p class="text-xs text-gray-600">
                                        <i class="mr-1 fas fa-map-marker-alt text-blue-600"></i> ${escapeHtml(service.location)}
                                    </p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-lg font-bold text-green-600">₱${formatNumber(service.price)}</p>
                                    <p class="text-xs text-gray-500">per event</p>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                contentHTML += '</div></div>';
            }
        }
        contentHTML += '</div>';
    }
    
    messageDiv.innerHTML = `
        <div class="flex items-center justify-center shrink-0 w-10 h-10 bg-indigo-600 rounded-full">
            <i class="text-white fas fa-robot"></i>
        </div>
        <div class="max-w-3xl p-4 bg-white rounded-2xl shadow-md border-2 border-gray-200">
            <p class="text-gray-800 whitespace-pre-line leading-relaxed">${escapeHtml(message)}</p>
            ${contentHTML}
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
        <div class="flex items-center justify-center shrink-0 w-10 h-10 bg-indigo-600 rounded-full">
            <i class="text-white fas fa-robot"></i>
        </div>
        <div class="p-4 bg-white rounded-2xl shadow-md border-2 border-gray-200">
            <div class="flex gap-1">
                <div class="w-2 h-2 bg-indigo-600 rounded-full animate-bounce" style="animation-delay: 0ms;"></div>
                <div class="w-2 h-2 bg-indigo-600 rounded-full animate-bounce" style="animation-delay: 150ms;"></div>
                <div class="w-2 h-2 bg-indigo-600 rounded-full animate-bounce" style="animation-delay: 300ms;"></div>
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

// Close dropdown with Escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && profileDropdown && !profileDropdown.classList.contains('hidden')) {
        profileDropdown.classList.add('hidden');
    }
});
