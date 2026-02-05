<?php

namespace App\Controllers\Admin;

use App\Models\Category;
use Core\Database;

class CategoryController extends BaseController
{
    public function index()
    {
        $db = Database::getInstance(config('db'));
        // Count posts per category
        $sql = "SELECT c.*, COUNT(p.id) as post_count 
                FROM categories c 
                LEFT JOIN posts p ON c.id = p.category_id 
                GROUP BY c.id 
                ORDER BY c.id DESC";
        $categories = $db->query($sql)->fetchAll();
        
        $this->view('admin/categories/index', ['categories' => $categories]);
    }

    public function create()
    {
        $this->view('admin/categories/create');
    }

    public function store()
    {
        $name = trim($_POST['name']);
        $slug = trim($_POST['slug']);
        $description = trim($_POST['description'] ?? '');

        if (empty($name)) {
            $_SESSION['error'] = '分类名称不能为空';
            header('Location: ' . url('/admin/categories/create'));
            exit;
        }
        
        // Simple slug generation if empty
        if (empty($slug)) {
             $slug = md5(uniqid()); 
        }

        $db = Database::getInstance(config('db'));
        $sql = "INSERT INTO categories (name, slug, description) VALUES (:name, :slug, :desc)";
        
        $db->query($sql, [
            ':name' => $name,
            ':slug' => $slug,
            ':desc' => $description
        ]);
        
        $_SESSION['success'] = "分类 {$name} 创建成功！";
        header('Location: ' . url('/admin/categories'));
    }

    public function edit($id)
    {
        $db = Database::getInstance(config('db'));
        $category = $db->query("SELECT * FROM categories WHERE id = :id", [':id' => $id])->fetch();
        
        if (!$category) {
            $_SESSION['error'] = '分类不存在';
            header('Location: ' . url('/admin/categories'));
            exit;
        }
        
        $this->view('admin/categories/edit', ['category' => $category]);
    }

    public function update($id)
    {
        $name = trim($_POST['name']);
        $slug = trim($_POST['slug']);
        $description = trim($_POST['description'] ?? '');
        
        $db = Database::getInstance(config('db'));
        $sql = "UPDATE categories SET name=:name, slug=:slug, description=:desc WHERE id=:id";
        
        $db->query($sql, [
            ':id' => $id,
            ':name' => $name,
            ':slug' => $slug,
            ':desc' => $description
        ]);
        
        $_SESSION['success'] = '分类更新成功！';
        header('Location: ' . url('/admin/categories'));
    }

    public function delete($id)
    {
        $db = Database::getInstance(config('db'));
        
        // $db->query("UPDATE posts SET category_id = 1 WHERE category_id = :id", [':id' => $id]);
        $db->query("DELETE FROM categories WHERE id = :id", [':id' => $id]);
        
        $_SESSION['success'] = '分类已删除';
        header('Location: ' . url('/admin/categories'));
    }
}
