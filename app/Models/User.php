<?php

namespace App\Models;

use Core\Model;

class User extends Model
{
    protected $table = 'users';

    public function search($query, $limit = 20, $offset = 0)
    {
        $sql = "SELECT id, uid, username, avatar, role FROM users WHERE username LIKE :q OR uid LIKE :q LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        return $this->db->query($sql, [':q' => "%$query%"])->fetchAll();
    }

    public function findByUsername($username)
    {
        $sql = "SELECT * FROM users WHERE username = :username LIMIT 1";
        return $this->db->query($sql, [':username' => $username])->fetch();
    }

    public function findByEmail($email)
    {
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        return $this->db->query($sql, [':email' => $email])->fetch();
    }

    public function create($data)
    {
        $sql = "INSERT INTO users (uid, username, email, password, avatar, role, password_set, created_at) VALUES (:uid, :username, :email, :password, :avatar, :role, :password_set, NOW())";
        
        $params = [
            ':uid' => $data['uid'],
            ':username' => $data['username'],
            ':email' => $data['email'],
            ':password' => $data['password'], // Already Hashed
            ':avatar' => $data['avatar'] ?? '/assets/default-avatar.png',
            ':role' => $data['role'] ?? 'user',
            ':password_set' => $data['password_set'] ?? 1
        ];
        
        $this->db->query($sql, $params);
        return $this->db->getPDO()->lastInsertId();
    }

    public function getLatestUsers($limit = 5)
    {
        $sql = "SELECT id, username, avatar, role, created_at FROM users ORDER BY created_at DESC LIMIT " . (int)$limit;
        return $this->db->query($sql)->fetchAll();
    }
}
