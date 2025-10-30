<?php

/**
 * Dynamic Pricing Engine
 * Implements time-series forecasting and demand-based pricing
 * 
 * Features:
 * - Seasonal pricing (peak vs off-peak)
 * - Day-based pricing (weekday vs weekend)
 * - Demand-based multipliers
 * - Occupancy rate optimization
 * - Price forecasting
 */

class DynamicPricingEngine
{
    private $conn;

    // Pricing multipliers
    private $peakSeasonMultiplier = 1.30;  // 30% increase
    private $offPeakMultiplier = 0.85;     // 15% discount
    private $weekendMultiplier = 1.20;     // 20% increase
    private $weekdayMultiplier = 0.95;     // 5% discount

    // Demand thresholds
    private $highDemandThreshold = 5;      // 5+ inquiries
    private $lowDemandThreshold = 1;       // 1 or fewer inquiries

    public function __construct($dbConnection)
    {
        $this->conn = $dbConnection;
    }

    /**
     * Calculate dynamic price for a venue on a specific date
     */
    public function calculatePrice($venueId, $date)
    {
        // Get venue base price
        $venue = $this->getVenue($venueId);
        if (!$venue) {
            return null;
        }

        $basePrice = $venue['base_price'];

        // Calculate multipliers
        $seasonMultiplier = $this->getSeasonMultiplier($date);
        $dayTypeMultiplier = $this->getDayTypeMultiplier($date);
        $demandMultiplier = $this->getDemandMultiplier($venueId, $date);

        // Calculate final price
        $calculatedPrice = $basePrice * $seasonMultiplier * $dayTypeMultiplier * $demandMultiplier;

        // Round to nearest 100
        $calculatedPrice = round($calculatedPrice / 100) * 100;

        // Get occupancy rate for context
        $occupancyRate = $this->calculateOccupancyRate($venueId, $date);

        // Log pricing history
        $this->logPricingHistory(
            $venueId,
            $date,
            $calculatedPrice,
            $demandMultiplier,
            $seasonMultiplier,
            $dayTypeMultiplier,
            $occupancyRate
        );

        return [
            'base_price' => $basePrice,
            'calculated_price' => $calculatedPrice,
            'season_multiplier' => $seasonMultiplier,
            'day_type_multiplier' => $dayTypeMultiplier,
            'demand_multiplier' => $demandMultiplier,
            'occupancy_rate' => $occupancyRate,
            'discount_percentage' => $this->calculateDiscountPercentage($basePrice, $calculatedPrice),
            'pricing_type' => $this->getPricingType($seasonMultiplier, $dayTypeMultiplier, $demandMultiplier)
        ];
    }

    /**
     * Get venue details
     */
    private function getVenue($venueId)
    {
        $sql = "SELECT * FROM venues WHERE venue_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $venueId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Determine season multiplier
     * Peak seasons: December-February (Christmas/New Year), June-August (Summer/Graduations)
     */
    private function getSeasonMultiplier($date)
    {
        $month = date('n', strtotime($date));

        // Peak months
        if (in_array($month, [12, 1, 2, 6, 7, 8])) {
            return $this->peakSeasonMultiplier;
        }

        // Shoulder months (moderate demand)
        if (in_array($month, [3, 4, 5, 9, 10, 11])) {
            return 1.0; // Base price
        }

        return $this->offPeakMultiplier;
    }

    /**
     * Determine day type multiplier
     */
    private function getDayTypeMultiplier($date)
    {
        $dayOfWeek = date('N', strtotime($date));

        // Weekend (Saturday=6, Sunday=7)
        if ($dayOfWeek >= 6) {
            return $this->weekendMultiplier;
        }

        // Weekday
        return $this->weekdayMultiplier;
    }

    /**
     * Calculate demand multiplier based on inquiry patterns
     * More inquiries = higher price (hotel-style pricing)
     */
    private function getDemandMultiplier($venueId, $date)
    {
        // Get inquiries for this venue around this date (±7 days window)
        $dateObj = new DateTime($date);
        $startDate = (clone $dateObj)->modify('-7 days')->format('Y-m-d');
        $endDate = (clone $dateObj)->modify('+7 days')->format('Y-m-d');

        $sql = "SELECT SUM(inquiry_count) as total_inquiries
                FROM venue_demand_log
                WHERE venue_id = ?
                AND inquiry_date BETWEEN ? AND ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iss", $venueId, $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        $inquiries = $data['total_inquiries'] ?? 0;

        // High demand - increase price
        if ($inquiries >= $this->highDemandThreshold * 2) {
            return 1.30; // 30% increase
        } elseif ($inquiries >= $this->highDemandThreshold) {
            return 1.15; // 15% increase
        }

        // Low demand - offer discount
        if ($inquiries <= $this->lowDemandThreshold) {
            return 0.90; // 10% discount
        }

        // Moderate demand - base price
        return 1.0;
    }

    /**
     * Calculate occupancy rate for a venue
     */
    private function calculateOccupancyRate($venueId, $date)
    {
        // Get month for analysis
        $month = date('Y-m', strtotime($date));
        $monthStart = $month . '-01';
        $monthEnd = date('Y-m-t', strtotime($monthStart));

        // Count booked days in the month
        $sql = "SELECT COUNT(DISTINCT DATE(event_date)) as booked_days
                FROM events
                WHERE venue_id = ?
                AND event_date BETWEEN ? AND ?
                AND status IN ('confirmed', 'completed')";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iss", $venueId, $monthStart, $monthEnd);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        $bookedDays = $data['booked_days'] ?? 0;
        $totalDays = date('t', strtotime($monthStart));

        return round(($bookedDays / $totalDays) * 100, 2);
    }

    /**
     * Log pricing history for analysis
     */
    private function logPricingHistory($venueId, $date, $price, $demandMult, $seasonMult, $dayMult, $occupancy)
    {
        $sql = "INSERT INTO pricing_history 
                (venue_id, date, calculated_price, demand_multiplier, 
                season_multiplier, day_type_multiplier, occupancy_rate)
                VALUES (?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                calculated_price = VALUES(calculated_price),
                demand_multiplier = VALUES(demand_multiplier),
                season_multiplier = VALUES(season_multiplier),
                day_type_multiplier = VALUES(day_type_multiplier),
                occupancy_rate = VALUES(occupancy_rate)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "isddddd",
            $venueId,
            $date,
            $price,
            $demandMult,
            $seasonMult,
            $dayMult,
            $occupancy
        );
        $stmt->execute();
    }

    /**
     * Track inquiry for demand forecasting
     */
    public function trackInquiry($venueId, $date = null)
    {
        $date = $date ?? date('Y-m-d');

        $sql = "INSERT INTO venue_demand_log (venue_id, inquiry_date, inquiry_count)
                VALUES (?, ?, 1)
                ON DUPLICATE KEY UPDATE
                inquiry_count = inquiry_count + 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is", $venueId, $date);
        $stmt->execute();
    }

    /**
     * Track booking for demand forecasting
     */
    public function trackBooking($venueId, $date = null)
    {
        $date = $date ?? date('Y-m-d');

        $sql = "UPDATE venue_demand_log 
                SET booking_count = booking_count + 1
                WHERE venue_id = ? AND inquiry_date = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is", $venueId, $date);
        $stmt->execute();
    }

    /**
     * Forecast occupancy rate for next N months
     */
    public function forecastOccupancy($venueId, $months = 6)
    {
        // Get historical occupancy data
        $sql = "SELECT 
                DATE_FORMAT(event_date, '%Y-%m') as month,
                COUNT(DISTINCT DATE(event_date)) as booked_days,
                DAY(LAST_DAY(event_date)) as total_days
                FROM events
                WHERE venue_id = ?
                AND event_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                AND status IN ('confirmed', 'completed')
                GROUP BY DATE_FORMAT(event_date, '%Y-%m')
                ORDER BY month";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $venueId);
        $stmt->execute();
        $result = $stmt->get_result();
        $historicalData = $result->fetch_all(MYSQLI_ASSOC);

        // Calculate average occupancy
        $totalOccupancy = 0;
        $dataPoints = count($historicalData);

        foreach ($historicalData as $data) {
            $occupancy = ($data['booked_days'] / $data['total_days']) * 100;
            $totalOccupancy += $occupancy;
        }

        $avgOccupancy = $dataPoints > 0 ? $totalOccupancy / $dataPoints : 50;

        // Simple forecast (could be enhanced with more sophisticated ML)
        $forecast = [];
        $currentMonth = new DateTime();

        for ($i = 0; $i < $months; $i++) {
            $forecastMonth = (clone $currentMonth)->modify("+$i months");
            $monthKey = $forecastMonth->format('Y-m');
            $monthNum = $forecastMonth->format('n');

            // Adjust for seasonality
            $seasonalAdjustment = in_array($monthNum, [12, 1, 2, 6, 7, 8]) ? 1.2 : 0.9;
            $forecastedOccupancy = min(100, $avgOccupancy * $seasonalAdjustment);

            $forecast[] = [
                'month' => $monthKey,
                'forecasted_occupancy' => round($forecastedOccupancy, 2),
                'confidence' => $dataPoints >= 6 ? 'high' : 'moderate'
            ];
        }

        return $forecast;
    }

    /**
     * Suggest optimal price for maximum revenue
     */
    public function suggestOptimalPrice($venueId, $date)
    {
        $pricingData = $this->calculatePrice($venueId, $date);
        $occupancyForecast = $this->forecastOccupancy($venueId, 1)[0] ?? ['forecasted_occupancy' => 50];

        $basePrice = $pricingData['calculated_price'];
        $forecastedOccupancy = $occupancyForecast['forecasted_occupancy'];

        // If low occupancy forecast, suggest discount
        if ($forecastedOccupancy < 40) {
            $suggestedPrice = $basePrice * 0.85; // 15% discount
            $strategy = 'Discount strategy to increase bookings';
        } elseif ($forecastedOccupancy > 80) {
            $suggestedPrice = $basePrice * 1.10; // 10% premium
            $strategy = 'Premium pricing due to high demand';
        } else {
            $suggestedPrice = $basePrice;
            $strategy = 'Optimal pricing maintained';
        }

        return [
            'current_price' => $basePrice,
            'suggested_price' => round($suggestedPrice / 100) * 100,
            'forecasted_occupancy' => $forecastedOccupancy,
            'strategy' => $strategy
        ];
    }

    /**
     * Calculate discount percentage
     */
    private function calculateDiscountPercentage($basePrice, $calculatedPrice)
    {
        $diff = $basePrice - $calculatedPrice;
        return round(($diff / $basePrice) * 100, 1);
    }

    /**
     * Get pricing type label
     */
    private function getPricingType($seasonMult, $dayMult, $demandMult)
    {
        if ($seasonMult > 1.0 && $dayMult > 1.0 && $demandMult > 1.0) {
            return 'Peak Premium';
        } elseif ($seasonMult < 1.0 || $demandMult < 1.0) {
            return 'Discounted';
        } elseif ($demandMult > 1.0) {
            return 'High Demand';
        } else {
            return 'Standard';
        }
    }

    /**
     * Get pricing insights for venue owners
     */
    public function getPricingInsights($venueId)
    {
        // Get recent pricing trends
        $sql = "SELECT 
                DATE_FORMAT(date, '%Y-%m') as month,
                AVG(calculated_price) as avg_price,
                AVG(occupancy_rate) as avg_occupancy,
                AVG(demand_multiplier) as avg_demand
                FROM pricing_history
                WHERE venue_id = ?
                AND date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(date, '%Y-%m')
                ORDER BY month";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $venueId);
        $stmt->execute();
        $result = $stmt->get_result();
        $trends = $result->fetch_all(MYSQLI_ASSOC);

        return [
            'trends' => $trends,
            'forecast' => $this->forecastOccupancy($venueId, 3)
        ];
    }
}
