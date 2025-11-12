<?php

session_start();

// Enable error logging (errors go to PHP error log, not displayed)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once 'dbconnect.php';

// Check if database connection was successful
if (!isset($conn)) {
    $_SESSION['error'] = "Database connection failed. The connection variable is not set.";
    error_log("ERROR: Database connection failed in signin-handler.php - \$conn variable not set");
    header("Location: ../../public/pages/signin.php");
    exit();
}

if ($conn->connect_error) {
    $_SESSION['error'] = "Database connection failed: " . $conn->connect_error;
    error_log("ERROR: Database connection failed: " . $conn->connect_error);
    header("Location: ../../public/pages/signin.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    error_log("DEBUG: Sign in attempt for email: " . $email);

    // Check if email and password are provided
    if (empty($email)) {
        $_SESSION['error'] = "Email field is required.";
        error_log("DEBUG: Email field is empty");
        header("Location: ../../public/pages/signin.php");
        exit();
    }

    if (empty($password)) {
        $_SESSION['error'] = "Password field is required.";
        error_log("DEBUG: Password field is empty");
        header("Location: ../../public/pages/signin.php");
        exit();
    }

    // Prepare the SQL statement
    $sql = "SELECT user_id, password, role, first_name, last_name FROM users WHERE email = ?";
    
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        $_SESSION['error'] = "Database query failed. Please try again.";
        error_log("ERROR: Failed to prepare statement: " . $conn->error);
        header("Location: ../../public/pages/signin.php");
        exit();
    }

    $stmt->bind_param("s", $email);

    if (!$stmt->execute()) {
        $_SESSION['error'] = "Database query execution failed. Please try again.";
        error_log("ERROR: Failed to execute statement: " . $stmt->error);
        $stmt->close();
        $conn->close();
        header("Location: ../../public/pages/signin.php");
        exit();
    }

    $stmt->store_result();
    
    error_log("DEBUG: Number of rows found for email '$email': " . $stmt->num_rows);

    if ($stmt->num_rows > 0) {
        error_log("DEBUG: User found in database");

        if (!$stmt->bind_result($user_id, $password_hash, $role, $first_name, $last_name)) {
            $_SESSION['error'] = "Failed to retrieve user data. Please try again.";
            error_log("ERROR: Failed to bind result: " . $stmt->error);
            $stmt->close();
            $conn->close();
            header("Location: ../../public/pages/signin.php");
            exit();
        }

        if (!$stmt->fetch()) {
            $_SESSION['error'] = "Failed to retrieve user data. Please try again.";
            error_log("ERROR: Failed to fetch result: " . $stmt->error);
            $stmt->close();
            $conn->close();
            header("Location: ../../public/pages/signin.php");
            exit();
        }

        error_log("DEBUG: User ID: $user_id, Role: $role, Name: $first_name $last_name");
        error_log("DEBUG: Password hash exists: " . (!empty($password_hash) ? "YES" : "NO"));

        if (password_verify($password, $password_hash)) {
            error_log("DEBUG: Password verification successful for user: $email");

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

            error_log("DEBUG: Redirecting to $role dashboard");

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
                    error_log("DEBUG: Unknown role: $role, redirecting to home");
                    header("Location: ../../public/pages/home.php");
            }
            exit();
        } else {
            // Invalid credentials
            error_log("DEBUG: Password verification FAILED for email: $email");
            $_SESSION['error'] = "Invalid email or password.";
            $stmt->close();
            $conn->close();
            header("Location: ../../public/pages/signin.php");
            exit();
        }
    } else {
        // No user found with that email
        error_log("DEBUG: No user found with email: $email");
        $_SESSION['error'] = "Invalid email or password.";
        $stmt->close();
        $conn->close();
        header("Location: ../../public/pages/signin.php");
        exit();
    }
} else {
    error_log("DEBUG: Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    $_SESSION['error'] = "Invalid request method.";
    header("Location: ../../public/pages/signin.php");
    exit();
}