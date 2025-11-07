<?php
session_start();

// Check if user is logged in and is a manager
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: ../signin.php");
    exit();
}

require_once '../../../src/services/dbconnect.php';

$first_name = $_SESSION['first_name'] ?? 'Manager';
$user_id = $_SESSION['user_id'];

// Handle delete action
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM venues WHERE venue_id = $id");
    echo "<script>alert('Venue deleted successfully!'); window.location='my-venues.php';</script>";
    exit();
}

// Handle update via POST
if (isset($_POST['update_venue'])) {
    $venue_id = intval($_POST['venue_id']);
    $venue_name = $conn->real_escape_string($_POST['venue_name']);
    $location = $conn->real_escape_string($_POST['location']);
    $capacity = intval($_POST['capacity']);
    $base_price = floatval($_POST['base_price']);
    $description = $conn->real_escape_string($_POST['description']);
    $availability_status = $_POST['availability_status'];
    $price_percentage = floatval($_POST['price_percentage']);

    // Calculate derived prices
    $peak_price = $base_price * (1 + $price_percentage/100);
    $offpeak_price = $base_price * 0.8; // example logic
    $weekday_price = $base_price;
    $weekend_price = $peak_price;

    // Handle image upload
    if (!empty($_FILES['image']['tmp_name'])) {
        $imageData = addslashes(file_get_contents($_FILES['image']['tmp_name']));
        $conn->query("UPDATE venues SET venue_name='$venue_name', location='$location', capacity=$capacity,
            base_price=$base_price, description='$description', availability_status='$availability_status',
            price_percentage=$price_percentage, peak_price=$peak_price, offpeak_price=$offpeak_price,
            weekday_price=$weekday_price, weekend_price=$weekend_price, image='$imageData'
            WHERE venue_id=$venue_id");
    } else {
        $conn->query("UPDATE venues SET venue_name='$venue_name', location='$location', capacity=$capacity,
            base_price=$base_price, description='$description', availability_status='$availability_status',
            price_percentage=$price_percentage, peak_price=$peak_price, offpeak_price=$offpeak_price,
            weekday_price=$weekday_price, weekend_price=$weekend_price
            WHERE venue_id=$venue_id");
    }

    echo "<script>alert('Venue updated successfully!'); window.location='my-venues.php';</script>";
    exit();
}

// Fetch all venues
$query = "SELECT * FROM venues ORDER BY venue_id ASC";
$venues = $conn->query($query);
?>

<!DOCTYPE html>

<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Venues | Gatherly</title>
<link rel="icon" type="image/x-icon" href="../../assets/images/logo.png">
<link rel="stylesheet" href="../../../src/output.css?v=<?php echo filemtime(__DIR__ . '/../../../src/output.css'); ?>">
<script src="https://kit.fontawesome.com/2a99de0fa5.js" crossorigin="anonymous"></script>
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

<div class="container px-4 py-10 mx-auto sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">My Venues</h1>
            <p class="text-gray-600">Manage your venues, view details, and track availability</p>
        </div>
        <a href="add-venue.php"
           class="bg-green-600 hover:bg-green-700 text-white px-5 py-3 rounded-lg shadow-md flex items-center gap-2 transition-all hover:scale-105">
            <i class="fas fa-plus-circle"></i> Add New Venue
        </a>
    </div>

```
<?php if ($venues && $venues->num_rows > 0): ?>
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <?php while ($venue = $venues->fetch_assoc()): ?>
            <?php
                $imageSrc = !empty($venue['image']) 
                    ? 'data:image/jpeg;base64,' . base64_encode($venue['image'])
                    : '../../assets/images/venue-placeholder.jpg';
            ?>
            <div class="overflow-hidden bg-white border border-gray-200 shadow-md rounded-xl hover:shadow-lg transition-all">
                <div class="relative w-full h-48 overflow-hidden bg-gray-100 rounded-t-xl">
                    <img src="<?php echo $imageSrc; ?>" 
                        alt="Venue Image" 
                        class="w-full h-full object-cover object-center transition-transform duration-300 hover:scale-105 pointer-events-none">
                    </div>
                <div class="p-5">
                    <h2 class="text-lg font-bold text-gray-800 mb-1"><?php echo htmlspecialchars($venue['venue_name']); ?></h2>
                    <p class="text-sm text-gray-600 mb-2">
                        <i class="fas fa-map-marker-alt text-green-500 mr-1.5"></i>
                        <?php echo htmlspecialchars($venue['location']); ?>
                    </p>
                    <p class="text-sm text-gray-600 mb-2">
                        <i class="fas fa-users text-blue-500 mr-1.5"></i>
                        Capacity: <?php echo htmlspecialchars($venue['capacity']); ?>
                    </p>
                    <div class="mb-3 bg-gray-50 rounded-lg p-3 border border-gray-100">
                        <p class="text-sm font-semibold text-green-700">
                            <i class="fas fa-peso-sign mr-1"></i>Base Price: ₱<?php echo number_format($venue['base_price'], 2); ?>
                        </p>
                        <p class="text-xs text-gray-700 mt-1">Peak Price: ₱<?php echo number_format($venue['peak_price'], 2); ?></p>
                        <p class="text-xs text-gray-700">Off-Peak Price: ₱<?php echo number_format($venue['offpeak_price'], 2); ?></p>
                        <p class="text-xs text-gray-700">Weekday Price: ₱<?php echo number_format($venue['weekday_price'], 2); ?></p>
                        <p class="text-xs text-gray-700">Weekend Price: ₱<?php echo number_format($venue['weekend_price'], 2); ?></p>
                    </div>
                    <p class="text-sm text-gray-700 line-clamp-3 mb-4"><?php echo htmlspecialchars($venue['description']); ?></p>

                    <div class="flex items-center justify-between relative z-10">
                        <div class="flex gap-3">
                            <button class="edit-btn flex items-center gap-1 text-blue-600 hover:text-blue-700 font-semibold text-sm"
                                    data-venue='<?php echo json_encode($venue); ?>'>
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <a href="?delete=<?php echo $venue['venue_id']; ?>"
                            onclick="return confirm('Are you sure you want to delete this venue?');"
                            class="flex items-center gap-1 text-red-600 hover:text-red-700 font-semibold text-sm">
                            <i class="fas fa-trash-alt"></i> Delete
                            </a>
                        </div>

                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-full
                            <?php echo ($venue['availability_status'] ?? 'available') === 'available'
                                ? 'bg-green-50 text-green-700 border border-green-300 shadow-sm'
                                : 'bg-red-50 text-red-700 border border-red-300 shadow-sm'; ?>">
                            <i class="fas fa-circle text-[6px]
                                <?php echo ($venue['availability_status'] ?? 'available') === 'available'
                                    ? 'text-green-500'
                                    : 'text-red-500'; ?>">
                            </i>
                            <?php echo ucfirst($venue['availability_status'] ?? 'Available'); ?>
                        </span>
                    </div>
                </div>
                <div class="p-3 bg-gray-50 border-t border-gray-200 text-xs text-gray-500">
                    Venue ID: <?php echo htmlspecialchars($venue['venue_id']); ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <div class="flex flex-col items-center justify-center py-20 text-center bg-white border border-gray-200 rounded-2xl shadow-md">
        <i class="mb-3 text-5xl text-gray-400 fas fa-building"></i>
        <h3 class="mb-2 text-xl font-semibold text-gray-700">No venues added yet</h3>
        <p class="mb-4 text-gray-500">Start by adding your first venue to display it here.</p>
        <a href="add-venue.php" class="px-6 py-3 font-semibold text-white bg-green-600 rounded-lg shadow-md hover:bg-green-700 transition-all">
            <i class="mr-2 fas fa-plus-circle"></i> Add Venue
        </a>
    </div>
<?php endif; ?>
```

</div>

<?php include '../../../src/components/footer.php'; ?>

<!-- Edit Modal -->
<div id="edit-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-lg p-6 relative">
        <!-- Close button -->
        <button id="close-modal" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
        
        <!-- Modal Title -->
        <h2 class="text-2xl font-bold text-center mb-6">Edit Venue</h2>
        
        <!-- Form -->
        <form method="POST" enctype="multipart/form-data" id="edit-form" class="space-y-4">
            <input type="hidden" name="venue_id" id="venue_id">
            
            <div>
                <label class="block font-semibold mb-1">Venue Name</label>
                <input type="text" name="venue_name" id="venue_name" class="w-full border rounded-lg p-2" required>
            </div>
            <div>
                <label class="block font-semibold mb-1">Location</label>
                <input type="text" name="location" id="location" class="w-full border rounded-lg p-2" required>
            </div>
            <div>
                <label class="block font-semibold mb-1">Capacity</label>
                <input type="number" name="capacity" id="capacity" class="w-full border rounded-lg p-2" required>
            </div>
            <div>
                <label class="block font-semibold mb-1">Base Price</label>
                <input type="number" step="0.01" name="base_price" id="base_price" class="w-full border rounded-lg p-2" required>
            </div>
            <div>
                <label class="block font-semibold mb-1">Price Percentage</label>
                <input type="number" step="0.01" name="price_percentage" id="price_percentage" class="w-full border rounded-lg p-2" required>
            </div>
            <div>
                <label class="block font-semibold mb-1">Description</label>
                <textarea name="description" id="description" rows="3" class="w-full border rounded-lg p-2" required></textarea>
            </div>
            <div>
                <label class="block font-semibold mb-1">Availability Status</label>
                <select name="availability_status" id="availability_status" class="w-full border rounded-lg p-2">
                    <option value="available">Available</option>
                    <option value="unavailable">Unavailable</option>
                </select>
            </div>
            <div>
                <label class="block font-semibold mb-1">Image (Optional)</label>
                <input type="file" name="image" accept="image/*" class="w-full border rounded-lg p-2">
            </div>
            
            <!-- Buttons -->
            <div class="flex justify-end gap-3 mt-4">
                <button type="button" id="cancel-btn" class="px-4 py-2 border rounded-lg hover:bg-gray-100">Cancel</button>
                <button type="submit" name="update_venue" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
// Open modal on Edit button click
document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const venue = JSON.parse(btn.dataset.venue);
        document.getElementById('venue_id').value = venue.venue_id;
        document.getElementById('venue_name').value = venue.venue_name;
        document.getElementById('location').value = venue.location;
        document.getElementById('capacity').value = venue.capacity;
        document.getElementById('base_price').value = venue.base_price;
        document.getElementById('description').value = venue.description;
        document.getElementById('availability_status').value = venue.availability_status;
        document.getElementById('price_percentage').value = venue.price_percentage;
        document.getElementById('edit-modal').classList.remove('hidden');
    });
});

// Close modal
document.getElementById('close-modal').addEventListener('click', () => document.getElementById('edit-modal').classList.add('hidden'));
document.getElementById('cancel-btn').addEventListener('click', () => document.getElementById('edit-modal').classList.add('hidden'));
</script>


</body>
</html>
