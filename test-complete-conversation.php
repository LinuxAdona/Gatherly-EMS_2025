<?php

/**
 * Test Complete Conversational Flow
 */

require_once __DIR__ . '/src/services/dbconnect.php';
require_once __DIR__ . '/src/services/ai/ConversationalPlanner.php';

echo "=== Test Complete Conversation Flow ===\n\n";

$planner = new ConversationalPlanner($pdo);

// Simulate a conversation with all required info
$conversationState = [
    'event_type' => 'wedding',
    'guests' => 150,
    'budget' => 50000,
    'date' => '2025-12-25',
    'services' => ['all']
];

echo "Generating final recommendations...\n\n";

try {
    $result = $planner->generateFinalRecommendations($conversationState);

    if ($result['success']) {
        echo "✅ Success!\n\n";

        if (isset($result['venues']) && !empty($result['venues'])) {
            echo "Top " . count($result['venues']) . " Venues:\n";
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

            foreach ($result['venues'] as $index => $venue) {
                echo ($index + 1) . ". " . $venue['name'] . "\n";
                echo "   📊 Ensemble Score: " . $venue['score'] . "%\n";

                if (isset($venue['algorithm_breakdown'])) {
                    echo "   Algorithm Breakdown:\n";
                    echo "     🔵 MCDM:        " . $venue['algorithm_breakdown']['mcdm'] . "%\n";
                    echo "     🟣 KNN:         " . $venue['algorithm_breakdown']['knn'] . "%\n";
                    echo "     🟢 Decision Tree: " . $venue['algorithm_breakdown']['decision_tree'] . "%\n";
                }

                echo "   Details:\n";
                echo "     Capacity: " . $venue['capacity'] . " guests\n";
                echo "     Price: ₱" . number_format($venue['price']) . "\n";
                echo "     Location: " . $venue['location'] . "\n\n";
            }
        } else {
            echo "No venues found.\n\n";
        }

        echo "Response to user:\n";
        echo $result['response'] . "\n";
    } else {
        echo "❌ Error: " . ($result['error'] ?? 'Unknown') . "\n";
    }
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
