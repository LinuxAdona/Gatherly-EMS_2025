<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

require_once '../../src/services/dbconnect.php';

$first_name = $_SESSION['first_name'] ?? 'User';
$role = $_SESSION['role'];

// Fetch all suppliers
$suppliers_query = "SELECT s.*, 
                    (SELECT COUNT(*) FROM services WHERE supplier_id = s.supplier_id) as service_count
                    FROM suppliers s 
                    ORDER BY s.supplier_name";
$result = $conn->query($suppliers_query);
$suppliers = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Suppliers | Gatherly</title>
    <link rel="icon" type="image/x-icon" href="../assets/images/logo.png">
    <link rel="stylesheet" href="../../src/output.css?v=<?php echo filemtime(__DIR__ . '/../../src/output.css'); ?>">
    <script src="https://kit.fontawesome.com/2a99de0fa5.js" crossorigin="anonymous"></script>
</head>

<body class="bg-linear-to-br from-indigo-50 via-white to-purple-50 font-['Montserrat']">
    <!-- Navbar -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-20">
                <div class="flex items-center gap-3">
                    <img src="../assets/images/logo.png" alt="Gatherly Logo" class="w-10 h-10 sm:w-12 sm:h-12">
                    <span class="text-xl sm:text-2xl font-bold text-gray-800">Gatherly</span>
                </div>
                <div class="hidden md:flex items-center gap-6">
                    <a href="<?php echo $role === 'client' ? 'client-dashboard.php' : ($role === 'coordinator' ? 'coordinator-dashboard.php' : 'home.php'); ?>"
                        class="text-gray-700 hover:text-indigo-600 transition-colors">Dashboard</a>
                    <a href="venue/search.php" class="text-gray-700 hover:text-indigo-600 transition-colors">Search
                        Venues</a>
                    <a href="suppliers.php" class="text-indigo-600 font-semibold">Suppliers</a>
                    <a href="../../src/services/signout-handler.php"
                        class="text-gray-700 hover:text-red-600 transition-colors">Sign Out</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-800 mb-2">Event Suppliers & Services</h1>
            <p class="text-gray-600">Browse our trusted partners for your event needs</p>
        </div>

        <!-- Service Categories -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
            <div class="bg-white rounded-xl shadow-md p-4 border-l-4 border-blue-500">
                <div class="flex items-center gap-3">
                    <i class="fas fa-lightbulb text-2xl text-blue-600"></i>
                    <div>
                        <h3 class="font-bold text-gray-800">Lights & Sounds</h3>
                        <p class="text-sm text-gray-600">Professional audio-visual services</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-4 border-l-4 border-purple-500">
                <div class="flex items-center gap-3">
                    <i class="fas fa-camera text-2xl text-purple-600"></i>
                    <div>
                        <h3 class="font-bold text-gray-800">Photography</h3>
                        <p class="text-sm text-gray-600">Capture your precious moments</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-4 border-l-4 border-green-500">
                <div class="flex items-center gap-3">
                    <i class="fas fa-flower text-2xl text-green-600"></i>
                    <div>
                        <h3 class="font-bold text-gray-800">Styling & Flowers</h3>
                        <p class="text-sm text-gray-600">Beautiful event decorations</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Suppliers List -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Available Suppliers</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($suppliers as $supplier): ?>
                    <div
                        class="border border-gray-200 rounded-lg p-5 hover:border-indigo-300 hover:shadow-lg transition-all">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <h3 class="text-lg font-bold text-gray-800">
                                    <?php echo htmlspecialchars($supplier['supplier_name']); ?></h3>
                                <span
                                    class="inline-block px-2 py-1 text-xs font-semibold text-indigo-600 bg-indigo-100 rounded-full mt-1">
                                    <?php echo htmlspecialchars($supplier['service_category']); ?>
                                </span>
                            </div>
                            <span
                                class="px-2 py-1 text-xs font-semibold <?php echo $supplier['availability_status'] === 'available' ? 'text-green-600 bg-green-100' : 'text-gray-600 bg-gray-100'; ?> rounded-full">
                                <?php echo ucfirst($supplier['availability_status']); ?>
                            </span>
                        </div>

                        <div class="space-y-2 text-sm text-gray-600 mb-4">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-envelope w-4"></i>
                                <span><?php echo htmlspecialchars($supplier['email']); ?></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fas fa-phone w-4"></i>
                                <span><?php echo htmlspecialchars($supplier['phone']); ?></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fas fa-map-marker-alt w-4"></i>
                                <span><?php echo htmlspecialchars($supplier['location']); ?></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fas fa-box w-4"></i>
                                <span><?php echo $supplier['service_count']; ?> services offered</span>
                            </div>
                        </div>

                        <button
                            class="w-full px-4 py-2 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition-colors">
                            <i class="fas fa-info-circle mr-2"></i>View Services
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (empty($suppliers)): ?>
                <div class="text-center py-12">
                    <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500">No suppliers available at the moment</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../../src/components/Footer.php'; ?>
</body>

</html>