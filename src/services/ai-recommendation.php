<?php
session_start();

// Check if user is logged in and is an organizer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

require_once 'dbconnect.php';

header('Content-Type: application/json');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$message = isset($input['message']) ? trim($input['message']) : '';

if (empty($message)) {
    echo json_encode(['success' => false, 'error' => 'Message is required']);
    exit();
}

// Parse user message to extract event requirements
$parsedData = parseEventRequirements($message);

// Get venue recommendations based on parsed data
$venues = getRecommendedVenues($conn, $parsedData);

// Generate AI response
$response = generateResponse($parsedData, $venues);

echo json_encode([
    'success' => true,
    'response' => $response,
    'venues' => $venues,
    'parsed_data' => $parsedData
]);

$conn->close();

/**
 * Parse event requirements from user message
 */
function parseEventRequirements($message)
{
    $message = strtolower($message);
    $data = [
        'event_type' => null,
        'guests' => null,
        'budget' => null,
        'date' => null,
        'amenities' => []
    ];

    // Extract event type
    $eventTypes = [
        'wedding' => ['wedding', 'marriage', 'nuptial'],
        'corporate' => ['corporate', 'business', 'conference', 'seminar', 'meeting'],
        'birthday' => ['birthday', 'party', 'celebration'],
        'concert' => ['concert', 'music', 'show', 'performance']
    ];

    foreach ($eventTypes as $type => $keywords) {
        foreach ($keywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                $data['event_type'] = $type;
                break 2;
            }
        }
    }

    // Extract number of guests
    if (preg_match('/(\d+)\s*(guests?|people|attendees?|pax)/i', $message, $matches)) {
        $data['guests'] = intval($matches[1]);
    }

    // Extract budget
    if (preg_match('/(₱|php|peso|pesos?)\s*(\d+[,\d]*)/i', $message, $matches)) {
        $data['budget'] = intval(str_replace(',', '', $matches[2]));
    } elseif (preg_match('/(\d+[,\d]*)\s*(₱|php|peso|pesos?|budget)/i', $message, $matches)) {
        $data['budget'] = intval(str_replace(',', '', $matches[1]));
    }

    // Extract amenities
    $amenityKeywords = [
        'parking' => ['parking', 'park'],
        'catering' => ['catering', 'food', 'buffet'],
        'sound' => ['sound', 'audio', 'speaker'],
        'stage' => ['stage', 'platform'],
        'ac' => ['air conditioning', 'aircon', 'ac', 'airconditioned'],
        'wifi' => ['wifi', 'wi-fi', 'internet']
    ];

    foreach ($amenityKeywords as $amenity => $keywords) {
        foreach ($keywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                $data['amenities'][] = $amenity;
                break;
            }
        }
    }

    return $data;
}

/**
 * Get recommended venues based on requirements
 */
function getRecommendedVenues($conn, $data)
{
    $venues = [];

    // Build query based on available data
    $query = "SELECT 
                v.venue_id,
                v.venue_name,
                v.capacity,
                v.base_price,
                v.location,
                v.description,
                v.availability_status";

    // Calculate suitability score
    $scoreComponents = [];
    $whereConditions = ["v.availability_status = 'available'"];

    if ($data['guests']) {
        $guests = $data['guests'];
        $scoreComponents[] = "(CASE 
            WHEN v.capacity >= $guests AND v.capacity <= ($guests * 1.5) THEN 40
            WHEN v.capacity >= ($guests * 0.8) AND v.capacity < $guests THEN 30
            WHEN v.capacity > ($guests * 1.5) THEN 20
            ELSE 10
        END)";
    } else {
        $scoreComponents[] = "25";
    }

    if ($data['budget']) {
        $budget = $data['budget'];
        $scoreComponents[] = "(CASE 
            WHEN v.base_price <= $budget THEN 40
            WHEN v.base_price <= ($budget * 1.2) THEN 30
            WHEN v.base_price <= ($budget * 1.5) THEN 20
            ELSE 10
        END)";
    } else {
        $scoreComponents[] = "25";
    }

    // Add base scores for location and amenities
    $scoreComponents[] = "20"; // Location score

    $scoreFormula = implode(' + ', $scoreComponents);
    $query .= ", ($scoreFormula) as suitability_score";

    $query .= " FROM venues v WHERE " . implode(' AND ', $whereConditions);
    $query .= " ORDER BY suitability_score DESC LIMIT 5";

    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $venues[] = [
                'id' => $row['venue_id'],
                'name' => $row['venue_name'],
                'capacity' => intval($row['capacity']),
                'price' => floatval($row['base_price']),
                'location' => $row['location'],
                'description' => $row['description'],
                'score' => round($row['suitability_score'])
            ];
        }
    }

    return $venues;
}

/**
 * Generate AI response based on parsed data and recommendations
 */
function generateResponse($data, $venues)
{
    $response = "";

    // Acknowledge what was understood
    $understood = [];
    if ($data['event_type']) {
        $understood[] = "a " . ucfirst($data['event_type']) . " event";
    }
    if ($data['guests']) {
        $understood[] = $data['guests'] . " guests";
    }
    if ($data['budget']) {
        $understood[] = "₱" . number_format($data['budget']) . " budget";
    }

    if (!empty($understood)) {
        $response = "Great! I understand you're planning " . implode(" for ", $understood) . ". ";
    } else {
        $response = "I'd love to help you find the perfect venue! ";
    }

    // Provide recommendations
    if (!empty($venues)) {
        $response .= "Here are my top " . count($venues) . " venue recommendations based on your requirements:";
    } else {
        $response .= "I couldn't find any venues matching your exact criteria. Could you provide more details about your event? For example:\n\n";
        $response .= "• Number of expected guests\n";
        $response .= "• Your budget range\n";
        $response .= "• Type of event (wedding, corporate, birthday, etc.)\n";
        $response .= "• Any special requirements or amenities needed";
    }

    return $response;
}
