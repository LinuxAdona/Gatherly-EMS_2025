<?php

/**
 * Test script for AI Recommendation API
 * Run this from command line: php test_api.php
 */

echo "Testing AI Recommendation System...\n\n";

// Test 1: Test Python script directly
echo "=== Test 1: Python Script Direct Test ===\n";
$pythonPath = 'C:/Python314/python.exe';
$pythonScript = __DIR__ . '/venue_recommender.py';
$testMessage = "I need a wedding venue for 150 guests with budget of 100000";

$command = "\"$pythonPath\" \"$pythonScript\" " . escapeshellarg($testMessage) . " 2>&1";
echo "Command: $command\n\n";

$output = shell_exec($command);
echo "Output:\n$output\n\n";

$result = json_decode($output, true);
if ($result && isset($result['success']) && $result['success']) {
    echo "✓ Python script test PASSED\n";
    echo "  Found " . count($result['venues']) . " venues\n";
    if (!empty($result['venues'])) {
        echo "  Top venue: " . $result['venues'][0]['name'] . " (Score: " . $result['venues'][0]['score'] . "%)\n";
    }
} else {
    echo "✗ Python script test FAILED\n";
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "  JSON Error: " . json_last_error_msg() . "\n";
    }
}

echo "\n" . str_repeat("-", 50) . "\n\n";

// Test 2: Test different queries
echo "=== Test 2: Multiple Query Tests ===\n";

$testQueries = [
    "corporate event for 200 people",
    "birthday party with 50 guests and 30000 budget",
    "wedding venue with parking and catering",
    "concert venue for 500 people in Makati"
];

foreach ($testQueries as $index => $query) {
    echo ($index + 1) . ". Testing: \"$query\"\n";

    $command = "\"$pythonPath\" \"$pythonScript\" " . escapeshellarg($query) . " 2>&1";
    $output = shell_exec($command);
    $result = json_decode($output, true);

    if ($result && isset($result['success']) && $result['success']) {
        echo "   ✓ Success - Found " . count($result['venues']) . " venues\n";

        // Show parsed data
        if (isset($result['parsed_data'])) {
            $parsed = $result['parsed_data'];
            echo "   Parsed: ";
            if ($parsed['event_type']) echo "Type={$parsed['event_type']} ";
            if ($parsed['guests']) echo "Guests={$parsed['guests']} ";
            if ($parsed['budget']) echo "Budget=₱{$parsed['budget']} ";
            if (!empty($parsed['amenities'])) echo "Amenities=" . implode(',', $parsed['amenities']);
            echo "\n";
        }
    } else {
        echo "   ✗ Failed\n";
    }
    echo "\n";
}

echo str_repeat("-", 50) . "\n\n";

// Test 3: Check database connection
echo "=== Test 3: Database Connection Test ===\n";
$dbTestScript = "
import mysql.connector
import sys

try:
    conn = mysql.connector.connect(
        host='localhost',
        user='root',
        password='',
        database='sad_db'
    )
    cursor = conn.cursor()
    cursor.execute('SELECT COUNT(*) FROM venues WHERE availability_status = \"available\"')
    count = cursor.fetchone()[0]
    print(f'✓ Database connected successfully')
    print(f'  Available venues: {count}')
    cursor.close()
    conn.close()
except Exception as e:
    print(f'✗ Database connection failed: {str(e)}')
    sys.exit(1)
";

$tempFile = __DIR__ . '/test_db.py';
file_put_contents($tempFile, $dbTestScript);

$dbOutput = shell_exec("\"$pythonPath\" \"$tempFile\" 2>&1");
echo $dbOutput . "\n";

unlink($tempFile);

echo "\n" . str_repeat("=", 50) . "\n";
echo "Testing Complete!\n";
echo str_repeat("=", 50) . "\n";
