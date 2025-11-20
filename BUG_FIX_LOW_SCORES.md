# Bug Fix: Low Scores in Conversational Mode

## Problem

When using the AI Event Planner conversational interface, users were receiving venue recommendations with scores around 60% or lower, even though the same query via the direct recommendation API returned 90%+ scores.

## Root Cause

The `ConversationalPlanner.php` class had its own **simple scoring algorithm** (`calculateVenueScore`) that was completely separate from the **ensemble algorithm** in `VenueRecommender.php`.

### Two Different Scoring Systems:

1. **VenueRecommender (Ensemble)**: Combined MCDM + KNN + Decision Tree = 90%+ scores
2. **ConversationalPlanner (Simple)**: Basic point system (max 100 points) = 60% scores

## Solution

Updated `ConversationalPlanner.php` to use the `VenueRecommender` class instead of its own scoring logic.

### Changes Made in `ConversationalPlanner.php`:

**Before:**

```php
public function getVenueRecommendations($requirements)
{
    // Query venues directly
    $venues = $this->db->query(...);

    // Use simple scoring
    $score = $this->calculateVenueScore($venue, $requirements);

    return array_slice($scoredVenues, 0, 5);
}
```

**After:**

```php
public function getVenueRecommendations($requirements)
{
    // Use VenueRecommender class
    require_once __DIR__ . '/VenueRecommender.php';
    $venueRecommender = new VenueRecommender($this->db);

    // Get venues
    $venues = $venueRecommender->getVenueFeatures();

    // Use ensemble scoring
    $ensembleScore = $venueRecommender->calculateMLScore($venue, $requirements);
    $algorithmScores = $venueRecommender->calculateAllAlgorithmScores($venue, $requirements);

    // Include algorithm breakdown
    'algorithm_breakdown' => [
        'mcdm' => round($algorithmScores['mcdm'], 2),
        'knn' => round($algorithmScores['knn'], 2),
        'decision_tree' => round($algorithmScores['decision_tree'], 2),
        'ensemble' => round($ensembleScore, 2)
    ]

    return array_slice($scoredVenues, 0, 3);
}
```

## Test Results

### Before Fix:

- Scores: ~60% or lower
- No algorithm breakdown
- Top 5 venues

### After Fix:

**Query:** "Wedding for 150 guests with budget of ₱50000"

```
1. Emerald Garden - 95.5%
   MCDM: 87% | KNN: 100% | Decision Tree: 100%

2. Aurora Pavilion - 93%
   MCDM: 87% | KNN: 93% | Decision Tree: 100%

3. Crystal Hall - 90.7%
   MCDM: 78% | KNN: 95.5% | Decision Tree: 100%
```

## Impact

### ✅ Fixed:

- Conversational mode now returns same high-quality scores as direct API
- Algorithm breakdown now visible in conversational mode
- Top 3 venues (consistent with ensemble approach)
- Users see transparent scoring

### 📊 Unified Scoring:

Both interfaces now use the same ensemble algorithm:

- `ai-conversation.php` (Conversational) → Uses VenueRecommender
- `ai-recommendation.php` (Direct) → Uses VenueRecommender

## Files Modified

- `/src/services/ai/ConversationalPlanner.php` - Updated `getVenueRecommendations()` method

## Verification

Run test scripts:

```bash
/opt/lampp/bin/php test-ensemble.php
/opt/lampp/bin/php test-complete-conversation.php
```

Both should show 90%+ scores with algorithm breakdown.

---

**The bug is now fixed! Both conversational and direct modes use the same robust ensemble algorithm.** ✅
