<?php
include '../../src/services/dbconnect.php';

// Fetch Data
function safeQuery($conn, $sql, $default = 0)
{
    $res = $conn->query($sql);
    return ($res && $row = $res->fetch_assoc()) ? reset($row) : $default;
}

$totalBookings = safeQuery($conn, "SELECT COUNT(*) FROM events WHERE status IN ('confirmed','completed')");
$totalRevenue = safeQuery($conn, "SELECT IFNULL(SUM(total_cost),0) FROM events WHERE status IN ('confirmed','completed')");
$activeVenues = safeQuery($conn, "SELECT COUNT(*) FROM venues WHERE availability_status='available'");
$avgMatchScore = safeQuery($conn, "SELECT ROUND(AVG(suitability_score),2) FROM recommendations");

// Charts and analytics queries
$bookingTrends = $conn->query("SELECT DATE_FORMAT(event_date, '%b') AS month, COUNT(*) AS count FROM events WHERE status IN ('confirmed','completed') GROUP BY MONTH(event_date) ORDER BY MONTH(event_date)");
$eventTypes = $conn->query("SELECT event_type, COUNT(*) AS total FROM events WHERE event_type IS NOT NULL GROUP BY event_type");
$revenueForecast = $conn->query("SELECT DATE_FORMAT(event_date, '%b') AS month, SUM(total_cost) AS revenue FROM events WHERE status IN ('confirmed','completed') GROUP BY MONTH(event_date) ORDER BY MONTH(event_date)");
$topVenues = $conn->query("SELECT v.venue_name, COUNT(e.event_id) AS total_booked FROM events e JOIN venues v ON e.venue_id = v.venue_id WHERE e.status IN ('confirmed','completed') GROUP BY v.venue_name ORDER BY total_booked DESC LIMIT 3");
$avgBudget = $conn->query("SELECT event_type, ROUND(AVG(total_cost),2) AS avg_budget FROM events WHERE status IN ('confirmed','completed') AND event_type IS NOT NULL GROUP BY event_type");
$occupancy = $conn->query("SELECT DATE_FORMAT(event_date, '%b') AS month, COUNT(DISTINCT venue_id) AS booked_venues, (SELECT COUNT(*) FROM venues) AS total_venues FROM events WHERE status IN ('confirmed','completed') GROUP BY MONTH(event_date) ORDER BY MONTH(event_date)");

// Convert to arrays for charts
$bookingTrendsData = [];
while ($r = $bookingTrends->fetch_assoc()) $bookingTrendsData[] = $r;

$eventTypeData = [];
while ($r = $eventTypes->fetch_assoc()) $eventTypeData[] = $r;

$revenueData = [];
while ($r = $revenueForecast->fetch_assoc()) $revenueData[] = $r;

$topVenuesData = [];
while ($r = $topVenues->fetch_assoc()) $topVenuesData[] = $r;

$avgBudgetData = [];
while ($r = $avgBudget->fetch_assoc()) $avgBudgetData[] = $r;

$occupancyData = [];
while ($r = $occupancy->fetch_assoc()) $occupancyData[] = $r;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GEMS Dashboard | Analytics</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        canvas {
            width: 100% !important;
            height: 100% !important;
        }

        @media (max-width: 640px) {
            body {
                font-size: 14px;
            }
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-900">

    <!-- Navbar -->
    <nav class="flex items-center justify-between px-4 sm:px-6 md:px-10 py-4 bg-white shadow-md border-b border-gray-200">
        <h1 class="text-lg sm:text-xl md:text-2xl font-bold text-indigo-600">GEMS Dashboard</h1>
        <button class="md:hidden p-2 text-gray-700 hover:bg-gray-100 rounded-lg">
            <i data-lucide="menu" class="w-6 h-6"></i>
        </button>
    </nav>

    <!-- Dashboard Section -->
    <section class="max-w-7xl mx-auto mt-6 sm:mt-8 p-4 sm:p-6">
        <h2 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6 text-gray-900">Analytics Dashboard</h2>

        <!-- Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5 mb-6 sm:mb-8">
            <!-- Total Bookings -->
            <div class="relative p-5 sm:p-6 bg-white shadow-lg rounded-xl text-center hover:shadow-xl transition-shadow border border-gray-100">
                <div class="absolute top-3 right-3 text-gray-400">
                    <i data-lucide="calendar-check" class="w-5 h-5"></i>
                </div>
                <h3 class="text-xs sm:text-sm text-gray-500 uppercase tracking-wide font-medium">Total Bookings</h3>
                <p class="text-2xl sm:text-3xl font-bold text-indigo-600 mt-2"><?= $totalBookings ?></p>
            </div>

            <!-- Revenue -->
            <div class="relative p-5 sm:p-6 bg-white shadow-lg rounded-xl text-center hover:shadow-xl transition-shadow border border-gray-100">
                <div class="absolute top-3 right-3 text-gray-400">
                    <i data-lucide="dollar-sign" class="w-5 h-5"></i>
                </div>
                <h3 class="text-xs sm:text-sm text-gray-500 uppercase tracking-wide font-medium">Revenue</h3>
                <p class="text-xl sm:text-2xl md:text-3xl font-bold text-green-600 mt-2">₱<?= number_format($totalRevenue, 2) ?></p>
            </div>

            <!-- Avg. Match Score -->
            <div class="relative p-5 sm:p-6 bg-white shadow-lg rounded-xl text-center hover:shadow-xl transition-shadow border border-gray-100">
                <div class="absolute top-3 right-3 text-gray-400">
                    <i data-lucide="star" class="w-5 h-5"></i>
                </div>
                <h3 class="text-xs sm:text-sm text-gray-500 uppercase tracking-wide font-medium">Avg. Match Score</h3>
                <p class="text-2xl sm:text-3xl font-bold text-blue-600 mt-2"><?= $avgMatchScore ?>%</p>
            </div>

            <!-- Active Venues -->
            <div class="relative p-5 sm:p-6 bg-white shadow-lg rounded-xl text-center hover:shadow-xl transition-shadow border border-gray-100">
                <div class="absolute top-3 right-3 text-gray-400">
                    <i data-lucide="map-pin" class="w-5 h-5"></i>
                </div>
                <h3 class="text-xs sm:text-sm text-gray-500 uppercase tracking-wide font-medium">Active Venues</h3>
                <p class="text-2xl sm:text-3xl font-bold text-purple-600 mt-2"><?= $activeVenues ?></p>
            </div>
        </div>

        <!-- Charts and Lists -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            <!-- Booking Trends -->
            <div class="p-4 sm:p-5 md:p-6 bg-white rounded-xl shadow-lg border border-gray-100 hover:shadow-xl transition-shadow">
                <h3 class="text-sm sm:text-base md:text-lg font-bold mb-3 sm:mb-4 flex items-center gap-2 text-gray-800">
                    <i data-lucide="bar-chart-2" class="w-4 h-4 sm:w-5 sm:h-5 text-indigo-600"></i> Booking Trends
                </h3>
                <div class="h-64 sm:h-72">
                    <canvas id="bookingChart"></canvas>
                </div>
            </div>

            <!-- Event Type Distribution -->
            <div class="p-4 sm:p-5 md:p-6 bg-white rounded-xl shadow-lg border border-gray-100 hover:shadow-xl transition-shadow">
                <h3 class="text-sm sm:text-base md:text-lg font-bold mb-3 sm:mb-4 flex items-center gap-2 text-gray-800">
                    <i data-lucide="pie-chart" class="w-4 h-4 sm:w-5 sm:h-5 text-indigo-600"></i> Event Type Distribution
                </h3>
                <div class="h-64 sm:h-72">
                    <canvas id="eventTypeChart"></canvas>
                </div>
            </div>

            <!-- Revenue Forecast -->
            <div class="lg:col-span-2 p-4 sm:p-5 md:p-6 bg-white rounded-xl shadow-lg border border-gray-100 hover:shadow-xl transition-shadow">
                <h3 class="text-sm sm:text-base md:text-lg font-bold mb-3 sm:mb-4 flex items-center gap-2 text-gray-800">
                    <i data-lucide="line-chart" class="w-4 h-4 sm:w-5 sm:h-5 text-indigo-600"></i> Revenue Forecast
                </h3>
                <div class="h-64 sm:h-72">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <!-- Top Venues -->
            <div class="p-4 sm:p-5 md:p-6 bg-white rounded-xl shadow-lg border border-gray-100 hover:shadow-xl transition-shadow">
                <h3 class="text-sm sm:text-base md:text-lg font-bold mb-3 sm:mb-4 flex items-center gap-2 text-gray-800">
                    <i data-lucide="building" class="w-4 h-4 sm:w-5 sm:h-5 text-indigo-600"></i> Top 3 Most Booked Venues
                </h3>
                <ul class="space-y-3">
                    <?php while ($v = array_shift($topVenuesData)): ?>
                        <li class="flex justify-between items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <span class="text-xs sm:text-sm font-medium text-gray-700"><?= $v['venue_name'] ?></span>
                            <span class="font-bold text-indigo-600 text-sm sm:text-base"><?= $v['total_booked'] ?>x</span>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>

            <!-- Avg Budget per Type -->
            <div class="p-4 sm:p-5 md:p-6 bg-white rounded-xl shadow-lg border border-gray-100 hover:shadow-xl transition-shadow">
                <h3 class="text-sm sm:text-base md:text-lg font-bold mb-3 sm:mb-4 flex items-center gap-2 text-gray-800">
                    <i data-lucide="wallet" class="w-4 h-4 sm:w-5 sm:h-5 text-indigo-600"></i> Average Budget per Event Type
                </h3>
                <ul class="space-y-3">
                    <?php while ($b = array_shift($avgBudgetData)): ?>
                        <li class="flex justify-between items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <span class="text-xs sm:text-sm font-medium text-gray-700"><?= $b['event_type'] ?></span>
                            <span class="font-bold text-green-600 text-xs sm:text-sm">₱<?= number_format($b['avg_budget'], 2) ?></span>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>

            <!-- Venue Occupancy -->
            <div class="lg:col-span-2 p-4 sm:p-5 md:p-6 bg-white rounded-xl shadow-lg border border-gray-100 hover:shadow-xl transition-shadow">
                <h3 class="text-sm sm:text-base md:text-lg font-bold mb-3 sm:mb-4 flex items-center gap-2 text-gray-800">
                    <i data-lucide="activity" class="w-4 h-4 sm:w-5 sm:h-5 text-indigo-600"></i> Monthly Venue Occupancy Rate
                </h3>
                <div class="h-64 sm:h-72">
                    <canvas id="occupancyChart"></canvas>
                </div>
            </div>
        </div>
    </section>

    <script>
        lucide.createIcons();
        const chartOptions = {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    labels: {
                        font: {
                            size: window.innerWidth < 640 ? 10 : 12
                        }
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        font: {
                            size: window.innerWidth < 640 ? 9 : 11
                        }
                    }
                },
                y: {
                    ticks: {
                        font: {
                            size: window.innerWidth < 640 ? 9 : 11
                        }
                    }
                }
            }
        };

        // Booking Trends
        new Chart(document.getElementById('bookingChart'), {
            type: 'bar',
            data: {
                labels: [<?php foreach ($bookingTrendsData as $r) echo "'{$r['month']}',"; ?>],
                datasets: [{
                    label: 'Bookings',
                    data: [<?php foreach ($bookingTrendsData as $r) echo "{$r['count']},"; ?>],
                    backgroundColor: '#6366f1'
                }]
            },
            options: chartOptions
        });

        // Event Type Distribution
        new Chart(document.getElementById('eventTypeChart'), {
            type: 'pie',
            data: {
                labels: [<?php foreach ($eventTypeData as $r) echo "'{$r['event_type']}',"; ?>],
                datasets: [{
                    data: [<?php foreach ($eventTypeData as $r) echo "{$r['total']},"; ?>],
                    backgroundColor: ['#3b82f6', '#22c55e', '#f59e0b', '#8b5cf6']
                }]
            },
            options: chartOptions
        });

        // Revenue Forecast
        new Chart(document.getElementById('revenueChart'), {
            type: 'line',
            data: {
                labels: [<?php foreach ($revenueData as $r) echo "'{$r['month']}',"; ?>],
                datasets: [{
                    label: 'Revenue',
                    data: [<?php foreach ($revenueData as $r) echo "{$r['revenue']},"; ?>],
                    borderColor: '#22c55e',
                    fill: false,
                    tension: 0.3
                }]
            },
            options: chartOptions
        });

        // Venue Occupancy
        new Chart(document.getElementById('occupancyChart'), {
            type: 'line',
            data: {
                labels: [<?php foreach ($occupancyData as $r) echo "'{$r['month']}',"; ?>],
                datasets: [{
                    label: 'Occupancy (%)',
                    data: [
                        <?php foreach ($occupancyData as $r) echo round(($r['booked_venues'] / $r['total_venues']) * 100, 2) . ","; ?>
                    ],
                    borderColor: '#4f46e5',
                    fill: false,
                    tension: 0.3
                }]
            },
            options: chartOptions
        });
    </script>
</body>

</html>