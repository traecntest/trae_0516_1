<?php
namespace App\Core;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        $config = require '/mnt/github/trae_0516_1/config/database.php';
        
        try {
            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
            $this->connection = new PDO($dsn, $config['username'], $config['password']);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function query($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetch($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($data);
        return $this->connection->lastInsertId();
    }

    public function update($table, $data, $where) {
        $setClause = implode(', ', array_map(function($key) { return "$key = :$key"; }, array_keys($data)));
        $whereClause = implode(' AND ', array_map(function($key) { return "$key = :where_$key"; }, array_keys($where)));
        $params = $data;
        foreach ($where as $key => $value) {
            $params["where_$key"] = $value;
        }
        $sql = "UPDATE $table SET $setClause WHERE $whereClause";
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($table, $where) {
        $whereClause = implode(' AND ', array_map(function($key) { return "$key = :$key"; }, array_keys($where)));
        $sql = "DELETE FROM $table WHERE $whereClause";
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute($where);
    }
}
?>