<?php
session_start();

// Check if user is logged in and is an organizer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
    header("Location: ../signin.php");
    exit();
}

require_once '../../../src/services/dbconnect.php';

$first_name = $_SESSION['first_name'] ?? 'Organizer';
$user_id = $_SESSION['user_id'];

// Fetch venues with amenities
$venues_query = "
    SELECT v.venue_id, v.venue_name, v.location, v.capacity, v.base_price, v.description
    FROM venues v 
    WHERE v.availability_status = 'available'
    ORDER BY v.venue_name
";
$venues_result = $conn->query($venues_query);

// Fetch all amenities grouped by venue_id
$amenities_query = "SELECT venue_id, amenity_name FROM venue_amenities";
$amenities_result = $conn->query($amenities_query);
$amenities_by_venue = [];
while ($row = $amenities_result->fetch_assoc()) {
    $amenities_by_venue[$row['venue_id']][] = $row['amenity_name'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Venues | Gatherly</title>
    <link rel="icon" type="image/x-icon" href="../../assets/images/logo.png">
    <link rel="stylesheet" href="../../../src/output.css?v=<?php echo filemtime(__DIR__ . '/../../../src/output.css'); ?>">
    <script src="https://kit.fontawesome.com/2a99de0fa5.js" crossorigin="anonymous"></script>
</head>
<body class="bg-linear-to-br from-indigo-50 via-white to-cyan-50 font-['Montserrat']">
    <!-- Navbar: copied exactly from organizer-dashboard.php -->
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
                    <a href="organizer-dashboard.php" class="text-gray-700 transition-colors hover:text-indigo-600">Dashboard</a>
                    <a href="my-events.php" class="text-gray-700 transition-colors hover:text-indigo-600">My Events</a>
                    <a href="find-venues.php" class="font-semibold text-indigo-600 transition-colors hover:text-indigo-700">Find Venues</a>
                    <a href="ai-planner.php" class="text-gray-700 transition-colors hover:text-indigo-600">AI Planner</a>
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
    <div class="container px-4 py-8 mx-auto sm:px-6 lg:px-8">
        <!-- Back Button -->
        <div class="flex items-center gap-4 mb-6">
            <a href="javascript:history.back()" class="text-gray-600 transition-colors hover:text-indigo-600">
                <i class="text-2xl fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Find Venues</h1>
                <p class="text-gray-600">Browse and select the perfect venue for your upcoming events</p>
            </div>
        </div>

        <!-- Search and Filters (simplified for now – can enhance later) -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex-1">
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input
                            type="text"
                            id="searchInput"
                            placeholder="Search venues by name or location..."
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            oninput="filterVenues()"
                        >
                    </div>
                </div>
            </div>
        </div>

        <!-- Venue Cards -->
        <div id="venuesContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if ($venues_result && $venues_result->num_rows > 0): ?>
                <?php while ($venue = $venues_result->fetch_assoc()): ?>
                    <?php
                    $amenities = $amenities_by_venue[$venue['venue_id']] ?? [];
                    $amenities_html = '';
                    $more_count = 0;
                    if (count($amenities) > 3) {
                        $display = array_slice($amenities, 0, 3);
                        $more_count = count($amenities) - 3;
                    } else {
                        $display = $amenities;
                    }
                    foreach ($display as $a) {
                        $amenities_html .= '<span class="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded-md">' . htmlspecialchars($a) . '</span>';
                    }
                    if ($more_count > 0) {
                        $amenities_html .= '<span class="px-2 py-1 bg-gray-100 text-gray-500 text-xs rounded-md">+' . $more_count . ' more</span>';
                    }
                    ?>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
                        <div class="relative">
                            <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                <span class="text-gray-500">No image</span>
                            </div>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($venue['venue_name']); ?></h3>
                            <div class="flex items-center text-gray-600 mb-3">
                                <i class="fas fa-map-marker-alt mr-2"></i>
                                <span class="text-sm"><?php echo htmlspecialchars($venue['location']); ?></span>
                            </div>
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-users mr-2"></i>
                                    <span class="text-sm"><?php echo $venue['capacity']; ?> capacity</span>
                                </div>
                                <span class="text-lg font-bold text-indigo-600">₱<?php echo number_format($venue['base_price'], 2); ?></span>
                            </div>
                            <?php if (!empty($amenities)): ?>
                            <div class="mb-4">
                                <p class="text-sm font-medium text-gray-900 mb-2">Amenities:</p>
                                <div class="flex flex-wrap gap-2">
                                    <?php echo $amenities_html; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            <a href="create-event.php?venue_id=<?php echo $venue['venue_id']; ?>"
                               class="w-full block text-center bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-lg font-medium transition-colors">
                                Select Venue
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full text-center py-12">
                    <div class="text-gray-400 mb-4">
                        <i class="fas fa-map-marker-alt text-4xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No venues available</h3>
                    <p class="text-gray-600">Check back later or contact support.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function filterVenues() {
            const term = document.getElementById('searchInput').value.toLowerCase();
            const cards = document.querySelectorAll('#venuesContainer > div');
            let count = 0;
            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                if (text.includes(term)) {
                    card.classList.remove('hidden');
                    count++;
                } else {
                    card.classList.add('hidden');
                }
            });
        }
    </script>
</body>
</html>