<?php
class SMSApi {
    private $url = "http://sms.textware.lk:5000/bulk/sms.php";
    private $username = "dsitsolution";  // Replace with your username
    private $password = "ds123it";  // Replace with your password
    private $senderID = "TWTEST";  // Sender ID

    public function sendSMS($phoneNumbers, $message) {
        $records = [];
        foreach ($phoneNumbers as $phoneNumber) {
            $records[] = [
                "src" => $this->senderID,
                "dst" => $phoneNumber,
                "msg" => $message,
                "dr" => "1"
            ];
        }

        $data = [
            "action" => "bulk_put",
            "user" => $this->username,
            "password" => $this->password,
            "ea" => $this->username,
            "campaign" => "bulk",
            "records" => $records
        ];

        $jsonData = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ]);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            return 'Curl error: ' . curl_error($ch);
        }
        curl_close($ch);
        return $response;
    }
}
?>
