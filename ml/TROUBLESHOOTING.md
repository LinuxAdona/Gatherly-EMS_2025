# AI Planner Troubleshooting Guide

## Quick Diagnostic Steps

### Step 1: Run the Test Endpoint

Open your browser and navigate to:

```
http://localhost/Gatherly-EMS_2025/src/services/test-ai.php
```

This will show you detailed diagnostic information about your AI planner setup.

### Step 2: Check the Output

Look for these key indicators:

✅ **All Working** - You should see:

- `python_script_exists`: true
- `python_exists`: true
- `python_executable`: true
- `script_readable`: true
- `json_parse_success`: true
- `parsed_result`: Contains valid response

❌ **Common Issues**:

1. **`python_exists: false`**

   - Python virtual environment not found
   - **Fix**: Run setup script again:
     ```bash
     cd /opt/lampp/htdocs/Gatherly-EMS_2025/ml
     python3 -m venv venv
     source venv/bin/activate
     pip install -r requirements.txt
     ```

2. **`python_executable: false`**

   - Python not executable
   - **Fix**:
     ```bash
     chmod +x /opt/lampp/htdocs/Gatherly-EMS_2025/ml/venv/bin/python3
     ```

3. **`json_parse_success: false`**

   - Python script has errors
   - Check `raw_output` for error messages
   - **Common causes**:
     - Missing Python packages
     - Database connection error
     - Syntax error in Python script

4. **`output_empty: true` or `output_null: true`**

   - shell_exec disabled or failed
   - **Fix**: Check if shell_exec is disabled:
     - Look at `disabled_functions` in test output
     - Edit `/opt/lampp/etc/php.ini` and remove shell_exec from disable_functions

5. **Missing packages** (`has_numpy: false`, etc.)
   - **Fix**:
     ```bash
     cd /opt/lampp/htdocs/Gatherly-EMS_2025/ml
     source venv/bin/activate
     pip install -r requirements.txt
     ```

### Step 3: Test Python Script Directly

```bash
cd /opt/lampp/htdocs/Gatherly-EMS_2025/ml
./venv/bin/python3 conversational_planner.py "Hello"
```

**Expected output**: JSON response with event planning question

### Step 4: Check Apache/Web Server Logs

```bash
# Check LAMPP error logs
tail -50 /opt/lampp/logs/error_log

# Check PHP error logs
tail -50 /opt/lampp/logs/php_error_log
```

### Step 5: Verify Database Connection

Make sure the database is running and accessible:

```bash
/opt/lampp/lampp status
```

If MySQL is not running:

```bash
sudo /opt/lampp/lampp startmysql
```

## Common Error Messages

### "AI service is not available"

**Cause**: Python script didn't return any output  
**Solutions**:

1. Check if Python virtual environment exists
2. Verify all packages are installed
3. Check database connection in `ml/conversational_planner.py`
4. Run test endpoint to see exact error

### "Failed to parse AI response"

**Cause**: Python script returned non-JSON output (usually an error)  
**Solutions**:

1. Run test endpoint and check `raw_output`
2. Run Python script directly to see error
3. Check for Python syntax errors or missing imports

### "Unauthorized"

**Cause**: Not logged in or not an organizer  
**Solution**: Make sure you're logged in as an organizer

### "Message is required"

**Cause**: Empty message sent  
**Solution**: This is a frontend issue, check browser console

## Manual Test Script

Create and run this test:

```bash
cd /opt/lampp/htdocs/Gatherly-EMS_2025

# Test 1: Direct Python execution
./ml/venv/bin/python3 ml/conversational_planner.py "Hello"

# Test 2: Via PHP
/opt/lampp/bin/php -r "
\$pythonScript = 'ml/conversational_planner.py';
\$pythonPath = 'ml/venv/bin/python3';
\$message = 'Hello';
\$command = \"\$pythonPath \" . escapeshellarg(\$pythonScript) . \" \" . escapeshellarg(\$message) . \" 2>&1\";
echo shell_exec(\$command);
"
```

Both should return valid JSON with event planning questions.

## Getting Help

If you're still experiencing issues after following these steps:

1. Run the test endpoint and save the output
2. Run the manual test script and save the output
3. Check the error logs
4. Provide all this information when asking for help

## Enhanced Error Logging

The system now includes better error messages. When you encounter an error in the web interface:

1. Open browser Developer Tools (F12)
2. Go to Console tab
3. Look for error messages that show:
   - API Error details
   - Debug information
   - Exact Python output

These will help pinpoint the exact issue.
