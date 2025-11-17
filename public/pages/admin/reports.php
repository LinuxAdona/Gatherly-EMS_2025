<?php
session_start();

// Check if user is logged in and is an administrator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'administrator') {
    header("Location: ../signin.php");
    exit();
}

require_once '../../../src/services/dbconnect.php';

$first_name = $_SESSION['first_name'] ?? 'Admin';

// Get report type
$report_type = $_GET['type'] ?? 'revenue';

// Get date filters
$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? '';

// Revenue statistics
$revenue_query = "SELECT 
    SUM(total_cost) as total_revenue,
    COUNT(*) as total_events,
    AVG(total_cost) as avg_revenue
    FROM events 
    WHERE status = 'completed'";

if ($year) {
    $revenue_query .= " AND YEAR(event_date) = $year";
}
if ($month) {
    $revenue_query .= " AND MONTH(event_date) = $month";
}

$revenue_stats = $conn->query($revenue_query)->fetch_assoc();

// Event type distribution
$event_type_query = "SELECT event_type, COUNT(*) as count, SUM(total_cost) as revenue 
    FROM events 
    WHERE status = 'completed'";
if ($year) {
    $event_type_query .= " AND YEAR(event_date) = $year";
}
if ($month) {
    $event_type_query .= " AND MONTH(event_date) = $month";
}
$event_type_query .= " GROUP BY event_type ORDER BY revenue DESC";
$event_types = $conn->query($event_type_query);

// Store event type data for charts
$event_type_data = [];
if ($event_types && $event_types->num_rows > 0) {
    while ($row = $event_types->fetch_assoc()) {
        $event_type_data[] = $row;
    }
    $event_types->data_seek(0);
}

// Venue performance
$venue_query = "SELECT v.venue_name, v.location, 
    COUNT(e.event_id) as event_count, 
    SUM(e.total_cost) as total_revenue,
    AVG(e.expected_guests) as avg_guests
    FROM venues v
    LEFT JOIN events e ON v.venue_id = e.venue_id AND e.status = 'completed'";
if ($year) {
    $venue_query .= " AND YEAR(e.event_date) = $year";
}
if ($month) {
    $venue_query .= " AND MONTH(e.event_date) = $month";
}
$venue_query .= " GROUP BY v.venue_id ORDER BY total_revenue DESC LIMIT 10";
$venues = $conn->query($venue_query);

// Monthly revenue trend
$monthly_query = "SELECT 
    DATE_FORMAT(event_date, '%Y-%m') as month,
    COUNT(*) as event_count,
    SUM(total_cost) as revenue
    FROM events 
    WHERE status = 'completed'";
if ($year) {
    $monthly_query .= " AND YEAR(event_date) = $year";
}
$monthly_query .= " GROUP BY month ORDER BY month DESC LIMIT 12";
$monthly_data = $conn->query($monthly_query);

// Store monthly data for charts
$monthly_trend_data = [];
if ($monthly_data && $monthly_data->num_rows > 0) {
    while ($row = $monthly_data->fetch_assoc()) {
        $monthly_trend_data[] = $row;
    }
    $monthly_data->data_seek(0);
}

// User activity
$user_activity_query = "SELECT 
    u.first_name, u.last_name, u.role,
    COUNT(e.event_id) as events_organized,
    SUM(e.total_cost) as total_value
    FROM users u
    LEFT JOIN events e ON u.user_id = e.client_id AND e.status IN ('confirmed', 'completed')
    WHERE u.role = 'organizer'
    GROUP BY u.user_id
    ORDER BY events_organized DESC
    LIMIT 10";
$user_activity = $conn->query($user_activity_query);

// Get available years
$years_query = "SELECT DISTINCT YEAR(event_date) as year FROM events ORDER BY year DESC";
$years_result = $conn->query($years_query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics | Gatherly</title>
    <link rel="icon" type="image/x-icon" href="../../assets/images/logo.png">
    <link rel="stylesheet"
        href="../../../src/output.css?v=<?php echo filemtime(__DIR__ . '/../../../src/output.css'); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Almarai:wght@300;400;700;800&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
    <style>
    @media print {
        body * {
            visibility: hidden;
        }

        #printableArea,
        #printableArea * {
            visibility: visible;
        }

        #printableArea {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }

        aside,
        nav,
        button,
        .no-print {
            display: none !important;
        }

        .bg-white {
            background: white !important;
        }

        table {
            page-break-inside: auto;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
    }
    </style>
</head>

<body class="<?php
                $nav_layout = $_SESSION['nav_layout'] ?? 'sidebar';
                echo $nav_layout === 'sidebar' ? 'bg-gray-100' : 'bg-linear-to-br from-slate-50 via-white to-blue-50';
                ?> font-['Montserrat']">
    <?php include '../../../src/components/AdminSidebar.php'; ?>

    <!-- Main Content -->
    <div id="printableArea"
        class="<?php echo $nav_layout === 'sidebar' ? 'lg:ml-64' : 'container mx-auto'; ?> <?php echo $nav_layout === 'sidebar' ? '' : 'px-4 sm:px-6 lg:px-8'; ?> min-h-screen">
        <?php if ($nav_layout === 'sidebar'): ?>
        <!-- Top Bar for Sidebar Layout -->
        <div class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-20 px-4 sm:px-6 lg:px-8 py-4 mb-8">
            <h1 class="text-2xl font-bold text-gray-800">
                <!-- <i class="mr-2 text-indigo-600 fas fa-chart-bar"></i> -->
                Reports & Analytics
            </h1>
            <p class="text-sm text-gray-600">View system analytics and generate reports</p>
        </div>
        <div class="px-4 sm:px-6 lg:px-8">
            <?php else: ?>
            <!-- Header for Navbar Layout -->
            <div class="mb-8">
                <h1 class="mb-2 text-3xl font-bold text-gray-800 sm:text-4xl">
                    <!-- <i class="mr-2 text-indigo-600 fas fa-chart-bar"></i> -->
                    Reports & Analytics
                </h1>
                <p class="text-gray-600">Comprehensive business insights and performance metrics</p>
            </div>
            <?php endif; ?>

            <!-- Revenue Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-4 mb-6 md:mb-8">
                <div class="p-3 md:p-4 bg-white shadow-sm rounded-lg border border-gray-200">
                    <div class="flex flex-col">
                        <div class="flex items-center gap-2 mb-1 md:mb-2">
                            <i class="fas fa-money-bill-wave text-green-500 text-sm md:text-base"></i>
                            <span class="text-lg md:text-xl font-bold text-gray-800">
                                ₱<?php echo number_format($revenue_stats['total_revenue'] ?? 0, 2); ?>
                            </span>
                        </div>
                        <p class="text-xs text-gray-600">Total Revenue</p>
                    </div>
                </div>
                <div class="p-3 md:p-4 bg-white shadow-sm rounded-lg border border-gray-200">
                    <div class="flex flex-col">
                        <div class="flex items-center gap-2 mb-1 md:mb-2">
                            <i class="fas fa-calendar-check text-blue-500 text-sm md:text-base"></i>
                            <span class="text-lg md:text-xl font-bold text-gray-800">
                                <?php echo number_format($revenue_stats['total_events'] ?? 0); ?>
                            </span>
                        </div>
                        <p class="text-xs text-gray-600">Total Events</p>
                    </div>
                </div>
                <div class="p-3 md:p-4 bg-white shadow-sm rounded-lg border border-gray-200">
                    <div class="flex flex-col">
                        <div class="flex items-center gap-2 mb-1 md:mb-2">
                            <i class="fas fa-chart-line text-purple-500 text-sm md:text-base"></i>
                            <span class="text-lg md:text-xl font-bold text-gray-800">
                                ₱<?php echo number_format($revenue_stats['avg_revenue'] ?? 0, 2); ?>
                            </span>
                        </div>
                        <p class="text-xs text-gray-600">Average Revenue</p>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="p-4 md:p-6 mb-6 bg-white shadow-md rounded-xl">
                <form method="GET" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Report Type</label>
                        <select name="type"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="revenue" <?php echo $report_type === 'revenue' ? 'selected' : ''; ?>>Revenue
                                Reports</option>
                            <option value="bookings" <?php echo $report_type === 'bookings' ? 'selected' : ''; ?>>
                                Booking
                                Analytics</option>
                            <option value="venues" <?php echo $report_type === 'venues' ? 'selected' : ''; ?>>Venue
                                Performance</option>
                            <option value="users" <?php echo $report_type === 'users' ? 'selected' : ''; ?>>User
                                Activity
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Year</label>
                        <select name="year"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">All Years</option>
                            <?php while ($yr = $years_result->fetch_assoc()): ?>
                            <option value="<?php echo $yr['year']; ?>"
                                <?php echo $year == $yr['year'] ? 'selected' : ''; ?>>
                                <?php echo $yr['year']; ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Month</label>
                        <select name="month"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">All Months</option>
                            <?php for ($i = 1; $i <= 12; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo $month == $i ? 'selected' : ''; ?>>
                                <?php echo date('F', mktime(0, 0, 0, $i, 1)); ?>
                            </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit"
                            class="flex-1 px-6 py-2 text-white transition-colors bg-indigo-600 rounded-lg hover:bg-indigo-700">
                            <i class="mr-2 fas fa-filter"></i>Apply
                        </button>
                        <a href="reports.php"
                            class="px-4 py-2 text-gray-600 transition-colors bg-gray-200 rounded-lg hover:bg-gray-300">
                            <i class="fas fa-redo"></i>
                        </a>
                    </div>
                </form>
            </div>

            <!-- Report Content based on type -->
            <?php if ($report_type === 'revenue' || $report_type === 'bookings'): ?>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Event Type Chart -->
                <div class="p-6 bg-white shadow-md rounded-xl">
                    <h2 class="mb-4 text-xl font-bold text-gray-800">
                        <i class="mr-2 text-blue-600 fas fa-chart-pie"></i>
                        Event Type Distribution
                    </h2>
                    <div class="relative h-64">
                        <canvas id="eventTypeChart"></canvas>
                    </div>
                </div>

                <!-- Monthly Trend Chart -->
                <div class="p-6 bg-white shadow-md rounded-xl">
                    <h2 class="mb-4 text-xl font-bold text-gray-800">
                        <i class="mr-2 text-purple-600 fas fa-chart-line"></i>
                        Monthly Revenue Trend
                    </h2>
                    <div class="relative h-64">
                        <canvas id="monthlyTrendChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Event Type Distribution -->
            <div class="p-6 mb-8 bg-white shadow-md rounded-xl">
                <h2 class="mb-4 text-xl font-bold text-gray-800">
                    <i class="mr-2 text-blue-600 fas fa-table"></i>
                    Event Type Details
                </h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-xs font-semibold tracking-wider text-left text-gray-700 uppercase">
                                    Event Type</th>
                                <th
                                    class="px-6 py-3 text-xs font-semibold tracking-wider text-left text-gray-700 uppercase">
                                    Count</th>
                                <th
                                    class="px-6 py-3 text-xs font-semibold tracking-wider text-left text-gray-700 uppercase">
                                    Revenue</th>
                                <th
                                    class="px-6 py-3 text-xs font-semibold tracking-wider text-left text-gray-700 uppercase">
                                    Avg Revenue</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if ($event_types->num_rows > 0): ?>
                            <?php while ($type = $event_types->fetch_assoc()): ?>
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($type['event_type']); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900"><?php echo number_format($type['count']); ?>
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-green-600">
                                    ₱<?php echo number_format($type['revenue'], 2); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    ₱<?php echo number_format($type['revenue'] / $type['count'], 2); ?></td>
                            </tr>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">No data available</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Monthly Trend Table -->
            <div class="p-6 mb-8 bg-white shadow-md rounded-xl">
                <h2 class="mb-4 text-xl font-bold text-gray-800">
                    <i class="mr-2 text-purple-600 fas fa-table"></i>
                    Monthly Revenue Details
                </h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-xs font-semibold tracking-wider text-left text-gray-700 uppercase">
                                    Month</th>
                                <th
                                    class="px-6 py-3 text-xs font-semibold tracking-wider text-left text-gray-700 uppercase">
                                    Events</th>
                                <th
                                    class="px-6 py-3 text-xs font-semibold tracking-wider text-left text-gray-700 uppercase">
                                    Revenue</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if ($monthly_data->num_rows > 0): ?>
                            <?php while ($month_data = $monthly_data->fetch_assoc()): ?>
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    <?php echo date('F Y', strtotime($month_data['month'] . '-01')); ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <?php echo number_format($month_data['event_count']); ?></td>
                                <td class="px-6 py-4 text-sm font-semibold text-green-600">
                                    ₱<?php echo number_format($month_data['revenue'], 2); ?></td>
                            </tr>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-center text-gray-500">No data available</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($report_type === 'venues'): ?>

            <!-- Venue Performance Chart -->
            <div class="p-6 mb-8 bg-white shadow-md rounded-xl">
                <h2 class="mb-4 text-xl font-bold text-gray-800">
                    <i class="mr-2 text-green-600 fas fa-chart-bar"></i>
                    Venue Revenue Comparison
                </h2>
                <div class="relative h-80">
                    <canvas id="venueChart"></canvas>
                </div>
            </div>

            <!-- Venue Performance -->
            <div class="p-6 mb-8 bg-white shadow-md rounded-xl">
                <h2 class="mb-4 text-xl font-bold text-gray-800">
                    <i class="mr-2 text-green-600 fas fa-table"></i>
                    Top Performing Venues
                </h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-xs font-semibold tracking-wider text-left text-gray-700 uppercase">
                                    Venue</th>
                                <th
                                    class="px-6 py-3 text-xs font-semibold tracking-wider text-left text-gray-700 uppercase">
                                    Location</th>
                                <th
                                    class="px-6 py-3 text-xs font-semibold tracking-wider text-left text-gray-700 uppercase">
                                    Events</th>
                                <th
                                    class="px-6 py-3 text-xs font-semibold tracking-wider text-left text-gray-700 uppercase">
                                    Revenue</th>
                                <th
                                    class="px-6 py-3 text-xs font-semibold tracking-wider text-left text-gray-700 uppercase">
                                    Avg Guests</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if ($venues->num_rows > 0): ?>
                            <?php while ($venue = $venues->fetch_assoc()): ?>
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($venue['venue_name']); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <?php echo htmlspecialchars($venue['location']); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <?php echo number_format($venue['event_count']); ?></td>
                                <td class="px-6 py-4 text-sm font-semibold text-green-600">
                                    ₱<?php echo number_format($venue['total_revenue'] ?? 0, 2); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <?php echo number_format($venue['avg_guests'] ?? 0); ?></td>
                            </tr>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">No data available</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($report_type === 'users'): ?>

            <!-- User Activity Chart -->
            <div class="p-6 mb-8 bg-white shadow-md rounded-xl">
                <h2 class="mb-4 text-xl font-bold text-gray-800">
                    <i class="mr-2 text-orange-600 fas fa-chart-bar"></i>
                    User Activity Overview
                </h2>
                <div class="relative h-80">
                    <canvas id="userActivityChart"></canvas>
                </div>
            </div>

            <!-- User Activity -->
            <div class="p-6 mb-8 bg-white shadow-md rounded-xl">
                <h2 class="mb-4 text-xl font-bold text-gray-800">
                    <i class="mr-2 text-orange-600 fas fa-table"></i>
                    Top Active Users
                </h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-xs font-semibold tracking-wider text-left text-gray-700 uppercase">
                                    User</th>
                                <th
                                    class="px-6 py-3 text-xs font-semibold tracking-wider text-left text-gray-700 uppercase">
                                    Role</th>
                                <th
                                    class="px-6 py-3 text-xs font-semibold tracking-wider text-left text-gray-700 uppercase">
                                    Events Organized</th>
                                <th
                                    class="px-6 py-3 text-xs font-semibold tracking-wider text-left text-gray-700 uppercase">
                                    Total Value</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if ($user_activity->num_rows > 0): ?>
                            <?php while ($user = $user_activity->fetch_assoc()): ?>
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900"><?php echo ucfirst($user['role']); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <?php echo number_format($user['events_organized']); ?></td>
                                <td class="px-6 py-4 text-sm font-semibold text-green-600">
                                    ₱<?php echo number_format($user['total_value'] ?? 0, 2); ?></td>
                            </tr>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">No data available</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Export Options -->
            <div class="p-6 bg-white shadow-md rounded-xl mb-8 no-print">
                <h2 class="mb-4 text-xl font-bold text-gray-800">
                    <i class="mr-2 text-indigo-600 fas fa-download"></i>
                    Export Options
                </h2>
                <div class="flex flex-wrap gap-3">
                    <button onclick="exportToExcel()"
                        class="cursor-pointer px-6 py-2 text-white transition-colors bg-green-600 rounded-lg hover:bg-green-700">
                        <i class="mr-2 fas fa-file-excel"></i>Export to Excel
                    </button>
                    <button onclick="exportToPDF()"
                        class="cursor-pointer px-6 py-2 text-white transition-colors bg-red-600 rounded-lg hover:bg-red-700">
                        <i class="mr-2 fas fa-file-pdf"></i>Export to PDF
                    </button>
                    <button onclick="exportToCSV()"
                        class="cursor-pointer px-6 py-2 text-white transition-colors bg-blue-600 rounded-lg hover:bg-blue-700">
                        <i class="mr-2 fas fa-file-csv"></i>Export to CSV
                    </button>
                    <button onclick="window.print()"
                        class="cursor-pointer px-6 py-2 text-gray-700 transition-colors bg-gray-200 rounded-lg hover:bg-gray-300">
                        <i class="mr-2 fas fa-print"></i>Print Report
                    </button>
                </div>
            </div>
        </div>

        <?php if ($nav_layout === 'sidebar'): ?>
    </div> <!-- Close sidebar inner wrapper -->
    <?php endif; ?>
    </div> <!-- Close main content -->

    <script>
    // Export Functions - Defined globally so they're always available
    function exportToCSV() {
        let csv = [];
        const tables = document.querySelectorAll('#printableArea table');

        if (tables.length === 0) {
            alert('No data available to export');
            return;
        }

        // Add header
        csv.push('"Gatherly Reports & Analytics"');
        csv.push('"Generated: <?php echo date('F d, Y H:i:s'); ?>"');
        csv.push('"Report Type: <?php echo ucfirst($report_type); ?>"');
        csv.push('');

        // Export all tables
        tables.forEach((table, index) => {
            const section = table.closest('.rounded-xl');
            if (section) {
                const title = section.querySelector('h2');
                if (title) {
                    csv.push('"' + title.textContent.trim() + '"');
                }
            }

            const rows = table.querySelectorAll('tr');
            for (let row of rows) {
                const cols = row.querySelectorAll('td, th');
                const csvRow = [];
                for (let col of cols) {
                    csvRow.push('"' + col.textContent.trim().replace(/"/g, '""') + '"');
                }
                csv.push(csvRow.join(','));
            }
            csv.push('');
        });

        const csvContent = csv.join('\n');
        const blob = new Blob([csvContent], {
            type: 'text/csv;charset=utf-8;'
        });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);

        link.setAttribute('href', url);
        link.setAttribute('download', 'gatherly_report_<?php echo date('Y-m-d'); ?>.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    function exportToExcel() {
        // Create HTML table structure with styles
        let html = '<html><head><meta charset="utf-8">';
        html += '<title>Gatherly Reports & Analytics</title>';
        html += '<style>';
        html += 'body { font-family: Arial, sans-serif; }';
        html += 'h1 { color: #1f2937; }';
        html += 'h2 { color: #4b5563; margin-top: 20px; }';
        html += 'table { border-collapse: collapse; width: 100%; margin: 10px 0; }';
        html +=
            'th { background-color: #f9fafb; border: 1px solid #e5e7eb; padding: 8px; text-align: left; font-weight: bold; }';
        html += 'td { border: 1px solid #e5e7eb; padding: 8px; }';
        html += 'tr:nth-child(even) { background-color: #f9fafb; }';
        html += '</style></head><body>';
        html += '<h1>Gatherly Reports & Analytics</h1>';
        html += '<p><strong>Generated:</strong> <?php echo date('F d, Y H:i:s'); ?></p>';
        html += '<p><strong>Report Type:</strong> <?php echo ucfirst($report_type); ?></p>';
        html += '<p><strong>Period:</strong> ';
        <?php if ($year): ?>
        html += 'Year: <?php echo $year; ?>';
        <?php endif; ?>
        <?php if ($month): ?>
        html += ', Month: <?php echo date('F', mktime(0, 0, 0, $month, 1)); ?>';
        <?php endif; ?>
        html += '</p><hr>';

        const tables = document.querySelectorAll('#printableArea table');
        tables.forEach(table => {
            const section = table.closest('.rounded-xl');
            if (section) {
                const title = section.querySelector('h2');
                if (title) {
                    html += '<h2>' + title.textContent.trim() + '</h2>';
                }
            }
            html += table.outerHTML;
        });

        html += '</body></html>';

        const blob = new Blob([html], {
            type: 'application/vnd.ms-excel'
        });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);

        link.setAttribute('href', url);
        link.setAttribute('download', 'gatherly_report_<?php echo date('Y-m-d'); ?>.xls');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    function exportToPDF() {
        try {
            const {
                jsPDF
            } = window.jspdf;

            if (!jsPDF) {
                alert('PDF library not loaded. Please refresh the page and try again.');
                return;
            }

            const doc = new jsPDF('p', 'mm', 'a4');

            // Add header
            doc.setFontSize(18);
            doc.setTextColor(31, 41, 55);
            doc.text('Gatherly Reports & Analytics', 14, 20);

            doc.setFontSize(10);
            doc.setTextColor(75, 85, 99);
            doc.text('Generated: <?php echo date('F d, Y H:i:s'); ?>', 14, 28);
            doc.text('Report Type: <?php echo ucfirst($report_type); ?>', 14, 34);

            let yPos = 45;

            // Get all tables
            const tables = document.querySelectorAll('#printableArea table');

            tables.forEach((table, index) => {
                // Add section title
                const section = table.closest('.rounded-xl');
                if (section) {
                    const title = section.querySelector('h2');
                    if (title) {
                        if (yPos > 250) {
                            doc.addPage();
                            yPos = 20;
                        }
                        doc.setFontSize(12);
                        doc.setTextColor(31, 41, 55);
                        doc.text(title.textContent.trim(), 14, yPos);
                        yPos += 8;
                    }
                }

                // Extract table data
                const headers = [];
                const rows = [];

                const headerCells = table.querySelectorAll('thead th');
                headerCells.forEach(cell => {
                    headers.push(cell.textContent.trim());
                });

                const bodyRows = table.querySelectorAll('tbody tr');
                bodyRows.forEach(row => {
                    const rowData = [];
                    const cells = row.querySelectorAll('td');
                    cells.forEach(cell => {
                        rowData.push(cell.textContent.trim());
                    });
                    if (rowData.length > 0 && !rowData[0].includes('No data')) {
                        rows.push(rowData);
                    }
                });

                // Add table to PDF
                if (headers.length > 0 && rows.length > 0) {
                    doc.autoTable({
                        head: [headers],
                        body: rows,
                        startY: yPos,
                        theme: 'grid',
                        headStyles: {
                            fillColor: [249, 250, 251],
                            textColor: [55, 65, 81],
                            fontStyle: 'bold',
                            lineWidth: 0.1,
                            lineColor: [229, 231, 235]
                        },
                        bodyStyles: {
                            textColor: [31, 41, 55],
                            lineWidth: 0.1,
                            lineColor: [229, 231, 235]
                        },
                        alternateRowStyles: {
                            fillColor: [249, 250, 251]
                        },
                        margin: {
                            top: 10,
                            left: 14,
                            right: 14
                        },
                        styles: {
                            fontSize: 8,
                            cellPadding: 3
                        }
                    });
                    yPos = doc.lastAutoTable.finalY + 15;
                }
            });

            // Save the PDF
            doc.save('gatherly_report_<?php echo date('Y-m-d'); ?>.pdf');
        } catch (error) {
            console.error('PDF generation error:', error);
            alert('Error generating PDF: ' + error.message);
        }
    }

    // Chart.js configurations
    const chartColors = {
        blue: 'rgb(59, 130, 246)',
        green: 'rgb(34, 197, 94)',
        purple: 'rgb(168, 85, 247)',
        orange: 'rgb(251, 146, 60)',
        red: 'rgb(239, 68, 68)',
        yellow: 'rgb(234, 179, 8)',
        pink: 'rgb(236, 72, 153)',
        indigo: 'rgb(99, 102, 241)',
    };

    <?php if ($report_type === 'revenue' || $report_type === 'bookings'): ?>
    // Event Type Distribution Chart
    <?php
        $event_labels = [];
        $event_counts = [];
        $event_revenues = [];
        foreach ($event_type_data as $type) {
            $event_labels[] = $type['event_type'];
            $event_counts[] = $type['count'];
            $event_revenues[] = $type['revenue'];
        }
        ?>
    const eventTypeCtx = document.getElementById('eventTypeChart');
    if (eventTypeCtx) {
        new Chart(eventTypeCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($event_labels); ?>,
                datasets: [{
                    label: 'Revenue',
                    data: <?php echo json_encode($event_revenues); ?>,
                    backgroundColor: [
                        chartColors.blue,
                        chartColors.green,
                        chartColors.purple,
                        chartColors.orange,
                        chartColors.red,
                        chartColors.yellow,
                        chartColors.pink,
                        chartColors.indigo,
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ₱' + context.parsed.toLocaleString('en-PH', {
                                    minimumFractionDigits: 2
                                });
                            }
                        }
                    }
                }
            }
        });
    }

    // Monthly Trend Chart
    <?php
        $month_labels = [];
        $month_revenues = [];
        $month_counts = [];
        foreach ($monthly_trend_data as $month_data) {
            $month_labels[] = date('M Y', strtotime($month_data['month'] . '-01'));
            $month_revenues[] = $month_data['revenue'];
            $month_counts[] = $month_data['event_count'];
        }
        $month_labels = array_reverse($month_labels);
        $month_revenues = array_reverse($month_revenues);
        $month_counts = array_reverse($month_counts);
        ?>
    const monthlyTrendCtx = document.getElementById('monthlyTrendChart');
    if (monthlyTrendCtx) {
        new Chart(monthlyTrendCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($month_labels); ?>,
                datasets: [{
                    label: 'Revenue (₱)',
                    data: <?php echo json_encode($month_revenues); ?>,
                    borderColor: chartColors.green,
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y',
                }, {
                    label: 'Events',
                    data: <?php echo json_encode($month_counts); ?>,
                    borderColor: chartColors.blue,
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y1',
                }]
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
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.dataset.yAxisID === 'y') {
                                    label += '₱' + context.parsed.y.toLocaleString('en-PH', {
                                        minimumFractionDigits: 2
                                    });
                                } else {
                                    label += context.parsed.y;
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false,
                        },
                    },
                }
            }
        });
    }
    <?php endif; ?>

    <?php if ($report_type === 'venues'): ?>
    // Venue Performance Chart
    <?php
        $venues->data_seek(0);
        $venue_labels = [];
        $venue_revenues = [];
        $venue_counts = [];
        while ($venue = $venues->fetch_assoc()) {
            $venue_labels[] = $venue['venue_name'];
            $venue_revenues[] = $venue['total_revenue'] ?? 0;
            $venue_counts[] = $venue['event_count'];
        }
        ?>
    const venueCtx = document.getElementById('venueChart');
    if (venueCtx) {
        new Chart(venueCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($venue_labels); ?>,
                datasets: [{
                    label: 'Revenue (₱)',
                    data: <?php echo json_encode($venue_revenues); ?>,
                    backgroundColor: chartColors.green,
                    yAxisID: 'y',
                }, {
                    label: 'Events',
                    data: <?php echo json_encode($venue_counts); ?>,
                    backgroundColor: chartColors.blue,
                    yAxisID: 'y1',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.dataset.yAxisID === 'y') {
                                    label += '₱' + context.parsed.y.toLocaleString('en-PH', {
                                        minimumFractionDigits: 2
                                    });
                                } else {
                                    label += context.parsed.y;
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false,
                        },
                    },
                }
            }
        });
    }
    <?php endif; ?>

    <?php if ($report_type === 'users'): ?>
    // User Activity Chart
    <?php
        $user_activity->data_seek(0);
        $user_labels = [];
        $user_events = [];
        $user_values = [];
        while ($user = $user_activity->fetch_assoc()) {
            $user_labels[] = $user['first_name'] . ' ' . $user['last_name'];
            $user_events[] = $user['events_organized'];
            $user_values[] = $user['total_value'] ?? 0;
        }
        ?>
    const userActivityCtx = document.getElementById('userActivityChart');
    if (userActivityCtx) {
        new Chart(userActivityCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($user_labels); ?>,
                datasets: [{
                    label: 'Events Organized',
                    data: <?php echo json_encode($user_events); ?>,
                    backgroundColor: chartColors.orange,
                    yAxisID: 'y',
                }, {
                    label: 'Total Value (₱)',
                    data: <?php echo json_encode($user_values); ?>,
                    backgroundColor: chartColors.green,
                    yAxisID: 'y1',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.dataset.yAxisID === 'y1') {
                                    label += '₱' + context.parsed.y.toLocaleString('en-PH', {
                                        minimumFractionDigits: 2
                                    });
                                } else {
                                    label += context.parsed.y;
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false,
                        },
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    },
                }
            }
        });
    }
    <?php endif; ?>
    </script>
</body>

</html>
<?php $conn->close(); ?>