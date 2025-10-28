<?php

require 'dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
    $last_name = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $password2 = isset($_POST['password2']) ? $_POST['password2'] : '';

    // Basic validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($password2)) {
        die("All fields are required.");
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

    // Hash the password
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $first_name, $last_name, $email, $password_hash);

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