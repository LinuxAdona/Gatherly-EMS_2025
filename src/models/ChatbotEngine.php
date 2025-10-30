<?php

/**
 * AI Chatbot Engine
 * Natural Language Processing for Venue Recommendations
 * 
 * Features:
 * - Parse natural language queries
 * - Extract event requirements
 * - Provide conversational venue recommendations
 * - Context-aware responses
 */

class ChatbotEngine
{
    private $conn;
    private $recommendationEngine;
    private $pricingEngine;

    // Keywords for intent detection
    private $eventTypes = ['wedding', 'birthday', 'corporate', 'conference', 'concert', 'party', 'celebration'];
    private $amenities = [
        'catering' => ['catering', 'food', 'buffet', 'dining'],
        'parking' => ['parking', 'car park', 'parking space', 'parking lot'],
        'sound_system' => ['sound', 'audio', 'speakers', 'sound system'],
        'stage' => ['stage', 'platform', 'stage setup'],
        'air_conditioning' => ['air conditioning', 'ac', 'aircon', 'air-conditioned']
    ];

    public function __construct($dbConnection, $recommendationEngine, $pricingEngine)
    {
        $this->conn = $dbConnection;
        $this->recommendationEngine = $recommendationEngine;
        $this->pricingEngine = $pricingEngine;
    }

    /**
     * Main chat processing function
     */
    public function processMessage($userId, $message)
    {
        $message = strtolower(trim($message));

        // Detect intent
        $intent = $this->detectIntent($message);

        // Extract entities from message
        $entities = $this->extractEntities($message);

        // Generate response based on intent
        $response = $this->generateResponse($intent, $entities, $message);

        // Save conversation
        $this->saveConversation($userId, $message, $response['text'], $entities);

        return $response;
    }

    /**
     * Detect user intent from message
     */
    private function detectIntent($message)
    {
        // Search/Recommendation intent
        if (preg_match('/\b(find|search|looking for|need|want|show|recommend|suggest)\b/i', $message)) {
            return 'search_venue';
        }

        // Price inquiry
        if (preg_match('/\b(price|cost|rate|budget|how much|expensive|cheap|affordable)\b/i', $message)) {
            return 'price_inquiry';
        }

        // Availability check
        if (preg_match('/\b(available|free|book|reserve|vacancy)\b/i', $message)) {
            return 'availability_check';
        }

        // Comparison
        if (preg_match('/\b(compare|difference|better|versus|vs|which)\b/i', $message)) {
            return 'compare_venues';
        }

        // Help/Information
        if (preg_match('/\b(help|how|what|explain|tell me)\b/i', $message)) {
            return 'help';
        }

        // Greeting
        if (preg_match('/\b(hi|hello|hey|good morning|good afternoon)\b/i', $message)) {
            return 'greeting';
        }

        return 'unknown';
    }

    /**
     * Extract entities (event details) from message
     */
    private function extractEntities($message)
    {
        $entities = [
            'event_type' => null,
            'guest_count' => null,
            'budget' => null,
            'date' => null,
            'location' => null,
            'amenities' => []
        ];

        // Extract event type
        foreach ($this->eventTypes as $type) {
            if (strpos($message, $type) !== false) {
                $entities['event_type'] = ucfirst($type);
                break;
            }
        }

        // Extract guest count
        if (preg_match('/(\d+)\s*(guests?|people|persons?|pax)/i', $message, $matches)) {
            $entities['guest_count'] = intval($matches[1]);
        }

        // Extract budget
        if (preg_match('/(\d+)k/i', $message, $matches)) {
            $entities['budget'] = intval($matches[1]) * 1000;
        } elseif (preg_match('/(₱|php|peso|pesos)?\s*(\d+(?:,\d{3})*)/i', $message, $matches)) {
            $entities['budget'] = intval(str_replace(',', '', $matches[2]));
        }

        // Extract location
        $locations = ['taguig', 'makati', 'quezon city', 'pasay', 'manila', 'bgc', 'ortigas'];
        foreach ($locations as $loc) {
            if (strpos($message, $loc) !== false) {
                $entities['location'] = ucwords($loc);
                break;
            }
        }

        // Extract amenities
        foreach ($this->amenities as $amenity => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($message, $keyword) !== false) {
                    $entities['amenities'][] = $amenity;
                    break;
                }
            }
        }
        $entities['amenities'] = array_unique($entities['amenities']);

        return $entities;
    }

    /**
     * Generate response based on intent and entities
     */
    private function generateResponse($intent, $entities, $originalMessage)
    {
        switch ($intent) {
            case 'search_venue':
                return $this->handleVenueSearch($entities);

            case 'price_inquiry':
                return $this->handlePriceInquiry($entities);

            case 'availability_check':
                return $this->handleAvailabilityCheck($entities);

            case 'compare_venues':
                return $this->handleVenueComparison($entities);

            case 'greeting':
                return [
                    'text' => "Hello! 👋 I'm your AI venue assistant. I can help you find the perfect venue for your event. Just tell me:\n\n" .
                        "• Event type (wedding, birthday, corporate, etc.)\n" .
                        "• Number of guests\n" .
                        "• Your budget\n" .
                        "• Any specific requirements\n\n" .
                        "For example: 'Find me a wedding venue for 200 guests under 150K with catering'",
                    'type' => 'text',
                    'suggestions' => [
                        'Show me wedding venues',
                        'I need a venue for 150 guests',
                        'What venues have parking?',
                        'Compare top 3 venues'
                    ]
                ];

            case 'help':
                return [
                    'text' => "I can help you with:\n\n" .
                        "🔍 **Search Venues**: Tell me your requirements\n" .
                        "💰 **Check Prices**: Ask about venue pricing\n" .
                        "📅 **Check Availability**: See which venues are free\n" .
                        "⚖️ **Compare Options**: Compare different venues\n\n" .
                        "Try asking: 'Find a birthday venue for 100 guests around 80K'",
                    'type' => 'text'
                ];

            default:
                return [
                    'text' => "I'm not sure I understand. Could you please provide more details?\n\n" .
                        "Example: 'I need a venue for a corporate event, 150 guests, budget around 100K'",
                    'type' => 'text'
                ];
        }
    }

    /**
     * Handle venue search request
     */
    private function handleVenueSearch($entities)
    {
        // Check if we have enough information
        $missingInfo = [];
        if (!$entities['event_type']) $missingInfo[] = 'event type';
        if (!$entities['guest_count']) $missingInfo[] = 'number of guests';
        if (!$entities['budget']) $missingInfo[] = 'budget';

        if (!empty($missingInfo)) {
            return [
                'text' => "I'd love to help! Could you please tell me:\n" .
                    implode(', ', $missingInfo) . "?",
                'type' => 'clarification',
                'missing' => $missingInfo
            ];
        }

        // Build criteria for recommendation engine
        $criteria = [
            'event_type' => $entities['event_type'],
            'guest_count' => $entities['guest_count'],
            'budget' => $entities['budget'],
            'event_date' => date('Y-m-d', strtotime('+1 month')), // Default to next month
            'required_amenities' => $entities['amenities']
        ];

        // Get recommendations
        $recommendations = $this->recommendationEngine->getRecommendations($criteria, 3);

        if (empty($recommendations)) {
            return [
                'text' => "I couldn't find any venues matching your exact requirements. Try adjusting your budget or guest count.",
                'type' => 'no_results'
            ];
        }

        // Format response
        $response = "Great! I found **" . count($recommendations) . " perfect venues** for your ";
        $response .= $entities['event_type'] . " with " . $entities['guest_count'] . " guests:\n\n";

        foreach ($recommendations as $index => $rec) {
            $venue = $rec['venue'];
            $response .= "**" . ($index + 1) . ". " . $venue['venue_name'] . "** - " .
                $rec['match_percentage'] . " match\n";
            $response .= "   📍 " . $venue['location'] . "\n";
            $response .= "   💰 ₱" . number_format($venue['base_price'], 2) . "\n";
            $response .= "   👥 Capacity: " . $venue['capacity'] . " guests\n";
            $response .= "   ⭐ " . $rec['reason'] . "\n\n";
        }

        $response .= "Would you like to see more details or book one of these venues?";

        return [
            'text' => $response,
            'type' => 'venue_list',
            'venues' => $recommendations,
            'actions' => [
                'View Details',
                'Book Now',
                'See More Options'
            ]
        ];
    }

    /**
     * Handle price inquiry
     */
    private function handlePriceInquiry($entities)
    {
        $sql = "SELECT venue_id, venue_name, location, base_price, capacity 
                FROM venues 
                WHERE availability_status = 'available'";

        if ($entities['budget']) {
            $maxBudget = $entities['budget'] * 1.2; // 20% flexibility
            $sql .= " AND base_price <= $maxBudget";
        }

        if ($entities['guest_count']) {
            $minCapacity = $entities['guest_count'] * 0.8;
            $sql .= " AND capacity >= $minCapacity";
        }

        $sql .= " ORDER BY base_price ASC LIMIT 5";

        $result = $this->conn->query($sql);
        $venues = $result->fetch_all(MYSQLI_ASSOC);

        if (empty($venues)) {
            return [
                'text' => "I found no venues in that price range. Try increasing your budget.",
                'type' => 'no_results'
            ];
        }

        $response = "Here are venues ";
        if ($entities['budget']) {
            $response .= "under ₱" . number_format($entities['budget'], 2) . ":\n\n";
        } else {
            $response .= "sorted by price:\n\n";
        }

        foreach ($venues as $venue) {
            $response .= "• **" . $venue['venue_name'] . "**\n";
            $response .= "  💰 ₱" . number_format($venue['base_price'], 2) . "\n";
            $response .= "  📍 " . $venue['location'] . "\n";
            $response .= "  👥 Up to " . $venue['capacity'] . " guests\n\n";
        }

        return [
            'text' => $response,
            'type' => 'price_list',
            'venues' => $venues
        ];
    }

    /**
     * Handle availability check
     */
    private function handleAvailabilityCheck($entities)
    {
        $sql = "SELECT v.venue_id, v.venue_name, v.location, v.base_price, v.capacity
                FROM venues v
                WHERE v.availability_status = 'available'";

        // Check for date-specific availability
        if ($entities['date']) {
            $sql .= " AND v.venue_id NOT IN (
                SELECT venue_id FROM events 
                WHERE event_date = '" . $entities['date'] . "' 
                AND status IN ('confirmed', 'pending')
            )";
        }

        $sql .= " LIMIT 10";

        $result = $this->conn->query($sql);
        $venues = $result->fetch_all(MYSQLI_ASSOC);

        $response = "**" . count($venues) . " venues** are currently available";
        if ($entities['date']) {
            $response .= " on " . date('F j, Y', strtotime($entities['date']));
        }
        $response .= ":\n\n";

        foreach ($venues as $venue) {
            $response .= "✅ **" . $venue['venue_name'] . "** - " . $venue['location'] . "\n";
            $response .= "   ₱" . number_format($venue['base_price'], 2) . " | " .
                $venue['capacity'] . " guests\n\n";
        }

        return [
            'text' => $response,
            'type' => 'availability_list',
            'venues' => $venues
        ];
    }

    /**
     * Handle venue comparison
     */
    private function handleVenueComparison($entities)
    {
        // Get top venues based on criteria
        $criteria = [
            'event_type' => $entities['event_type'] ?? 'Corporate',
            'guest_count' => $entities['guest_count'] ?? 150,
            'budget' => $entities['budget'] ?? 100000,
            'event_date' => date('Y-m-d', strtotime('+1 month'))
        ];

        $recommendations = $this->recommendationEngine->getRecommendations($criteria, 3);

        if (count($recommendations) < 2) {
            return [
                'text' => "I need at least 2 venues to compare. Please provide more details.",
                'type' => 'insufficient_data'
            ];
        }

        $response = "**Venue Comparison:**\n\n";

        $response .= "| Feature | ";
        foreach ($recommendations as $rec) {
            $response .= $rec['venue']['venue_name'] . " | ";
        }
        $response .= "\n|---------|";
        foreach ($recommendations as $rec) {
            $response .= "---------|";
        }
        $response .= "\n";

        // Match Score
        $response .= "| **Match Score** | ";
        foreach ($recommendations as $rec) {
            $response .= $rec['match_percentage'] . " | ";
        }
        $response .= "\n";

        // Price
        $response .= "| **Price** | ";
        foreach ($recommendations as $rec) {
            $response .= "₱" . number_format($rec['venue']['base_price']) . " | ";
        }
        $response .= "\n";

        // Capacity
        $response .= "| **Capacity** | ";
        foreach ($recommendations as $rec) {
            $response .= $rec['venue']['capacity'] . " | ";
        }
        $response .= "\n";

        // Location
        $response .= "| **Location** | ";
        foreach ($recommendations as $rec) {
            $response .= $rec['venue']['location'] . " | ";
        }
        $response .= "\n";

        $response .= "\n**Recommendation:** Based on your requirements, **" .
            $recommendations[0]['venue']['venue_name'] . "** is your best option!";

        return [
            'text' => $response,
            'type' => 'comparison',
            'venues' => $recommendations
        ];
    }

    /**
     * Save conversation to database
     */
    private function saveConversation($userId, $message, $response, $context)
    {
        $sql = "INSERT INTO ai_chat_messages (user_id, message, response, context_data) 
                VALUES (?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $contextJson = json_encode($context);
        $stmt->bind_param("isss", $userId, $message, $response, $contextJson);
        $stmt->execute();
    }

    /**
     * Get conversation history for context
     */
    public function getConversationHistory($userId, $limit = 10)
    {
        $sql = "SELECT * FROM ai_chat_messages 
                WHERE user_id = ? 
                ORDER BY timestamp DESC 
                LIMIT ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $userId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        return array_reverse($result->fetch_all(MYSQLI_ASSOC));
    }
}
