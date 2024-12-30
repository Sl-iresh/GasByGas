<?php
require_once '../models/Crud.php'; // Adjust this path if necessary
session_start();

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
    $query = "SELECT * FROM customer WHERE email = ?";
    $user = $crud->selectOne($query, [$email]);

    if ($user) {
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_type'] = $user['customer_type'];

            // Redirect based on user type
            if ($user['customer_type'] === 'individual') {
                header('Location: ../admin/dashboard.php');
            } elseif ($user['customer_type'] === 'business') {
                header('Location: ../user/dashboard.php');
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
?>
