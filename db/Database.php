<?php

require_once __DIR__ . '/../config.php';

class Database
{
    private PDO $conn;
    private static ?Database $instance = null;

    private function __construct()
    {
        try {
            $this->conn = new PDO(
                "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function tableExists(string $table): bool
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :table"
        );
        $stmt->execute([
            'db' => DB_NAME,
            'table' => $table
        ]);
        return (bool) $stmt->fetchColumn();
    }

    public function createTable(string $table, array $columns, array $constraints = []): bool
    {
        $definitions = [];
        foreach ($columns as $column) {
            $definitions[] = "{$column['name']} {$column['definition']}";
        }
        $definitions = array_merge($definitions, $constraints);
        $query = "CREATE TABLE IF NOT EXISTS $table (" . implode(', ', $definitions) . ")";
        return $this->conn->exec($query) !== false;
    }

    public function insert(string $table, array $data): bool
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $stmt = $this->conn->prepare("INSERT INTO $table ($columns) VALUES ($placeholders)");
        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        return $stmt->execute();
    }

    public function select(string $table, array $columns = ['*'], array $conditions = []): array
    {
        $query = "SELECT " . implode(', ', $columns) . " FROM $table";
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(' AND ', $conditions);
        }
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete(string $table, array $conditions): bool {
    $query = "DELETE FROM $table";
    if (!empty($conditions)) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }
    $stmt = $this->conn->prepare($query);
    return $stmt->execute();
    }

    public function update(string $table, array $data, array $conditions): bool {
    $set = implode(', ', array_map(fn($col) => "$col = :$col", array_keys($data)));
    $query = "UPDATE $table SET $set";
    if (!empty($conditions)) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }
    $stmt = $this->conn->prepare($query);
    foreach ($data as $key => $val) {
        $stmt->bindValue(":$key", $val);
    }
    return $stmt->execute();
    }

}


