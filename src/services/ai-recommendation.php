<?php
session_start();

// Check if user is logged in and is an organizer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$message = isset($input['message']) ? trim($input['message']) : '';

if (empty($message)) {
    echo json_encode(['success' => false, 'error' => 'Message is required']);
    exit();
}

// Call Python ML script for recommendations
$pythonScript = __DIR__ . '/../../ml/venue_recommender.py';
$pythonPath = 'C:/Python314/python.exe'; // Use 'python3' on Linux/Mac or full path if needed

// Escape message for command line
$escapedMessage = escapeshellarg($message);

// Execute Python script
$command = "\"$pythonPath\" \"$pythonScript\" $escapedMessage 2>&1";
$output = shell_exec($command);

// Parse Python output
if ($output === null || empty($output)) {
    echo json_encode([
        'success' => false,
        'error' => 'Python ML service is not available. Please ensure Python and required packages are installed.'
    ]);
    exit();
}

// Decode JSON response from Python
$result = json_decode($output, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to parse ML response',
        'debug' => $output
    ]);
    exit();
}

// Return the ML-based recommendations
echo json_encode($result);
