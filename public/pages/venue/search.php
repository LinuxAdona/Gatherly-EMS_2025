<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../signin.php');
    exit();
}

require_once '../../../src/services/dbconnect.php';
require_once '../../../src/models/RecommendationEngine.php';
require_once '../../../src/models/DynamicPricingEngine.php';

$recommendationEngine = new RecommendationEngine($conn);
$pricingEngine = new DynamicPricingEngine($conn);

// Get user information
$userId = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Handle search/recommendation request
$recommendations = [];
$searchPerformed = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $searchPerformed = true;

    // Track inquiries for dynamic pricing
    $trackVenues = $_POST['track_venues'] ?? '';
    if ($trackVenues) {
        $venueIds = explode(',', $trackVenues);
        foreach ($venueIds as $vid) {
            $pricingEngine->trackInquiry($vid, $_POST['event_date'] ?? null);
        }
    }

    // Build criteria from form
    $criteria = [
        'event_type' => $_POST['event_type'] ?? '',
        'event_date' => $_POST['event_date'] ?? null,
        'guest_count' => intval($_POST['guest_count'] ?? 0),
        'budget' => floatval($_POST['budget'] ?? 0),
        'latitude' => floatval($_POST['latitude'] ?? 0),
        'longitude' => floatval($_POST['longitude'] ?? 0),
        'required_amenities' => []
    ];

    // Parse amenities
    if (isset($_POST['catering'])) $criteria['required_amenities'][] = 'catering';
    if (isset($_POST['sound_system'])) $criteria['required_amenities'][] = 'sound system';
    if (isset($_POST['parking'])) $criteria['required_amenities'][] = 'parking';
    if (isset($_POST['stage_setup'])) $criteria['required_amenities'][] = 'stage setup';
    if (isset($_POST['air_conditioning'])) $criteria['required_amenities'][] = 'air conditioning';

    // Custom weights if provided
    $customWeights = null;
    if (isset($_POST['use_custom_weights']) && $_POST['use_custom_weights'] == '1') {
        $customWeights = [
            'capacity' => floatval($_POST['weight_capacity'] ?? 0.25),
            'price' => floatval($_POST['weight_price'] ?? 0.30),
            'location' => floatval($_POST['weight_location'] ?? 0.20),
            'amenities' => floatval($_POST['weight_amenities'] ?? 0.15),
            'availability' => floatval($_POST['weight_availability'] ?? 0.10)
        ];
    }

    // Get recommendations
    $recommendations = $recommendationEngine->getRecommendations($criteria, 3, $customWeights);

    // Add pricing information
    foreach ($recommendations as &$rec) {
        $venueId = $rec['venue']['venue_id'];
        $eventDate = $criteria['event_date'] ?? date('Y-m-d', strtotime('+1 month'));
        $pricingData = $pricingEngine->calculatePrice($venueId, $eventDate);
        $rec['pricing'] = $pricingData;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Your Perfect Venue | Gatherly</title>
    <link rel="icon" type="image/x-icon" href="../../assets/images/logo.png">
    <link rel="stylesheet" href="../../../src/output.css">
    <script src="https://kit.fontawesome.com/2a99de0fa5.js" crossorigin="anonymous"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY&libraries=places"></script>
</head>

<body class="bg-gray-50 font-['Montserrat']">
    <!-- Navigation -->
    <nav class="sticky top-0 z-50 w-full shadow-md bg-white border-b border-gray-200">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="../home.php" class="flex items-center group">
                    <img class="w-8 h-8 mr-2 transition-transform group-hover:scale-110" src="../../assets/images/logo.png" alt="Logo">
                    <span class="text-xl font-bold text-gray-800">Gatherly</span>
                </a>
                <div class="flex items-center gap-4">
                    <a href="../dashboard.php" class="text-sm font-semibold text-gray-700 hover:text-indigo-600">Dashboard</a>
                    <a href="chat.php" class="text-sm font-semibold text-gray-700 hover:text-indigo-600">Messages</a>
                    <span class="text-sm text-gray-600">Welcome, <?= htmlspecialchars($user['first_name']) ?></span>
                    <a href="../../services/logout.php" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Find Your Perfect Venue</h1>
            <p class="text-gray-600">Our AI-powered system will recommend the best venues based on your requirements</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Search Form -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-lg p-6 sticky top-24">
                    <h2 class="text-xl font-bold mb-4 text-gray-900">Event Details</h2>
                    <form method="POST" id="searchForm">
                        <!-- Event Type -->
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Event Type *</label>
                            <select name="event_type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                <option value="">Select type</option>
                                <option value="Wedding">Wedding</option>
                                <option value="Birthday">Birthday</option>
                                <option value="Corporate">Corporate Event</option>
                                <option value="Concert">Concert</option>
                                <option value="Conference">Conference</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <!-- Event Date -->
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Event Date *</label>
                            <input type="date" name="event_date" required min="<?= date('Y-m-d') ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <!-- Guest Count -->
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Expected Guests *</label>
                            <input type="number" name="guest_count" required min="1" placeholder="e.g., 150"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <!-- Budget -->
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Budget (₱) *</label>
                            <input type="number" name="budget" required min="1000" step="1000" placeholder="e.g., 120000"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <!-- Location -->
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Preferred Location (Optional)</label>
                            <input type="text" id="location-input" placeholder="Enter address or click on map"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <input type="hidden" name="latitude" id="latitude">
                            <input type="hidden" name="longitude" id="longitude">
                            <p class="text-xs text-gray-500 mt-1">We'll find venues closest to this location</p>
                        </div>

                        <!-- Required Amenities -->
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Required Amenities</label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" name="catering" class="mr-2 rounded text-indigo-600">
                                    <span class="text-sm">Catering Available</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="sound_system" class="mr-2 rounded text-indigo-600">
                                    <span class="text-sm">Sound System</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="parking" class="mr-2 rounded text-indigo-600">
                                    <span class="text-sm">Parking Space</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="stage_setup" class="mr-2 rounded text-indigo-600">
                                    <span class="text-sm">Stage Setup</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="air_conditioning" class="mr-2 rounded text-indigo-600">
                                    <span class="text-sm">Air Conditioning</span>
                                </label>
                            </div>
                        </div>

                        <!-- Advanced Options -->
                        <div class="mb-4">
                            <button type="button" onclick="toggleAdvanced()" class="text-sm text-indigo-600 hover:text-indigo-700 font-semibold">
                                <i class="fas fa-sliders-h mr-1"></i> Advanced Options
                            </button>
                            <div id="advanced-options" class="hidden mt-4 p-4 bg-gray-50 rounded-lg">
                                <p class="text-xs text-gray-600 mb-3">Customize the importance of each factor:</p>
                                <input type="hidden" name="use_custom_weights" id="use_custom_weights" value="0">

                                <div class="space-y-3">
                                    <div>
                                        <label class="text-xs font-semibold text-gray-700">Capacity Match</label>
                                        <input type="range" name="weight_capacity" min="0" max="1" step="0.05" value="0.25" class="w-full">
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-gray-700">Price/Budget</label>
                                        <input type="range" name="weight_price" min="0" max="1" step="0.05" value="0.30" class="w-full">
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-gray-700">Location</label>
                                        <input type="range" name="weight_location" min="0" max="1" step="0.05" value="0.20" class="w-full">
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-gray-700">Amenities</label>
                                        <input type="range" name="weight_amenities" min="0" max="1" step="0.05" value="0.15" class="w-full">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="track_venues" id="track_venues">

                        <button type="submit" class="w-full px-4 py-3 font-semibold text-white bg-indigo-600 rounded-lg shadow-md hover:bg-indigo-700 hover:shadow-lg transition-all">
                            <i class="fas fa-search mr-2"></i> Find Perfect Venues
                        </button>
                    </form>
                </div>
            </div>

            <!-- Results -->
            <div class="lg:col-span-2">
                <?php if ($searchPerformed): ?>
                    <?php if (empty($recommendations)): ?>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 text-center">
                            <i class="fas fa-exclamation-triangle text-yellow-600 text-3xl mb-3"></i>
                            <p class="text-yellow-800 font-semibold">No venues match your criteria</p>
                            <p class="text-yellow-700 text-sm mt-2">Try adjusting your budget, date, or requirements</p>
                        </div>
                    <?php else: ?>
                        <div class="mb-4">
                            <h2 class="text-2xl font-bold text-gray-900 mb-2">Top Recommendations for You</h2>
                            <p class="text-gray-600">Based on your requirements, here are the best matches</p>
                        </div>

                        <div class="space-y-6">
                            <?php foreach ($recommendations as $index => $rec): ?>
                                <?php $venue = $rec['venue']; ?>
                                <?php $pricing = $rec['pricing']; ?>
                                <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
                                    <!-- Rank Badge -->
                                    <div class="relative">
                                        <div class="absolute top-4 left-4 z-10">
                                            <span class="px-3 py-1 bg-indigo-600 text-white font-bold rounded-full text-sm">
                                                #<?= $index + 1 ?> Best Match
                                            </span>
                                        </div>
                                        <div class="absolute top-4 right-4 z-10">
                                            <span class="px-3 py-1 bg-green-600 text-white font-bold rounded-full text-lg">
                                                <?= $rec['match_percentage'] ?> Match
                                            </span>
                                        </div>
                                        <!-- Venue Image Placeholder -->
                                        <div class="w-full h-48 bg-gradient-to-r from-indigo-400 to-purple-500 flex items-center justify-center">
                                            <i class="fas fa-building text-white text-6xl opacity-50"></i>
                                        </div>
                                    </div>

                                    <div class="p-6">
                                        <div class="flex justify-between items-start mb-4">
                                            <div>
                                                <h3 class="text-2xl font-bold text-gray-900 mb-1"><?= htmlspecialchars($venue['venue_name']) ?></h3>
                                                <p class="text-gray-600 flex items-center">
                                                    <i class="fas fa-map-marker-alt mr-2"></i>
                                                    <?= htmlspecialchars($venue['location']) ?>
                                                </p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-sm text-gray-500 line-through">₱<?= number_format($pricing['base_price'], 2) ?></p>
                                                <p class="text-2xl font-bold text-green-600">₱<?= number_format($pricing['calculated_price'], 2) ?></p>
                                                <?php if ($pricing['discount_percentage'] > 0): ?>
                                                    <span class="inline-block px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded">
                                                        <?= $pricing['discount_percentage'] ?>% OFF
                                                    </span>
                                                <?php endif; ?>
                                                <p class="text-xs text-gray-500 mt-1"><?= $pricing['pricing_type'] ?></p>
                                            </div>
                                        </div>

                                        <!-- Match Score Breakdown -->
                                        <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                                            <p class="text-sm font-semibold text-gray-700 mb-2">Why This Venue?</p>
                                            <p class="text-sm text-gray-600 mb-3"><?= htmlspecialchars($rec['reason']) ?></p>

                                            <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                                                <div class="text-center">
                                                    <p class="text-xs text-gray-500">Capacity</p>
                                                    <p class="text-sm font-bold text-indigo-600"><?= $rec['capacity_score'] ?>%</p>
                                                </div>
                                                <div class="text-center">
                                                    <p class="text-xs text-gray-500">Price</p>
                                                    <p class="text-sm font-bold text-indigo-600"><?= $rec['price_score'] ?>%</p>
                                                </div>
                                                <div class="text-center">
                                                    <p class="text-xs text-gray-500">Location</p>
                                                    <p class="text-sm font-bold text-indigo-600"><?= $rec['location_score'] ?>%</p>
                                                </div>
                                                <div class="text-center">
                                                    <p class="text-xs text-gray-500">Amenities</p>
                                                    <p class="text-sm font-bold text-indigo-600"><?= $rec['amenities_score'] ?>%</p>
                                                </div>
                                                <div class="text-center">
                                                    <p class="text-xs text-gray-500">Available</p>
                                                    <p class="text-sm font-bold text-indigo-600"><?= $rec['availability_score'] ?>%</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Venue Details -->
                                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-users text-indigo-600"></i>
                                                <div>
                                                    <p class="text-xs text-gray-500">Capacity</p>
                                                    <p class="text-sm font-semibold"><?= $venue['capacity'] ?> guests</p>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-car text-indigo-600"></i>
                                                <div>
                                                    <p class="text-xs text-gray-500">Parking</p>
                                                    <p class="text-sm font-semibold"><?= $venue['parking_capacity'] ?> slots</p>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-star text-indigo-600"></i>
                                                <div>
                                                    <p class="text-xs text-gray-500">Rating</p>
                                                    <p class="text-sm font-semibold"><?= $venue['average_rating'] ?>/5.0</p>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-calendar-check text-indigo-600"></i>
                                                <div>
                                                    <p class="text-xs text-gray-500">Bookings</p>
                                                    <p class="text-sm font-semibold"><?= $venue['total_bookings'] ?>+ events</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Amenities -->
                                        <div class="mb-4">
                                            <p class="text-sm font-semibold text-gray-700 mb-2">Amenities:</p>
                                            <div class="flex flex-wrap gap-2">
                                                <?php if ($venue['catering_available']): ?>
                                                    <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-semibold">
                                                        <i class="fas fa-utensils mr-1"></i> Catering
                                                    </span>
                                                <?php endif; ?>
                                                <?php if ($venue['sound_system_available']): ?>
                                                    <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-semibold">
                                                        <i class="fas fa-volume-up mr-1"></i> Sound System
                                                    </span>
                                                <?php endif; ?>
                                                <?php if ($venue['has_stage_setup']): ?>
                                                    <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-semibold">
                                                        <i class="fas fa-theater-masks mr-1"></i> Stage
                                                    </span>
                                                <?php endif; ?>
                                                <?php if ($venue['parking_capacity'] > 0): ?>
                                                    <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-semibold">
                                                        <i class="fas fa-parking mr-1"></i> Parking
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <!-- Action Buttons -->
                                        <div class="flex flex-col sm:flex-row gap-3">
                                            <button onclick="viewDetails(<?= $venue['venue_id'] ?>)"
                                                class="flex-1 px-4 py-2 font-semibold text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors">
                                                <i class="fas fa-info-circle mr-2"></i> View Details
                                            </button>
                                            <button onclick="bookVenue(<?= $venue['venue_id'] ?>)"
                                                class="flex-1 px-4 py-2 font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">
                                                <i class="fas fa-calendar-plus mr-2"></i> Book Now
                                            </button>
                                            <button onclick="chatWithManager(<?= $venue['venue_id'] ?>)"
                                                class="flex-1 px-4 py-2 font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">
                                                <i class="fas fa-comments mr-2"></i> Chat
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- Initial State -->
                    <div class="bg-white rounded-xl shadow-lg p-12 text-center">
                        <i class="fas fa-search text-indigo-600 text-6xl mb-4"></i>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Start Your Search</h3>
                        <p class="text-gray-600 mb-4">Fill in your event details to get personalized venue recommendations</p>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-8">
                            <div class="p-4 bg-indigo-50 rounded-lg">
                                <i class="fas fa-robot text-indigo-600 text-3xl mb-2"></i>
                                <p class="text-sm font-semibold text-gray-800">AI-Powered Matching</p>
                            </div>
                            <div class="p-4 bg-indigo-50 rounded-lg">
                                <i class="fas fa-chart-line text-indigo-600 text-3xl mb-2"></i>
                                <p class="text-sm font-semibold text-gray-800">Dynamic Pricing</p>
                            </div>
                            <div class="p-4 bg-indigo-50 rounded-lg">
                                <i class="fas fa-percent text-indigo-600 text-3xl mb-2"></i>
                                <p class="text-sm font-semibold text-gray-800">Best Deals</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function toggleAdvanced() {
            const advancedOptions = document.getElementById('advanced-options');
            const useCustomWeights = document.getElementById('use_custom_weights');
            advancedOptions.classList.toggle('hidden');
            useCustomWeights.value = advancedOptions.classList.contains('hidden') ? '0' : '1';
        }

        function viewDetails(venueId) {
            window.location.href = `venue-details.php?id=${venueId}`;
        }

        function bookVenue(venueId) {
            window.location.href = `booking.php?venue_id=${venueId}`;
        }

        function chatWithManager(venueId) {
            window.location.href = `../chat/venue-chat.php?venue_id=${venueId}`;
        }

        // Google Maps Autocomplete
        let autocomplete;

        function initAutocomplete() {
            const input = document.getElementById('location-input');
            autocomplete = new google.maps.places.Autocomplete(input);

            autocomplete.addListener('place_changed', function() {
                const place = autocomplete.getPlace();
                if (place.geometry) {
                    document.getElementById('latitude').value = place.geometry.location.lat();
                    document.getElementById('longitude').value = place.geometry.location.lng();
                }
            });
        }

        window.addEventListener('load', initAutocomplete);

        // Track venue views for demand analytics
        document.getElementById('searchForm').addEventListener('submit', function(e) {
            // This will be sent to track inquiries
            const venues = <?= json_encode(array_column(array_column($recommendations, 'venue'), 'venue_id')) ?>;
            document.getElementById('track_venues').value = venues.join(',');
        });
    </script>
</body>

</html>