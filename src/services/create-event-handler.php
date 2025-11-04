<?php
session_start();

// Check if user is logged in and is an organizer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit();
}

header('Content-Type: application/json');

require_once 'dbconnect.php';

// Get POST data
$event_name = isset($_POST['event_name']) ? trim($_POST['event_name']) : '';
$event_type = isset($_POST['event_type']) ? trim($_POST['event_type']) : '';
$theme = isset($_POST['theme']) ? trim($_POST['theme']) : null;
$expected_guests = isset($_POST['expected_guests']) ? intval($_POST['expected_guests']) : 0;
$event_date = isset($_POST['event_date']) ? $_POST['event_date'] : '';
$venue_id = isset($_POST['venue_id']) ? intval($_POST['venue_id']) : 0;
$total_cost = isset($_POST['total_cost']) ? floatval($_POST['total_cost']) : 0;
$services = isset($_POST['services']) ? $_POST['services'] : [];

// Get organizer ID from session
$client_id = $_SESSION['user_id'];

// Validation
if (empty($event_name)) {
    echo json_encode(['success' => false, 'error' => 'Event name is required']);
    exit();
}

if (empty($event_type)) {
    echo json_encode(['success' => false, 'error' => 'Event type is required']);
    exit();
}

if ($expected_guests < 1) {
    echo json_encode(['success' => false, 'error' => 'Expected guests must be at least 1']);
    exit();
}

if (empty($event_date)) {
    echo json_encode(['success' => false, 'error' => 'Event date is required']);
    exit();
}

if ($venue_id < 1) {
    echo json_encode(['success' => false, 'error' => 'Please select a venue']);
    exit();
}

// Validate event date is in the future
$event_datetime = new DateTime($event_date);
$now = new DateTime();
if ($event_datetime < $now) {
    echo json_encode(['success' => false, 'error' => 'Event date must be in the future']);
    exit();
}

// Start transaction
$conn->begin_transaction();

try {
    // Insert event
    $stmt = $conn->prepare("INSERT INTO events (event_name, event_type, theme, expected_guests, total_cost, event_date, status, client_id, venue_id) 
                           VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, ?)");

    $stmt->bind_param("sssiisii", $event_name, $event_type, $theme, $expected_guests, $total_cost, $event_date, $client_id, $venue_id);

    if (!$stmt->execute()) {
        throw new Exception("Failed to create event: " . $stmt->error);
    }

    $event_id = $conn->insert_id;
    $stmt->close();

    // Insert selected services
    if (!empty($services) && is_array($services)) {
        $service_stmt = $conn->prepare("INSERT INTO event_services (event_id, service_id, status) VALUES (?, ?, 'pending')");

        foreach ($services as $service_id) {
            $service_id = intval($service_id);
            if ($service_id > 0) {
                $service_stmt->bind_param("ii", $event_id, $service_id);
                if (!$service_stmt->execute()) {
                    throw new Exception("Failed to add service: " . $service_stmt->error);
                }
            }
        }

        $service_stmt->close();
    }

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Event created successfully!',
        'event_id' => $event_id,
        'event_name' => $event_name
    ]);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();

    echo json_encode([
        'success' => false,
        'error' => 'Failed to create event',
        'details' => $e->getMessage()
    ]);
}

$conn->close();
