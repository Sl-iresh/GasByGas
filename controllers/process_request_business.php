<?php
require_once '../config/Database.php';
$db = new Database();
$conn = $db->connect();
$user = $_SESSION['user'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	


   $new_user_id = $_POST['new_user_id'];

	$outlet_id = $_POST['outlet_id'];
	$gas_type = $_POST['gas_type'];
	$pickup_date = $_POST['pickup_date'];


$date = new DateTime($pickup_date);
$date->modify('+13 days');
$tolerance_end_date = $date->format('Y-m-d');

	// Check stock
	$stmt = $conn->prepare("SELECT stock FROM gas_stock WHERE outlet_id = ?");
	$stmt->execute([$outlet_id]);
	$outlet = $stmt->fetch(PDO::FETCH_ASSOC);

	if ($outlet) {
		$gas_stock = json_decode($outlet['stock'], true);

		if (isset($gas_stock[$gas_type]) && $gas_stock[$gas_type] > 0) {
			// Insert request
			$stmt = $conn->prepare("INSERT INTO gas_requests (user_id, outlet_id, gas_type, pickup_date,tolerance_end_date) 
                                    VALUES (?, ?, ?, ?, ?)");
			if ($stmt->execute([$new_user_id, $outlet_id, $gas_type, $pickup_date,$tolerance_end_date])) {
				// Update stock
				$gas_stock[$gas_type] -= 1;
				$updated_stock = json_encode($gas_stock);
				$stmt = $conn->prepare("UPDATE gas_stock SET stock = ? WHERE outlet_id = ?");
				$stmt->execute([$updated_stock, $outlet_id]);

				$success = "Gas request submitted successfully!";
				header("Location: ../views/manager/request_gas.php?success=" . urlencode($success));
				exit();
			} else {
				$error = "Error: " . $stmt->errorInfo()[2];
			}
		} else {
			$error = "Gas type is out of stock.";
		}
	} else {
		$error = "Outlet not found!";
	}

	// Redirect with error message
	header("Location: ../views/manager/request_gas.php??error=" . urlencode($error));
	exit();
}
