<?php
session_start();

// Check if user is logged in and is an administrator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'administrator') {
    header("Location: ../signin.php");
    exit();
}

require_once '../../../src/services/dbconnect.php';

$first_name = $_SESSION['first_name'] ?? 'Admin';

// Handle event actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $event_id = $_POST['event_id'] ?? null;
    $action = $_POST['action'];

    if ($event_id) {
        switch ($action) {
            case 'confirm':
                $conn->query("UPDATE events SET status = 'confirmed' WHERE event_id = $event_id");
                break;
            case 'cancel':
                $conn->query("UPDATE events SET status = 'canceled' WHERE event_id = $event_id");
                break;
            case 'complete':
                $conn->query("UPDATE events SET status = 'completed' WHERE event_id = $event_id");
                break;
            case 'delete':
                $conn->query("DELETE FROM events WHERE event_id = $event_id");
                break;
        }
    }
    header("Location: manage-events.php");
    exit();
}

// Fetch events with filters
$status_filter = $_GET['status'] ?? '';
$type_filter = $_GET['type'] ?? '';
$search = $_GET['search'] ?? '';

$query = "SELECT e.*, v.venue_name, u.first_name, u.last_name 
          FROM events e 
          LEFT JOIN venues v ON e.venue_id = v.venue_id 
          LEFT JOIN users u ON e.client_id = u.user_id 
          WHERE 1=1";
if ($status_filter) {
    $query .= " AND e.status = '" . $conn->real_escape_string($status_filter) . "'";
}
if ($type_filter) {
    $query .= " AND e.event_type = '" . $conn->real_escape_string($type_filter) . "'";
}
if ($search) {
    $search_term = $conn->real_escape_string($search);
    $query .= " AND (e.event_name LIKE '%$search_term%' OR e.theme LIKE '%$search_term%')";
}
$query .= " ORDER BY e.event_date DESC";

$events_result = $conn->query($query);

// Get statistics
$stats = [];
$stats['total'] = $conn->query("SELECT COUNT(*) as count FROM events")->fetch_assoc()['count'];
$stats['pending'] = $conn->query("SELECT COUNT(*) as count FROM events WHERE status = 'pending'")->fetch_assoc()['count'];
$stats['confirmed'] = $conn->query("SELECT COUNT(*) as count FROM events WHERE status = 'confirmed'")->fetch_assoc()['count'];
$stats['completed'] = $conn->query("SELECT COUNT(*) as count FROM events WHERE status = 'completed'")->fetch_assoc()['count'];
$stats['canceled'] = $conn->query("SELECT COUNT(*) as count FROM events WHERE status = 'canceled'")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events | Gatherly</title>
    <link rel="icon" type="image/x-icon" href="../../assets/images/logo.png">
    <link rel="stylesheet"
        href="../../../src/output.css?v=<?php echo filemtime(__DIR__ . '/../../../src/output.css'); ?>">
    <script src="https://kit.fontawesome.com/2a99de0fa5.js" crossorigin="anonymous"></script>
</head>

<body class="bg-linear-to-br from-slate-50 via-white to-blue-50 font-['Montserrat']">
    <!-- Navbar -->
    <nav class="sticky top-0 z-50 bg-white shadow-md">
        <div class="container px-4 mx-auto sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-12 sm:h-16">
                <div class="flex items-center h-full">
                    <a href="../home.php" class="flex items-center group">
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
                    <a href="manage-events.php"
                        class="font-semibold text-indigo-600 transition-colors hover:text-indigo-700">Events</a>
                    <a href="reports.php" class="text-gray-700 transition-colors hover:text-indigo-600">Reports</a>
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
                <i class="mr-2 text-purple-600 fas fa-calendar-check"></i>
                Event Management
            </h1>
            <p class="text-gray-600">Oversee and manage all events in the system</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 gap-4 mb-8 sm:grid-cols-2 lg:grid-cols-5">
            <div class="p-4 bg-white border-l-4 border-blue-500 shadow-md rounded-xl">
                <p class="mb-1 text-xs text-gray-600 uppercase">Total Events</p>
                <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['total']); ?></p>
            </div>
            <div class="p-4 bg-white border-l-4 border-yellow-500 shadow-md rounded-xl">
                <p class="mb-1 text-xs text-gray-600 uppercase">Pending</p>
                <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['pending']); ?></p>
            </div>
            <div class="p-4 bg-white border-l-4 border-green-500 shadow-md rounded-xl">
                <p class="mb-1 text-xs text-gray-600 uppercase">Confirmed</p>
                <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['confirmed']); ?></p>
            </div>
            <div class="p-4 bg-white border-l-4 border-purple-500 shadow-md rounded-xl">
                <p class="mb-1 text-xs text-gray-600 uppercase">Completed</p>
                <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['completed']); ?></p>
            </div>
            <div class="p-4 bg-white border-l-4 border-red-500 shadow-md rounded-xl">
                <p class="mb-1 text-xs text-gray-600 uppercase">Canceled</p>
                <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['canceled']); ?></p>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="p-6 mb-6 bg-white shadow-md rounded-xl">
            <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div>
                    <label class="block mb-2 text-sm font-semibold text-gray-700">Search</label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                        placeholder="Event name or theme..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block mb-2 text-sm font-semibold text-gray-700">Event Type</label>
                    <select name="type"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Types</option>
                        <option value="Wedding" <?php echo $type_filter === 'Wedding' ? 'selected' : ''; ?>>Wedding
                        </option>
                        <option value="Corporate" <?php echo $type_filter === 'Corporate' ? 'selected' : ''; ?>>
                            Corporate</option>
                        <option value="Birthday" <?php echo $type_filter === 'Birthday' ? 'selected' : ''; ?>>Birthday
                        </option>
                        <option value="Concert" <?php echo $type_filter === 'Concert' ? 'selected' : ''; ?>>Concert
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block mb-2 text-sm font-semibold text-gray-700">Status</label>
                    <select name="status"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending
                        </option>
                        <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>
                            Confirmed</option>
                        <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>
                            Completed</option>
                        <option value="canceled" <?php echo $status_filter === 'canceled' ? 'selected' : ''; ?>>Canceled
                        </option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit"
                        class="flex-1 px-6 py-2 text-white transition-colors bg-indigo-600 rounded-lg hover:bg-indigo-700">
                        <i class="mr-2 fas fa-search"></i>Filter
                    </button>
                    <a href="manage-events.php"
                        class="px-4 py-2 text-gray-600 transition-colors bg-gray-200 rounded-lg hover:bg-gray-300">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Events Table -->
        <div class="overflow-hidden bg-white shadow-md rounded-xl">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-xs font-semibold tracking-wider text-left text-gray-700 uppercase">
                                Event</th>
                            <th
                                class="px-6 py-3 text-xs font-semibold tracking-wider text-left text-gray-700 uppercase">
                                Client</th>
                            <th
                                class="px-6 py-3 text-xs font-semibold tracking-wider text-left text-gray-700 uppercase">
                                Venue</th>
                            <th
                                class="px-6 py-3 text-xs font-semibold tracking-wider text-left text-gray-700 uppercase">
                                Date</th>
                            <th
                                class="px-6 py-3 text-xs font-semibold tracking-wider text-left text-gray-700 uppercase">
                                Guests</th>
                            <th
                                class="px-6 py-3 text-xs font-semibold tracking-wider text-left text-gray-700 uppercase">
                                Cost</th>
                            <th
                                class="px-6 py-3 text-xs font-semibold tracking-wider text-left text-gray-700 uppercase">
                                Status</th>
                            <th
                                class="px-6 py-3 text-xs font-semibold tracking-wider text-center text-gray-700 uppercase">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if ($events_result->num_rows > 0): ?>
                        <?php while ($event = $events_result->fetch_assoc()): ?>
                        <tr class="transition-colors hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="text-sm font-semibold text-gray-900">
                                    <?php echo htmlspecialchars($event['event_name']); ?></div>
                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($event['event_type']); ?>
                                    - <?php echo htmlspecialchars($event['theme']); ?></div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                                <?php echo htmlspecialchars($event['first_name'] . ' ' . $event['last_name']); ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                                <?php echo htmlspecialchars($event['venue_name'] ?? 'N/A'); ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                <?php echo date('M d, Y', strtotime($event['event_date'])); ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                                <?php echo number_format($event['expected_guests']); ?>
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900 whitespace-nowrap">
                                ₱<?php echo number_format($event['total_cost'], 2); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                        $status_colors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'confirmed' => 'bg-green-100 text-green-800',
                                            'completed' => 'bg-blue-100 text-blue-800',
                                            'canceled' => 'bg-red-100 text-red-800'
                                        ];
                                        $color = $status_colors[$event['status']] ?? 'bg-gray-100 text-gray-800';
                                        ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $color; ?>">
                                    <?php echo ucfirst($event['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <div class="flex justify-center gap-1">
                                    <?php if ($event['status'] === 'pending'): ?>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>">
                                        <input type="hidden" name="action" value="confirm">
                                        <button type="submit"
                                            class="px-2 py-1 text-xs text-white transition-colors bg-green-500 rounded hover:bg-green-600"
                                            title="Confirm">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                    <?php if ($event['status'] === 'confirmed'): ?>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>">
                                        <input type="hidden" name="action" value="complete">
                                        <button type="submit"
                                            class="px-2 py-1 text-xs text-white transition-colors bg-blue-500 rounded hover:bg-blue-600"
                                            title="Complete">
                                            <i class="fas fa-check-double"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                    <?php if ($event['status'] !== 'canceled' && $event['status'] !== 'completed'): ?>
                                    <form method="POST" class="inline" onsubmit="return confirm('Cancel this event?');">
                                        <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>">
                                        <input type="hidden" name="action" value="cancel">
                                        <button type="submit"
                                            class="px-2 py-1 text-xs text-white transition-colors bg-orange-500 rounded hover:bg-orange-600"
                                            title="Cancel">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                    <form method="POST" class="inline"
                                        onsubmit="return confirm('Delete this event permanently?');">
                                        <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit"
                                            class="px-2 py-1 text-xs text-white transition-colors bg-red-500 rounded hover:bg-red-600"
                                            title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                <i class="mb-2 text-4xl fas fa-calendar"></i>
                                <p>No events found</p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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