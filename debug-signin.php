<?php
/**
 * Sign-In Debug Page
 * This page helps diagnose Error 500 issues with the signin process
 */

// Enable all error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Start output buffering to catch any errors
ob_start();

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Sign-In Debugger - Gatherly EMS</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .header h1 {
            color: #667eea;
            margin-bottom: 10px;
        }
        .section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .section h2 {
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        .status {
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .check-item {
            padding: 8px;
            margin: 5px 0;
            border-left: 3px solid #667eea;
            padding-left: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #667eea;
            color: white;
        }
        .code {
            background: #f4f4f4;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
            font-family: monospace;
            margin: 10px 0;
        }
        .test-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-top: 15px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #5568d3;
        }
        .btn-secondary {
            background: #6c757d;
            margin-left: 10px;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        pre {
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
</head>
<body>
    <div class='container'>";

echo "<div class='header'>
    <h1>🔍 Sign-In Debug Tool</h1>
    <p>Comprehensive diagnostics for Error 500 issues</p>
    <small>Generated: " . date('Y-m-d H:i:s') . "</small>
</div>";

// Test 1: PHP Configuration
echo "<div class='section'>
    <h2>1. PHP Configuration</h2>";

$php_checks = [
    'PHP Version' => PHP_VERSION,
    'PHP SAPI' => php_sapi_name(),
    'Error Reporting' => error_reporting(),
    'Display Errors' => ini_get('display_errors'),
    'Log Errors' => ini_get('log_errors'),
    'Error Log File' => ini_get('error_log'),
    'Memory Limit' => ini_get('memory_limit'),
    'Max Execution Time' => ini_get('max_execution_time'),
    'Post Max Size' => ini_get('post_max_size'),
    'Upload Max Filesize' => ini_get('upload_max_filesize'),
];

echo "<table>";
foreach ($php_checks as $key => $value) {
    $display_value = $value ?: 'Not Set';
    echo "<tr><th>$key</th><td>$display_value</td></tr>";
}
echo "</table>";

// Check required extensions
$required_extensions = ['mysqli', 'pdo', 'pdo_mysql', 'session', 'json'];
echo "<h3 style='margin-top: 20px;'>Required PHP Extensions</h3>";
foreach ($required_extensions as $ext) {
    $loaded = extension_loaded($ext);
    $class = $loaded ? 'success' : 'error';
    $status = $loaded ? '✓ Loaded' : '✗ Not Loaded';
    echo "<div class='status $class'>$ext: $status</div>";
}

echo "</div>";

// Test 2: File Permissions
echo "<div class='section'>
    <h2>2. File Permissions & Paths</h2>";

$files_to_check = [
    'Database Config' => '../../config/database.php',
    'Environment Config' => '../../config/env.php',
    'DB Connect' => '../src/services/dbconnect.php',
    'Signin Handler' => '../src/services/signin-handler.php',
    'Signin Page' => '../public/pages/signin.php',
];

foreach ($files_to_check as $name => $path) {
    $abs_path = realpath($path);
    if ($abs_path && file_exists($abs_path)) {
        $readable = is_readable($abs_path) ? '✓' : '✗';
        $writable = is_writable($abs_path) ? '✓' : '✗';
        $perms = substr(sprintf('%o', fileperms($abs_path)), -4);
        echo "<div class='check-item'>
            <strong>$name:</strong><br>
            Path: <code>$abs_path</code><br>
            Permissions: $perms | Readable: $readable | Writable: $writable
        </div>";
    } else {
        echo "<div class='status error'>$name: File not found at $path</div>";
    }
}

echo "</div>";

// Test 3: Database Connection
echo "<div class='section'>
    <h2>3. Database Connection Test</h2>";

try {
    // Check if config file exists
    $config_path = realpath('../../config/database.php');
    if (!$config_path || !file_exists($config_path)) {
        echo "<div class='status error'>❌ Database config file not found!</div>";
        echo "<div class='code'>Expected: config/database.php</div>";
    } else {
        echo "<div class='status success'>✓ Config file found: $config_path</div>";
        
        // Include config
        require_once $config_path;
        
        // Display config (without password)
        echo "<div class='info'>
            <strong>Database Configuration:</strong><br>
            Host: " . (defined('DB_HOST') ? DB_HOST : 'Not defined') . "<br>
            Database: " . (defined('DB_NAME') ? DB_NAME : 'Not defined') . "<br>
            User: " . (defined('DB_USER') ? DB_USER : 'Not defined') . "<br>
            Password: " . (defined('DB_PASS') ? (DB_PASS ? '***set***' : 'empty') : 'Not defined') . "
        </div>";
        
        // Test connection
        if (defined('DB_HOST') && defined('DB_USER') && defined('DB_PASS') && defined('DB_NAME')) {
            $test_conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($test_conn->connect_error) {
                echo "<div class='status error'>
                    ❌ Database Connection Failed!<br>
                    Error: " . $test_conn->connect_error . "
                </div>";
            } else {
                echo "<div class='status success'>✓ Database connection successful!</div>";
                
                // Check if users table exists
                $result = $test_conn->query("SHOW TABLES LIKE 'users'");
                if ($result && $result->num_rows > 0) {
                    echo "<div class='status success'>✓ Users table exists</div>";
                    
                    // Get table structure
                    $columns = $test_conn->query("DESCRIBE users");
                    echo "<h3>Users Table Structure:</h3><table>";
                    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
                    while ($col = $columns->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>{$col['Field']}</td>";
                        echo "<td>{$col['Type']}</td>";
                        echo "<td>{$col['Null']}</td>";
                        echo "<td>{$col['Key']}</td>";
                        echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                    
                    // Count users
                    $count = $test_conn->query("SELECT COUNT(*) as total FROM users");
                    $total = $count->fetch_assoc()['total'];
                    echo "<div class='info'>Total users in database: $total</div>";
                    
                    // Show sample user (without password)
                    if ($total > 0) {
                        $sample = $test_conn->query("SELECT user_id, email, role, first_name, last_name, created_at FROM users LIMIT 1");
                        if ($sample && $sample->num_rows > 0) {
                            $user = $sample->fetch_assoc();
                            echo "<h3>Sample User Record:</h3>";
                            echo "<div class='code'><pre>" . print_r($user, true) . "</pre></div>";
                        }
                    }
                    
                } else {
                    echo "<div class='status error'>❌ Users table does not exist!</div>";
                }
                
                $test_conn->close();
            }
        } else {
            echo "<div class='status error'>❌ Database constants not defined in config file!</div>";
        }
    }
} catch (Exception $e) {
    echo "<div class='status error'>
        ❌ Exception occurred: " . $e->getMessage() . "
    </div>";
}

echo "</div>";

// Test 4: Session Test
echo "<div class='section'>
    <h2>4. Session Configuration</h2>";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$session_info = [
    'Session Status' => session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive',
    'Session ID' => session_id(),
    'Session Name' => session_name(),
    'Session Save Path' => session_save_path(),
    'Session Cookie Lifetime' => ini_get('session.cookie_lifetime'),
    'Session GC Maxlifetime' => ini_get('session.gc_maxlifetime'),
];

echo "<table>";
foreach ($session_info as $key => $value) {
    echo "<tr><th>$key</th><td>$value</td></tr>";
}
echo "</table>";

if (!empty($_SESSION)) {
    echo "<h3>Current Session Data:</h3>";
    echo "<div class='code'><pre>" . print_r($_SESSION, true) . "</pre></div>";
} else {
    echo "<div class='info'>No session data currently set</div>";
}

echo "</div>";

// Test 5: Test Sign-In Form
echo "<div class='section'>
    <h2>5. Test Sign-In Process</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_signin'])) {
    echo "<div class='warning'>
        <strong>Sign-In Test Results:</strong><br><br>";
    
    $test_email = $_POST['test_email'] ?? '';
    $test_password = $_POST['test_password'] ?? '';
    
    echo "Testing with email: <strong>" . htmlspecialchars($test_email) . "</strong><br><br>";
    
    if (empty($test_email) || empty($test_password)) {
        echo "<div class='error'>Email and password are required!</div>";
    } else {
        try {
            require_once realpath('../../config/database.php');
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($conn->connect_error) {
                echo "<div class='error'>Connection failed: " . $conn->connect_error . "</div>";
            } else {
                echo "<div class='success'>✓ Database connected</div>";
                
                $sql = "SELECT user_id, password, role, first_name, last_name FROM users WHERE email = ?";
                $stmt = $conn->prepare($sql);
                
                if (!$stmt) {
                    echo "<div class='error'>Failed to prepare statement: " . $conn->error . "</div>";
                } else {
                    echo "<div class='success'>✓ Statement prepared</div>";
                    
                    $stmt->bind_param("s", $test_email);
                    $stmt->execute();
                    $stmt->store_result();
                    
                    echo "<div class='info'>Query returned: {$stmt->num_rows} row(s)</div>";
                    
                    if ($stmt->num_rows > 0) {
                        $stmt->bind_result($user_id, $password_hash, $role, $first_name, $last_name);
                        $stmt->fetch();
                        
                        echo "<div class='info'>
                            User found:<br>
                            - ID: $user_id<br>
                            - Role: $role<br>
                            - Name: $first_name $last_name<br>
                            - Password hash exists: " . (!empty($password_hash) ? 'Yes' : 'No') . "
                        </div>";
                        
                        if (password_verify($test_password, $password_hash)) {
                            echo "<div class='success'>✓ Password verification successful!</div>";
                            echo "<div class='info'>Would redirect to: /public/pages/$role/{$role}-dashboard.php</div>";
                        } else {
                            echo "<div class='error'>✗ Password verification failed!</div>";
                            echo "<div class='info'>Note: Passwords are case-sensitive</div>";
                        }
                    } else {
                        echo "<div class='error'>No user found with this email</div>";
                        
                        // List available users
                        $all_users = $conn->query("SELECT email, role FROM users");
                        if ($all_users && $all_users->num_rows > 0) {
                            echo "<h4>Available users in database:</h4><ul>";
                            while ($u = $all_users->fetch_assoc()) {
                                echo "<li>" . htmlspecialchars($u['email']) . " ({$u['role']})</li>";
                            }
                            echo "</ul>";
                        }
                    }
                    
                    $stmt->close();
                }
                
                $conn->close();
            }
        } catch (Exception $e) {
            echo "<div class='error'>Exception: " . $e->getMessage() . "</div>";
        }
    }
    
    echo "</div>";
}

echo "<div class='test-form'>
    <h3>Test Authentication</h3>
    <form method='POST'>
        <div class='form-group'>
            <label>Email:</label>
            <input type='email' name='test_email' required placeholder='user@example.com'>
        </div>
        <div class='form-group'>
            <label>Password:</label>
            <input type='password' name='test_password' required placeholder='Enter password'>
        </div>
        <button type='submit' name='test_signin'>Test Sign-In</button>
        <button type='button' class='btn-secondary' onclick='window.location.reload()'>Refresh</button>
    </form>
</div>";

echo "</div>";

// Test 6: Recent Error Logs
echo "<div class='section'>
    <h2>6. Recent PHP Error Logs</h2>";

$error_log_path = '/opt/lampp/logs/error_log';
if (file_exists($error_log_path) && is_readable($error_log_path)) {
    $lines = [];
    $handle = @fopen($error_log_path, 'r');
    if ($handle) {
        // Read last 50 lines
        $buffer = 4096;
        fseek($handle, -1, SEEK_END);
        $count = 0;
        $output = '';
        
        while (ftell($handle) > 0 && $count < 50) {
            $seek = min(ftell($handle), $buffer);
            fseek($handle, -$seek, SEEK_CUR);
            $output = fread($handle, $seek) . $output;
            fseek($handle, -$seek, SEEK_CUR);
            $count += substr_count($output, "\n");
        }
        
        fclose($handle);
        $lines = explode("\n", $output);
        $lines = array_filter(array_slice($lines, -50));
        
        // Filter for signin-related errors
        $signin_errors = array_filter($lines, function($line) {
            return stripos($line, 'signin') !== false || 
                   stripos($line, 'dbconnect') !== false ||
                   stripos($line, 'Fatal error') !== false ||
                   stripos($line, 'mysqli') !== false;
        });
        
        if (!empty($signin_errors)) {
            echo "<h3>Sign-In Related Errors (Last 50 lines):</h3>";
            echo "<div class='code'><pre>" . htmlspecialchars(implode("\n", $signin_errors)) . "</pre></div>";
        } else {
            echo "<div class='info'>No sign-in related errors found in recent logs</div>";
        }
        
        echo "<h3>All Recent Errors (Last 20 lines):</h3>";
        echo "<div class='code'><pre>" . htmlspecialchars(implode("\n", array_slice($lines, -20))) . "</pre></div>";
    }
} else {
    echo "<div class='warning'>Error log file not accessible: $error_log_path</div>";
}

echo "</div>";

// Test 7: Recommendations
echo "<div class='section'>
    <h2>7. Troubleshooting Recommendations</h2>
    <div class='check-item'>
        <strong>Common Error 500 Causes:</strong>
        <ol style='margin-left: 20px; margin-top: 10px;'>
            <li>Database connection failure - Check database.php configuration</li>
            <li>File permission issues - Ensure files are readable</li>
            <li>Missing PHP extensions - Install mysqli, pdo_mysql</li>
            <li>Syntax errors in PHP files - Check error logs above</li>
            <li>Memory limits exceeded - Increase memory_limit in php.ini</li>
            <li>Session configuration issues - Check session save path permissions</li>
            <li>Incorrect file paths - Verify relative paths in require statements</li>
        </ol>
    </div>
    
    <div class='info' style='margin-top: 15px;'>
        <strong>Quick Fixes:</strong>
        <ul style='margin-left: 20px; margin-top: 10px;'>
            <li>Clear browser cache and cookies</li>
            <li>Check Apache error logs: <code>tail -f /opt/lampp/logs/error_log</code></li>
            <li>Verify database credentials in config/database.php</li>
            <li>Ensure all required tables exist in database</li>
            <li>Test with different browser or incognito mode</li>
        </ul>
    </div>
</div>";

echo "<div class='section'>
    <h2>Navigation</h2>
    <button onclick=\"window.location.href='public/pages/signin.php'\">Go to Sign-In Page</button>
    <button class='btn-secondary' onclick='window.location.reload()'>Refresh Debug Page</button>
</div>";

echo "</div></body></html>";

$output = ob_get_clean();
echo $output;
?>
