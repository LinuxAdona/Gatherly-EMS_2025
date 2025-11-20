<?php

/**
 * Debug Test - Check what's happening with scoring
 */

require_once __DIR__ . '/src/services/dbconnect.php';
require_once __DIR__ . '/src/services/ai/VenueRecommender.php';

echo "=== Debug Test - Wedding 150 guests ₱50000 ===\n\n";

$testMessage = "Wedding for 150 guests with budget of ₱50000";

try {
    $recommender = new VenueRecommender($pdo);

    // Parse requirements first
    $requirements = $recommender->parseRequirements($testMessage);

    echo "Parsed Requirements:\n";
    print_r($requirements);
    echo "\n";

    // Get recommendations
    $result = $recommender->getRecommendations($testMessage);

    if ($result['success']) {
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
        echo "Error: " . ($result['error'] ?? 'Unknown') . "\n";
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
