<?php

// Include database connection
require_once '../config/Database.php';

$db = new Database();
$conn = $db->connect();

// Check if the request ID and new date are provided via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_id']) && isset($_POST['new_date'])) {
    $request_id = $_POST['request_id'];
    $new_date = $_POST['new_date'];

    // Validate the request ID and new date
    if (empty($request_id) || empty($new_date)) {
        die("Invalid input.");
    }

    // Fetch the request details securely
    $stmt = $conn->prepare("SELECT * FROM business_gas_requests WHERE id = :id");
    $stmt->bindParam(':id', $request_id, PDO::PARAM_INT);
    $stmt->execute();
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        die("Request not found.");
    }

    // Update the request with the new date
    $stmt = $conn->prepare("UPDATE business_gas_requests SET pickup_date    = :new_date WHERE id = :id");
    $stmt->bindParam(':new_date', $new_date, PDO::PARAM_STR);
    $stmt->bindParam(':id', $request_id, PDO::PARAM_INT);
    $stmt->execute();

    // Provide a success response
    echo json_encode(['status' => 'success', 'message' => 'Request rescheduled successfully.']);
    exit();
} else {
    // If invalid request
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
    exit();
}
?>