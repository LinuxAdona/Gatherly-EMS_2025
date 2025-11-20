<?php

/**
 * Test Conversational Planner with Ensemble
 */

require_once __DIR__ . '/src/services/dbconnect.php';
require_once __DIR__ . '/src/services/ai/ConversationalPlanner.php';

echo "=== Test Conversational Planner ===\n\n";

$planner = new ConversationalPlanner($pdo);

// Simulate conversation state after gathering all info
$conversationState = [
    'event_type' => 'wedding',
    'guests' => 150,
    'budget' => 50000,
    'services_needed' => []
];

echo "Query: Wedding for 150 guests with budget of ₱50000\n\n";

$result = $planner->processConversation("Wedding for 150 guests with budget of 50000", []);

if ($result['success']) {
    if (!$result['needs_more_info'] && isset($result['venues'])) {
        echo "Top 3 Venues:\n";
        foreach ($result['venues'] as $index => $venue) {
            echo "\n" . ($index + 1) . ". " . $venue['name'] . "\n";
            echo "   Ensemble: " . $venue['score'] . "%\n";
            if (isset($venue['algorithm_breakdown'])) {
                echo "   MCDM: " . $venue['algorithm_breakdown']['mcdm'] . "%\n";
                echo "   KNN: " . $venue['algorithm_breakdown']['knn'] . "%\n";
                echo "   Decision Tree: " . $venue['algorithm_breakdown']['decision_tree'] . "%\n";
            }
            echo "   Capacity: " . $venue['capacity'] . " | Price: ₱" . number_format($venue['price']) . "\n";
        }
    } else {
        echo "Still gathering info...\n";
        echo "Response: " . $result['response'] . "\n";
    }
} else {
    echo "Error: " . ($result['error'] ?? 'Unknown') . "\n";
}
