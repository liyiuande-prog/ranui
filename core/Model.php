<?php

namespace Core;

class Model
{
    protected $db;
    protected $table;
    protected $config;

    public function __construct($config = [])
    {
        // Allow passing config manually, or fallback to global config if available
        // But dependency injection is cleaner. 
        // For simplicity in this framework, we assume global config access or pass it in.
        // Let's rely on the App passing config or fetching it.
        
        // However, since Models might be instantiated inside Controllers which have config...
        
        if (empty($config)) {
             global $config; // Fallback
        }
        $this->config = $config;
        $this->db = Database::getInstance($config['db']);
    }

    public function findAll()
    {
        $sql = "SELECT * FROM {$this->table}";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function find($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->query($sql, ['id' => $id]);
        return $stmt->fetch();
    }

    public function insert($data)
    {
        $keys = array_keys($data);
        $fields = implode(', ', $keys);
        $placeholders = ':' . implode(', :', $keys);
        
        $sql = "INSERT INTO {$this->table} ({$fields}) VALUES ({$placeholders})";
        $this->db->query($sql, $data);
        
        return $this->db->getPDO()->lastInsertId();
    }
    
    public function update($id, $data)
    {
        $fields = '';
        foreach ($data as $key => $value) {
            $fields .= "{$key} = :{$key}, ";
        }
        $fields = rtrim($fields, ', ');
        
        $data['id'] = $id;
        
        $sql = "UPDATE {$this->table} SET {$fields} WHERE id = :id";
        return $this->db->query($sql, $data);
    }

    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        return $this->db->query($sql, ['id' => $id]);
    }
    
    // Custom query capability
    public function query($sql, $params = [])
    {
        return $this->db->query($sql, $params);
    }

    public function countAll()
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
}
