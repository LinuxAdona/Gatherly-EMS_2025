<?php
session_start();

// Ensure user is logged in and is an organizer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
    header("Location: ../signin.php");
    exit();
}

require_once '../../../src/services/dbconnect.php';

$first_name = $_SESSION['first_name'] ?? 'Organizer';
$user_id = $_SESSION['user_id'];

// Fetch organizer's upcoming or ongoing events with venue info
$query = "
    SELECT 
        e.event_id,
        e.event_name,
        e.event_type,
        e.event_date,
        e.status,
        e.total_cost,
        v.venue_name,
        v.location,
        v.capacity
    FROM events e
    LEFT JOIN venues v ON e.venue_id = v.venue_id
    WHERE e.client_id = ?
      AND e.event_date >= NOW()
      AND e.status IN ('pending', 'confirmed')
    ORDER BY e.event_date ASC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Venues | Gatherly</title>
    <link rel="icon" type="image/x-icon" href="../../assets/images/logo.png">
    <link rel="stylesheet"
        href="../../../src/output.css?v=<?php echo filemtime(__DIR__ . '/../../../src/output.css'); ?>">
    <script src="https://kit.fontawesome.com/2a99de0fa5.js" crossorigin="anonymous"></script>
</head>

<body class="bg-linear-to-br from-indigo-50 via-white to-cyan-50 font-['Montserrat'] min-h-screen flex flex-col">
    <!-- Navbar: exact copy from organizer-dashboard.php -->
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
                    <a href="organizer-dashboard.php"
                        class="text-gray-700 transition-colors hover:text-indigo-600">Dashboard</a>
                    <a href="my-events.php" class="font-semibold text-indigo-600 transition-colors hover:text-indigo-700">My Events</a>
                    <a href="find-venues.php" class="text-gray-700 transition-colors hover:text-indigo-600">Find
                        Venues</a>
                    <a href="ai-planner.php"
                        class="text-gray-700 transition-colors hover:text-indigo-600">AI Planner</a>
                    <a href="chats.php" class="text-gray-700 transition-colors hover:text-indigo-600">Chat</a>
                    <div class="relative">
                        <button id="profile-dropdown-btn"
                            class="flex items-center gap-2 text-gray-700 transition-colors hover:text-indigo-600">
                            <i class="text-2xl fas fa-user-circle"></i>
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
    <div class="container px-4 py-8 mx-auto sm:px-6 lg:px-8 grow">
        <!-- Back Button -->
        <div class="flex items-center gap-4 mb-6">
            <a href="javascript:history.back()" class="text-gray-600 transition-colors hover:text-indigo-600">
                <i class="text-2xl fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="mb-2 text-3xl font-bold text-gray-800">My Venues</h1>
                <p class="text-gray-600">View your booked or upcoming venue assignments</p>
            </div>
        </div>

        <!-- Event Cards -->
        <?php if ($result && $result->num_rows > 0): ?>
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                <?php while ($event = $result->fetch_assoc()): ?>
                    <div
                        class="overflow-hidden transition-shadow bg-white border border-gray-200 shadow-sm rounded-xl hover:shadow-md">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-3">
                                <h3 class="text-xl font-bold text-gray-900">
                                    <?php echo htmlspecialchars($event['event_name']); ?></h3>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full
                                    <?php
                                    switch ($event['status']) {
                                        case 'confirmed':
                                            echo 'bg-green-100 text-green-700';
                                            break;
                                        case 'pending':
                                            echo 'bg-yellow-100 text-yellow-700';
                                            break;
                                        default:
                                            echo 'bg-gray-100 text-gray-700';
                                    }
                                    ?>">
                                    <?php echo ucfirst($event['status']); ?>
                                </span>
                            </div>

                            <p class="mb-2 text-sm text-gray-600">
                                <i class="mr-2 text-indigo-600 fas fa-building"></i>
                                <?php echo htmlspecialchars($event['venue_name'] ?? '—'); ?>
                            </p>

                            <p class="mb-2 text-sm text-gray-600">
                                <i class="mr-2 text-indigo-600 fas fa-map-marker-alt"></i>
                                <?php echo htmlspecialchars($event['location'] ?? 'No location'); ?>
                            </p>

                            <p class="mb-2 text-sm text-gray-600">
                                <i class="mr-2 text-indigo-600 fas fa-calendar"></i>
                                <?php echo date('M d, Y \a\t g:i A', strtotime($event['event_date'])); ?>
                            </p>

                            <p class="mb-2 text-sm text-gray-600">
                                <i class="mr-2 text-indigo-600 fas fa-users"></i>
                                Capacity: <?php echo $event['capacity'] ?? '—'; ?>
                            </p>

                            <p class="mt-3 text-lg font-bold text-indigo-600">
                                ₱<?php echo number_format($event['total_cost'] ?? 0, 2); ?>
                            </p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="py-12 text-center">
                <div class="mb-4 text-gray-400">
                    <i class="text-4xl fas fa-calendar-check"></i>
                </div>
                <h3 class="mb-2 text-lg font-medium text-gray-900">No upcoming venue bookings</h3>
                <p class="text-gray-600">You haven’t booked any venues for future events yet.</p>
                <a href="find-venues.php"
                    class="inline-block px-4 py-2 mt-4 font-medium text-white transition-colors bg-indigo-600 rounded-lg hover:bg-indigo-700">
                    Find a Venue
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php include '../../../src/components/Footer.php'; ?>
</body>

</html>