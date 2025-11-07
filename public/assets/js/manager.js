// Manager Dashboard JavaScript

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

// Dynamic Pricing Tool Modal Management
const openPricingToolBtn = document.getElementById('openPricingTool');
const closePricingToolBtn = document.getElementById('closePricingTool');
const pricingModal = document.getElementById('pricingModal');
const calculatePriceBtn = document.getElementById('calculatePrice');
const pricingResult = document.getElementById('pricingResult');
const optimalPriceDisplay = document.getElementById('optimalPrice');

// Open pricing tool modal
if (openPricingToolBtn) {
    openPricingToolBtn.addEventListener('click', () => {
        pricingModal.classList.remove('hidden');
        pricingModal.classList.add('flex');
    });
}

// Close pricing tool modal
if (closePricingToolBtn) {
    closePricingToolBtn.addEventListener('click', () => {
        pricingModal.classList.add('hidden');
        pricingModal.classList.remove('flex');
    });
}

// Close modal when clicking outside
if (pricingModal) {
    pricingModal.addEventListener('click', (e) => {
        if (e.target === pricingModal) {
            pricingModal.classList.add('hidden');
            pricingModal.classList.remove('flex');
        }
    });
}

// Calculate optimal price
if (calculatePriceBtn) {
    calculatePriceBtn.addEventListener('click', () => {
        const basePrice = parseFloat(document.getElementById('basePrice').value) || 0;
        const seasonFactor = parseFloat(document.getElementById('season').value) || 1.0;
        const dayTypeFactor = parseFloat(document.getElementById('dayType').value) || 1.0;
        const demandFactor = parseFloat(document.getElementById('demand').value) || 1.0;

        // Calculate optimal price using dynamic pricing formula
        const optimalPrice = basePrice * seasonFactor * dayTypeFactor * demandFactor;

        // Display result
        if (pricingResult && optimalPriceDisplay) {
            optimalPriceDisplay.textContent = '₱' + formatNumber(optimalPrice.toFixed(2));
            pricingResult.classList.remove('hidden');
        }
    });
}

// Close modal with Escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && pricingModal && !pricingModal.classList.contains('hidden')) {
        pricingModal.classList.add('hidden');
        pricingModal.classList.remove('flex');
    }
});

// Venue Performance Chart
document.addEventListener('DOMContentLoaded', function() {
    initializeVenuePerformanceChart();
});

function initializeVenuePerformanceChart() {
    const ctx = document.getElementById('venuePerformanceChart');
    if (!ctx) return;

    // Sample data - in production, this would come from the server
    const venueData = {
        labels: ['Crystal Hall', 'Aurora Pavilion', 'Emerald Garden', 'Sunset Veranda', 'Grand Ballroom'],
        bookings: [15, 12, 10, 8, 6],
        revenue: [750000, 600000, 450000, 400000, 300000]
    };

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: venueData.labels,
            datasets: [
                {
                    label: 'Bookings',
                    data: venueData.bookings,
                    backgroundColor: 'rgba(34, 197, 94, 0.7)',
                    borderColor: 'rgba(34, 197, 94, 1)',
                    borderWidth: 2,
                    yAxisID: 'y',
                    order: 2
                },
                {
                    label: 'Revenue (₱)',
                    data: venueData.revenue,
                    type: 'line',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: 'rgba(59, 130, 246, 1)',
                    yAxisID: 'y1',
                    order: 1,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 15,
                        font: {
                            size: 12,
                            family: 'Montserrat'
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: {
                        size: 14,
                        family: 'Montserrat'
                    },
                    bodyFont: {
                        size: 13,
                        family: 'Montserrat'
                    },
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                if (context.datasetIndex === 1) {
                                    label += '₱' + formatNumber(context.parsed.y);
                                } else {
                                    label += context.parsed.y + ' booking(s)';
                                }
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 11,
                            family: 'Montserrat'
                        },
                        maxRotation: 45,
                        minRotation: 0
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Bookings',
                        font: {
                            size: 12,
                            family: 'Montserrat',
                            weight: 'bold'
                        }
                    },
                    ticks: {
                        stepSize: 2,
                        font: {
                            size: 11,
                            family: 'Montserrat'
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Revenue (₱)',
                        font: {
                            size: 12,
                            family: 'Montserrat',
                            weight: 'bold'
                        }
                    },
                    ticks: {
                        callback: function(value) {
                            return '₱' + formatNumber(value);
                        },
                        font: {
                            size: 11,
                            family: 'Montserrat'
                        }
                    },
                    grid: {
                        drawOnChartArea: false
                    }
                }
            }
        }
    });
}

// Format number with commas
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
