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

// Revenue Chart Management
let revenueChart = null;
let currentYear = new Date().getFullYear();
let currentMonth = null;

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeRevenueChart();
    setupRevenueEventListeners();
});

function setupRevenueEventListeners() {
    const yearSelect = document.getElementById('yearSelect');
    const monthSelect = document.getElementById('monthSelect');
    
    if (yearSelect) {
        yearSelect.addEventListener('change', function() {
            currentYear = this.value;
            loadRevenueData();
        });
    }
    
    if (monthSelect) {
        monthSelect.addEventListener('change', function() {
            currentMonth = this.value || null;
            loadRevenueData();
        });
    }
}

async function initializeRevenueChart() {
    try {
        // First, load available years
        const response = await fetch('../../../src/services/get-revenue-data.php');
        const data = await response.json();
        
        if (data.available_years && data.available_years.length > 0) {
            populateYearSelect(data.available_years);
            currentYear = data.available_years[0]; // Most recent year
            await loadRevenueData();
        } else {
            console.error('No revenue data available');
            showNoDataMessage();
        }
    } catch (error) {
        console.error('Error initializing chart:', error);
        showErrorMessage();
    }
}

function populateYearSelect(years) {
    const yearSelect = document.getElementById('yearSelect');
    if (!yearSelect) return;
    
    yearSelect.innerHTML = '';
    years.forEach(year => {
        const option = document.createElement('option');
        option.value = year;
        option.textContent = year;
        if (year === currentYear) {
            option.selected = true;
        }
        yearSelect.appendChild(option);
    });
}

async function loadRevenueData() {
    try {
        const url = new URL('../../../src/services/get-revenue-data.php', window.location.href);
        url.searchParams.append('year', currentYear);
        if (currentMonth) {
            url.searchParams.append('month', currentMonth);
        }
        
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.error) {
            console.error('Error:', data.error);
            showErrorMessage();
            return;
        }
        
        updateSummaryStats(data);
        updateChart(data);
    } catch (error) {
        console.error('Error loading revenue data:', error);
        showErrorMessage();
    }
}

function updateSummaryStats(data) {
    const periodLabel = document.getElementById('periodLabel');
    const totalRevenue = document.getElementById('totalRevenue');
    const totalEvents = document.getElementById('totalEvents');
    
    if (periodLabel) {
        periodLabel.textContent = data.period_label || 'N/A';
    }
    
    if (totalRevenue) {
        totalRevenue.textContent = '₱' + formatNumber(data.total_revenue || 0);
    }
    
    if (totalEvents) {
        totalEvents.textContent = formatNumber(data.total_events || 0);
    }
}

function updateChart(data) {
    const ctx = document.getElementById('revenueChart');
    if (!ctx) return;
    
    // Destroy existing chart if it exists
    if (revenueChart) {
        revenueChart.destroy();
    }
    
    const labels = data.data.map(item => item.label);
    const revenues = data.data.map(item => item.revenue);
    const eventCounts = data.data.map(item => item.event_count);
    
    revenueChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Revenue (₱)',
                    data: revenues,
                    backgroundColor: 'rgba(251, 191, 36, 0.7)',
                    borderColor: 'rgba(251, 191, 36, 1)',
                    borderWidth: 2,
                    yAxisID: 'y',
                    order: 2
                },
                {
                    label: 'Number of Events',
                    data: eventCounts,
                    type: 'line',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    borderColor: 'rgba(99, 102, 241, 1)',
                    borderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: 'rgba(99, 102, 241, 1)',
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
                                if (context.datasetIndex === 0) {
                                    label += '₱' + formatNumber(context.parsed.y);
                                } else {
                                    label += context.parsed.y + ' event(s)';
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
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Number of Events',
                        font: {
                            size: 12,
                            family: 'Montserrat',
                            weight: 'bold'
                        }
                    },
                    ticks: {
                        stepSize: 1,
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

function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function showNoDataMessage() {
    const chartContainer = document.getElementById('revenueChart');
    if (chartContainer) {
        chartContainer.parentElement.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500"><p>No revenue data available</p></div>';
    }
}

function showErrorMessage() {
    const chartContainer = document.getElementById('revenueChart');
    if (chartContainer) {
        chartContainer.parentElement.innerHTML = '<div class="flex items-center justify-center h-full text-red-500"><p>Error loading revenue data</p></div>';
    }
}