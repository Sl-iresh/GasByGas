<?php

require_once '../config/Database.php';

class BaseModel {
    protected $conn;
    protected $table;

    public function __construct($table) {
        $db = new Database();
        $this->conn = $db->connect();
        $this->table = $table;
    }

    // Generic Insert
    public function insert($data) {
        $columns = implode(',', array_keys($data));
        $placeholders = implode(',', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute(array_values($data)) ? $this->conn->lastInsertId() : false;
    }

    // Generic Select
    public function select($columns = '*', $conditions = '') {
        $sql = "SELECT $columns FROM {$this->table}" . ($conditions ? " WHERE $conditions" : '');
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Generic Update
    public function update($data, $conditions) {
        $fields = implode(' = ?, ', array_keys($data)) . ' = ?';
        $sql = "UPDATE {$this->table} SET $fields WHERE $conditions";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute(array_values($data));
    }

    // Generic Delete
    public function delete($conditions) {
        $sql = "DELETE FROM {$this->table} WHERE $conditions";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute();
    }
}
?>
