<?php
// Simple test endpoint to debug AI planner issues
// Access via: http://localhost/Gatherly-EMS_2025/src/services/test-ai.php

header('Content-Type: application/json');

$pythonScript = __DIR__ . '/../../ml/conversational_planner.py';
$venvPython = __DIR__ . '/../../ml/venv/bin/python3';
$pythonPath = file_exists($venvPython) ? $venvPython : '/usr/bin/python3';

$tests = [];

// Test 1: Check if files exist
$tests['python_script_exists'] = file_exists($pythonScript);
$tests['python_script_path'] = $pythonScript;
$tests['python_exists'] = file_exists($pythonPath);
$tests['python_path'] = $pythonPath;
$tests['python_executable'] = is_executable($pythonPath);
$tests['script_readable'] = is_readable($pythonScript);

// Test 2: Try to execute Python
$message = 'Hello test';
$command = "$pythonPath " . escapeshellarg($pythonScript) . " " . escapeshellarg($message) . " 2>&1";
$tests['command'] = $command;
$tests['current_dir'] = getcwd();
$tests['__DIR__'] = __DIR__;

$output = shell_exec($command);
$tests['raw_output'] = $output;
$tests['output_empty'] = empty($output);
$tests['output_null'] = ($output === null);

// Test 3: Try to parse output
if (!empty($output)) {
    $result = json_decode($output, true);
    $tests['json_parse_success'] = (json_last_error() === JSON_ERROR_NONE);
    $tests['json_error'] = json_last_error_msg();
    $tests['parsed_result'] = $result;
}

// Test 4: Check shell_exec availability
$tests['shell_exec_enabled'] = function_exists('shell_exec');
$tests['disabled_functions'] = ini_get('disable_functions');

// Test 5: Check Python version
$pythonVersion = shell_exec("$pythonPath --version 2>&1");
$tests['python_version'] = trim($pythonVersion);

// Test 6: Check if venv has required packages
$pipList = shell_exec("$pythonPath -m pip list 2>&1");
$tests['has_numpy'] = (strpos($pipList, 'numpy') !== false);
$tests['has_sklearn'] = (strpos($pipList, 'scikit-learn') !== false);
$tests['has_mysql'] = (strpos($pipList, 'mysql-connector') !== false);

echo json_encode($tests, JSON_PRETTY_PRINT);
