<?php
session_start();

// Check if user is logged in and is an administrator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'administrator') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once 'dbconnect.php';

header('Content-Type: application/json');

$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : null;

// Get available years from events
$years_query = "SELECT DISTINCT YEAR(event_date) as year FROM events WHERE status = 'completed' ORDER BY year DESC";
$years_result = $conn->query($years_query);
$available_years = [];
while ($row = $years_result->fetch_assoc()) {
    $available_years[] = $row['year'];
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
