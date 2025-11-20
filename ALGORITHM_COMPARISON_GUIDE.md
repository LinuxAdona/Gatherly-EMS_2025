# AI Algorithm Comparison Mode - Implementation Guide

## Overview

The AI Event Planner now supports **Algorithm Comparison Mode**, allowing you to see venue recommendations from all 3 machine learning algorithms simultaneously.

## Available Algorithms

### 1. MCDM (Multi-Criteria Decision Making)

- **Type**: Weighted Average Scoring
- **Strengths**:
  - Balanced evaluation across all criteria
  - Transparent scoring with clear weights
  - Great for budget-conscious users
- **Weights**:
  - Capacity: 30%
  - Budget: 35%
  - Location: 15%
  - Amenities: 20%

### 2. KNN (K-Nearest Neighbors)

- **Type**: Machine Learning - Instance-Based
- **Strengths**:
  - Learns from historical successful bookings
  - Adapts to past user preferences
  - Better with more historical data
- **Configuration**: Uses 5 nearest neighbors (k=5)

### 3. Decision Tree

- **Type**: Rule-Based Decision Making
- **Strengths**:
  - Clear decision logic
  - Strict filtering for critical criteria
  - Eliminates poor matches early
- **Features**:
  - Hard constraints for capacity and budget
  - Bonus points for ideal matches

## How to Use

### Option 1: Comparison Mode (NEW)

1. Go to **AI Event Planner** page
2. Toggle **AI Comparison Mode** switch (ON)
3. Type your event requirements in one message, for example:
   - "Wedding for 150 guests with budget of ₱50000"
   - "Corporate event for 100 people"
   - "Birthday party for 80 guests budget ₱30000"
4. Click **Send**
5. You'll receive **top 3 venues from each algorithm** (9 venues total)

### Option 2: Conversational Mode (Original)

1. Keep **AI Comparison Mode** switch OFF
2. Chat with the AI assistant
3. Answer questions about your event step-by-step
4. Receive recommendations using the default algorithm (MCDM)

## What You'll See in Comparison Mode

The results are displayed in 3 color-coded sections:

### 🔵 MCDM Results (Blue)

- Shows venues with the best weighted average scores
- Balanced recommendations considering all factors equally

### 🟣 KNN Results (Purple)

- Shows venues similar to successful past bookings
- Data-driven recommendations based on historical patterns

### 🟢 Decision Tree Results (Green)

- Shows venues that pass strict rule-based filtering
- Conservative recommendations with hard constraints

## Example Output Structure

```
🧠 Multi-Criteria Decision Making
  1. Grand Ballroom (98.5% Match)
  2. Elegant Hall (95.2% Match)
  3. Garden Venue (92.8% Match)

🧠 K-Nearest Neighbors
  1. Elegant Hall (96.3% Match)
  2. Modern Space (94.1% Match)
  3. Grand Ballroom (91.7% Match)

🧠 Decision Tree
  1. Grand Ballroom (95.0% Match)
  2. Elegant Hall (93.0% Match)
  3. Garden Venue (88.5% Match)
```

## Benefits of Comparison Mode

1. **See All Perspectives**: Compare how different algorithms evaluate venues
2. **Make Informed Decisions**: Understand why venues rank differently
3. **Find Consensus**: Venues appearing in multiple algorithms are strong choices
4. **Learn Algorithm Behavior**: Understand each algorithm's priorities

## Which Algorithm is Best?

There's no single "best" algorithm - each has strengths:

- **Choose MCDM** if you want balanced, transparent scoring
- **Choose KNN** if you want data-driven, adaptive recommendations
- **Choose Decision Tree** if you want strict, rule-based filtering

**Use Comparison Mode** when you want to see all options and make your own decision!

## Technical Implementation

### Backend Changes

- Added `getAllAlgorithmRecommendations()` method in `VenueRecommender.php`
- Added `calculateAllAlgorithmScores()` helper method
- Updated `ai-recommendation.php` to support `compare_all` parameter

### Frontend Changes

- Added comparison mode toggle switch in `ai-planner.php`
- Updated `ai-planner.js` to handle algorithm comparison display
- Added color-coded algorithm sections (Blue, Purple, Green)
- Dual API endpoint support (conversational vs. direct recommendation)

## Sample Queries for Testing

```
Wedding for 150 guests budget ₱50000
Corporate event for 100 people with parking
Birthday party for 80 guests budget ₱30000
Concert for 200 people need sound system
```

## Files Modified

1. `/src/services/ai/VenueRecommender.php` - Core recommendation logic
2. `/src/services/ai/ai-recommendation.php` - API endpoint
3. `/public/pages/organizer/ai-planner.php` - UI with toggle
4. `/public/assets/js/ai-planner.js` - Frontend logic

---

**Enjoy exploring the AI recommendations!** 🎉
