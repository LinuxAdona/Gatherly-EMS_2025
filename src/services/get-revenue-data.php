<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1); // TEMPORARILY ENABLE to see errors
ini_set('log_errors', 1);
ini_set('error_log', '/opt/lampp/htdocs/Gatherly-EMS_2025/error.log');

// Log that script started
error_log("get-revenue-data.php: Script started");

session_start();
error_log("get-revenue-data.php: Session started");

// Check if user is logged in and is an administrator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'administrator') {
    error_log("get-revenue-data.php: Unauthorized access attempt");
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

error_log("get-revenue-data.php: User authenticated as " . $_SESSION['role']);

try {
    require_once 'dbconnect.php';
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    error_log("Database connection error in get-revenue-data.php: " . $e->getMessage());
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

header('Content-Type: application/json');

$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : null;

try {
    // Get available years from events
    $years_query = "SELECT DISTINCT YEAR(event_date) as year FROM events WHERE status = 'completed' ORDER BY year DESC";
    $years_result = $conn->query($years_query);

    if (!$years_result) {
        throw new Exception("Query failed: " . $conn->error);
    }

    $available_years = [];
    while ($row = $years_result->fetch_assoc()) {
        $available_years[] = intval($row['year']);
    }
} catch (Exception $e) {
    error_log("Error fetching available years: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error fetching data: ' . $e->getMessage()]);
    exit();
}

// Initialize response
$response = [
    'available_years' => $available_years,
    'current_year' => $year,
    'current_month' => $month,
    'data' => []
];

if ($month !== null) {
    // Get daily revenue for specific month
    $query = "SELECT 
                DAY(event_date) as day,
                SUM(total_cost) as revenue,
                COUNT(*) as event_count
              FROM events 
              WHERE status = 'completed' 
                AND YEAR(event_date) = ? 
                AND MONTH(event_date) = ?
              GROUP BY DAY(event_date)
              ORDER BY day";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $year, $month);
    $stmt->execute();
    $result = $stmt->get_result();

    // Create array with all days of the month initialized to 0
    $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $daily_data = array_fill(1, $days_in_month, ['revenue' => 0, 'event_count' => 0]);

    while ($row = $result->fetch_assoc()) {
        $daily_data[$row['day']] = [
            'revenue' => floatval($row['revenue']),
            'event_count' => intval($row['event_count'])
        ];
    }

    // Convert to indexed array for Chart.js
    foreach ($daily_data as $day => $data) {
        $response['data'][] = [
            'label' => 'Day ' . $day,
            'revenue' => $data['revenue'],
            'event_count' => $data['event_count']
        ];
    }

    $response['total_revenue'] = array_sum(array_column($response['data'], 'revenue'));
    $response['total_events'] = array_sum(array_column($response['data'], 'event_count'));
    $response['view_type'] = 'daily';
    $response['period_label'] = date('F Y', mktime(0, 0, 0, $month, 1, $year));

    $stmt->close();
} else {
    // Get monthly revenue for entire year
    $query = "SELECT 
                MONTH(event_date) as month,
                SUM(total_cost) as revenue,
                COUNT(*) as event_count
              FROM events 
              WHERE status = 'completed' 
                AND YEAR(event_date) = ?
              GROUP BY MONTH(event_date)
              ORDER BY month";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $year);
    $stmt->execute();
    $result = $stmt->get_result();

    // Create array with all months initialized to 0
    $months = [
        'January',
        'February',
        'March',
        'April',
        'May',
        'June',
        'July',
        'August',
        'September',
        'October',
        'November',
        'December'
    ];
    $monthly_data = array_fill(1, 12, ['revenue' => 0, 'event_count' => 0]);

    while ($row = $result->fetch_assoc()) {
        $monthly_data[$row['month']] = [
            'revenue' => floatval($row['revenue']),
            'event_count' => intval($row['event_count'])
        ];
    }

    // Convert to indexed array for Chart.js
    foreach ($monthly_data as $month_num => $data) {
        $response['data'][] = [
            'label' => $months[$month_num - 1],
            'revenue' => $data['revenue'],
            'event_count' => $data['event_count']
        ];
    }

    $response['total_revenue'] = array_sum(array_column($response['data'], 'revenue'));
    $response['total_events'] = array_sum(array_column($response['data'], 'event_count'));
    $response['view_type'] = 'monthly';
    $response['period_label'] = $year;

    $stmt->close();
}

$conn->close();

echo json_encode($response);
exit(); // Ensure no extra output after JSON