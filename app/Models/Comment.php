<?php

namespace App\Models;

use Core\Model;

class Comment extends Model
{
    protected $table = 'comments';

    public function getLatest($limit = 5)
    {
        // Fetch latest comments with associated post title and user info
        $sql = "SELECT c.*, p.title as post_title, u.username, u.avatar
                FROM comments c
                LEFT JOIN posts p ON c.post_id = p.id
                LEFT JOIN users u ON c.user_id = u.id
                ORDER BY c.created_at DESC
                LIMIT " . (int)$limit;
        
        return $this->db->query($sql)->fetchAll();
    }

    public function getByPostId($post_id)
    {
        // Fetch comments for a post with user info
        $sql = "SELECT c.*, u.username, u.avatar
                FROM comments c
                LEFT JOIN users u ON c.user_id = u.id
                WHERE c.post_id = :post_id
                ORDER BY c.created_at DESC";
        return $this->db->query($sql, [':post_id' => $post_id])->fetchAll();
    }

    public function search($query, $limit = 20, $offset = 0)
    {
        $sql = "SELECT c.*, p.title as post_title, u.username, u.avatar
                FROM comments c
                LEFT JOIN posts p ON c.post_id = p.id
                LEFT JOIN users u ON c.user_id = u.id
                WHERE c.content LIKE :q
                ORDER BY c.created_at DESC
                LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        return $this->db->query($sql, [':q' => "%$query%"])->fetchAll();       
    }
}
