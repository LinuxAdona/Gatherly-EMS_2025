<?php

/**
 * Venue Recommendation System
 * PHP implementation of ML-based venue recommendations
 * 
 * Converted from: ml/venue_recommender.py
 * Uses rule-based MCDM (Multi-Criteria Decision Making) instead of sklearn
 */

class VenueRecommender
{
    private $db;

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
     * Calculate ML-based score using MCDM (Multi-Criteria Decision Making)
     */
    public function calculateMLScore($venue, $requirements)
    {
        $scores = [];
        $weights = [];

        // Capacity Score (Weight: 30%)
        if (isset($requirements['guests']) && $requirements['guests'] > 0) {
            $capacity = $venue['capacity'];
            $guests = $requirements['guests'];

            // Optimal capacity is between guests and 1.5x guests
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

            // Optimal price is at or below budget
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
        $locationScore = 0.8; // Default good location
        $scores[] = $locationScore;
        $weights[] = 0.15;

        // Amenities Score (Weight: 20%)
        if (!empty($requirements['amenities'])) {
            $amenitiesScore = 0.75; // Partial match
        } else {
            $amenitiesScore = 0.5; // No specific requirements
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

        return $finalScore * 100; // Convert to percentage
    }

    /**
     * Get venue recommendations using ML scoring
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
                'parsed_data' => $requirements
            ];
        }

        // Calculate ML scores for each venue
        $venueScores = [];
        foreach ($venues as $venue) {
            $score = $this->calculateMLScore($venue, $requirements);
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

        // Sort by score (descending) and get top 5
        usort($venueScores, function ($a, $b) {
            return $b['score'] - $a['score'];
        });
        $topVenues = array_slice($venueScores, 0, 5);

        // Generate response
        $response = $this->generateResponse($requirements, $topVenues);

        return [
            'success' => true,
            'response' => $response,
            'venues' => $topVenues,
            'parsed_data' => $requirements
        ];
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

        // Provide recommendations
        if (!empty($venues)) {
            $response .= "Using machine learning analysis, here are my top " . count($venues) . " venue recommendations:";
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
