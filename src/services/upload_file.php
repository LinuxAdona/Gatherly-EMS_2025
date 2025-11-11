<?php
include 'dbconnect.php';

/* 

TODO: Implement file upload handling here

// Get POST data
$sender_id = $_POST['sender_id'] ?? null;
$receiver_id = $_POST['receiver_id'] ?? null;
$event_id = $_POST['event_id'] ?? null;
$message_text = $_POST['message_text'] ?? '';
$file_url = null;

// Validate required fields
if (!$sender_id || !$receiver_id || !$event_id) {
    die(json_encode(['error' => 'Missing required fields']));
}

// Handle file upload if exists
if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
    $fileName = $_FILES['file']['name'];
    $tmpName = $_FILES['file']['tmp_name'];
    $uploadDir = 'uploads/';

    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    // Prevent overwriting files with same name
    $uniqueName = time() . '_' . basename($fileName);
    $filePath = $uploadDir . $uniqueName;

    if (move_uploaded_file($tmpName, $filePath)) {
        $file_url = $filePath;
    } else {
        echo json_encode(['error' => 'File upload failed']);
        exit;
    }
}

// Insert message into DB
$stmt = $conn->prepare("INSERT INTO chat (event_id, sender_id, receiver_id, message_text, file_url, is_file) VALUES (?, ?, ?, ?, ?, ?)");
$is_file = $file_url ? 1 : 0;
$stmt->bind_param("iiissi", $event_id, $sender_id, $receiver_id, $message_text, $file_url, $is_file);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message_text' => $message_text,
        'file_url' => $file_url
    ]);
} else {
    echo json_encode(['error' => 'Failed to save message: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
 */