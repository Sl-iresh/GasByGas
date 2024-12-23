<?php
require_once 'config.php'; // Adjust the path if needed
class Database { 


    private $host = DB_HOST; // Database host
    private $db_name = DB_NAME; // Database name
    private $username = DB_USER; // Database username
    private $password = DB_PASS; // Database password
    private $conn;

    // Get database connection
    public function connect() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }
        return $this->conn;
    }
}
?>
