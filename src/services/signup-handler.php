<?php

require 'dbconnect.php';

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
        die("All fields are required.");
    }

    // Validate phone number
    if (!preg_match('/^[0-9]{11}$/', $phone)) {
        die("Please enter a valid 11-digit phone number.");
    }

    // Validate role
    $valid_roles = ['administrator', 'manager', 'organizer', 'supplier'];
    if (!in_array($role, $valid_roles)) {
        die("Invalid role selected.");
    }

    if ($password !== $password2) {
        die("Passwords do not match.");
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        die("Email is already registered.");
    }
    $stmt->close();

    // Check if username already exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        die("Username is already taken.");
    }
    $stmt->close();

    // Hash the password
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (username, password, first_name, last_name, email, phone, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $username, $password_hash, $first_name, $last_name, $email, $phone, $role);

    if ($stmt->execute()) {
        // Registration successful
        header("Location: ../../public/pages/signin.php");
        exit();
    } else {
        die("Error during registration: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();
}
