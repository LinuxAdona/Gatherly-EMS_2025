<?php
session_start();

// Check if user is logged in and is a manager
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: ../signin.php");
    exit();
}

require_once '../../../src/services/dbconnect.php';
$first_name = $_SESSION['first_name'] ?? 'Manager';

// ----------------- BACKEND HANDLERS -----------------

// Handle status updates (modal & quick actions)
if (isset($_POST['update_status'])) {
    $event_id = intval($_POST['event_id']);
    $new_status = $conn->real_escape_string($_POST['new_status']);
    $stmt = $conn->prepare("UPDATE events SET status = ? WHERE event_id = ?");
    $stmt->bind_param("si", $new_status, $event_id);
    $stmt->execute();
    $stmt->close();
    header("Location: bookings.php");
    exit();
}

// Handle delete (from Delete modal)
if (isset($_POST['delete_submit'])) {
    $del_id = intval($_POST['delete_id']);
    $stmt = $conn->prepare("DELETE FROM events WHERE event_id = ?");
    $stmt->bind_param("i", $del_id);
    $stmt->execute();
    $stmt->close();
    header("Location: bookings.php");
    exit();
}

// Handle edit (from Edit modal)
if (isset($_POST['edit_submit'])) {
    $eid = intval($_POST['edit_id']);
    $ename = $conn->real_escape_string($_POST['event_name']);
    $etype = $conn->real_escape_string($_POST['event_type']);
    $theme = $conn->real_escape_string($_POST['theme']);
    $guests = intval($_POST['expected_guests']);
    $cost = floatval($_POST['total_cost']);
    $edate = $conn->real_escape_string($_POST['event_date']); // Expect YYYY-MM-DD
    $stmt = $conn->prepare("UPDATE events SET event_name=?, event_type=?, theme=?, expected_guests=?, total_cost=?, event_date=? WHERE event_id=?");
    $stmt->bind_param("sssidsi", $ename, $etype, $theme, $guests, $cost, $edate, $eid);
    $stmt->execute();
    $stmt->close();
    header("Location: bookings.php");
    exit();
}

// ----------------- FILTER & SORT -----------------
$status_filter = $conn->real_escape_string($_GET['status'] ?? '');
$sort_by = $_GET['sort_by'] ?? 'date_desc';

$where_clause = $status_filter ? "WHERE e.status = '{$status_filter}'" : "";

switch ($sort_by) {
    case 'name_asc': $order_clause = "ORDER BY e.event_name ASC"; break;
    case 'name_desc': $order_clause = "ORDER BY e.event_name DESC"; break;
    case 'cost_asc': $order_clause = "ORDER BY e.total_cost ASC"; break;
    case 'cost_desc': $order_clause = "ORDER BY e.total_cost DESC"; break;
    case 'date_asc': $order_clause = "ORDER BY e.event_date ASC"; break;
    default: $order_clause = "ORDER BY e.event_date DESC"; break;
}

// ----------------- FETCH EVENTS -----------------
$sql = "
    SELECT 
        e.event_id,
        e.event_name,
        e.event_type,
        e.theme,
        e.expected_guests,
        e.total_cost,
        e.event_date,
        e.status,
        CONCAT(c.first_name, ' ', c.last_name) AS client_name,
        CONCAT(co.first_name, ' ', co.last_name) AS coordinator_name,
        v.venue_name
    FROM events e
    LEFT JOIN users c ON e.client_id = c.user_id
    LEFT JOIN users co ON e.coordinator_id = co.user_id
    LEFT JOIN venues v ON e.venue_id = v.venue_id
    $where_clause
    $order_clause
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>

<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Bookings | Gatherly</title>
<link rel="icon" type="image/x-icon" href="../../assets/images/logo.png">
<link rel="stylesheet" href="../../../src/output.css?v=<?php echo filemtime(__DIR__ . '/../../../src/output.css'); ?>">
<script src="https://kit.fontawesome.com/2a99de0fa5.js" crossorigin="anonymous"></script>

<style>
/* Modal tweaks */
.modal-overlay {
  position: fixed;
  inset: 0;
  display: none;
  align-items: center;
  justify-content: center;
  z-index: 60;
}
.modal-overlay.show { display: flex; }
.modal-backdrop { position: absolute; inset: 0; background: rgba(0,0,0,0.18); backdrop-filter: blur(3px); }
.modal-card { position: relative; z-index: 61; width: 100%; max-width: 760px; border-radius: 12px; overflow: hidden; }

/* Card hover actions */
.card-container { position: relative; transition: transform 0.2s; }
.card-container:hover { transform: scale(1.02); }
.card-actions { 
  position: relative;
  display: flex;
  gap: 5px;
  flex-wrap: wrap;
  margin-top: 10px;
  justify-content: flex-start;
  opacity: 1;
}

/* Ensure buttons are properly styled */
.card-actions button {
  transition: all 0.2s ease;
  border: none;
  cursor: pointer;
  min-width: 60px;
}

/* Make sure the colors are applied properly */
.card-actions .view-btn { background-color: rgb(22 163 74) !important; }
.card-actions .edit-btn { background-color: rgb(79 70 229) !important; }
.card-actions .status-btn { background-color: rgb(147 51 234) !important; }
.card-actions .delete-btn { background-color: rgb(220 38 38) !important; }

.card-actions .view-btn:hover { background-color: rgb(21 128 61) !important; }
.card-actions .edit-btn:hover { background-color: rgb(67 56 202) !important; }
.card-actions .status-btn:hover { background-color: rgb(126 34 206) !important; }
.card-actions .delete-btn:hover { background-color: rgb(185 28 28) !important; }
</style>

</head>
<body class="bg-gradient-to-br from-green-50 via-white to-teal-50 font-['Montserrat']">

<!-- Navbar (unchanged) -->

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
                <a href="manager-dashboard.php" class="text-gray-700 hover:text-green-600">Dashboard</a>
                <a href="my-venues.php" class="text-gray-700 hover:text-green-600">My Venues</a>
                <a href="bookings.php" class="font-semibold text-green-600 hover:text-green-700">Bookings</a>
                <a href="pricing.php" class="text-gray-700 hover:text-green-600">Pricing</a>
                <a href="analytics.php" class="text-gray-700 hover:text-green-600">Analytics</a>
                <div class="relative">
                    <button id="profile-dropdown-btn" class="flex items-center gap-2 text-gray-700 hover:text-green-600">
                        <i class="text-2xl fas fa-user-tie"></i>
                        <span><?php echo htmlspecialchars($first_name); ?></span>
                        <i class="text-xs fas fa-chevron-down"></i>
                    </button>
                    <div id="profile-dropdown" class="absolute right-0 hidden w-48 py-2 mt-2 bg-white rounded-lg shadow-lg">
                        <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-green-50">Profile</a>
                        <a href="settings.php" class="block px-4 py-2 text-gray-700 hover:bg-green-50">Settings</a>
                        <a href="../../../src/services/signout-handler.php" class="block px-4 py-2 text-red-600 hover:bg-red-50">Sign Out</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- MAIN CONTENT -->

<main class="container px-6 py-10 mx-auto">
  <div class="flex flex-col items-center justify-between mb-8 space-y-4 sm:flex-row sm:space-y-0">
    <div>
      <h1 class="text-3xl font-bold text-gray-800">Bookings</h1>
      <p class="text-gray-600">Create, view, and manage your bookings now</p>
    </div>
    <div class="flex items-center gap-3">
      <form method="GET" class="flex flex-wrap items-center gap-2">
        <label for="status" class="font-medium text-gray-700">Sort by:</label>
        <select name="status" id="status" class="p-2 border border-gray-300 rounded-lg">
          <option value="">All Status</option>
          <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
          <option value="confirmed" <?= $status_filter === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
          <option value="completed" <?= $status_filter === 'completed' ? 'selected' : '' ?>>Completed</option>
          <option value="canceled" <?= $status_filter === 'canceled' ? 'selected' : '' ?>>Canceled</option>
        </select>
        <select name="sort_by" id="sort_by" class="p-2 border border-gray-300 rounded-lg">
          <option value="date_desc" <?= $sort_by === 'date_desc' ? 'selected' : '' ?>>Date (Newest)</option>
          <option value="date_asc" <?= $sort_by === 'date_asc' ? 'selected' : '' ?>>Date (Oldest)</option>
          <option value="cost_desc" <?= $sort_by === 'cost_desc' ? 'selected' : '' ?>>Cost (High → Low)</option>
          <option value="cost_asc" <?= $sort_by === 'cost_asc' ? 'selected' : '' ?>>Cost (Low → High)</option>
          <option value="name_asc" <?= $sort_by === 'name_asc' ? 'selected' : '' ?>>Name (A → Z)</option>
          <option value="name_desc" <?= $sort_by === 'name_desc' ? 'selected' : '' ?>>Name (Z → A)</option>
        </select>
        <button type="submit" class="px-3 py-2 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700">Apply</button>
      </form>
      <button class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">+ Create Booking</button>
    </div>
  </div>

  <?php if ($result && $result->num_rows > 0): ?>

<div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
  <?php while ($row = $result->fetch_assoc()): 
    $statusColor = match (strtolower($row['status'])) {
      'pending' => 'bg-yellow-100 text-yellow-800',
      'confirmed' => 'bg-green-100 text-green-800',
      'completed' => 'bg-blue-100 text-blue-800',
      'canceled' => 'bg-red-100 text-red-800',
      default => 'bg-gray-100 text-gray-800',
    };
  ?>
  <div class="card-container p-5 bg-white shadow-md rounded-2xl">
    <div class="flex items-start justify-between mb-3">
      <h2 class="text-lg font-semibold text-gray-900 truncate"><?php echo htmlspecialchars($row['event_name']); ?></h2>
      <span class="px-3 py-1 text-xs font-medium rounded-full <?php echo $statusColor; ?>">
        <?php echo ucfirst($row['status']); ?>
      </span>
    </div>
    <div class="space-y-1 text-sm text-gray-700">
      <p><span class="font-semibold">Client:</span> <?php echo htmlspecialchars($row['client_name']); ?></p>
      <p><span class="font-semibold">Venue:</span> <?php echo htmlspecialchars($row['venue_name']); ?></p>
      <p><span class="font-semibold">Date:</span> <?php echo date('M d, Y', strtotime($row['event_date'])); ?></p>
      <p><span class="font-semibold">Total Cost:</span> ₱<?php echo number_format($row['total_cost'], 2); ?></p>
    </div>
    <div class="card-actions mt-3">
      <button class="view-btn px-3 py-1 text-xs font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors" data-booking='<?php echo htmlentities(json_encode($row)); ?>'>View</button>
      <button class="edit-btn px-3 py-1 text-xs font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors" data-booking='<?php echo htmlentities(json_encode($row)); ?>'>Edit</button>
      <button class="status-btn px-3 py-1 text-xs font-semibold text-white bg-purple-600 rounded-lg hover:bg-purple-700 transition-colors" data-id="<?php echo $row['event_id']; ?>">Change</button>
      <button class="delete-btn px-3 py-1 text-xs font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors" data-id="<?php echo $row['event_id']; ?>">Delete</button>
    </div>
  </div>
  <?php endwhile; ?>
</div>

  <?php else: ?>

<div class="py-20 text-center text-gray-500">
  <i class="mb-3 text-5xl text-gray-400 fas fa-calendar-times"></i>
  <p class="text-lg">No bookings found.</p>
</div>

  <?php endif; ?>

</main>

<!-- MODALS (retained as before) -->

<?php include 'bookings-modals.php'; ?>

<script>
// JS logic for modals (same as before)
<?php include 'bookings-modals-js.php'; ?>
</script>

</body>
</html>