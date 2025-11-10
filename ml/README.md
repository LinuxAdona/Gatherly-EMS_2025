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
- `scikit-learn` - Machine learning algorithms
- `mysql-connector-python` - Database connectivity
- `scipy` - Scientific computing

### Testing the Setup

Test the conversational planner:

```bash
cd /opt/lampp/htdocs/Gatherly-EMS_2025/ml
./venv/bin/python3 conversational_planner.py "Hello, I need help planning an event"
```

You should see a JSON response with event planning suggestions.

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
