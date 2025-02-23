<?php
require_once '../models/Crud.php'; // Adjust this path if necessary


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? htmlspecialchars(trim($_POST['email'])) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($email) || empty($password)) {
        $_SESSION['login_error'] = "Please fill in both email and password.";
        header('Location: ../public/login.php');
        exit;
    }

    $crud = new Crud();

    // Check if user exists in the database
    $query = "SELECT * FROM users WHERE email = ?";
    $user = $crud->selectOne($query, [$email]);

    if ($user) {
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user'] = $user;
            // Redirect based on user type
            if ($user['role'] === 'individual') {
                header('Location: ../views/user/dashboard.php');
            } elseif ($user['role'] === 'business') {
                header('Location: ../views/user/business_dashboard.php');
            } elseif ($user['role'] === 'admin') {
                header('Location: ../views/admin/dashboard.php');
            } elseif ($user['role'] === 'manager') {
                header('Location: ../views/manager/dashboard.php');
            }


            exit;
        } else {
            $_SESSION["login_error"] = "Invalid email or password.";
            header('Location: ../public/login.php');
            exit;
        }
    } else {
        $_SESSION["login_error"] = "User not found.";

        header('Location: ../public/login.php');
        exit;
    }
}
