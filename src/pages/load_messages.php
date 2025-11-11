<?php
header('Content-Type: application/json');
include 'db_connect.php';

// Get POST parameters safely
$event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : null;
$sender_id = isset($_POST['sender_id']) ? intval($_POST['sender_id']) : null;
$receiver_id = isset($_POST['receiver_id']) ? intval($_POST['receiver_id']) : null;

if (!$event_id || !$sender_id || !$receiver_id) {
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

// Prepare SQL query to fetch messages including file_url
$stmt = $conn->prepare("
    SELECT sender_id, receiver_id, message_text, timestamp, file_url 
    FROM messages 
    WHERE event_id = ? 
      AND ((sender_id = ? AND receiver_id = ?) 
           OR (sender_id = ? AND receiver_id = ?))
    ORDER BY messages_id ASC
");

$stmt->bind_param("iiiii", $event_id, $sender_id, $receiver_id, $receiver_id, $sender_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    // Format timestamp to 12-hour format with AM/PM
    $row['timestamp'] = date("h:i A", strtotime($row['timestamp']));

    // Ensure file_url is either a valid string or null
    $row['file_url'] = !empty($row['file_url']) ? $row['file_url'] : null;

    // Ensure message_text is not null
    $row['message_text'] = $row['message_text'] ?? '';

    $messages[] = $row;
}

echo json_encode($messages);
exit;
?>
