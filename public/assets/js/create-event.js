// Create Event Page JavaScript

// Profile dropdown toggle
const profileBtn = document.getElementById('profile-dropdown-btn');
const profileDropdown = document.getElementById('profile-dropdown');

if (profileBtn && profileDropdown) {
    profileBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        profileDropdown.classList.toggle('hidden');
    });

    document.addEventListener('click', (e) => {
        if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
            profileDropdown.classList.add('hidden');
        }
    });
}

// Venue selection handling
const venueCards = document.querySelectorAll('.venue-card');
venueCards.forEach(card => {
    card.addEventListener('click', function() {
        // Deselect all venue cards
        venueCards.forEach(c => {
            c.classList.remove('border-indigo-500', 'bg-indigo-50', 'shadow-lg');
            c.classList.add('border-gray-200');
        });
        
        // Select this card
        this.classList.remove('border-gray-200');
        this.classList.add('border-indigo-500', 'bg-indigo-50', 'shadow-lg');
        
        // Check the radio button
        const radio = this.querySelector('input[type="radio"]');
        radio.checked = true;
        
        // Update cost
        updateCostSummary();
    });
});

// Service checkbox handling
const serviceCheckboxes = document.querySelectorAll('.service-checkbox');
serviceCheckboxes.forEach(checkbox => {
    checkbox.addEventListener('change', updateCostSummary);
});

// Update cost summary
function updateCostSummary() {
    let venueCost = 0;
    let servicesCost = 0;
    
    // Get venue cost
    const selectedVenue = document.querySelector('input[name="venue_id"]:checked');
    if (selectedVenue) {
        const venueCard = selectedVenue.closest('.venue-card');
        venueCost = parseFloat(venueCard.dataset.venuePrice) || 0;
    }
    
    // Get services cost
    const selectedServices = document.querySelectorAll('.service-checkbox:checked');
    selectedServices.forEach(service => {
        servicesCost += parseFloat(service.dataset.price) || 0;
    });
    
    // Calculate total
    const totalCost = venueCost + servicesCost;
    
    // Update display
    document.getElementById('venue-cost').textContent = '₱' + formatNumber(venueCost.toFixed(2));
    document.getElementById('services-cost').textContent = '₱' + formatNumber(servicesCost.toFixed(2));
    document.getElementById('total-cost').textContent = '₱' + formatNumber(totalCost.toFixed(2));
    document.getElementById('total_cost').value = totalCost.toFixed(2);
}

// Format number with commas
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Form submission
const createEventForm = document.getElementById('createEventForm');
if (createEventForm) {
    createEventForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const submitBtn = document.getElementById('submitBtn');
        const originalText = submitBtn.innerHTML;
        
        // Validate form
        if (!validateForm()) {
            return;
        }
        
        // Disable submit button
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Creating Event...';
        
        // Gather form data
        const formData = new FormData(createEventForm);
        
        try {
            const response = await fetch('../../../src/services/create-event-handler.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showAlert('success', 'Event created successfully! Redirecting...', data.message);
                
                // Redirect after 2 seconds
                setTimeout(() => {
                    window.location.href = 'organizer-dashboard.php';
                }, 2000);
            } else {
                showAlert('error', 'Error creating event', data.error || 'Please try again.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('error', 'Connection Error', 'Unable to create event. Please try again.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
}

// Form validation
function validateForm() {
    const eventName = document.getElementById('event_name').value.trim();
    const eventType = document.getElementById('event_type').value;
    const expectedGuests = document.getElementById('expected_guests').value;
    const eventDate = document.getElementById('event_date').value;
    const selectedVenue = document.querySelector('input[name="venue_id"]:checked');
    
    if (!eventName) {
        showAlert('error', 'Validation Error', 'Please enter an event name.');
        return false;
    }
    
    if (!eventType) {
        showAlert('error', 'Validation Error', 'Please select an event type.');
        return false;
    }
    
    if (!expectedGuests || expectedGuests < 1) {
        showAlert('error', 'Validation Error', 'Please enter a valid number of expected guests.');
        return false;
    }
    
    if (!eventDate) {
        showAlert('error', 'Validation Error', 'Please select an event date and time.');
        return false;
    }
    
    // Check if date is in the future
    const selectedDate = new Date(eventDate);
    const now = new Date();
    if (selectedDate < now) {
        showAlert('error', 'Validation Error', 'Event date must be in the future.');
        return false;
    }
    
    if (!selectedVenue) {
        showAlert('error', 'Validation Error', 'Please select a venue.');
        return false;
    }
    
    // Check venue capacity vs expected guests
    const venueCard = selectedVenue.closest('.venue-card');
    const venueCapacityText = venueCard.querySelector('.fa-users').parentElement.textContent;
    const venueCapacity = parseInt(venueCapacityText.match(/\d+/)[0]);
    
    if (parseInt(expectedGuests) > venueCapacity) {
        showAlert('warning', 'Capacity Warning', 
            `The selected venue has a capacity of ${venueCapacity} guests, but you're expecting ${expectedGuests} guests. Consider selecting a larger venue.`);
        return confirm('Do you want to proceed anyway?');
    }
    
    return true;
}

// Show alert message
function showAlert(type, title, message) {
    const alertContainer = document.getElementById('alertContainer');
    
    const alertColors = {
        success: 'bg-green-100 border-green-500 text-green-800',
        error: 'bg-red-100 border-red-500 text-red-800',
        warning: 'bg-yellow-100 border-yellow-500 text-yellow-800',
        info: 'bg-blue-100 border-blue-500 text-blue-800'
    };
    
    const alertIcons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `${alertColors[type]} border-l-4 p-4 rounded-lg shadow-lg mb-4`;
    alertDiv.innerHTML = `
        <div class="flex items-start">
            <i class="fas ${alertIcons[type]} text-2xl mr-3 mt-1"></i>
            <div class="flex-1">
                <p class="font-bold text-lg">${title}</p>
                <p class="mt-1">${message}</p>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="text-2xl ml-4 hover:opacity-70">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    alertContainer.appendChild(alertDiv);
    
    // Auto-remove after 5 seconds for success messages
    if (type === 'success') {
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
    
    // Scroll to top to show alert
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Set minimum date to today
const eventDateInput = document.getElementById('event_date');
if (eventDateInput) {
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    eventDateInput.min = now.toISOString().slice(0, 16);
}

// Close dropdown with Escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && profileDropdown && !profileDropdown.classList.contains('hidden')) {
        profileDropdown.classList.add('hidden');
    }
});
