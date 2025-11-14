<?php

/**
 * AI Conversation API - Pure PHP Implementation
 * Converted from Python ML system to native PHP
 */

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

try {
    // Load database connection
    require_once '../dbconnect.php';

    // Load ConversationalPlanner class
    require_once 'ConversationalPlanner.php';

    // Create planner instance
    $planner = new ConversationalPlanner($pdo);

    // Process conversation
    $result = $planner->processConversation($message, $conversation_state);

    // Return response
    echo json_encode($result);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'line' => $e->getLine(),
        'file' => $e->getFile()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'AI service error: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'line' => $e->getLine(),
        'file' => $e->getFile()
    ]);
}
