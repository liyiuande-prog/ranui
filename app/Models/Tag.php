<?php

namespace App\Models;

use Core\Model;

class Tag extends Model
{
    protected $table = 'tags';

    public function findByName($name)
    {
        return $this->db->query("SELECT * FROM tags WHERE name = ?", [$name])->fetch();
    }
    
    public function findBySlug($slug)
    {
        return $this->db->query("SELECT * FROM tags WHERE slug = ?", [$slug])->fetch();
    }

    public function create($name, $slug)
    {
        $this->insert(['name' => $name, 'slug' => $slug, 'count' => 0]);
        return $this->db->getPDO()->lastInsertId();
    }
    
    public function incrementCount($id)
    {
        $this->db->query("UPDATE tags SET count = count + 1 WHERE id = ?", [$id]);
    }
    
    public function getTagsByPostId($postId)
    {
        $sql = "SELECT t.* FROM tags t 
                JOIN post_tags pt ON t.id = pt.tag_id 
                WHERE pt.post_id = ?";
        return $this->db->query($sql, [$postId])->fetchAll();
    }
    
    public function getHotTags($limit = 10)
    {
        $sql = "SELECT * FROM tags WHERE count > 0 ORDER BY count DESC LIMIT " . (int)$limit;
        return $this->db->query($sql)->fetchAll();
    }

    public function search($q, $limit = 20, $offset = 0)
    {
        $sql = "SELECT * FROM tags WHERE name LIKE :q LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        return $this->db->query($sql, [':q' => "%$q%"])->fetchAll();
    }
}
