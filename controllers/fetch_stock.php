<?php
require_once '../config/Database.php';
$db = new Database();
$conn = $db->connect();

if (isset($_GET['outlet_id'])) {
    $outlet_id = $_GET['outlet_id'];

    $query = "SELECT stock FROM gas_stock WHERE outlet_id = :outlet_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':outlet_id', $outlet_id, PDO::PARAM_INT);
    $stmt->execute();

    $stock = $stmt->fetchColumn();
    if ($stock) {
        echo $stock; // Return JSON data
    } else {
        echo json_encode(["LPG" => 0, "Propane" => 0, "Industrial" => 0]); // Default empty stock
    }
}
?>
