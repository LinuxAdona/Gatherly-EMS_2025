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
$pythonScript = __DIR__ . '/../../ml/conversational_planner.py';
$pythonPath = 'C:/Python313/python.exe';

// Windows-specific escaping function
function escapeForWindows($str)
{
    // Escape double quotes and wrap in double quotes for Windows
    return '"' . str_replace('"', '\\"', $str) . '"';
}

// Escape message for command line
$escapedMessage = escapeForWindows($message);

// Pass conversation state as second argument if available
$stateArg = '';
if (!empty($conversation_state)) {
    $stateJson = json_encode($conversation_state);
    // For JSON, we need to escape backslashes and quotes properly for Windows
    $stateArg = ' ' . escapeForWindows($stateJson);
}

// Execute Python script
$command = "\"$pythonPath\" \"$pythonScript\" $escapedMessage$stateArg 2>&1";
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
