<?php
// Error handling - catch any errors and redirect with message
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set custom error handler to catch all errors
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    error_log("Signup error: $errstr in $errfile on line $errline");
    header("Location: ../../public/pages/signup.php?error=" . urlencode("An error occurred. Please try again."));
    exit();
});

try {
    require 'dbconnect.php';
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    header("Location: ../../public/pages/signup.php?error=" . urlencode("Database connection failed. Please try again later."));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
    $last_name = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $role = isset($_POST['role']) ? trim($_POST['role']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $password2 = isset($_POST['password2']) ? $_POST['password2'] : '';

    // Basic validation
    if (empty($first_name) || empty($last_name) || empty($username) || empty($email) || empty($phone) || empty($role) || empty($password) || empty($password2)) {
        header("Location: ../../public/pages/signup.php?error=" . urlencode("All fields are required."));
        exit();
    }

    // Validate phone number
    if (!preg_match('/^[0-9]{11}$/', $phone)) {
        header("Location: ../../public/pages/signup.php?error=" . urlencode("Please enter a valid 11-digit phone number."));
        exit();
    }

    // Validate role
    $valid_roles = ['administrator', 'manager', 'organizer', 'supplier'];
    if (!in_array($role, $valid_roles)) {
        header("Location: ../../public/pages/signup.php?error=" . urlencode("Invalid role selected."));
        exit();
    }

    if ($password !== $password2) {
        header("Location: ../../public/pages/signup.php?error=" . urlencode("Passwords do not match."));
        exit();
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        header("Location: ../../public/pages/signup.php?error=" . urlencode("Email is already registered."));
        exit();
    }
    $stmt->close();

    // Check if username already exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        header("Location: ../../public/pages/signup.php?error=" . urlencode("Username is already taken."));
        exit();
    }
    $stmt->close();

    // Hash the password
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (username, password, first_name, last_name, email, phone, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $username, $password_hash, $first_name, $last_name, $email, $phone, $role);

    if ($stmt->execute()) {
        // Registration successful
        $stmt->close();
        $conn->close();
        header("Location: ../../public/pages/signup.php?success=" . urlencode("Account created successfully! Please sign in to continue."));
        exit();
    } else {
        $error_message = $stmt->error;
        $stmt->close();
        $conn->close();
        header("Location: ../../public/pages/signup.php?error=" . urlencode("Error during registration: " . $error_message));
        exit();
    }
} else {
    // If not POST request, redirect back to signup
    header("Location: ../../public/pages/signup.php?error=" . urlencode("Invalid request method."));
    exit();
}
