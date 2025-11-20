<?php

/**
 * Test Script for Ensemble Algorithm
 * Tests the combined scoring approach with all 3 algorithms
 */

require_once __DIR__ . '/src/services/dbconnect.php';
require_once __DIR__ . '/src/services/ai/VenueRecommender.php';

echo "=== Ensemble AI Algorithm Test ===\n\n";

// Test message
$testMessage = "Wedding for 150 guests with budget of ₱50000";
echo "Test Query: $testMessage\n\n";

try {
    $recommender = new VenueRecommender($pdo);

    // Get recommendations using ensemble approach
    $result = $recommender->getRecommendations($testMessage);

    if ($result['success']) {
        echo "✅ Success!\n\n";

        echo "Parsed Requirements:\n";
        echo "- Event Type: " . ($result['parsed_data']['event_type'] ?? 'N/A') . "\n";
        echo "- Guests: " . ($result['parsed_data']['guests'] ?? 'N/A') . "\n";
        echo "- Budget: ₱" . number_format($result['parsed_data']['budget'] ?? 0) . "\n\n";

        echo "Algorithm: " . $result['algorithm_used'] . "\n\n";

        echo "=== Top 3 Venue Recommendations ===\n\n";

        if (empty($result['venues'])) {
            echo "No venues found.\n";
        } else {
            foreach ($result['venues'] as $index => $venue) {
                echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
                echo "🏆 #" . ($index + 1) . " - " . $venue['name'] . "\n";
                echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

                echo "📊 Ensemble Score: " . $venue['score'] . "% Match\n\n";

                if (isset($venue['algorithm_breakdown'])) {
                    echo "Algorithm Breakdown:\n";
                    echo "  🔵 MCDM (35% weight):        " . $venue['algorithm_breakdown']['mcdm'] . "%\n";
                    echo "  🟣 KNN (35% weight):         " . $venue['algorithm_breakdown']['knn'] . "%\n";
                    echo "  🟢 Decision Tree (30% weight): " . $venue['algorithm_breakdown']['decision_tree'] . "%\n";
                    echo "  ⭐ Combined Ensemble:        " . $venue['algorithm_breakdown']['ensemble'] . "%\n\n";
                }

                echo "Details:\n";
                echo "  Capacity: " . $venue['capacity'] . " guests\n";
                echo "  Price: ₱" . number_format($venue['price']) . "\n";
                echo "  Location: " . $venue['location'] . "\n";
                echo "  Description: " . $venue['description'] . "\n\n";
            }
        }

        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

        // Calculate algorithm agreement
        if (!empty($result['venues'])) {
            echo "=== Algorithm Analysis ===\n\n";

            foreach ($result['venues'] as $index => $venue) {
                if (isset($venue['algorithm_breakdown'])) {
                    $scores = [
                        $venue['algorithm_breakdown']['mcdm'],
                        $venue['algorithm_breakdown']['knn'],
                        $venue['algorithm_breakdown']['decision_tree']
                    ];

                    $avg = array_sum($scores) / count($scores);
                    $variance = 0;
                    foreach ($scores as $score) {
                        $variance += pow($score - $avg, 2);
                    }
                    $variance /= count($scores);
                    $stdDev = sqrt($variance);

                    echo ($index + 1) . ". " . $venue['name'] . "\n";
                    echo "   Agreement Level: " . ($stdDev < 5 ? "HIGH" : ($stdDev < 10 ? "MEDIUM" : "LOW")) . " (std dev: " . round($stdDev, 2) . ")\n";
                    echo "   All algorithms " . ($stdDev < 5 ? "strongly agree" : ($stdDev < 10 ? "moderately agree" : "have different opinions")) . " on this venue\n\n";
                }
            }
        }
    } else {
        echo "❌ Error: " . ($result['error'] ?? 'Unknown error') . "\n";
    }
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
