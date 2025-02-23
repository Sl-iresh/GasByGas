<?php
session_start();
require_once '../config/Database.php';
require_once '../controllers/SMSApi.php'; // Include the SMSApi class

$db = new Database();
$conn = $db->connect();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'individual') {
    header("Location: index.php");
    exit();
}

$user = $_SESSION['user'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $outlet_id = $_POST['outlet_id'];
    $gas_type = $_POST['gas_type'];
    $pickup_date = $_POST['pickup_date'];
    $qty = (int)$_POST['qty'];

    // Calculate tolerance end date
    $date = new DateTime($pickup_date);
    $date->modify('+13 days');
    $tolerance_end_date = $date->format('Y-m-d');

    // Check stock
    $stmt = $conn->prepare("SELECT stock FROM gas_stock WHERE outlet_id = ?");
    $stmt->execute([$outlet_id]);
    $outlet = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($outlet) {
        $gas_stock = json_decode($outlet['stock'], true);

        if (isset($gas_stock[$gas_type]) && $gas_stock[$gas_type] >= $qty) {
            // Insert request
            $stmt = $conn->prepare("INSERT INTO gas_requests (user_id, outlet_id, gas_type, pickup_date, tolerance_end_date, qty) 
                                    VALUES (?, ?, ?, ?, ?, ?)");






            if ($stmt->execute([$user['user_id'], $outlet_id, $gas_type, $pickup_date, $tolerance_end_date, $qty])) {



                $stmt = $conn->query("SELECT LAST_INSERT_ID()");
                $lastInsertId = $stmt->fetchColumn();
                    // Format order ID with leading zeros
    $order_id = 'ORD' . str_pad($lastInsertId, 5, '0', STR_PAD_LEFT);


                // Update stock
                $gas_stock[$gas_type] -= $qty;
                $updated_stock = json_encode($gas_stock);
                $stmt = $conn->prepare("UPDATE gas_stock SET stock = ? WHERE outlet_id = ?");
                $stmt->execute([$updated_stock, $outlet_id]);

                // Fetch user's phone number
                $stmt = $conn->prepare("SELECT contact_number FROM users WHERE user_id = ?");
                $stmt->execute([$user['user_id']]);
                $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
                $phoneNumber = $user_data['contact_number'];

                // Prepare SMS message
                $message = "Your gas request (Token: $order_id ) for $qty x $gas_type has been submitted and is pending approval. Pickup date: $pickup_date.";

                // Send SMS
                $smsApi = new SMSApi();
                $response = $smsApi->sendSMS([$phoneNumber], $message);

                // Log SMS response (optional)
                error_log("SMS Response: " . $response);

                $success = "Gas request submitted successfully!";
                header("Location: ../views/user/request_gas.php?success=" . urlencode($success));
                exit();
            } else {
                $error = "Error: " . $stmt->errorInfo()[2];
            }
        } else {
            $error = "Insufficient stock for the selected gas type.";
        }
    } else {
        $error = "Outlet not found!";
    }

    // Redirect with error message
    header("Location: ../views/user/request_gas.php?error=" . urlencode($error));
    exit();
}