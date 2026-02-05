<?php

namespace Core;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    private $pdo;
    private $error;

    private function __construct($config)
    {
        $dsn = "mysql:host={$config['host']};dbname={$config['name']};charset={$config['charset']}";
        $options = [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];

        try {
            $this->pdo = new PDO($dsn, $config['user'], $config['pass'], $options);
            $this->pdo->exec("SET NAMES utf8mb4");
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            error_log("Database Connection Error: " . $this->error);
            throw new \Exception("Database Connection Error: " . $this->error);
        }
    }

    public static function getInstance($config)
    {
        if (self::$instance == null) {
            self::$instance = new Database($config);
        }
        return self::$instance;
    }

    public function getPDO()
    {
        return $this->pdo;
    }
    
    // Simple Query Wrapper
    public function query($sql, $params = [])
    {
        $start = microtime(true);
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            $duration = microtime(true) - $start;
            // Log slow queries (> 1s)
            if ($duration > 1.0) {
                 if (class_exists('Core\Log')) \Core\Log::warning("Slow Query: {$duration}s", ['sql' => $sql, 'params' => $params]);
            }
            
            return $stmt;
        } catch (PDOException $e) {
             if (class_exists('Core\Log')) \Core\Log::error("Query Error: " . $e->getMessage(), ['sql' => $sql]);
             throw new \Exception("Query Error: " . $e->getMessage());
        }
    }
    
    // Transaction Helpers
    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    public function commit()
    {
        return $this->pdo->commit();
    }

    public function rollBack()
    {
        return $this->pdo->rollBack();
    }
}
