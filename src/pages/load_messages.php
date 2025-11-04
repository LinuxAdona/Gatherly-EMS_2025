<?php
include 'db_connect.php'; // Ensure this file defines $conn correctly
header('Content-Type: application/json');

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['error' => 'Invalid request method']);
  exit;
}

// Validate event_id
if (empty($_POST['event_id'])) {
  echo json_encode(['error' => 'Missing event_id']);
  exit;
}

$event_id = intval($_POST['event_id']);

// Fetch all messages for this event
$sql = "
  SELECT 
    m.message_id,
    m.event_id,
    m.sender_id,
    m.receiver_id,
    m.message_text,
    m.timestamp,
    s.fullname AS sender_name,
    r.fullname AS receiver_name
  FROM messages m
  LEFT JOIN users s ON m.sender_id = s.user_id
  LEFT JOIN users r ON m.receiver_id = r.user_id
  WHERE m.event_id = ?
  ORDER BY m.timestamp ASC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
  $messages[] = [
    'message_id' => $row['message_id'],
    'event_id' => $row['event_id'],
    'sender_id' => $row['sender_id'],
    'receiver_id' => $row['receiver_id'],
    'message_text' => $row['message_text'],
    'timestamp' => $row['timestamp'],
    'sender_name' => $row['sender_name'] ?? 'Unknown',
    'receiver_name' => $row['receiver_name'] ?? 'Unknown'
  ];
}

echo json_encode($messages);
$stmt->close();
$conn->close();
?>
