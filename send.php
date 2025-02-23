<?php
require 'sms_api.php';

// Create an instance of SMSApi
$smsApi = new SMSApi();

// List of phone numbers
$phoneNumbers = ["0781810704"];

// Message to send
$message = "Testing via bulk API";

// Send SMS
$response = $smsApi->sendSMS($phoneNumbers, $message);

// Display response
echo "Response: " . $response;
?>
