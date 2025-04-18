<?php
namespace Core;

use PDO;
use PDOException;

/**
 * Database sınıfı, veritabanı bağlantılarını yönetir
 */
class Database {
    private static $instances = [];
    private $connection;
    
    /**
     * Singleton pattern - her veritabanı bağlantısı için bir instance
     */
    public static function getInstance($connectionName = 'default') {
        if (!isset(self::$instances[$connectionName])) {
            self::$instances[$connectionName] = new self($connectionName);
        }
        return self::$instances[$connectionName];
    }
    
    /**
     * Database constructor
     */
    private function __construct($connectionName) {
        $this->connect($connectionName);
    }
    
    /**
     * Veritabanına bağlan
     */
    private function connect($connectionName) {
        $dbConfig = Config::get('database.' . $connectionName);
        
        if (!$dbConfig) {
            throw new \Exception("Database configuration '{$connectionName}' not found");
        }
        
        $host = $dbConfig['host'];
        $dbname = $dbConfig['name'];
        $username = $dbConfig['username'];
        $password = $dbConfig['password'];
        $charset = $dbConfig['charset'] ?? 'utf8mb4';
        
        try {
            $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";
            $this->connection = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            // Hata günlüğe kaydedilip, kullanıcı dostu hata gösterilmeli
            error_log("Database connection failed: " . $e->getMessage());
            throw new \Exception("Veritabanı bağlantısı başarısız oldu.");
        }
    }
    
    /**
     * PDO bağlantısını döndür
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Sorgu çalıştır ve hazır ifade döndür
     */
    public function query($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    /**
     * Tek satır getir
     */
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Tüm sonuçları getir
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Tek bir değer getir
     */
    public function fetchValue($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchColumn();
    }
    
    /**
     * Insert işlemi gerçekleştir
     */
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, array_values($data));
        
        return $this->connection->lastInsertId();
    }
    
    /**
     * Update işlemi gerçekleştir
     */
    public function update($table, $data, $where, $whereParams = []) {
        $setClause = implode(' = ?, ', array_keys($data)) . ' = ?';
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        $params = array_merge(array_values($data), $whereParams);
        
        return $this->query($sql, $params)->rowCount();
    }
    
    /**
     * Delete işlemi gerçekleştir
     */
    public function delete($table, $where, $whereParams = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        return $this->query($sql, $whereParams)->rowCount();
    }
    
    /**
     * Transaction başlat
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Transaction commit
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    /**
     * Transaction rollback
     */
    public function rollback() {
        return $this->connection->rollBack();
    }
}
