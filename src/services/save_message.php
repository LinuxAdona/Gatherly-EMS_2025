<?php
header('Content-Type: application/json');
include 'dbconnect.php'; // your DB connection

$event_id = $_POST['event_id'] ?? null;
$sender_id = $_POST['sender_id'] ?? null;
$receiver_id = $_POST['receiver_id'] ?? null;
$message_text = $_POST['message_text'] ?? '';
$file_url = $_POST['file_url'] ?? null;

if (!$event_id || !$sender_id || !$receiver_id) {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

// If a file was sent (Base64)
if ($file_url) {
    // Extract the base64 string
    if (preg_match('/^data:(.*?);base64,(.*)$/', $file_url, $matches)) {
        $mime = $matches[1]; // e.g., image/png
        $data = base64_decode($matches[2]);

        // Generate unique filename
        $ext = explode('/', $mime)[1];
        $filename = 'uploads/' . uniqid() . '.' . $ext;

        // Make sure the uploads folder exists
        if (!is_dir('uploads')) mkdir('uploads', 0777, true);

        // Save file to server
        file_put_contents($filename, $data);

        // Save the relative path to DB
        $file_url_db = $filename;
    } else {
        $file_url_db = null;
    }
} else {
    $file_url_db = null;
}

// Insert message into DB
$stmt = $conn->prepare("INSERT INTO chat (event_id, sender_id, receiver_id, message_text, file_url) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iiiss", $event_id, $sender_id, $receiver_id, $message_text, $file_url_db);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}
