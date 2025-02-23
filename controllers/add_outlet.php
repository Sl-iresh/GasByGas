<?php

// Include database connection
require_once '../config/Database.php';

// Redirect if not admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../../index.php");
    exit();
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_outlet'])) {
    // Sanitize input
    $outlet_name = htmlspecialchars($_POST['outlet_name']);
    $district = htmlspecialchars($_POST['district']);
    $manager_id = intval($_POST['manager_id']);

    // Database connection
    $db = new Database();
    $conn = $db->connect();

    // Add outlet
    $query = "INSERT INTO outlets (name, district, manager_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    if ($stmt->execute([$outlet_name, $district, $manager_id])) {
        $_SESSION['success'] = "New outlet added successfully!";
    } else {
       // $_SESSION['error'] = "Error adding outlet: " . $conn->error;
    }

    // Redirect back to manage_outlets.php
    header("Location: ../views/admin/manage_outlets.php");
		
    exit();
}
