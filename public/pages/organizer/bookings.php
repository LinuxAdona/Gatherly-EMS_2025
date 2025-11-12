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

// Fetch ALL events (bookings) for this organizer, newest first
$query = "
    SELECT 
        e.event_id,
        e.event_name,
        e.event_type,
        e.event_date,
        e.status,
        e.total_cost,
        v.venue_name,
        v.location
    FROM events e
    LEFT JOIN venues v ON e.venue_id = v.venue_id
    WHERE e.client_id = ?
    ORDER BY e.created_at DESC
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
    <title>My Bookings | Gatherly</title>
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
                    <a href="my-events.php"
                        class="font-semibold text-indigo-600 transition-colors hover:text-indigo-700">My Events</a>
                    <a href="find-venues.php" class="text-gray-700 transition-colors hover:text-indigo-600">Find
                        Venues</a>
                    <a href="ai-planner.php" class="text-gray-700 transition-colors hover:text-indigo-600">AI
                        Planner</a>
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
                <h1 class="text-3xl font-bold text-gray-800 mb-2">My Bookings</h1>
                <p class="text-gray-600">Manage all your event bookings and reservations</p>
            </div>
        </div>

        <!-- Booking Cards -->
        <?php if ($result && $result->num_rows > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php while ($event = $result->fetch_assoc()): ?>
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-3">
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
                                        case 'completed':
                                            echo 'bg-blue-100 text-blue-700';
                                            break;
                                        case 'canceled':
                                            echo 'bg-red-100 text-red-700';
                                            break;
                                        default:
                                            echo 'bg-gray-100 text-gray-700';
                                    }
                                    ?>">
                                    <?php echo ucfirst($event['status']); ?>
                                </span>
                            </div>

                            <?php if (!empty($event['venue_name'])): ?>
                                <p class="text-sm text-gray-600 mb-2">
                                    <i class="fas fa-building mr-2 text-indigo-600"></i>
                                    <?php echo htmlspecialchars($event['venue_name']); ?>
                                </p>
                            <?php endif; ?>

                            <?php if (!empty($event['location'])): ?>
                                <p class="text-sm text-gray-600 mb-2">
                                    <i class="fas fa-map-marker-alt mr-2 text-indigo-600"></i>
                                    <?php echo htmlspecialchars($event['location']); ?>
                                </p>
                            <?php endif; ?>

                            <p class="text-sm text-gray-600 mb-2">
                                <i class="fas fa-calendar mr-2 text-indigo-600"></i>
                                <?php echo date('M d, Y \a\t g:i A', strtotime($event['event_date'])); ?>
                            </p>

                            <p class="text-lg font-bold text-indigo-600 mt-3">
                                ₱<?php echo number_format($event['total_cost'] ?? 0, 2); ?>
                            </p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <div class="text-gray-400 mb-4">
                    <i class="fas fa-ticket-alt text-4xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No bookings yet</h3>
                <p class="text-gray-600">You haven’t created any event bookings.</p>
                <a href="find-venues.php"
                    class="mt-4 inline-block px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors">
                    Find a Venue & Book
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php include '../../../src/components/Footer.php'; ?>
</body>

</html>