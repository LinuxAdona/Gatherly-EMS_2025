<?php

/**
 * Conversational AI Event Planning Assistant
 * PHP implementation of the Python ML system
 * 
 * Converted from: ml/conversational_planner.py
 * Uses rule-based scoring instead of sklearn ML models
 */

class ConversationalPlanner
{
    private $db;
    private $stages = [
        'greeting',
        'event_type',
        'guest_count',
        'budget',
        'date',
        'services_needed',
        'recommendations'
    ];

    private $serviceCategories = [
        'Catering',
        'Lights and Sounds',
        'Photography',
        'Videography',
        'Host/Emcee',
        'Styling and Flowers',
        'Equipment Rental'
    ];

    public function __construct($dbConnection)
    {
        $this->db = $dbConnection;
    }

    /**
     * Determine conversation stage and extract data from user message
     */
    public function determineStage($conversationState, $userMessage)
    {
        $messageLower = strtolower($userMessage);
        $data = [];

        // Parse event type
        $eventTypes = [
            'wedding' => ['wedding', 'marriage', 'nuptial', 'bride', 'groom'],
            'corporate' => ['corporate', 'business', 'conference', 'seminar', 'meeting', 'company'],
            'birthday' => ['birthday', 'party', 'celebration', 'debut', '18th', 'bday'],
            'concert' => ['concert', 'music', 'show', 'performance', 'festival']
        ];

        foreach ($eventTypes as $type => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($messageLower, $keyword) !== false) {
                    $data['event_type'] = $type;
                    break 2;
                }
            }
        }

        // Parse guest count
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

        // Parse budget
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

        // If at budget stage and message is just a number
        if (
            !isset($data['budget']) &&
            isset($conversationState['event_type']) &&
            isset($conversationState['guests'])
        ) {
            if (preg_match('/^\s*(\d[\d,]*)\s*$/', $messageLower, $matches)) {
                $data['budget'] = intval(str_replace(',', '', $matches[1]));
            }
        }

        // Parse date mention
        $datePatterns = [
            '/(\d{4})-(\d{1,2})-(\d{1,2})/',
            '/(\d{1,2})\/(\d{1,2})\/(\d{4})/',
            '/(january|february|march|april|may|june|july|august|september|october|november|december)\s+(\d{1,2})/i',
        ];

        foreach ($datePatterns as $pattern) {
            if (preg_match($pattern, $messageLower)) {
                $data['date_mentioned'] = true;
                break;
            }
        }

        // Parse services needed
        $serviceKeywords = [
            'catering' => ['catering', 'food', 'buffet', 'meal', 'cuisine', 'chef'],
            'lights_and_sounds' => ['sound', 'audio', 'lights', 'lighting', 'music', 'dj', 'speaker'],
            'photography' => ['photo', 'photographer', 'pictures', 'camera'],
            'videography' => ['video', 'videographer', 'film', 'recording'],
            'host' => ['host', 'emcee', 'mc', 'master of ceremonies'],
            'styling' => ['styling', 'decoration', 'flowers', 'floral', 'decor', 'theme'],
            'rental' => ['rental', 'tables', 'chairs', 'tent', 'equipment', 'stage']
        ];

        $servicesList = [];
        foreach ($serviceKeywords as $service => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($messageLower, $keyword) !== false) {
                    $servicesList[] = $service;
                    break;
                }
            }
        }

        if (!empty($servicesList)) {
            $data['services'] = $servicesList;
        }

        // Merge with existing state to determine next stage
        $mergedState = array_merge($conversationState, $data);

        // Determine next stage
        if (!isset($mergedState['event_type'])) {
            return ['stage' => 'event_type', 'data' => $data];
        } elseif (!isset($mergedState['guests'])) {
            return ['stage' => 'guest_count', 'data' => $data];
        } elseif (!isset($mergedState['budget'])) {
            return ['stage' => 'budget', 'data' => $data];
        } elseif (!isset($mergedState['date_mentioned'])) {
            return ['stage' => 'date', 'data' => $data];
        } elseif (
            !isset($mergedState['services_needed']) &&
            empty($data['services']) &&
            strpos($messageLower, 'all') === false
        ) {
            return ['stage' => 'services_needed', 'data' => $data];
        } else {
            // Mark services as confirmed
            if (
                !empty($data['services']) ||
                strpos($messageLower, 'all') !== false ||
                strpos($messageLower, 'everything') !== false
            ) {
                $data['services_needed'] = true;
            }
            return ['stage' => 'recommendations', 'data' => $data];
        }
    }

    /**
     * Generate question based on conversation stage
     */
    public function generateQuestion($stage, $conversationState)
    {
        $eventType = isset($conversationState['event_type']) ? $conversationState['event_type'] : 'event';
        $guests = isset($conversationState['guests']) ? $conversationState['guests'] : 'your';

        $questions = [
            'greeting' => "Hello! I'm your AI event planning assistant. I'll help you find the perfect venue and suppliers for your event. Let's start with the basics - what type of event are you planning? (e.g., wedding, corporate event, birthday party, concert)",

            'event_type' => "Great! What type of event are you planning? For example:\n• Wedding\n• Corporate event/Conference\n• Birthday party\n• Concert or performance\n• Other celebration",

            'guest_count' => "Perfect! For your $eventType, how many guests are you expecting?",

            'budget' => "Excellent! For $guests guests, what's your total budget for the event? (This will help me recommend venues and services within your range)",

            'date' => "When are you planning to hold this event? (You can provide a specific date or just the month/year)",

            'services_needed' => "Now let's talk about services! Which of these would you like me to recommend?\n\n" .
                "📋 Available Services:\n" .
                "1. 🍽️ Catering (food and beverages)\n" .
                "2. 🎵 Lights and Sounds (audio-visual)\n" .
                "3. 📸 Photography\n" .
                "4. 🎥 Videography\n" .
                "5. 🎤 Host/Emcee\n" .
                "6. 💐 Styling and Flowers\n" .
                "7. 🪑 Equipment Rental (tables, chairs, etc.)\n\n" .
                "You can say 'all', mention specific ones, or say 'just the venue for now'"
        ];

        return isset($questions[$stage]) ? $questions[$stage] : "How else can I help you with your event?";
    }

    /**
     * Extract numerical features for venue scoring
     */
    private function extractVenueFeatures($venue, $requirements)
    {
        $features = [];

        $guests = isset($requirements['guests']) ? $requirements['guests'] : 50;
        $budget = isset($requirements['budget']) ? $requirements['budget'] : 50000;
        $capacity = $venue['capacity'];
        $price = $venue['base_price'] ?? 10000;

        // Feature 1: Capacity ratio
        $capacityRatio = $guests > 0 ? $capacity / $guests : 1.0;
        $features[] = $capacityRatio;

        // Feature 2: Price ratio
        $priceRatio = $budget > 0 ? $price / $budget : 1.0;
        $features[] = $priceRatio;

        // Feature 3: Capacity utilization
        if ($capacity >= $guests) {
            $utilization = $capacity > 0 ? $guests / $capacity : 0.5;
        } else {
            $utilization = $guests > 0 ? $capacity / $guests : 0.5;
        }
        $features[] = $utilization;

        // Feature 4: Price per guest
        $pricePerGuest = $guests > 0 ? $price / $guests : $price / 50;
        $features[] = $pricePerGuest;

        // Feature 5: Budget fit score
        $idealVenuePrice = $budget * 0.375;
        $budgetFit = $budget > 0 ? 1.0 - abs($price - $idealVenuePrice) / $budget : 0.5;
        $budgetFit = max(0, min(1, $budgetFit));
        $features[] = $budgetFit;

        // Feature 6: Amenity score
        $amenityCount = !empty($venue['amenities']) ? count(explode(',', $venue['amenities'])) : 0;
        $amenityScore = min($amenityCount / 10.0, 1.0);
        $features[] = $amenityScore;

        return $features;
    }

    /**
     * Calculate venue suitability score (rule-based ML alternative)
     */
    public function calculateVenueScore($venue, $requirements)
    {
        $guests = isset($requirements['guests']) ? $requirements['guests'] : 50;
        $budget = isset($requirements['budget']) ? $requirements['budget'] : 50000;
        $capacity = $venue['capacity'];
        $price = $venue['base_price'] ?? 10000;

        // Component 1: Capacity match (0-25 points)
        $capacityRatio = $guests > 0 ? $capacity / $guests : 1.0;

        if ($capacityRatio >= 1.0 && $capacityRatio <= 1.2) {
            $capacityScore = 25;
        } elseif ($capacityRatio > 1.2 && $capacityRatio <= 1.5) {
            $capacityScore = 25 - (($capacityRatio - 1.2) / 0.3) * 5;
        } elseif ($capacityRatio > 1.5 && $capacityRatio <= 2.0) {
            $capacityScore = 20 - (($capacityRatio - 1.5) / 0.5) * 8;
        } elseif ($capacityRatio >= 0.85 && $capacityRatio < 1.0) {
            $capacityScore = 18;
        } elseif ($capacityRatio > 2.0 && $capacityRatio <= 3.0) {
            $capacityScore = 12 - (($capacityRatio - 2.0) / 1.0) * 7;
        } else {
            $capacityScore = 5;
        }

        // Component 2: Budget optimization (0-30 points)
        $idealVenueBudget = $budget * 0.35;
        $priceRatio = $idealVenueBudget > 0 ? $price / $idealVenueBudget : 10.0;

        if ($priceRatio >= 0.8 && $priceRatio <= 1.1) {
            $budgetScore = 30;
        } elseif ($priceRatio >= 0.6 && $priceRatio < 0.8) {
            $budgetScore = 28;
        } elseif ($priceRatio > 1.1 && $priceRatio <= 1.3) {
            $budgetScore = 25 - (($priceRatio - 1.1) / 0.2) * 8;
        } elseif ($priceRatio > 1.3 && $priceRatio <= 1.6) {
            $budgetScore = 17 - (($priceRatio - 1.3) / 0.3) * 10;
        } elseif ($priceRatio >= 0.4 && $priceRatio < 0.6) {
            $budgetScore = 20;
        } elseif ($priceRatio < 0.4) {
            $budgetScore = 15;
        } else {
            $budgetScore = max(3, 7 - ($priceRatio - 1.6) * 3);
        }

        // Component 3: Value efficiency (0-20 points)
        $pricePerCapacity = $capacity > 0 ? $price / $capacity : PHP_FLOAT_MAX;
        $optimalPpc = ($budget * 0.35) / ($guests * 1.2);
        $ppcRatio = $optimalPpc > 0 ? $pricePerCapacity / $optimalPpc : 10.0;

        if ($ppcRatio >= 0.7 && $ppcRatio <= 1.1) {
            $valueScore = 20;
        } elseif ($ppcRatio >= 0.5 && $ppcRatio < 0.7) {
            $valueScore = 18;
        } elseif ($ppcRatio > 1.1 && $ppcRatio <= 1.4) {
            $valueScore = 15 - (($ppcRatio - 1.1) / 0.3) * 7;
        } elseif ($ppcRatio > 1.4 && $ppcRatio <= 2.0) {
            $valueScore = 8 - (($ppcRatio - 1.4) / 0.6) * 5;
        } else {
            $valueScore = 3;
        }

        // Component 4: Amenities (0-15 points)
        $amenityList = !empty($venue['amenities']) ? explode(',', $venue['amenities']) : [];
        $amenityCount = count(array_filter($amenityList, function ($a) {
            return trim($a) != '';
        }));

        if ($amenityCount >= 6) {
            $amenityScore = 15;
        } elseif ($amenityCount >= 4) {
            $amenityScore = 12;
        } elseif ($amenityCount >= 2) {
            $amenityScore = 8;
        } elseif ($amenityCount >= 1) {
            $amenityScore = 5;
        } else {
            $amenityScore = 2;
        }

        // Component 5: Size appropriateness (0-10 points)
        if ($capacityRatio >= 1.0 && $capacityRatio <= 1.4) {
            $sizeScore = 10;
        } elseif ($capacityRatio > 1.4 && $capacityRatio <= 2.0) {
            $sizeScore = 7;
        } elseif ($capacityRatio >= 0.9 && $capacityRatio < 1.0) {
            $sizeScore = 6;
        } else {
            $sizeScore = 3;
        }

        // Combine all components
        $totalScore = $capacityScore + $budgetScore + $valueScore + $amenityScore + $sizeScore;

        return min(100, max(0, $totalScore));
    }

    /**
     * Get venue recommendations
     */
    public function getVenueRecommendations($requirements)
    {
        $query = "SELECT 
                    v.venue_id,
                    v.venue_name,
                    v.capacity,
                    v.base_price,
                    v.location,
                    v.description,
                    GROUP_CONCAT(va.amenity_name SEPARATOR ', ') as amenities
                FROM venues v
                LEFT JOIN venue_amenities va ON v.venue_id = va.venue_id
                WHERE v.availability_status = 'available'
                GROUP BY v.venue_id";

        $stmt = $this->db->query($query);
        $venues = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Score each venue
        $scoredVenues = [];
        foreach ($venues as $venue) {
            $score = $this->calculateVenueScore($venue, $requirements);

            if ($score > 30) {
                $scoredVenues[] = [
                    'id' => $venue['venue_id'],
                    'name' => $venue['venue_name'],
                    'capacity' => $venue['capacity'],
                    'price' => isset($venue['base_price']) && is_numeric($venue['base_price']) 
                              ? floatval($venue['base_price']) 
                              : 10000.0,
                    'location' => $venue['location'],
                    'description' => $venue['description'],
                    'amenities' => $venue['amenities'] ?? '',
                    'score' => round($score, 1)
                ];
            }
        }

        // Sort by score
        usort($scoredVenues, function ($a, $b) {
            return $b['score'] - $a['score'];
        });

        return array_slice($scoredVenues, 0, 5);
    }

    /**
     * Get supplier recommendations
     */
    public function getSupplierRecommendations($requirements, $categories)
    {
        $categoryMap = [
            'catering' => 'Catering',
            'lights_and_sounds' => 'Lights and Sounds',
            'photography' => 'Photography',
            'videography' => 'Videography',
            'host' => 'Host/Emcee',
            'styling' => 'Styling and Flowers',
            'rental' => 'Equipment Rental'
        ];

        $recommendations = [];

        foreach ($categories as $categoryKey) {
            $dbCategory = isset($categoryMap[$categoryKey]) ? $categoryMap[$categoryKey] : null;
            if (!$dbCategory) continue;

            $query = "SELECT 
                        s.service_id,
                        s.service_name,
                        s.category,
                        s.description,
                        s.price,
                        sup.supplier_name,
                        sup.location,
                        sup.phone,
                        sup.email
                    FROM services s
                    JOIN suppliers sup ON s.supplier_id = sup.supplier_id
                    WHERE s.category = ? AND sup.availability_status = 'available'
                    ORDER BY s.price ASC";

            $stmt = $this->db->prepare($query);
            $stmt->execute([$dbCategory]);
            $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($services)) {
                $filtered = $this->filterByBudget($services, $requirements['budget'] ?? 50000);
                $recommendations[$dbCategory] = array_slice($filtered, 0, 2);
            }
        }

        return $recommendations;
    }

    /**
     * Filter services by budget allocation
     */
    private function filterByBudget($services, $totalBudget)
    {
        $allocations = [
            'Catering' => 0.25,
            'Lights and Sounds' => 0.15,
            'Photography' => 0.12,
            'Videography' => 0.12,
            'Host/Emcee' => 0.08,
            'Styling and Flowers' => 0.15,
            'Equipment Rental' => 0.10
        ];

        if (empty($services)) return [];

        $category = $services[0]['category'] ?? 'Unknown';
        $budgetForCategory = $totalBudget * ($allocations[$category] ?? 0.10);

        $filtered = [];
        foreach ($services as $service) {
            // Handle NULL or invalid price values
            $price = isset($service['price']) && is_numeric($service['price']) ? (float)$service['price'] : 0;
            if ($price <= $budgetForCategory * 1.3) {
                $filtered[] = $service;
            }
        }

        return !empty($filtered) ? $filtered : $services;
    }

    /**
     * Process conversation and generate response
     */
    public function processConversation($message, $conversationState = [])
    {
        // Determine stage and extract data
        $result = $this->determineStage($conversationState, $message);
        $stage = $result['stage'];
        $extractedData = $result['data'];

        // Update conversation state
        if (!empty($extractedData)) {
            // Handle services - append instead of replace
            if (isset($extractedData['services']) && isset($conversationState['services'])) {
                $existingServices = $conversationState['services'];
                $newServices = $extractedData['services'];
                unset($extractedData['services']);
                $extractedData['services'] = array_unique(array_merge($existingServices, $newServices));
            }

            $conversationState = array_merge($conversationState, $extractedData);
        }

        // Generate response
        if ($stage === 'recommendations') {
            return $this->generateFinalRecommendations($conversationState);
        } else {
            $question = $this->generateQuestion($stage, $conversationState);

            // Acknowledgment for extracted data
            $acknowledgment = "";
            if (isset($extractedData['event_type'])) {
                $acknowledgment .= "Got it! A " . $extractedData['event_type'] . " event. ";
            }
            if (isset($extractedData['guests'])) {
                $acknowledgment .= "For " . $extractedData['guests'] . " guests. ";
            }
            if (isset($extractedData['budget'])) {
                $acknowledgment .= "With a budget of ₱" . number_format($extractedData['budget']) . ". ";
            }

            $response = !empty($acknowledgment) ? $acknowledgment . "\n\n" . $question : $question;

            return [
                'success' => true,
                'response' => $response,
                'stage' => $stage,
                'conversation_state' => $conversationState,
                'needs_more_info' => true
            ];
        }
    }

    /**
     * Generate final recommendations
     */
    public function generateFinalRecommendations($conversationState)
    {
        try {
            // Get venue recommendations
            $venues = $this->getVenueRecommendations($conversationState);

            // Get supplier recommendations
            $servicesNeeded = $conversationState['services'] ?? [];
            if (empty($servicesNeeded) || in_array('all', $servicesNeeded)) {
                $servicesNeeded = [
                    'catering',
                    'lights_and_sounds',
                    'photography',
                    'videography',
                    'host',
                    'styling',
                    'rental'
                ];
            }

            $suppliers = $this->getSupplierRecommendations($conversationState, $servicesNeeded);

            // Format response
            $response = $this->formatFinalResponse($conversationState, $venues, $suppliers);

            return [
                'success' => true,
                'response' => $response,
                'venues' => $venues,
                'suppliers' => $suppliers,
                'conversation_state' => $conversationState,
                'needs_more_info' => false,
                'stage' => 'complete'
            ];
        } catch (Exception $e) {
            // Log the error with full details
            error_log('Error in generateFinalRecommendations: ' . $e->getMessage());
            error_log('Trace: ' . $e->getTraceAsString());
            
            // Rethrow to be caught by ai-conversation.php with proper formatting
            throw new Exception('Failed to generate recommendations: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Format final response message
     */
    private function formatFinalResponse($state, $venues, $suppliers)
    {
        $eventType = ucfirst($state['event_type'] ?? 'event');
        $guests = $state['guests'] ?? 'N/A';
        $budget = isset($state['budget']) ? $state['budget'] : null;

        $response = "🎉 Perfect! Here's your complete event plan for your $eventType:\n\n";
        $response .= "📊 Event Summary:\n";
        $response .= "• Type: $eventType\n";
        $response .= "• Guests: $guests\n";
        if ($budget) {
            $response .= "• Budget: ₱" . number_format($budget) . "\n";
        }

        $venueCount = count($venues);
        $response .= "\n🏛️ Top Venue Recommendations ($venueCount found):\n";
        if ($venueCount > 0) {
            $response .= "I've found the best venues that match your requirements. Check them out below!\n";
        } else {
            $response .= "Unfortunately, no venues match your exact criteria right now. Try adjusting your budget or guest count.\n";
        }

        $supplierCount = array_sum(array_map('count', $suppliers));
        $response .= "\n👥 Recommended Suppliers & Services ($supplierCount found):\n";
        if ($supplierCount > 0) {
            $response .= "I've curated the best suppliers for each service you need. Scroll down to see all options!\n";
        } else {
            $response .= "I'm still working on finding the perfect suppliers for your event.\n";
        }

        return $response;
    }
}
