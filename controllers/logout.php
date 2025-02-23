<?php
session_start();

// Destroy the session and all session variables
session_unset(); // Remove all session variables
session_destroy(); // Destroy the session

// Redirect to the login page
header("Location: ../public/login.php");
exit;
?>
