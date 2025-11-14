<?php
/**
 * AI Recommendation API - Pure PHP Implementation
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

if (empty($message)) {
    echo json_encode(['success' => false, 'error' => 'Message is required']);
    exit();
}

try {
    // Load database connection
    require_once __DIR__ . '/../../services/dbconnect.php';
    
    // Load VenueRecommender class
    require_once __DIR__ . '/VenueRecommender.php';
    
    // Create recommender instance
    $recommender = new VenueRecommender($pdo);
    
    // Get recommendations
    $result = $recommender->getRecommendations($message);
    
    // Return response
    echo json_encode($result);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'AI service error: ' . $e->getMessage()
    ]);
}