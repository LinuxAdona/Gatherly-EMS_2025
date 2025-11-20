<?php

/**
 * Test Script for Algorithm Comparison
 * This script tests all 3 algorithms and shows their recommendations
 */

require_once __DIR__ . '/src/services/dbconnect.php';
require_once __DIR__ . '/src/services/ai/VenueRecommender.php';

echo "=== AI Algorithm Comparison Test ===\n\n";

// Test message
$testMessage = "Wedding for 150 guests with budget of ₱50000";
echo "Test Query: $testMessage\n\n";

try {
    $recommender = new VenueRecommender($pdo);

    // Get recommendations from all algorithms
    $result = $recommender->getAllAlgorithmRecommendations($testMessage);

    if ($result['success']) {
        echo "✅ Success!\n\n";

        echo "Parsed Requirements:\n";
        echo "- Event Type: " . ($result['parsed_data']['event_type'] ?? 'N/A') . "\n";
        echo "- Guests: " . ($result['parsed_data']['guests'] ?? 'N/A') . "\n";
        echo "- Budget: ₱" . number_format($result['parsed_data']['budget'] ?? 0) . "\n\n";

        echo "=== Algorithm Results ===\n\n";

        foreach ($result['algorithms'] as $algoKey => $algoData) {
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            echo "🧠 " . $algoData['name'] . " ($algoKey)\n";
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

            if (empty($algoData['venues'])) {
                echo "No venues found.\n\n";
                continue;
            }

            foreach ($algoData['venues'] as $index => $venue) {
                echo ($index + 1) . ". " . $venue['name'] . "\n";
                echo "   Score: " . $venue['score'] . "% Match\n";
                echo "   Capacity: " . $venue['capacity'] . " guests\n";
                echo "   Price: ₱" . number_format($venue['price']) . "\n";
                echo "   Location: " . $venue['location'] . "\n";
                echo "\n";
            }
        }

        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

        // Find consensus venues (appearing in multiple algorithms)
        echo "=== Consensus Analysis ===\n\n";
        $venueAppearances = [];

        foreach ($result['algorithms'] as $algoKey => $algoData) {
            foreach ($algoData['venues'] as $venue) {
                $venueName = $venue['name'];
                if (!isset($venueAppearances[$venueName])) {
                    $venueAppearances[$venueName] = [
                        'count' => 0,
                        'algorithms' => [],
                        'avg_score' => 0,
                        'scores' => []
                    ];
                }
                $venueAppearances[$venueName]['count']++;
                $venueAppearances[$venueName]['algorithms'][] = $algoKey;
                $venueAppearances[$venueName]['scores'][] = $venue['score'];
            }
        }

        // Calculate average scores
        foreach ($venueAppearances as $name => &$data) {
            $data['avg_score'] = array_sum($data['scores']) / count($data['scores']);
        }

        // Sort by appearance count and average score
        uasort($venueAppearances, function ($a, $b) {
            if ($a['count'] != $b['count']) {
                return $b['count'] - $a['count'];
            }
            return $b['avg_score'] <=> $a['avg_score'];
        });

        foreach ($venueAppearances as $venueName => $data) {
            if ($data['count'] > 1) {
                echo "🏆 " . $venueName . "\n";
                echo "   Recommended by " . $data['count'] . "/3 algorithms\n";
                echo "   Algorithms: " . implode(', ', $data['algorithms']) . "\n";
                echo "   Average Score: " . round($data['avg_score'], 2) . "%\n\n";
            }
        }

        if (count(array_filter($venueAppearances, fn($d) => $d['count'] > 1)) == 0) {
            echo "No consensus venues found. Each algorithm has different top choices.\n";
        }
    } else {
        echo "❌ Error: " . $result['error'] . "\n";
    }
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
