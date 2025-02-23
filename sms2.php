
<?php
// API details
$url = "http://sms.textware.lk:5000/bulk/sms.php"; // API endpoint URL
$username = "dsitsolution";  // Replace with your username
$password = "ds123it";  // Replace with your password
$senderID = "TWTEST";  // Sender ID
$phoneNumbers = ["0781810704"];  // List of recipient mobile numbers
$message = "Testing via bulk API";  // Message content


// Prepare records
$records = [];
foreach ($phoneNumbers as $phoneNumber) {
    $records[] = [
        "src" => $senderID,
        "dst" => $phoneNumber,
        "msg" => $message,
        "dr" => "1"
    ];
}

// Prepare data to send
$data = [
    "action" => "bulk_put",
    "user" => $username,
    "password" => $password,
    "ea" => $username,
    "campaign" => "bulk",
    "records" => $records
];

// Encode data as JSON
$jsonData = json_encode($data);

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($jsonData)
]);

// Execute request and capture response
$response = curl_exec($ch);

// Check for errors
if (curl_errno($ch)) {
    echo 'Curl error: ' . curl_error($ch);
} else {
    echo "Response: " . $response;
}

// Close cURL session
curl_close($ch);
?>
