<?php
// Test AI conversation endpoint with session
session_start();

// Simulate being logged in as organizer
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'organizer';
$_SESSION['first_name'] = 'Test';

// Simulate POST request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [];

// Simulate JSON input
$jsonInput = json_encode([
    'message' => 'Birthday party for 80 guests',
    'conversation_state' => []
]);

// Mock php://input
file_put_contents('php://temp/test-input.json', $jsonInput);

// Now include the actual service
ob_start();
include __DIR__ . '/ai-conversation.php';
$output = ob_get_clean();

echo "=== TEST OUTPUT ===\n";
echo $output;
echo "\n\n=== PARSED ===\n";
$result = json_decode($output, true);
print_r($result);
