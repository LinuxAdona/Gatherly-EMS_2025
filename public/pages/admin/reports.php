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
</head>

<body class="bg-linear-to-br from-slate-50 via-white to-blue-50 font-['Montserrat']">
    <!-- Navbar -->
    <nav class="sticky top-0 z-50 bg-white shadow-md">
        <div class="container px-4 mx-auto sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-12 sm:h-16">
                <div class="flex items-center h-full">
                    <a href="../../../index.php" class="flex items-center group">
                        <img class="w-8 h-8 mr-2 transition-transform sm:w-10 sm:h-10 group-hover:scale-110"
                            src="../../assets/images/logo.png" alt="Gatherly Logo">
                        <span class="text-lg font-bold text-gray-800 sm:text-xl">Gatherly</span>
                    </a>
                </div>
                <div class="items-center hidden gap-6 md:flex">
                    <a href="admin-dashboard.php"
                        class="text-gray-700 transition-colors hover:text-indigo-600">Dashboard</a>
                    <a href="manage-users.php" class="text-gray-700 transition-colors hover:text-indigo-600">Users</a>
                    <a href="manage-venues.php" class="text-gray-700 transition-colors hover:text-indigo-600">Venues</a>
                    <a href="manage-events.php" class="text-gray-700 transition-colors hover:text-indigo-600">Events</a>
                    <a href="reports.php"
                        class="font-semibold text-indigo-600 transition-colors hover:text-indigo-700">Reports</a>
                    <div class="relative">
                        <button id="profile-dropdown-btn"
                            class="flex items-center gap-2 text-gray-700 transition-colors cursor-pointer hover:text-indigo-600">
                            <i class="text-2xl fas fa-user-shield"></i>
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
    <div class="container px-4 py-8 mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="mb-2 text-3xl font-bold text-gray-800 sm:text-4xl">
                <i class="mr-2 text-green-600 fas fa-chart-line"></i>
                Reports & Analytics
            </h1>
            <p class="text-gray-600">Comprehensive business insights and performance metrics</p>
        </div>

        <!-- Filters -->
        <div class="p-6 mb-6 bg-white shadow-md rounded-xl">
            <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div>
                    <label class="block mb-2 text-sm font-semibold text-gray-700">Report Type</label>
                    <select name="type"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="revenue" <?php echo $report_type === 'revenue' ? 'selected' : ''; ?>>Revenue
                            Reports</option>
                        <option value="bookings" <?php echo $report_type === 'bookings' ? 'selected' : ''; ?>>Booking
                            Analytics</option>
                        <option value="venues" <?php echo $report_type === 'venues' ? 'selected' : ''; ?>>Venue
                            Performance</option>
                        <option value="users" <?php echo $report_type === 'users' ? 'selected' : ''; ?>>User Activity
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

        <!-- Revenue Summary Cards -->
        <div class="grid grid-cols-1 gap-4 mb-8 sm:grid-cols-3">
            <div class="p-6 bg-white border-l-4 border-green-500 shadow-md rounded-xl">
                <p class="mb-1 text-sm text-gray-600">Total Revenue</p>
                <h3 class="text-3xl font-bold text-gray-800">
                    ₱<?php echo number_format($revenue_stats['total_revenue'] ?? 0, 2); ?></h3>
            </div>
            <div class="p-6 bg-white border-l-4 border-blue-500 shadow-md rounded-xl">
                <p class="mb-1 text-sm text-gray-600">Total Events</p>
                <h3 class="text-3xl font-bold text-gray-800">
                    <?php echo number_format($revenue_stats['total_events'] ?? 0); ?></h3>
            </div>
            <div class="p-6 bg-white border-l-4 border-purple-500 shadow-md rounded-xl">
                <p class="mb-1 text-sm text-gray-600">Average Revenue</p>
                <h3 class="text-3xl font-bold text-gray-800">
                    ₱<?php echo number_format($revenue_stats['avg_revenue'] ?? 0, 2); ?></h3>
            </div>
        </div>

        <!-- Report Content based on type -->
        <?php if ($report_type === 'revenue' || $report_type === 'bookings'): ?>
            <!-- Event Type Distribution -->
            <div class="p-6 mb-8 bg-white shadow-md rounded-xl">
                <h2 class="mb-4 text-xl font-bold text-gray-800">
                    <i class="mr-2 text-blue-600 fas fa-chart-pie"></i>
                    Event Type Distribution
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

            <!-- Monthly Trend -->
            <div class="p-6 mb-8 bg-white shadow-md rounded-xl">
                <h2 class="mb-4 text-xl font-bold text-gray-800">
                    <i class="mr-2 text-purple-600 fas fa-chart-line"></i>
                    Monthly Revenue Trend
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
            <!-- Venue Performance -->
            <div class="p-6 mb-8 bg-white shadow-md rounded-xl">
                <h2 class="mb-4 text-xl font-bold text-gray-800">
                    <i class="mr-2 text-green-600 fas fa-building"></i>
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
            <!-- User Activity -->
            <div class="p-6 mb-8 bg-white shadow-md rounded-xl">
                <h2 class="mb-4 text-xl font-bold text-gray-800">
                    <i class="mr-2 text-orange-600 fas fa-users"></i>
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
        <div class="p-6 bg-white shadow-md rounded-xl">
            <h2 class="mb-4 text-xl font-bold text-gray-800">
                <i class="mr-2 text-indigo-600 fas fa-download"></i>
                Export Options
            </h2>
            <div class="flex flex-wrap gap-3">
                <button class="px-6 py-2 text-white transition-colors bg-green-600 rounded-lg hover:bg-green-700">
                    <i class="mr-2 fas fa-file-excel"></i>Export to Excel
                </button>
                <button class="px-6 py-2 text-white transition-colors bg-red-600 rounded-lg hover:bg-red-700">
                    <i class="mr-2 fas fa-file-pdf"></i>Export to PDF
                </button>
                <button class="px-6 py-2 text-white transition-colors bg-blue-600 rounded-lg hover:bg-blue-700">
                    <i class="mr-2 fas fa-file-csv"></i>Export to CSV
                </button>
            </div>
        </div>
    </div>

    <?php include '../../../src/components/Footer.php'; ?>

    <script>
        document.getElementById('profile-dropdown-btn')?.addEventListener('click', function(e) {
            e.stopPropagation();
            document.getElementById('profile-dropdown').classList.toggle('hidden');
        });

        document.addEventListener('click', function() {
            document.getElementById('profile-dropdown')?.classList.add('hidden');
        });
    </script>
</body>

</html>
<?php $conn->close(); ?>