<?php

require_once '../config/Database.php';

class Crud {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // Insert
    public function insert($table, $columns, $values) {
        $columns = implode(',', $columns);
        $placeholders = implode(',', array_fill(0, count($values), '?'));

        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $stmt = $this->conn->prepare($sql);

        try {
            $stmt->execute($values);
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            return "Insert Error: " . $e->getMessage();
        }
    }

    // Select
    public function select($table, $columns = '*', $conditions = '') {
        $sql = "SELECT $columns FROM $table";
        if ($conditions) {
            $sql .= " WHERE $conditions";
        }

        $stmt = $this->conn->prepare($sql);

        try {
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return "Select Error: " . $e->getMessage();
        }
    }

    // Update
    public function update($table, $data, $conditions) {
        $fields = '';
        $values = [];

        foreach ($data as $key => $value) {
            $fields .= "$key = ?, ";
            $values[] = $value;
        }

        $fields = rtrim($fields, ', ');
        $sql = "UPDATE $table SET $fields WHERE $conditions";

        $stmt = $this->conn->prepare($sql);

        try {
            $stmt->execute($values);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            return "Update Error: " . $e->getMessage();
        }
    }

    // Delete
    public function delete($table, $conditions) {
        $sql = "DELETE FROM $table WHERE $conditions";
        $stmt = $this->conn->prepare($sql);

        try {
            $stmt->execute();
            return $stmt->rowCount();
        } catch (PDOException $e) {
            return "Delete Error: " . $e->getMessage();
        }
    }


    public function selectOne($query, $params = []) {
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC); // Fetch one row as an associative array
    }

    
}
?>
