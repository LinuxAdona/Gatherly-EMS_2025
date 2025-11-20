# Quick Reference - Ensemble AI

## What Changed?

### Before (Comparison Mode)

- Toggle between single algorithm or compare all 3
- Top 3 from EACH algorithm (9 venues total)
- Choose which algorithm to trust

### Now (Ensemble Mode)

- **Always uses all 3 algorithms combined**
- **Top 3 venues overall** (best ensemble scores)
- **See algorithm breakdown** for each venue

## Ensemble Formula

```
Ensemble Score = (MCDM × 0.35) + (KNN × 0.35) + (Decision Tree × 0.30)
```

## How to Use

1. **Go to AI Event Planner**
2. **Describe your event** (e.g., "Wedding for 150 guests budget ₱50000")
3. **Get top 3 venues** with ensemble scores
4. **Check algorithm breakdown** to understand why each venue was recommended

## Reading the Results

### Venue Card Shows:

```
🏆 #1 - Emerald Garden
📊 Ensemble Score: 95.45% Match

Algorithm Breakdown:
  🔵 MCDM:        87%
  🟣 KNN:         100%
  🟢 Decision Tree: 100%
```

### What This Means:

- **Ensemble Score**: Overall match quality (higher = better)
- **MCDM**: How well it balances all criteria
- **KNN**: How similar to successful past bookings
- **Decision Tree**: Whether it passes strict requirements

## Choosing a Venue

### Strong Choice Indicators:

✅ High ensemble score (>90%)
✅ All algorithms score high (>85%)
✅ Scores are similar across algorithms

### Consider Carefully:

⚠️ Low ensemble score (<70%)
⚠️ Big differences between algorithm scores
⚠️ Only one algorithm scores high

## Example Interpretation

**Venue A: 95% (MCDM: 95%, KNN: 97%, Decision Tree: 93%)**
→ **EXCELLENT** - All algorithms strongly agree

**Venue B: 85% (MCDM: 90%, KNN: 95%, Decision Tree: 65%)**
→ **GOOD BUT...** - Barely passes Decision Tree rules, might be risky

**Venue C: 75% (MCDM: 60%, KNN: 85%, Decision Tree: 80%)**
→ **MIXED** - KNN likes it (similar past events) but MCDM is cautious

## Benefits

- 🎯 **Better Accuracy**: Combines best of all algorithms
- 🛡️ **More Reliable**: No single algorithm weakness
- 📊 **Transparent**: See how each algorithm scored
- ✅ **Focused Results**: Only top 3 best matches

## Quick Test

Try: `php test-ensemble.php` to see it in action!

---

**Trust the Ensemble - It's got your back!** 💪
