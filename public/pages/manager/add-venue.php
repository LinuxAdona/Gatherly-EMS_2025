<?php
session_start();

// Check if user is logged in and is a manager
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: ../signin.php");
    exit();
}

require_once '../../../src/services/dbconnect.php';

$first_name = $_SESSION['first_name'] ?? 'Manager';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $venue_name = $_POST['venue_name'];
    $location = $_POST['location'];
    $capacity = intval($_POST['capacity']);
    $base_price = floatval($_POST['base_price']);
    $price_percentage = floatval($_POST['price_percentage']);
    $description = $_POST['description'];
    $availability_status = $_POST['availability_status'] ?? 'available';
    $selected_amenities = $_POST['venue_amenities'] ?? [];

    // Compute dynamic pricing
    $peak_price = $base_price + ($base_price * ($price_percentage / 100));
    $offpeak_price = $base_price - ($base_price * ($price_percentage / 100));
    $weekday_price = $base_price;
    $weekend_price = $peak_price;

    // Handle image upload (optional)
    $imageData = null;
    if (!empty($_FILES['image']['tmp_name'])) {
        $imageData = file_get_contents($_FILES['image']['tmp_name']);
    }

    // Insert venue
    $stmt = $conn->prepare("INSERT INTO venues 
        (venue_name, location, capacity, base_price, peak_price, offpeak_price, weekday_price, weekend_price, description, availability_status, image, price_percentage)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssddddddssss", $venue_name, $location, $capacity, $base_price, $peak_price, $offpeak_price, $weekday_price, $weekend_price, $description, $availability_status, $imageData, $price_percentage);
    $stmt->send_long_data(10, $imageData);
    $stmt->execute();

    $venue_id = $conn->insert_id;
    $stmt->close();

    // Insert selected amenities
    if (!empty($selected_amenities)) {
        $stmtAmenity = $conn->prepare("INSERT INTO venue_amenities (venue_id, amenity_name) VALUES (?, ?)");
        foreach ($selected_amenities as $amenity_name) {
            $stmtAmenity->bind_param("is", $venue_id, $amenity_name);
            $stmtAmenity->execute();
        }
        $stmtAmenity->close();
    }

    echo "<script>alert('Venue added successfully!'); window.location='my-venues.php';</script>";
    exit();
}

// Default amenities
$default_amenities = [
    "Air Conditioning", "Wi-Fi", "Security Services", "Projector", "Parking Space",
    "Stage Setup", "Accessibility Features", "Garden Setup", "VIP Lounge", "Outdoor Seating", "Others"
];
?>

<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Venue | Gatherly</title>
    <link rel="icon" type="image/x-icon" href="../../assets/images/logo.png">
    <link rel="stylesheet" href="../../../src/output.css?v=<?php echo filemtime(__DIR__ . '/../../../src/output.css'); ?>">
    <script src="https://kit.fontawesome.com/2a99de0fa5.js" crossorigin="anonymous"></script>
</head>

<body class="bg-gradient-to-br from-green-50 via-white to-teal-50 font-['Montserrat']">
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
                    <a href="manager-dashboard.php" class="text-gray-700 hover:text-green-600">Dashboard</a>
                    <a href="my-venues.php" class="font-semibold text-green-600 hover:text-green-700">My Venues</a>
                    <a href="bookings.php" class="text-gray-700 hover:text-green-600">Bookings</a>
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

<!-- Main Content -->

<div class="container px-6 py-10 mx-auto sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto bg-white rounded-2xl shadow-md border border-gray-200 p-8">
        <div class="flex items-center gap-2 mb-6">
            <div class="flex items-center justify-center w-8 h-8 rounded-full bg-green-100 text-green-600">
                <i class="fas fa-plus"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-800">Add New Venue</h1>
                <p class="text-sm text-gray-500">Fill in the details to add a new venue to your listings</p>
            </div>
        </div>

```
    <form method="POST" enctype="multipart/form-data" class="space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-semibold text-gray-700">Venue Name</label>
                <input type="text" name="venue_name" placeholder="e.g., Aurora Pavilion" required
                       class="w-full mt-2 rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm focus:ring-green-500 focus:border-green-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">Location</label>
                <input type="text" name="location" placeholder="e.g., Taguig City" required
                       class="w-full mt-2 rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm focus:ring-green-500 focus:border-green-500">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-semibold text-gray-700">Capacity</label>
                <input type="number" name="capacity" placeholder="e.g., 150" min="1" required
                       class="w-full mt-2 rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm focus:ring-green-500 focus:border-green-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">Base Price (₱)</label>
                <input type="number" id="base_price" name="base_price" placeholder="e.g., 50000" step="0.01" required
                       class="w-full mt-2 rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm focus:ring-green-500 focus:border-green-500">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-semibold text-gray-700">Price Percentage (%)</label>
                <input type="number" id="price_percentage" name="price_percentage" placeholder="e.g., 15" step="0.01" min="0"
                       class="w-full mt-2 rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm focus:ring-green-500 focus:border-green-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">Availability</label>
                <select name="availability_status"
                        class="w-full mt-2 rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm focus:ring-green-500 focus:border-green-500">
                    <option value="available">Available</option>
                    <option value="unavailable">Unavailable</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600">Peak Price (₱)</label>
                <input type="text" id="peak_price" readonly placeholder="Auto"
                       class="w-full mt-2 bg-gray-100 border border-gray-300 px-3 py-2 rounded-md text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600">Off-Peak Price (₱)</label>
                <input type="text" id="offpeak_price" readonly placeholder="Auto"
                       class="w-full mt-2 bg-gray-100 border border-gray-300 px-3 py-2 rounded-md text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600">Weekday Price (₱)</label>
                <input type="text" id="weekday_price" readonly placeholder="Auto"
                       class="w-full mt-2 bg-gray-100 border border-gray-300 px-3 py-2 rounded-md text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600">Weekend Price (₱)</label>
                <input type="text" id="weekend_price" readonly placeholder="Auto"
                       class="w-full mt-2 bg-gray-100 border border-gray-300 px-3 py-2 rounded-md text-sm">
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700">Description</label>
            <textarea name="description" rows="3" placeholder="Describe the venue..."
                      class="w-full mt-2 border border-gray-300 px-4 py-2.5 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500"></textarea>
        </div>

        <!-- Venue Image Section with indicator -->
        <div>
            <label class="block text-sm font-semibold text-gray-700">Venue Image</label>
            <label class="flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition relative">
                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                <p class="text-sm text-gray-600">Click to upload image</p>
                <p class="text-xs text-gray-400">PNG, JPG up to 10MB</p>
                <input type="file" id="imageInput" name="image" accept="image/*" class="hidden">
            </label>
            <p id="imageStatus" class="text-xs text-green-600 mt-2 hidden font-medium"></p>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700">Amenities</label>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-2 bg-gray-50 p-4 rounded-lg border border-gray-300">
                <?php foreach ($default_amenities as $amenity): ?>
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="venue_amenities[]" value="<?php echo htmlspecialchars($amenity); ?>"
                               class="text-green-600 focus:ring-green-500">
                        <?php echo htmlspecialchars($amenity); ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
            <a href="my-venues.php" class="px-5 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition">Cancel</a>
            <button type="submit" class="px-6 py-2.5 rounded-lg bg-green-600 hover:bg-green-700 text-white font-semibold shadow-md transition">
                <i class="fas fa-plus-circle mr-1"></i> Add Venue
            </button>
        </div>
    </form>
</div>
```

</div>

<?php include '../../../src/components/footer.php'; ?>

<script>
    // Dynamic pricing auto-compute
    const baseInput = document.getElementById('base_price');
    const percentInput = document.getElementById('price_percentage');
    const peak = document.getElementById('peak_price');
    const offpeak = document.getElementById('offpeak_price');
    const weekday = document.getElementById('weekday_price');
    const weekend = document.getElementById('weekend_price');

    function computePrices() {
        const base = parseFloat(baseInput.value) || 0;
        const percent = parseFloat(percentInput.value) || 0;
        const peakPrice = base + (base * (percent / 100));
        const offpeakPrice = base - (base * (percent / 100));
        const weekdayPrice = base;
        const weekendPrice = peakPrice;

        peak.value = peakPrice.toFixed(2);
        offpeak.value = offpeakPrice.toFixed(2);
        weekday.value = weekdayPrice.toFixed(2);
        weekend.value = weekendPrice.toFixed(2);
    }

    baseInput.addEventListener('input', computePrices);
    percentInput.addEventListener('input', computePrices);

    // Image upload indicator
    const imageInput = document.getElementById('imageInput');
    const imageStatus = document.getElementById('imageStatus');
    imageInput.addEventListener('change', () => {
        if (imageInput.files && imageInput.files.length > 0) {
            imageStatus.textContent = `Image selected: ${imageInput.files[0].name}`;
            imageStatus.classList.remove('hidden');
        } else {
            imageStatus.textContent = '';
            imageStatus.classList.add('hidden');
        }
    });

    document.getElementById('profile-dropdown-btn').addEventListener('click', () => {
        document.getElementById('profile-dropdown').classList.toggle('hidden');
    });
</script>

</body>
</html>
