<?php
// Minimal test for get-revenue-data.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Test 1: Basic PHP works<br>";

session_start();
echo "Test 2: Session started<br>";

// Check session
echo "User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "<br>";
echo "Role: " . ($_SESSION['role'] ?? 'NOT SET') . "<br>";

// Test require
echo "Test 3: Attempting to require dbconnect.php<br>";
try {
    require_once 'dbconnect.php';
    echo "✅ dbconnect.php loaded successfully<br>";
    echo "Connection object exists: " . (isset($conn) ? 'YES' : 'NO') . "<br>";
} catch (Exception $e) {
    echo "❌ Error loading dbconnect.php: " . $e->getMessage() . "<br>";
    die();
}

// Test database query
echo "Test 4: Attempting database query<br>";
try {
    $years_query = "SELECT DISTINCT YEAR(event_date) as year FROM events WHERE status = 'completed' ORDER BY year DESC";
    echo "Query: $years_query<br>";

    $years_result = $conn->query($years_query);

    if (!$years_result) {
        throw new Exception("Query failed: " . $conn->error);
    }

    echo "✅ Query succeeded<br>";

    $available_years = [];
    while ($row = $years_result->fetch_assoc()) {
        $available_years[] = intval($row['year']);
    }

    echo "Years found: " . json_encode($available_years) . "<br>";
} catch (Exception $e) {
    echo "❌ Query error: " . $e->getMessage() . "<br>";
}

echo "<br>Test complete!";
