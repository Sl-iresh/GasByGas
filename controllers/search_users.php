<?php
// Include database connection
require_once '../config/Database.php';

$db = new Database();
$conn = $db->connect();

$query = "SELECT user_id, name 
          FROM users 
          WHERE role IN ('individual', 'business') 
          AND (name LIKE :query OR nic_or_registration_number LIKE :query) 
          LIMIT 10";

$stmt = $conn->prepare($query);
$stmt->bindValue(':query', '%' . $_GET['q'] . '%');
$stmt->execute();

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($users);
