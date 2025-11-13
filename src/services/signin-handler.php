<?php

session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'dbconnect.php';

// Check if database connection was successful
if (!isset($conn)) {
    die("ERROR: Database connection failed. The \$conn variable is not set. Check dbconnect.php file.");
}

if ($conn->connect_error) {
    die("ERROR: Database connection failed: " . $conn->connect_error);
}

echo "<!-- Debug: Database connected successfully -->\n";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    echo "<!-- Debug: Attempting to sign in user with email: " . htmlspecialchars($email) . " -->\n";

    // Check if email and password are provided
    if (empty($email)) {
        $_SESSION['error'] = "DEBUG: Email field is empty!";
        header("Location: ../../public/pages/signin.php");
        exit();
    }

    if (empty($password)) {
        $_SESSION['error'] = "DEBUG: Password field is empty!";
        header("Location: ../../public/pages/signin.php");
        exit();
    }

    // Prepare the SQL statement
    $sql = "SELECT user_id, password, role, first_name, last_name FROM users WHERE email = ?";
    echo "<!-- Debug: SQL Query: $sql -->\n";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("ERROR: Failed to prepare statement: " . $conn->error . "<br>SQL: $sql");
    }

    echo "<!-- Debug: Statement prepared successfully -->\n";

    $stmt->bind_param("s", $email);

    if (!$stmt->execute()) {
        die("ERROR: Failed to execute statement: " . $stmt->error);
    }

    echo "<!-- Debug: Statement executed successfully -->\n";

    $stmt->store_result();

    echo "<!-- Debug: Number of rows found: " . $stmt->num_rows . " -->\n";

    if ($stmt->num_rows > 0) {
        echo "<!-- Debug: User found in database! -->\n";

        if (!$stmt->bind_result($user_id, $password_hash, $role, $first_name, $last_name)) {
            die("ERROR: Failed to bind result: " . $stmt->error);
        }

        if (!$stmt->fetch()) {
            die("ERROR: Failed to fetch result: " . $stmt->error);
        }

        echo "<!-- Debug: User ID: $user_id, Role: $role, Name: $first_name $last_name -->\n";
        echo "<!-- Debug: Password hash from DB exists: " . (!empty($password_hash) ? "YES" : "NO") . " -->\n";

        if (password_verify($password, $password_hash)) {
            echo "<!-- Debug: Password verification successful! -->\n";

            // Successful login - store user info in session
            $_SESSION['user_id'] = $user_id;
            $_SESSION['role'] = $role;
            $_SESSION['first_name'] = $first_name;
            $_SESSION['last_name'] = $last_name;
            $_SESSION['email'] = $email;

            // Clear any previous error
            if (isset($_SESSION['error'])) {
                unset($_SESSION['error']);
            }

            $stmt->close();
            $conn->close();

            echo "<!-- Debug: Redirecting to $role dashboard -->\n";

            // Role-based redirection to specific dashboards
            switch ($role) {
                case 'administrator':
                    header("Location: ../../public/pages/admin/admin-dashboard.php");
                    break;
                case 'manager':
                    header("Location: ../../public/pages/manager/manager-dashboard.php");
                    break;
                case 'organizer':
                    header("Location: ../../public/pages/organizer/organizer-dashboard.php");
                    break;
                case 'supplier':
                    header("Location: ../../public/pages/supplier/supplier-dashboard.php");
                    break;
                default:
                    echo "<!-- Debug: Unknown role: $role, redirecting to home -->\n";
                    header("Location: ../../public/pages/home.php");
            }
            exit();
        } else {
            // Invalid credentials
            echo "<!-- Debug: Password verification FAILED! -->\n";
            $_SESSION['error'] = "DEBUG: Password verification failed. The password you entered does not match the hashed password in the database.";
            $stmt->close();
            $conn->close();
            header("Location: ../../public/pages/signin.php");
            exit();
        }
    } else {
        // No user found with that email
        echo "<!-- Debug: No user found with email: " . htmlspecialchars($email) . " -->\n";
        $_SESSION['error'] = "DEBUG: No user found with email: " . htmlspecialchars($email) . ". Check if the user exists in the 'users' table.";
        $stmt->close();
        $conn->close();
        header("Location: ../../public/pages/signin.php");
        exit();
    }
} else {
    echo "<!-- Debug: Not a POST request. Request method: " . $_SERVER['REQUEST_METHOD'] . " -->\n";
    die("ERROR: Invalid request method. Expected POST, got: " . $_SERVER['REQUEST_METHOD']);
}
