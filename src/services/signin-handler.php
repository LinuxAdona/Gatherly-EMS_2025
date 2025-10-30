<?php

session_start();

require_once 'dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    $stmt = $conn->prepare("SELECT user_id, password, role, first_name, last_name FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $password_hash, $role, $first_name, $last_name);
        $stmt->fetch();

        if (password_verify($password, $password_hash)) {
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

            // Role-based redirection to specific dashboards
            switch ($role) {
                case 'administrator':
                    header("Location: ../../public/pages/admin-dashboard.php");
                    break;
                case 'coordinator':
                    header("Location: ../../public/pages/coordinator-dashboard.php");
                    break;
                case 'client':
                    header("Location: ../../public/pages/client-dashboard.php");
                    break;
                case 'venue_manager':
                    header("Location: ../../public/pages/venue-manager-dashboard.php");
                    break;
                default:
                    header("Location: ../../public/pages/home.php");
            }
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
