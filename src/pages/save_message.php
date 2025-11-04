<?php
header('Content-Type: application/json');
include 'db_connect.php'; // make sure this file connects properly to $conn

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

if (
    !isset($_POST['event_id']) ||
    !isset($_POST['sender_id']) ||
    !isset($_POST['receiver_id']) ||
    !isset($_POST['message_text'])
) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

$event_id = intval($_POST['event_id']);
$sender_id = intval($_POST['sender_id']);
$receiver_id = intval($_POST['receiver_id']);
$message_text = trim($_POST['message_text']);

$stmt = $conn->prepare("
    INSERT INTO messages (event_id, sender_id, receiver_id, message_text, timestamp)
    VALUES (?, ?, ?, ?, NOW())
");

if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . $conn->error]);
    exit;
}

// ✅ FIX: correct parameter order and type binding
$stmt->bind_param("iiis", $event_id, $sender_id, $receiver_id, $message_text);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to send message: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
