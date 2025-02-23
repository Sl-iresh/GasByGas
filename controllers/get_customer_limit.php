<?php

require_once '../config/Database.php';
$db = new Database();
$conn = $db->connect();

if (!isset($_GET['user_id'])) {
    echo json_encode(['error' => 'User ID is required']);
    exit();
}

$user_id = $_GET['user_id'];

// Fetch user's role
$role_query = "SELECT role FROM users WHERE user_id = ?";
$stmt = $conn->prepare($role_query);
$stmt->execute([$user_id]);
$user_role = $stmt->fetchColumn();

// Fetch user's purchase limit
$limit_query = "SELECT order_limit FROM order_limits WHERE user_type = ?";
$stmt = $conn->prepare($limit_query);
$stmt->execute([$user_role]);
$purchase_limit = $stmt->fetchColumn();

// Count pending/scheduled orders for the user
$pending_orders_query = "SELECT COUNT(*) FROM gas_requests 
                         WHERE user_id = ? AND status IN ('pending', 'scheduled')";
$stmt = $conn->prepare($pending_orders_query);
$stmt->execute([$user_id]);
$pending_orders_count = $stmt->fetchColumn();

// Calculate remaining purchase limit
$remaining_limit = $purchase_limit - $pending_orders_count;

echo json_encode(['remaining_limit' => $remaining_limit]);