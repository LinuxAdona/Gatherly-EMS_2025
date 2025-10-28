<?php

session_start();

require_once 'dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    $stmt = $conn->prepare("SELECT user_id, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $password_hash);
        $stmt->fetch();

        if (password_verify($password, $password_hash)) {
            // Successful login
            $_SESSION['user_id'] = $user_id;
            // Clear any previous error
            if (isset($_SESSION['error'])) {
                unset($_SESSION['error']);
            }
            $stmt->close();
            $conn->close();
            header("Location: ../../public/pages/dashboard.php");
            exit();
        } else {
            // Invalid credentials
            $_SESSION['error'] = "Invalid email or password.";
            $stmt->close();
            $conn->close();
            header("Location: ../../public/pages/signin.php");
            exit();
        }
    } else {
        // No user found with that email
        $_SESSION['error'] = "Invalid email or password.";
        $stmt->close();
        $conn->close();
        header("Location: ../../public/pages/signin.php");
        exit();
    }
}