<?php

/**
 * AI Recommendation Engine
 * Multi-Criteria Decision Making System for Venue Recommendations
 * 
 * Features:
 * - Weighted scoring based on capacity, price, location, amenities, availability
 * - Collaborative filtering based on similar events
 * - Match score calculation (0-100%)
 */

class RecommendationEngine
{
    private $conn;

    // Default weights for criteria (can be customized per search)
    private $defaultWeights = [
        'capacity' => 0.25,
        'price' => 0.30,
        'location' => 0.20,
        'amenities' => 0.15,
        'availability' => 0.10
    ];

    public function __construct($dbConnection)
    {
        $this->conn = $dbConnection;
    }

    /**
     * Main recommendation function
     * Returns top N venues with suitability scores
     */
    public function getRecommendations($criteria, $topN = 3, $customWeights = null)
    {
        $weights = $customWeights ?? $this->defaultWeights;

        // Normalize weights to sum to 1.0
        $totalWeight = array_sum($weights);
        foreach ($weights as $key => $value) {
            $weights[$key] = $value / $totalWeight;
        }

        // Get all available venues
        $venues = $this->getAvailableVenues($criteria['event_date'] ?? null);

        // Calculate scores for each venue
        $scoredVenues = [];
        foreach ($venues as $venue) {
            $scores = $this->calculateVenueScores($venue, $criteria);
            $suitabilityScore = $this->calculateSuitabilityScore($scores, $weights);

            $scoredVenues[] = [
                'venue' => $venue,
                'suitability_score' => round($suitabilityScore, 2),
                'capacity_score' => round($scores['capacity'], 2),
                'price_score' => round($scores['price'], 2),
                'location_score' => round($scores['location'], 2),
                'amenities_score' => round($scores['amenities'], 2),
                'availability_score' => round($scores['availability'], 2),
                'match_percentage' => round($suitabilityScore, 1) . '%',
                'reason' => $this->generateRecommendationReason($venue, $scores, $criteria)
            ];
        }

        // Sort by suitability score (descending)
        usort($scoredVenues, function ($a, $b) {
            return $b['suitability_score'] <=> $a['suitability_score'];
        });

        // Apply collaborative filtering boost
        $scoredVenues = $this->applyCollaborativeFiltering($scoredVenues, $criteria);

        // Re-sort after collaborative filtering
        usort($scoredVenues, function ($a, $b) {
            return $b['suitability_score'] <=> $a['suitability_score'];
        });

        return array_slice($scoredVenues, 0, $topN);
    }

    /**
     * Get available venues (not booked on specified date)
     */
    private function getAvailableVenues($eventDate = null)
    {
        $sql = "SELECT v.*, 
                GROUP_CONCAT(va.amenity_name) as amenities,
                COUNT(DISTINCT vbh.booking_history_id) as historical_bookings
                FROM venues v
                LEFT JOIN venue_amenities va ON v.venue_id = va.venue_id
                LEFT JOIN venue_bookings_history vbh ON v.venue_id = vbh.venue_id
                WHERE v.availability_status = 'available'";

        if ($eventDate) {
            $sql .= " AND v.venue_id NOT IN (
                SELECT venue_id FROM events 
                WHERE event_date = ? AND status IN ('confirmed', 'pending')
            )";
        }

        $sql .= " GROUP BY v.venue_id";

        $stmt = $this->conn->prepare($sql);
        if ($eventDate) {
            $stmt->bind_param("s", $eventDate);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Calculate individual criterion scores (0-100)
     */
    private function calculateVenueScores($venue, $criteria)
    {
        return [
            'capacity' => $this->scoreCapacity($venue, $criteria),
            'price' => $this->scorePrice($venue, $criteria),
            'location' => $this->scoreLocation($venue, $criteria),
            'amenities' => $this->scoreAmenities($venue, $criteria),
            'availability' => $this->scoreAvailability($venue, $criteria)
        ];
    }

    /**
     * Score capacity match (100 = perfect fit, lower if over/under capacity)
     */
    private function scoreCapacity($venue, $criteria)
    {
        $guestCount = $criteria['guest_count'] ?? 0;
        $capacity = $venue['capacity'];

        if ($guestCount == 0) return 50; // No guest count provided

        // Perfect if guests are 70-90% of capacity
        $utilizationRatio = $guestCount / $capacity;

        if ($utilizationRatio >= 0.7 && $utilizationRatio <= 0.9) {
            return 100;
        } elseif ($utilizationRatio > 0.9 && $utilizationRatio <= 1.0) {
            return 90;
        } elseif ($utilizationRatio >= 0.5 && $utilizationRatio < 0.7) {
            return 80;
        } elseif ($utilizationRatio > 1.0) {
            return 0; // Over capacity - not suitable
        } else {
            // Under-utilized
            return max(0, 60 - (0.5 - $utilizationRatio) * 100);
        }
    }

    /**
     * Score price match (100 = within budget, lower if over budget)
     */
    private function scorePrice($venue, $criteria)
    {
        $budget = $criteria['budget'] ?? 0;

        if ($budget == 0) return 50; // No budget provided

        // Use appropriate price based on criteria
        $price = $this->getApplicablePrice($venue, $criteria);

        if ($price <= $budget) {
            // Within budget - score based on value (prefer not too cheap)
            $priceRatio = $price / $budget;
            if ($priceRatio >= 0.7 && $priceRatio <= 1.0) {
                return 100;
            } elseif ($priceRatio >= 0.5 && $priceRatio < 0.7) {
                return 90;
            } else {
                return 70 + ($priceRatio * 20);
            }
        } else {
            // Over budget - penalize more as it exceeds
            $overBudgetRatio = $price / $budget;
            if ($overBudgetRatio <= 1.1) {
                return 70; // Only 10% over
            } elseif ($overBudgetRatio <= 1.2) {
                return 50; // 20% over
            } elseif ($overBudgetRatio <= 1.3) {
                return 30; // 30% over
            } else {
                return 10; // Way over budget
            }
        }
    }

    /**
     * Get applicable price based on date and demand
     */
    private function getApplicablePrice($venue, $criteria)
    {
        $eventDate = $criteria['event_date'] ?? null;

        if (!$eventDate) {
            return $venue['base_price'];
        }

        // Determine if weekend
        $dayOfWeek = date('N', strtotime($eventDate));
        $isWeekend = ($dayOfWeek >= 6);

        // Determine season (example: Dec-Feb, June-Aug are peak)
        $month = date('n', strtotime($eventDate));
        $isPeakSeason = in_array($month, [12, 1, 2, 6, 7, 8]);

        if ($isPeakSeason && $isWeekend) {
            return $venue['peak_price'] ?? $venue['base_price'];
        } elseif ($isWeekend) {
            return $venue['weekend_price'] ?? $venue['base_price'];
        } elseif (!$isPeakSeason) {
            return $venue['offpeak_price'] ?? $venue['base_price'];
        } else {
            return $venue['weekday_price'] ?? $venue['base_price'];
        }
    }

    /**
     * Score location (based on distance if coordinates provided)
     */
    private function scoreLocation($venue, $criteria)
    {
        if (!isset($criteria['latitude']) || !isset($criteria['longitude'])) {
            // No location provided, score based on general preference
            return 70;
        }

        $distance = $this->calculateDistance(
            $criteria['latitude'],
            $criteria['longitude'],
            $venue['latitude'],
            $venue['longitude']
        );

        // Score based on distance (km)
        if ($distance <= 5) return 100;
        if ($distance <= 10) return 90;
        if ($distance <= 15) return 75;
        if ($distance <= 25) return 60;
        if ($distance <= 40) return 40;
        return 20;
    }

    /**
     * Calculate distance between two coordinates (Haversine formula)
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return $distance;
    }

    /**
     * Score amenities match
     */
    private function scoreAmenities($venue, $criteria)
    {
        $score = 60; // Base score

        $requiredAmenities = $criteria['required_amenities'] ?? [];
        if (empty($requiredAmenities)) {
            return $score; // No specific requirements
        }

        $venueAmenities = explode(',', strtolower($venue['amenities'] ?? ''));
        $venueAmenities = array_map('trim', $venueAmenities);

        $matchCount = 0;
        foreach ($requiredAmenities as $required) {
            $required = strtolower(trim($required));

            // Check direct matches
            if (in_array($required, $venueAmenities)) {
                $matchCount++;
                continue;
            }

            // Check for partial matches in venue features
            if (strpos($required, 'catering') !== false && $venue['catering_available']) {
                $matchCount++;
            } elseif (strpos($required, 'sound') !== false && $venue['sound_system_available']) {
                $matchCount++;
            } elseif (strpos($required, 'stage') !== false && $venue['has_stage_setup']) {
                $matchCount++;
            } elseif (strpos($required, 'parking') !== false && $venue['parking_capacity'] > 0) {
                $matchCount++;
            }
        }

        $matchRate = count($requiredAmenities) > 0 ? $matchCount / count($requiredAmenities) : 0;
        return 60 + ($matchRate * 40); // 60-100 range
    }

    /**
     * Score availability (always 100 for available venues)
     */
    private function scoreAvailability($venue, $criteria)
    {
        return 100; // Already filtered for available venues
    }

    /**
     * Calculate weighted suitability score
     */
    private function calculateSuitabilityScore($scores, $weights)
    {
        $totalScore = 0;
        foreach ($scores as $criterion => $score) {
            $weight = $weights[$criterion] ?? 0;
            $totalScore += $score * $weight;
        }
        return $totalScore;
    }

    /**
     * Apply collaborative filtering boost
     * Venues that were successful for similar events get a boost
     */
    private function applyCollaborativeFiltering(&$scoredVenues, $criteria)
    {
        $eventType = $criteria['event_type'] ?? null;
        $guestCount = $criteria['guest_count'] ?? 0;

        if (!$eventType) return $scoredVenues;

        // Get similar successful bookings
        $sql = "SELECT venue_id, AVG(satisfaction_score) as avg_satisfaction, COUNT(*) as booking_count
                FROM venue_bookings_history
                WHERE event_type = ?
                AND guest_count BETWEEN ? AND ?
                GROUP BY venue_id
                HAVING avg_satisfaction >= 4.0";

        $guestMin = $guestCount * 0.8;
        $guestMax = $guestCount * 1.2;

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sii", $eventType, $guestMin, $guestMax);
        $stmt->execute();
        $result = $stmt->get_result();
        $similarBookings = $result->fetch_all(MYSQLI_ASSOC);

        // Create lookup for boost values
        $boostLookup = [];
        foreach ($similarBookings as $booking) {
            // Boost up to 5 points based on satisfaction and frequency
            $boost = min(5, ($booking['avg_satisfaction'] - 4.0) * 5 + ($booking['booking_count'] * 0.5));
            $boostLookup[$booking['venue_id']] = $boost;
        }

        // Apply boosts
        foreach ($scoredVenues as &$scoredVenue) {
            $venueId = $scoredVenue['venue']['venue_id'];
            if (isset($boostLookup[$venueId])) {
                $scoredVenue['suitability_score'] += $boostLookup[$venueId];
                $scoredVenue['suitability_score'] = min(100, $scoredVenue['suitability_score']);
                $scoredVenue['collaborative_boost'] = $boostLookup[$venueId];
            }
        }

        return $scoredVenues;
    }

    /**
     * Generate human-readable recommendation reason
     */
    private function generateRecommendationReason($venue, $scores, $criteria)
    {
        $reasons = [];

        if ($scores['capacity'] >= 90) {
            $reasons[] = "Perfect capacity for " . ($criteria['guest_count'] ?? 'your') . " guests";
        }

        if ($scores['price'] >= 90) {
            $reasons[] = "Excellent value within your budget";
        }

        if ($scores['location'] >= 85) {
            $reasons[] = "Great location and accessibility";
        }

        if ($scores['amenities'] >= 85) {
            $reasons[] = "Has all required amenities";
        }

        if ($venue['average_rating'] >= 4.5) {
            $reasons[] = "Highly rated by previous clients";
        }

        if ($venue['total_bookings'] >= 15) {
            $reasons[] = "Popular choice for similar events";
        }

        if (empty($reasons)) {
            $reasons[] = "Good option for your event";
        }

        return implode('. ', $reasons) . '.';
    }

    /**
     * Save recommendation to database for tracking
     */
    public function saveRecommendation($eventId, $recommendations)
    {
        $sql = "INSERT INTO recommendations 
                (event_id, recommended_venue_id, suitability_score, capacity_score, 
                price_score, location_score, amenities_score, availability_score, 
                alternative_rank, reason, criteria_weights) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        $rank = 1;
        foreach ($recommendations as $rec) {
            $venueId = $rec['venue']['venue_id'];
            $score = $rec['suitability_score'];
            $capScore = $rec['capacity_score'];
            $priceScore = $rec['price_score'];
            $locScore = $rec['location_score'];
            $amenScore = $rec['amenities_score'];
            $availScore = $rec['availability_score'];
            $reason = $rec['reason'];
            $weights = json_encode($this->defaultWeights);

            $stmt->bind_param(
                "iidddddddis",
                $eventId,
                $venueId,
                $score,
                $capScore,
                $priceScore,
                $locScore,
                $amenScore,
                $availScore,
                $rank,
                $reason,
                $weights
            );
            $stmt->execute();
            $rank++;
        }

        return true;
    }
}
