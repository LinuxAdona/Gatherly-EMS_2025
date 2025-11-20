<?php

/**
 * Venue Recommendation System
 * Ensemble Multi-Algorithm Implementation
 * 
 * Uses all 3 algorithms combined for robust recommendations:
 * - MCDM: Multi-Criteria Decision Making (Weighted Average)
 * - KNN: K-Nearest Neighbors
 * - DECISION_TREE: Rule-based Decision Tree
 * 
 * Final score = Weighted combination of all algorithm scores
 */

class VenueRecommender
{
    private $db;

    // Algorithm weights for ensemble scoring
    private $algorithmWeights = [
        'MCDM' => 0.35,          // 35% - Balanced criteria evaluation
        'KNN' => 0.35,           // 35% - Historical pattern matching
        'DECISION_TREE' => 0.30  // 30% - Rule-based filtering
    ];

    // KNN Configuration
    private $k = 5; // Number of nearest neighbors

    public function __construct($dbConnection)
    {
        $this->db = $dbConnection;
    }

    /**
     * Parse event requirements from user message
     */
    public function parseRequirements($message)
    {
        $messageLower = strtolower($message);

        $data = [
            'event_type' => null,
            'guests' => null,
            'budget' => null,
            'date' => null,
            'amenities' => []
        ];

        // Event type detection
        $eventTypes = [
            'wedding' => ['wedding', 'marriage', 'nuptial', 'wed'],
            'corporate' => ['corporate', 'business', 'conference', 'seminar', 'meeting', 'office'],
            'birthday' => ['birthday', 'party', 'celebration', 'bday'],
            'concert' => ['concert', 'music', 'show', 'performance', 'gig']
        ];

        foreach ($eventTypes as $type => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($messageLower, $keyword) !== false) {
                    $data['event_type'] = $type;
                    break 2;
                }
            }
        }

        // Extract guest count
        $guestPatterns = [
            '/(\d+)\s*(?:guests?|people|attendees?|pax|persons?)/i',
            '/(?:for|about|around|approximately)\s*(\d+)/i',
        ];

        foreach ($guestPatterns as $pattern) {
            if (preg_match($pattern, $messageLower, $matches)) {
                $data['guests'] = intval($matches[1]);
                break;
            }
        }

        // Extract budget
        $budgetPatterns = [
            '/(?:₱|php|peso|pesos?)\s*([\d,]+)/i',
            '/([\d,]+)\s*(?:₱|php|peso|pesos?|budget)/i',
            '/budget\s*(?:of|is|:)?\s*([\d,]+)/i',
        ];

        foreach ($budgetPatterns as $pattern) {
            if (preg_match($pattern, $messageLower, $matches)) {
                $data['budget'] = intval(str_replace(',', '', $matches[1]));
                break;
            }
        }

        // Extract amenities
        $amenityKeywords = [
            'parking' => ['parking', 'park', 'parking space'],
            'catering' => ['catering', 'food', 'buffet', 'meal'],
            'sound' => ['sound', 'audio', 'speaker', 'sound system'],
            'stage' => ['stage', 'platform', 'podium'],
            'ac' => ['air conditioning', 'aircon', 'ac', 'airconditioned', 'cooling'],
            'wifi' => ['wifi', 'wi-fi', 'internet', 'wireless']
        ];

        foreach ($amenityKeywords as $amenity => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($messageLower, $keyword) !== false) {
                    $data['amenities'][] = $amenity;
                    break;
                }
            }
        }

        return $data;
    }

    /**
     * Get venue features from database
     */
    public function getVenueFeatures()
    {
        $query = "SELECT 
                    venue_id,
                    venue_name,
                    capacity,
                    base_price,
                    location,
                    description,
                    availability_status
                FROM venues
                WHERE availability_status = 'available'";

        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * ============================================
     * ALGORITHM 1: MCDM (Multi-Criteria Decision Making)
     * ============================================
     */
    private function calculateMCDMScore($venue, $requirements)
    {
        $scores = [];
        $weights = [];

        // Capacity Score (Weight: 30%)
        if (isset($requirements['guests']) && $requirements['guests'] > 0) {
            $capacity = $venue['capacity'];
            $guests = $requirements['guests'];

            if ($capacity >= $guests && $capacity <= $guests * 1.5) {
                $capacityScore = 1.0;
            } elseif ($capacity >= $guests * 0.8 && $capacity < $guests) {
                $capacityScore = 0.85;
            } elseif ($capacity > $guests * 1.5 && $capacity <= $guests * 2) {
                $capacityScore = 0.7;
            } elseif ($capacity < $guests * 0.8) {
                $capacityScore = max(0.3, $capacity / $guests);
            } else {
                $capacityScore = max(0.4, ($guests * 1.5) / $capacity);
            }

            $scores[] = $capacityScore;
            $weights[] = 0.30;
        }

        // Budget Score (Weight: 35%)
        if (isset($requirements['budget']) && $requirements['budget'] > 0) {
            $price = floatval($venue['base_price']);
            $budget = $requirements['budget'];

            if ($price <= $budget) {
                $budgetScore = 1.0;
            } elseif ($price <= $budget * 1.2) {
                $budgetScore = 0.8;
            } elseif ($price <= $budget * 1.5) {
                $budgetScore = 0.6;
            } else {
                $budgetScore = max(0.2, $budget / $price);
            }

            $scores[] = $budgetScore;
            $weights[] = 0.35;
        }

        // Location Score (Weight: 15%)
        $locationScore = 0.8;
        $scores[] = $locationScore;
        $weights[] = 0.15;

        // Amenities Score (Weight: 20%)
        if (!empty($requirements['amenities'])) {
            $amenitiesScore = 0.75;
        } else {
            $amenitiesScore = 0.5;
        }
        $scores[] = $amenitiesScore;
        $weights[] = 0.20;

        // Normalize weights
        $totalWeight = array_sum($weights);
        $normalizedWeights = array_map(function ($w) use ($totalWeight) {
            return $w / $totalWeight;
        }, $weights);

        // Calculate weighted average
        $finalScore = 0;
        for ($i = 0; $i < count($scores); $i++) {
            $finalScore += $scores[$i] * $normalizedWeights[$i];
        }

        return $finalScore * 100;
    }

    /**
     * ============================================
     * ALGORITHM 2: K-Nearest Neighbors (KNN)
     * ============================================
     */
    private function calculateKNNScore($venue, $requirements)
    {
        // Get historical successful bookings
        $historicalData = $this->getHistoricalBookings();

        if (empty($historicalData)) {
            // Fallback to MCDM if no historical data
            return $this->calculateMCDMScore($venue, $requirements);
        }

        // Calculate distances to all historical bookings for this venue
        $distances = [];
        foreach ($historicalData as $booking) {
            if ($booking['venue_id'] == $venue['venue_id']) {
                $distance = $this->euclideanDistance($requirements, [
                    'guests' => $booking['guest_count'],
                    'budget' => $booking['base_price'],
                    'event_type' => $booking['event_type']
                ]);

                $distances[] = [
                    'distance' => $distance,
                    'success' => ($booking['status'] == 'completed') ? 1 : 0.5
                ];
            }
        }

        if (empty($distances)) {
            // No bookings for this venue, use current requirements match
            $distance = $this->euclideanDistance($requirements, [
                'guests' => $venue['capacity'],
                'budget' => $venue['base_price'],
                'event_type' => 'general'
            ]);
            return max(0, 100 - ($distance * 10));
        }

        // Sort by distance
        usort($distances, fn($a, $b) => $a['distance'] <=> $b['distance']);

        // Get K nearest neighbors
        $kNearest = array_slice($distances, 0, min($this->k, count($distances)));

        // Calculate score based on nearest neighbors
        $totalWeight = 0;
        $weightedScore = 0;

        foreach ($kNearest as $neighbor) {
            $weight = 1 / (1 + $neighbor['distance']); // Inverse distance weighting
            $weightedScore += $neighbor['success'] * $weight;
            $totalWeight += $weight;
        }

        $score = $totalWeight > 0 ? ($weightedScore / $totalWeight) * 100 : 50;

        // Adjust based on current capacity and budget match
        $capacityMatch = $this->getCapacityMatchScore($venue['capacity'], $requirements['guests'] ?? 0);
        $budgetMatch = $this->getBudgetMatchScore($venue['base_price'], $requirements['budget'] ?? 0);

        // Combine KNN score with current match (70% KNN, 30% current match)
        $finalScore = ($score * 0.7) + (($capacityMatch + $budgetMatch) / 2 * 0.3);

        return $finalScore;
    }

    /**
     * Calculate Euclidean distance between two requirement sets
     */
    private function euclideanDistance($req1, $req2)
    {
        // Normalize and calculate distance
        $guestDiff = 0;
        if (isset($req1['guests']) && $req1['guests'] > 0) {
            $guests1 = $req1['guests'];
            $guests2 = $req2['guests'] ?? $guests1;
            $guestDiff = abs($guests1 - $guests2) / 1000; // Normalize by 1000
        }

        $budgetDiff = 0;
        if (isset($req1['budget']) && $req1['budget'] > 0) {
            $budget1 = $req1['budget'];
            $budget2 = $req2['budget'] ?? $budget1;
            $budgetDiff = abs($budget1 - $budget2) / 100000; // Normalize by 100k
        }

        // Event type match (0 if same, 1 if different)
        $eventDiff = 0;
        if (isset($req1['event_type']) && isset($req2['event_type'])) {
            $eventDiff = ($req1['event_type'] === $req2['event_type']) ? 0 : 1;
        }

        return sqrt(pow($guestDiff, 2) + pow($budgetDiff, 2) + pow($eventDiff, 2));
    }

    /**
     * Get historical booking data
     */
    private function getHistoricalBookings()
    {
        $query = "SELECT v.venue_id, v.base_price, e.event_type, e.expected_guests as guest_count, e.status
                  FROM events e
                  JOIN venues v ON e.venue_id = v.venue_id
                  WHERE e.status IN ('confirmed', 'completed')
                  LIMIT 100";

        try {
            $stmt = $this->db->query($query);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * ============================================
     * ALGORITHM 3: Decision Tree
     * ============================================
     */
    private function calculateDecisionTreeScore($venue, $requirements)
    {
        $score = 100;

        // Decision Node 1: Capacity Check
        if (isset($requirements['guests']) && $requirements['guests'] > 0) {
            $capacity = $venue['capacity'];
            $guests = $requirements['guests'];

            if ($capacity < $guests * 0.7) {
                // Too small - major penalty
                return 15;
            } elseif ($capacity < $guests * 0.9) {
                // Slightly small
                $score -= 35;
            } elseif ($capacity > $guests * 3) {
                // Way too large
                $score -= 40;
            } elseif ($capacity > $guests * 2) {
                // Too large
                $score -= 25;
            } elseif ($capacity >= $guests && $capacity <= $guests * 1.5) {
                // Perfect fit
                $score += 10;
            }
        }

        // Decision Node 2: Budget Check
        if (isset($requirements['budget']) && $requirements['budget'] > 0) {
            $price = floatval($venue['base_price']);
            $budget = $requirements['budget'];

            if ($price > $budget * 2) {
                // Way over budget - reject
                return 10;
            } elseif ($price > $budget * 1.5) {
                // Very expensive
                $score -= 45;
            } elseif ($price > $budget * 1.2) {
                // Over budget
                $score -= 30;
            } elseif ($price > $budget) {
                // Slightly over budget
                $score -= 15;
            } elseif ($price <= $budget * 0.7) {
                // Great value
                $score += 15;
            } elseif ($price <= $budget) {
                // Within budget
                $score += 5;
            }
        }

        // Decision Node 3: Event Type Compatibility
        if (isset($requirements['event_type'])) {
            $compatible = $this->checkEventTypeCompatibility(
                $venue['venue_id'],
                $requirements['event_type']
            );

            if ($compatible === false) {
                $score -= 20;
            } elseif ($compatible === true) {
                $score += 10;
            }
        }

        // Decision Node 4: Amenities Check
        if (!empty($requirements['amenities'])) {
            $amenitiesMatch = $this->checkAmenitiesMatch(
                $venue['venue_id'],
                $requirements['amenities']
            );

            if ($amenitiesMatch < 0.3) {
                $score -= 15;
            } elseif ($amenitiesMatch > 0.7) {
                $score += 10;
            }
        }

        // Decision Node 5: Location Factor
        if ($venue['location']) {
            // Premium locations get bonus
            $premiumLocations = ['makati', 'bgc', 'ortigas', 'alabang'];
            $locationLower = strtolower($venue['location']);

            foreach ($premiumLocations as $premium) {
                if (strpos($locationLower, $premium) !== false) {
                    $score += 5;
                    break;
                }
            }
        }

        return max(0, min(100, $score));
    }

    /**
     * Check event type compatibility
     */
    private function checkEventTypeCompatibility($venueId, $eventType)
    {
        $query = "SELECT COUNT(*) as count 
                  FROM events 
                  WHERE venue_id = ? 
                  AND event_type = ? 
                  AND status IN ('confirmed', 'completed')";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute([$venueId, $eventType]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] > 0) {
                return true; // Has hosted this type before
            }
            return null; // Unknown
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Check amenities match
     */
    private function checkAmenitiesMatch($venueId, $requiredAmenities)
    {
        // Simplified amenities check
        // In a real system, you'd query an amenities table
        return 0.6; // Default partial match
    }

    /**
     * Helper: Get capacity match score
     */
    private function getCapacityMatchScore($capacity, $guests)
    {
        if ($guests == 0) return 50;

        if ($capacity >= $guests && $capacity <= $guests * 1.5) {
            return 100;
        } elseif ($capacity >= $guests * 0.8 && $capacity < $guests) {
            return 85;
        } elseif ($capacity > $guests * 1.5 && $capacity <= $guests * 2) {
            return 70;
        } else {
            return max(30, 100 - (abs($capacity - $guests) / $guests * 50));
        }
    }

    /**
     * Helper: Get budget match score
     */
    private function getBudgetMatchScore($price, $budget)
    {
        if ($budget == 0) return 50;

        if ($price <= $budget) {
            return 100;
        } elseif ($price <= $budget * 1.2) {
            return 80;
        } elseif ($price <= $budget * 1.5) {
            return 60;
        } else {
            return max(20, 100 - (($price - $budget) / $budget * 50));
        }
    }

    /**
     * Main scoring method - Ensemble approach using ALL algorithms
     * Combines scores from MCDM, KNN, and Decision Tree
     */
    public function calculateMLScore($venue, $requirements)
    {
        // Get scores from all algorithms
        $mcdmScore = $this->calculateMCDMScore($venue, $requirements);
        $knnScore = $this->calculateKNNScore($venue, $requirements);
        $decisionTreeScore = $this->calculateDecisionTreeScore($venue, $requirements);

        // Calculate weighted ensemble score
        $ensembleScore =
            ($mcdmScore * $this->algorithmWeights['MCDM']) +
            ($knnScore * $this->algorithmWeights['KNN']) +
            ($decisionTreeScore * $this->algorithmWeights['DECISION_TREE']);

        return $ensembleScore;
    }

    /**
     * Calculate scores from ALL algorithms with details
     */
    public function calculateAllAlgorithmScores($venue, $requirements)
    {
        return [
            'mcdm' => $this->calculateMCDMScore($venue, $requirements),
            'knn' => $this->calculateKNNScore($venue, $requirements),
            'decision_tree' => $this->calculateDecisionTreeScore($venue, $requirements)
        ];
    }

    /**
     * Get venue recommendations using ensemble algorithm approach
     */
    public function getRecommendations($message)
    {
        // Parse requirements
        $requirements = $this->parseRequirements($message);

        // Get venues
        $venues = $this->getVenueFeatures();

        if (empty($venues)) {
            return [
                'success' => true,
                'response' => 'No venues are currently available. Please check back later.',
                'venues' => [],
                'parsed_data' => $requirements,
                'algorithm_used' => 'Ensemble (MCDM + KNN + Decision Tree)'
            ];
        }

        // Calculate ensemble scores for each venue (combining all 3 algorithms)
        $venueScores = [];
        foreach ($venues as $venue) {
            $ensembleScore = $this->calculateMLScore($venue, $requirements);
            $algorithmScores = $this->calculateAllAlgorithmScores($venue, $requirements);

            $venueScores[] = [
                'id' => $venue['venue_id'],
                'name' => $venue['venue_name'],
                'capacity' => $venue['capacity'],
                'price' => floatval($venue['base_price']),
                'location' => $venue['location'],
                'description' => $venue['description'],
                'score' => round($ensembleScore, 2),
                'algorithm_breakdown' => [
                    'mcdm' => round($algorithmScores['mcdm'], 2),
                    'knn' => round($algorithmScores['knn'], 2),
                    'decision_tree' => round($algorithmScores['decision_tree'], 2),
                    'ensemble' => round($ensembleScore, 2)
                ]
            ];
        }

        // Sort by ensemble score (descending) and get top 3
        usort($venueScores, function ($a, $b) {
            return $b['score'] - $a['score'];
        });
        $topVenues = array_slice($venueScores, 0, 3);

        // Generate response
        $response = $this->generateResponse($requirements, $topVenues);

        return [
            'success' => true,
            'response' => $response,
            'venues' => $topVenues,
            'parsed_data' => $requirements,
            'algorithm_used' => 'Ensemble (MCDM + KNN + Decision Tree)'
        ];
    }

    /**
     * Get venue recommendations from ALL algorithms for comparison
     */
    public function getAllAlgorithmRecommendations($message)
    {
        // Parse requirements
        $requirements = $this->parseRequirements($message);

        // Get venues
        $venues = $this->getVenueFeatures();

        if (empty($venues)) {
            return [
                'success' => true,
                'response' => 'No venues are currently available. Please check back later.',
                'algorithms' => [],
                'parsed_data' => $requirements
            ];
        }

        $algorithms = ['MCDM', 'KNN', 'DECISION_TREE'];
        $algorithmResults = [];

        foreach ($algorithms as $algo) {
            $venueScores = [];

            foreach ($venues as $venue) {
                // Calculate score using specific algorithm
                switch ($algo) {
                    case 'KNN':
                        $score = $this->calculateKNNScore($venue, $requirements);
                        break;
                    case 'DECISION_TREE':
                        $score = $this->calculateDecisionTreeScore($venue, $requirements);
                        break;
                    case 'MCDM':
                    default:
                        $score = $this->calculateMCDMScore($venue, $requirements);
                        break;
                }

                $venueScores[] = [
                    'id' => $venue['venue_id'],
                    'name' => $venue['venue_name'],
                    'capacity' => $venue['capacity'],
                    'price' => floatval($venue['base_price']),
                    'location' => $venue['location'],
                    'description' => $venue['description'],
                    'score' => round($score, 2)
                ];
            }

            // Sort by score (descending) and get top 3
            usort($venueScores, function ($a, $b) {
                return $b['score'] - $a['score'];
            });
            $topVenues = array_slice($venueScores, 0, 3);

            $algorithmResults[$algo] = [
                'name' => $this->getAlgorithmName($algo),
                'venues' => $topVenues
            ];
        }

        // Generate comprehensive response
        $response = $this->generateComparisonResponse($requirements, $algorithmResults);

        return [
            'success' => true,
            'response' => $response,
            'algorithms' => $algorithmResults,
            'parsed_data' => $requirements,
            'comparison_mode' => true
        ];
    }

    /**
     * Get algorithm display name
     */
    private function getAlgorithmName($algo)
    {
        $names = [
            'MCDM' => 'Multi-Criteria Decision Making',
            'KNN' => 'K-Nearest Neighbors',
            'DECISION_TREE' => 'Decision Tree'
        ];
        return $names[$algo] ?? $algo;
    }

    /**
     * Generate comparison response for all algorithms
     */
    private function generateComparisonResponse($requirements, $algorithmResults)
    {
        $response = "";

        // Acknowledge what was understood
        $understood = [];
        if ($requirements['event_type']) {
            $understood[] = "a {$requirements['event_type']} event";
        }
        if ($requirements['guests']) {
            $understood[] = "{$requirements['guests']} guests";
        }
        if ($requirements['budget']) {
            $understood[] = "budget of ₱" . number_format($requirements['budget']);
        }

        if (!empty($understood)) {
            $response = "Great! I understand you're planning " . implode(" for ", $understood) . ". ";
        } else {
            $response = "I'll help you find the perfect venue! ";
        }

        $response .= "\n\nI've analyzed all venues using 3 different AI algorithms. Here are the top 3 recommendations from each:\n\n";

        return $response;
    }

    /**
     * Generate natural language response
     */
    private function generateResponse($requirements, $venues)
    {
        $response = "";

        // Acknowledge what was understood
        $understood = [];
        if ($requirements['event_type']) {
            $understood[] = "a " . ucfirst($requirements['event_type']) . " event";
        }
        if ($requirements['guests']) {
            $understood[] = $requirements['guests'] . " guests";
        }
        if ($requirements['budget']) {
            $understood[] = "₱" . number_format($requirements['budget']) . " budget";
        }

        if (!empty($understood)) {
            $response = "Great! I understand you're planning " . implode(" for ", $understood) . ". ";
        } else {
            $response = "I'd love to help you find the perfect venue! ";
        }

        // Provide recommendations with ensemble algorithm info
        if (!empty($venues)) {
            $response .= "Using our advanced Ensemble AI (combining MCDM, KNN, and Decision Tree algorithms), here are your top " . count($venues) . " venue recommendations:";
        } else {
            $response .= "I couldn't find any venues matching your criteria. Could you provide more details?\n\n";
            $response .= "• Number of expected guests\n";
            $response .= "• Your budget range\n";
            $response .= "• Type of event (wedding, corporate, birthday, etc.)\n";
            $response .= "• Any special requirements or amenities needed";
        }

        return $response;
    }
}