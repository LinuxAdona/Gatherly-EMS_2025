# AI Venue Recommendation System Setup Guide

This guide will help you set up the Python-based Machine Learning recommendation system for the Gatherly EMS.

## Prerequisites

- **Python 3.7 or higher** installed on your system
- **pip** (Python package installer)
- **XAMPP** running with MySQL/MariaDB

## Installation Steps

### 1. Verify Python Installation

Open a terminal/command prompt and check if Python is installed:

```bash
python --version
```

or

```bash
python3 --version
```

If Python is not installed, download it from [python.org](https://www.python.org/downloads/)

### 2. Install Required Python Packages

Navigate to the `ml` directory:

```bash
cd c:\xampp\htdocs\Gatherly-EMS_2025\ml
```

Install the dependencies:

```bash
pip install -r requirements.txt
```

or if you're using `pip3`:

```bash
pip3 install -r requirements.txt
```

This will install:

- **numpy**: For numerical computations
- **scikit-learn**: For machine learning algorithms
- **mysql-connector-python**: For database connectivity

### 3. Test the Python Script Standalone

Test if the script works correctly:

```bash
python venue_recommender.py "I need a wedding venue for 150 guests with a budget of 100000"
```

You should see JSON output with venue recommendations.

### 4. Configure PHP to Use Python

Edit `src/services/ai-recommendation.php` if needed to specify the correct Python command:

- **Windows**: Usually `python`
- **Linux/Mac**: May need `python3`
- **Custom Installation**: Use full path like `C:\Python39\python.exe`

Example modification in `ai-recommendation.php`:

```php
// Change this line if needed
$command = "python3 " . escapeshellarg($pythonScript) . " " . escapeshellarg($message);
```

### 5. Test Through the Web Interface

1. Start XAMPP (Apache and MySQL)
2. Log in to your organizer account
3. Open the AI Chatbot
4. Try a query like: "I need a venue for a corporate event with 200 people and budget of 150000"

## How It Works

### Architecture

```
User → Organizer Dashboard → ai-recommendation.php → venue_recommender.py → MySQL → Results
```

### ML Algorithm

The system uses **Multi-Criteria Decision Making (MCDM)** with the following weights:

- **Capacity Match**: 30% - How well the venue capacity fits the guest count
- **Budget Match**: 35% - How affordable the venue is within budget
- **Location Score**: 15% - Location preference matching
- **Amenities Match**: 20% - How many required amenities are available

### Natural Language Processing

The system extracts:

- **Event Type**: wedding, corporate, birthday, concert, etc.
- **Guest Count**: Number of attendees
- **Budget**: Available budget in pesos
- **Amenities**: parking, catering, sound, stage, AC, WiFi, etc.

## Troubleshooting

### Error: "Python command not found"

**Solution**: Add Python to your system PATH or use the full path in the PHP script.

### Error: "No module named 'sklearn'"

**Solution**: Run `pip install -r requirements.txt` again.

### Error: "Can't connect to MySQL server"

**Solution**:

1. Make sure XAMPP MySQL is running
2. Check database credentials in `venue_recommender.py` (lines 17-21)
3. Verify the database name is `sad_db`

### Error: "No venues found"

**Solution**:

1. Check if there are venues in the `venues` table
2. Verify venues have `availability_status = 'available'`
3. Try broader search criteria

### Empty or Malformed JSON Response

**Solution**:

1. Test the Python script directly from terminal
2. Check PHP error logs in `C:\xampp\apache\logs\error.log`
3. Enable error reporting in `ai-recommendation.php`:
   ```php
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```

## Database Schema Requirements

The system expects the following tables:

### venues table

```sql
CREATE TABLE venues (
    venue_id INT PRIMARY KEY,
    venue_name VARCHAR(255),
    capacity INT,
    base_price DECIMAL(10,2),
    location VARCHAR(255),
    description TEXT,
    availability_status VARCHAR(50)
);
```

### recommendations table (optional, for logging)

```sql
CREATE TABLE recommendations (
    rec_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    venue_id INT,
    recommendation_date DATETIME,
    score FLOAT
);
```

## Maintenance

### Updating the Algorithm

To modify recommendation weights, edit `venue_recommender.py`:

```python
self.criteria_weights = {
    'capacity': 0.30,  # Modify these values
    'budget': 0.35,
    'location': 0.15,
    'amenities': 0.20
}
```

### Adding New Event Types

Update the `event_types` dictionary in `venue_recommender.py`:

```python
'event_type': {
    'your_event_type': ['keyword1', 'keyword2'],
    # ...
}
```

### Adding New Amenities

Update the `amenities` dictionary in `venue_recommender.py`:

```python
'amenities': {
    'your_amenity': ['keyword1', 'keyword2'],
    # ...
}
```

## Performance Optimization

For large datasets (1000+ venues):

1. **Add Database Indexes**:

   ```sql
   CREATE INDEX idx_capacity ON venues(capacity);
   CREATE INDEX idx_price ON venues(base_price);
   CREATE INDEX idx_status ON venues(availability_status);
   ```

2. **Implement Caching**: Consider caching venue data in Redis or Memcached

3. **Limit Initial Query**: Modify the SQL query to pre-filter venues before ML scoring

## Support

For issues or questions:

1. Check XAMPP logs: `C:\xampp\apache\logs\error.log`
2. Check Python output: Run the script manually to see errors
3. Review the conversation history in this project

## Version History

- **v1.0** (2025): Initial ML-based recommendation system using scikit-learn
- **v0.5** (2025): Previous PHP-based rule system (deprecated)
