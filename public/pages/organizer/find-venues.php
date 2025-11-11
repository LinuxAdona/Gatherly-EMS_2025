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
$all_amenities = [];
while ($row = $amenities_result->fetch_assoc()) {
    $amenities_by_venue[$row['venue_id']][] = $row['amenity_name'];
    $all_amenities[] = $row['amenity_name'];
}
$all_amenities = array_unique($all_amenities);
sort($all_amenities);

// Get min/max price and capacity for filters
$stats_query = "SELECT MIN(base_price) as min_price, MAX(base_price) as max_price, MIN(capacity) as min_cap, MAX(capacity) as max_cap FROM venues WHERE availability_status = 'available'";
$stats = $conn->query($stats_query)->fetch_assoc();
$min_price = (int)($stats['min_price'] ?? 0);
$max_price = (int)($stats['max_price'] ?? 100000);
$min_cap = (int)($stats['min_cap'] ?? 0);
$max_cap = (int)($stats['max_cap'] ?? 1000);

// Fetch unique locations
$locations_query = "SELECT DISTINCT location FROM venues WHERE availability_status = 'available' ORDER BY location";
$locations_result = $conn->query($locations_query);
$locations = [];
while ($row = $locations_result->fetch_assoc()) {
    $locations[] = $row['location'];
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
    <style>
        .filter-drawer {
            position: fixed;
            top: 0;
            right: 0;
            height: 100vh;
            width: 100%;
            max-width: 320px;
            background: white;
            box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            overflow-y: auto;
        }

        .filter-drawer.open {
            transform: translateX(0);
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .overlay.open {
            opacity: 1;
            visibility: visible;
        }

        @media (max-width: 1023px) {
            .filter-drawer {
                max-width: 100%;
            }
        }
    </style>
</head>

<body class="bg-linear-to-br from-indigo-50 via-white to-cyan-50 font-['Montserrat'] min-h-screen flex flex-col">
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
    <div class="container px-4 py-8 mx-auto sm:px-6 lg:px-8 grow">
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

        <!-- Search + Filter Button: SIDE-BY-SIDE -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-2 mb-6 flex flex-col sm:flex-row gap-2">
            <div class="relative flex-1">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input
                    type="text"
                    id="searchInput"
                    placeholder="Search venues by name or location..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    oninput="applyFilters()">
            </div>
            <button onclick="openFilterDrawer()"
                class="px-4 py-2 bg-indigo-100 text-indigo-700 font-medium rounded-lg hover:bg-indigo-200 whitespace-nowrap flex items-center justify-center">
                <i class="fas fa-filter mr-2"></i> Filters
            </button>
        </div>

        <!-- Venue Listings -->
        <div id="venuesContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
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
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow venue-card"
                        data-name="<?php echo htmlspecialchars($venue['venue_name']); ?>"
                        data-location="<?php echo htmlspecialchars($venue['location']); ?>"
                        data-capacity="<?php echo $venue['capacity']; ?>"
                        data-price="<?php echo $venue['base_price']; ?>"
                        data-amenities="<?php echo implode(',', array_map('htmlspecialchars', $amenities)); ?>">
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

    <!-- Filter Drawer -->
    <div id="filterDrawer" class="filter-drawer">
        <div class="p-4">
            <div class="flex justify-between items-center mb-6">
                <h3 class="font-bold text-gray-800 text-lg">Filters</h3>
                <button onclick="closeFilterDrawer()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="space-y-5">
                <!-- Price Range -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Price Range (₱)</label>
                    <div class="text-sm text-gray-600 mb-1">
                        <span id="priceRangeText">₱<?php echo number_format($min_price, 0); ?> – ₱<?php echo number_format($max_price, 0); ?></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="range" id="priceMin" min="<?php echo $min_price; ?>" max="<?php echo $max_price; ?>" value="<?php echo $min_price; ?>" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer" oninput="updatePriceRange()">
                        <input type="range" id="priceMax" min="<?php echo $min_price; ?>" max="<?php echo $max_price; ?>" value="<?php echo $max_price; ?>" class="w-full h-2 bg-indigo-200 rounded-lg appearance-none cursor-pointer" oninput="updatePriceRange()">
                    </div>
                </div>

                <!-- Capacity -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Capacity (guests)</label>
                    <div class="text-sm text-gray-600 mb-1">
                        <span id="capRangeText"><?php echo $min_cap; ?> – <?php echo $max_cap; ?></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="range" id="capMin" min="<?php echo $min_cap; ?>" max="<?php echo $max_cap; ?>" value="<?php echo $min_cap; ?>" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer" oninput="updateCapRange()">
                        <input type="range" id="capMax" min="<?php echo $min_cap; ?>" max="<?php echo $max_cap; ?>" value="<?php echo $max_cap; ?>" class="w-full h-2 bg-indigo-200 rounded-lg appearance-none cursor-pointer" oninput="updateCapRange()">
                    </div>
                </div>

                <!-- Location -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Location</label>
                    <select id="locationFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">All Locations</option>
                        <?php foreach ($locations as $loc): ?>
                            <option value="<?php echo htmlspecialchars($loc); ?>"><?php echo htmlspecialchars($loc); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Amenities -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Amenities</label>
                    <div class="space-y-2 max-h-40 overflow-y-auto">
                        <?php foreach ($all_amenities as $amenity): ?>
                            <label class="flex items-center text-sm">
                                <input type="checkbox" class="amenity-checkbox rounded text-indigo-600" value="<?php echo htmlspecialchars($amenity); ?>">
                                <span class="ml-2"><?php echo htmlspecialchars($amenity); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button onclick="clearAllFilters()" class="w-full py-2 text-indigo-600 font-medium border border-indigo-200 rounded-lg hover:bg-indigo-50">
                    Clear All Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Overlay -->
    <div id="overlay" class="overlay" onclick="closeFilterDrawer()"></div>

    <script>
        function openFilterDrawer() {
            document.getElementById('filterDrawer').classList.add('open');
            document.getElementById('overlay').classList.add('open');
        }

        function closeFilterDrawer() {
            document.getElementById('filterDrawer').classList.remove('open');
            document.getElementById('overlay').classList.remove('open');
        }

        function updatePriceRange() {
            const minSlider = document.getElementById('priceMin');
            const maxSlider = document.getElementById('priceMax');
            const minVal = parseInt(minSlider.value);
            const maxVal = parseInt(maxSlider.value);

            if (minVal > maxVal) {
                minSlider.value = maxVal;
                maxSlider.value = minVal;
            }

            document.getElementById('priceRangeText').textContent =
                '₱' + parseInt(minSlider.value).toLocaleString() + ' – ₱' + parseInt(maxSlider.value).toLocaleString();
            applyFilters();
        }

        function updateCapRange() {
            const minSlider = document.getElementById('capMin');
            const maxSlider = document.getElementById('capMax');
            const minVal = parseInt(minSlider.value);
            const maxVal = parseInt(maxSlider.value);

            if (minVal > maxVal) {
                minSlider.value = maxVal;
                maxSlider.value = minVal;
            }

            document.getElementById('capRangeText').textContent =
                minSlider.value + ' – ' + maxSlider.value;
            applyFilters();
        }

        function applyFilters() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const minPrice = parseInt(document.getElementById('priceMin').value);
            const maxPrice = parseInt(document.getElementById('priceMax').value);
            const minCap = parseInt(document.getElementById('capMin').value);
            const maxCap = parseInt(document.getElementById('capMax').value);
            const locationFilter = document.getElementById('locationFilter').value;
            const selectedAmenities = Array.from(document.querySelectorAll('.amenity-checkbox:checked'))
                .map(cb => cb.value);

            const cards = document.querySelectorAll('.venue-card');
            cards.forEach(card => {
                const name = card.dataset.name.toLowerCase();
                const location = card.dataset.location.toLowerCase();
                const capacity = parseInt(card.dataset.capacity);
                const price = parseFloat(card.dataset.price);
                const amenities = card.dataset.amenities ? card.dataset.amenities.split(',') : [];

                let matches = true;

                if (searchTerm && !name.includes(searchTerm) && !location.includes(searchTerm)) matches = false;
                if (price < minPrice || price > maxPrice) matches = false;
                if (capacity < minCap || capacity > maxCap) matches = false;
                if (locationFilter && card.dataset.location !== locationFilter) matches = false;
                if (selectedAmenities.length > 0) {
                    for (let amenity of selectedAmenities) {
                        if (!amenities.includes(amenity)) {
                            matches = false;
                            break;
                        }
                    }
                }

                card.classList.toggle('hidden', !matches);
            });
        }

        function clearAllFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('priceMin').value = <?php echo $min_price; ?>;
            document.getElementById('priceMax').value = <?php echo $max_price; ?>;
            document.getElementById('capMin').value = <?php echo $min_cap; ?>;
            document.getElementById('capMax').value = <?php echo $max_cap; ?>;
            document.getElementById('locationFilter').value = '';
            document.querySelectorAll('.amenity-checkbox').forEach(cb => cb.checked = false);
            document.getElementById('priceRangeText').textContent = '₱<?php echo number_format($min_price, 0); ?> – ₱<?php echo number_format($max_price, 0); ?>';
            document.getElementById('capRangeText').textContent = '<?php echo $min_cap; ?> – <?php echo $max_cap; ?>';
            applyFilters();
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('#priceMin, #priceMax, #capMin, #capMax, #locationFilter').forEach(el => {
                el.addEventListener('input', applyFilters);
            });
            document.querySelectorAll('.amenity-checkbox').forEach(cb => {
                cb.addEventListener('change', applyFilters);
            });
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeFilterDrawer();
        });
    </script>

    <?php include '../../../src/components/Footer.php'; ?>
</body>

</html>