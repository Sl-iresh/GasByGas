<?php
// Include the Database class
require_once '../config/Database.php';

// Create a new instance of the Database class
$db = new Database();
$conn = $db->connect();

// Check if the required parameter is sent via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['field']) && isset($_POST['value'])) {
    $field = $_POST['field'];
    $value = $_POST['value'];

    // Define allowed fields for security
    $allowedFields = ['email', 'nic_or_registration_number','contact_number'];

    if (in_array($field, $allowedFields)) {
        try {
            // Prepare the query to check if the field exists
            $query = "SELECT COUNT(*) FROM users WHERE $field = :value";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':value', $value, PDO::PARAM_STR);
            $stmt->execute();

            $count = $stmt->fetchColumn();

            if ($count > 0) {
                echo json_encode(['exists' => true, 'message' => ucfirst($field) . " already exists."]);
            } else {
                echo json_encode(['exists' => false, 'message' => ucfirst($field) . " is available."]);
            }
        } catch (PDOException $e) {
            echo json_encode(['error' => true, 'message' => "Database Error: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(['error' => true, 'message' => "Invalid field specified."]);
    }
} else {
    echo json_encode(['error' => true, 'message' => "Invalid request."]);
}
?>
