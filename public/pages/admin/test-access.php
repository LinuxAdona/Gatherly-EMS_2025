<?php
session_start();
require_once '../../../src/services/dbconnect.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Access Test</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        padding: 20px;
        background: #f5f5f5;
    }

    .container {
        max-width: 800px;
        margin: 0 auto;
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    h1 {
        color: #333;
    }

    .status {
        padding: 15px;
        margin: 10px 0;
        border-radius: 5px;
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

    .info {
        background: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }

    pre {
        background: #f4f4f4;
        padding: 10px;
        border-radius: 5px;
        overflow-x: auto;
    }

    a {
        color: #007bff;
        text-decoration: none;
    }

    a:hover {
        text-decoration: underline;
    }
    </style>
</head>

<body>
    <div class="container">
        <h1>🔍 Admin Access Diagnostic</h1>

        <h2>1. Session Status</h2>
        <?php if (isset($_SESSION['user_id'])): ?>
        <div class="status success">
            ✅ You are logged in!
            <pre><?php print_r($_SESSION); ?></pre>
        </div>
        <?php else: ?>
        <div class="status error">
            ❌ You are NOT logged in!
            <p><a href="../signin.php">Click here to sign in</a></p>
        </div>
        <?php endif; ?>

        <h2>2. Admin Role Check</h2>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'administrator'): ?>
        <div class="status success">
            ✅ You have administrator privileges!
        </div>
        <?php else: ?>
        <div class="status error">
            ❌ You do NOT have administrator privileges!
            <?php if (isset($_SESSION['role'])): ?>
            <p>Your role: <strong><?php echo htmlspecialchars($_SESSION['role']); ?></strong></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <h2>3. Database Connection</h2>
        <?php if ($conn && !$conn->connect_error): ?>
        <div class="status success">
            ✅ Database connected successfully!
        </div>
        <?php else: ?>
        <div class="status error">
            ❌ Database connection failed!
            <?php if ($conn): ?>
            <p>Error: <?php echo $conn->connect_error; ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <h2>4. Admin Account Check</h2>
        <?php
        if ($conn) {
            $admin_check = $conn->query("SELECT * FROM users WHERE role = 'administrator' LIMIT 1");
            if ($admin_check && $admin_check->num_rows > 0):
                $admin = $admin_check->fetch_assoc();
        ?>
        <div class="status success">
            ✅ Administrator account exists!
            <p><strong>Email:</strong> <?php echo htmlspecialchars($admin['email']); ?></p>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']); ?>
            </p>
            <div class="status info">
                <strong>Default Admin Credentials:</strong><br>
                Email: admin@example.com<br>
                Password: admin123
            </div>
        </div>
        <?php else: ?>
        <div class="status error">
            ❌ No administrator account found in database!
        </div>
        <?php
            endif;
        }
        ?>

        <h2>5. Page Access Links</h2>
        <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'administrator'): ?>
        <div class="status info">
            <p>✅ You can access these admin pages:</p>
            <ul>
                <li><a href="admin-dashboard.php">Admin Dashboard</a></li>
                <li><a href="manage-users.php">Manage Users</a></li>
                <li><a href="manage-venues.php">Manage Venues</a></li>
                <li><a href="manage-events.php">Manage Events</a></li>
                <li><a href="reports.php">Reports & Analytics</a></li>
            </ul>
        </div>
        <?php else: ?>
        <div class="status error">
            <p>❌ You need to sign in as administrator to access admin pages.</p>
            <p><a href="../signin.php">Go to Sign In Page</a></p>
        </div>
        <?php endif; ?>

        <h2>6. File Permissions</h2>
        <?php
        $files = [
            'admin-dashboard.php',
            'manage-users.php',
            'manage-venues.php',
            'manage-events.php',
            'reports.php'
        ];
        $all_readable = true;
        ?>
        <div class="status info">
            <?php foreach ($files as $file): ?>
            <?php if (file_exists($file) && is_readable($file)): ?>
            ✅ <?php echo $file; ?> - Exists and readable<br>
            <?php else:
                    $all_readable = false;
                ?>
            ❌ <?php echo $file; ?> - Missing or not readable<br>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] !== 'administrator'): ?>
        <div class="status info">
            <h3>⚠️ Note:</h3>
            <p>You are logged in but with role: <strong><?php echo htmlspecialchars($_SESSION['role']); ?></strong></p>
            <p>Admin pages require 'administrator' role. Please:</p>
            <ol>
                <li><a href="../../../src/services/signout-handler.php">Sign out</a></li>
                <li>Sign in with administrator credentials</li>
            </ol>
        </div>
        <?php endif; ?>
    </div>
</body>

</html>
<?php if ($conn) $conn->close(); ?>