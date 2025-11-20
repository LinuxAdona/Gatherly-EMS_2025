# Ensemble AI Recommendation System - Implementation Guide

## Overview

The AI Event Planner now uses an **Ensemble Algorithm Approach** that combines all 3 machine learning algorithms to provide the most robust and accurate venue recommendations.

## What is Ensemble Learning?

Ensemble learning combines predictions from multiple algorithms to produce better results than any single algorithm could achieve alone. Think of it as getting a second (and third) opinion from experts before making a decision.

### Our Ensemble Configuration

```
Final Score = (MCDM × 35%) + (KNN × 35%) + (Decision Tree × 30%)
```

**Algorithm Weights:**

- **MCDM**: 35% - Balanced criteria evaluation
- **KNN**: 35% - Historical pattern matching
- **Decision Tree**: 30% - Rule-based filtering

## How It Works

### Step 1: User Input

User describes their event requirements:

- Event type (wedding, corporate, etc.)
- Number of guests
- Budget
- Special requirements

### Step 2: Multi-Algorithm Scoring

Each venue is scored by all 3 algorithms independently:

1. **MCDM (Multi-Criteria Decision Making)**

   - Evaluates: Capacity (30%), Budget (35%), Location (15%), Amenities (20%)
   - Strength: Balanced, transparent scoring
   - Best for: Budget-conscious events

2. **KNN (K-Nearest Neighbors)**

   - Learns from historical successful bookings
   - Uses k=5 nearest neighbors
   - Strength: Adaptive, data-driven
   - Best for: Following proven patterns

3. **Decision Tree**
   - Applies strict rule-based filters
   - Hard constraints on capacity and budget
   - Strength: Conservative, eliminates poor matches
   - Best for: Events with strict requirements

### Step 3: Ensemble Combination

All three scores are combined using weighted averaging to produce a final ensemble score.

### Step 4: Top 3 Selection

Venues are ranked by ensemble score and the top 3 are presented with full algorithm breakdown.

## Example Results

**Query:** "Wedding for 150 guests with budget of ₱50000"

### Top Result: Emerald Garden (95.45% Match)

| Algorithm     | Individual Score | Weight | Contribution |
| ------------- | ---------------- | ------ | ------------ |
| MCDM          | 87%              | 35%    | 30.45%       |
| KNN           | 100%             | 35%    | 35%          |
| Decision Tree | 100%             | 30%    | 30%          |
| **Ensemble**  | **95.45%**       | -      | **Total**    |

**Why This Works:**

- **High KNN score (100%)**: Similar successful weddings booked this venue
- **Perfect Decision Tree (100%)**: Meets all strict capacity/budget requirements
- **Good MCDM (87%)**: Balanced evaluation shows it's a solid choice
- **Strong Consensus**: All algorithms agree this is an excellent match

## Benefits of Ensemble Approach

### 1. Robustness

- No single algorithm's weakness dominates
- Compensates for individual algorithm biases
- More stable recommendations

### 2. Accuracy

- Combines strengths of multiple approaches
- Reduces false positives and false negatives
- Better overall match quality

### 3. Transparency

- Users see how each algorithm scored the venue
- Understand why a venue is recommended
- Can identify venues with strong consensus

### 4. Flexibility

- Adapts to different event types
- Handles various user priorities
- Works well with limited or extensive data

## Algorithm Breakdown Display

Each recommended venue shows:

```
🏆 #1 - Emerald Garden
📊 Ensemble Score: 95.45% Match

Algorithm Breakdown:
  🔵 MCDM (35% weight):        87%
  🟣 KNN (35% weight):         100%
  🟢 Decision Tree (30% weight): 100%
  ⭐ Combined Ensemble:        95.45%
```

## Understanding Agreement Levels

The system analyzes how much algorithms agree:

- **HIGH Agreement** (std dev < 5): All algorithms strongly agree
- **MEDIUM Agreement** (std dev 5-10): Algorithms moderately agree
- **LOW Agreement** (std dev > 10): Algorithms have different opinions

**High agreement venues** are generally safer choices with broad appeal.

## Performance Characteristics

### Computation

- **Speed**: Calculates all 3 algorithms simultaneously
- **Efficiency**: Single database query, multiple scoring passes
- **Scalability**: Handles hundreds of venues efficiently

### Results

- **Quantity**: Top 3 venues (focused recommendations)
- **Quality**: High-confidence matches
- **Diversity**: May show venues appealing to different priorities

## Use Cases

### Perfect For:

- ✅ Users who want the best overall recommendations
- ✅ Events with multiple important criteria
- ✅ Users who value transparent AI decisions
- ✅ Anyone wanting data-backed choices

### Example Scenarios:

**Scenario 1: Tight Budget**

- Decision Tree filters out expensive venues
- MCDM prioritizes budget-friendly options
- KNN finds similar successful budget events
- **Result**: Affordable venues that have worked before

**Scenario 2: Large Event**

- Decision Tree ensures minimum capacity
- MCDM balances capacity with other factors
- KNN learns from successful large events
- **Result**: Spacious venues with proven track record

**Scenario 3: Specific Requirements**

- Decision Tree applies hard constraints
- MCDM evaluates all criteria holistically
- KNN finds similar successful events
- **Result**: Venues meeting all requirements

## Technical Implementation

### Files Modified:

1. `VenueRecommender.php` - Ensemble scoring logic
2. `ai-recommendation.php` - Simplified API (no mode switching)
3. `ai-planner.php` - Updated UI (removed toggle)
4. `ai-planner.js` - Algorithm breakdown display

### Key Changes:

- Removed single-algorithm mode
- Added `calculateMLScore()` with ensemble logic
- Enhanced venue response with `algorithm_breakdown`
- Top 3 results instead of top 5
- Transparent scoring display

## Testing

Run the ensemble test script:

```bash
/opt/lampp/bin/php test-ensemble.php
```

This will show:

- Ensemble scores for each venue
- Individual algorithm scores
- Algorithm agreement analysis
- Detailed breakdown of recommendations

## Future Enhancements

Potential improvements:

- **Dynamic Weights**: Adjust algorithm weights based on event type
- **User Preferences**: Learn user's algorithm preferences over time
- **Confidence Scores**: Add uncertainty metrics to recommendations
- **More Algorithms**: Incorporate additional ML techniques

---

**The Ensemble AI delivers the best of all three algorithms combined!** 🎯
