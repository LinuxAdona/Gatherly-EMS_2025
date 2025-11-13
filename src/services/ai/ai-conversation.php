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
$conversation_state = isset($input['conversation_state']) ? $input['conversation_state'] : [];

if (empty($message)) {
    echo json_encode(['success' => false, 'error' => 'Message is required']);
    exit();
}

// Call Python conversational planner script
$pythonScript = '/home2/linuxman/public_html/ml/conversational_planner.py';

// Detect OS and set Python path accordingly
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $pythonPath = 'C:/Python313/python.exe';
} else {
    // Linux/Unix - use virtual environment python
    $venvPython = '/home2/linuxman/public_html/ml/venv/bin/python3';
    $pythonPath = file_exists($venvPython) ? $venvPython : '/usr/bin/python3';
}

// Verify files exist
if (!file_exists($pythonScript)) {
    echo json_encode([
        'success' => false,
        'error' => 'Python script not found',
        'debug' => "Script path: $pythonScript"
    ]);
    exit();
}

if (!file_exists($pythonPath)) {
    echo json_encode([
        'success' => false,
        'error' => 'Python interpreter not found',
        'debug' => "Python path: $pythonPath"
    ]);
    exit();
}

// Escape message for command line (works for both Linux and Windows)
$escapedMessage = escapeshellarg($message);

// Pass conversation state as second argument if available
$stateArg = '';
if (!empty($conversation_state)) {
    $stateJson = json_encode($conversation_state);
    $stateArg = ' ' . escapeshellarg($stateJson);
}

// Execute Python script
$command = "$pythonPath " . escapeshellarg($pythonScript) . " $escapedMessage$stateArg 2>&1";
$output = shell_exec($command);

// Parse Python output
if ($output === null || empty($output)) {
    echo json_encode([
        'success' => false,
        'error' => 'AI service is not available. Please ensure Python and required packages are installed.'
    ]);
    exit();
}

// Decode JSON response from Python
$result = json_decode($output, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to parse AI response',
        'debug' => $output
    ]);
    exit();
}

// Return the conversational response
echo json_encode($result);
