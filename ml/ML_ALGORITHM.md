# Gatherly ML Setup Guide

## Python Environment Setup

This project uses Python for AI-powered event planning features. The ML components are located in the `ml/` directory.

### Prerequisites

- Python 3.x
- pip (Python package manager)

### Installation (Linux)

1. **Install pip** (if not already installed):

   ```bash
   # For Arch Linux
   sudo pacman -S python-pip

   # For Debian/Ubuntu
   sudo apt-get install python3-pip
   ```

2. **Create and activate virtual environment**:

   ```bash
   cd /opt/lampp/htdocs/Gatherly-EMS_2025/ml
   python3 -m venv venv
   source venv/bin/activate
   ```

3. **Install required packages**:
   ```bash
   pip install -r requirements.txt
   ```

### Required Python Packages

- `numpy` - Numerical computing
- `scikit-learn` - Machine learning algorithms (Naive Bayes classifier)
- `mysql-connector-python` - Database connectivity
- `scipy` - Scientific computing

## Machine Learning Algorithm

### Venue Recommendation System

The AI planner uses a **hybrid scoring system** that combines:

1. **Rule-Based Scoring (60% weight)**: Multi-factor algorithm considering:

   - **Capacity Match (25 points)**: Optimal when venue is 100-120% of guest count
   - **Budget Optimization (30 points)**: Ideal when venue is 35% of total budget
   - **Value Efficiency (20 points)**: Price per capacity unit relative to optimal
   - **Amenities (15 points)**: Number and quality of venue features
   - **Size Appropriateness (10 points)**: Bonus for properly sized venues

2. **Naive Bayes Classifier (40% weight)**: ML model that:
   - Extracts 6 numerical features from each venue
   - Trains on synthetic labels based on venue suitability criteria
   - Predicts probability of venue being a good match
   - Scales predictions to 0-100 score

### Feature Engineering

The system extracts these features for ML:

- Capacity ratio (venue capacity / required guests)
- Price ratio (venue price / total budget)
- Capacity utilization (how well guests fit in venue)
- Price per guest
- Budget fit score (normalized deviation from ideal)
- Amenity score (normalized count)

### Scalability

The algorithm is designed to scale with more data:

- **More venues**: Better ML training with diverse examples
- **More bookings**: Can incorporate user preferences and feedback
- **Historical data**: Can learn from past successful events
- **Feature expansion**: Easy to add location preferences, ratings, reviews

Future enhancements can include:

- Collaborative filtering based on similar events
- Deep learning for image-based venue matching
- Natural language processing for venue descriptions
- Time-series analysis for seasonal pricing

### Testing the Setup

Test the conversational planner:

```bash
cd /opt/lampp/htdocs/Gatherly-EMS_2025/ml
./venv/bin/python3 conversational_planner.py "Hello, I need help planning an event"
```

Test ML-enhanced scoring:

```bash
./venv/bin/python3 test_ml_scoring.py
```

You should see venues with differentiated scores (e.g., 87.2%, 73.5%, 65.8%) instead of identical matches.

### PHP Integration

The PHP files automatically detect the operating system and use:

- **Linux**: `ml/venv/bin/python3` (virtual environment)
- **Windows**: Hardcoded Python path

The following PHP files interact with the Python ML scripts:

- `src/services/ai-conversation.php` - Conversational event planner
- `src/services/ai-recommendation.php` - Venue recommender

### Troubleshooting

**Error: "AI service is not available"**

- Ensure Python virtual environment is created and packages are installed
- Check that `ml/venv/bin/python3` exists
- Verify database connection in Python scripts (host, user, password, database)

**Error: "Failed to parse AI response"**

- Check Python script output for errors
- Verify all required packages are installed
- Check database connectivity

**Module not found errors**

- Activate virtual environment: `source venv/bin/activate`
- Reinstall packages: `pip install -r requirements.txt`

**All venues have the same score**

- This is fixed in the latest version
- The enhanced algorithm provides differentiated scores
- Check that numpy and scikit-learn are properly installed
