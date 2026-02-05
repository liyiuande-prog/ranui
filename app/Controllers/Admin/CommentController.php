<?php

namespace App\Controllers\Admin;

use Core\Database;

class CommentController extends BaseController
{
    public function index()
    {
        $db = Database::getInstance(config('db'));
        
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;
        $search = trim($_GET['q'] ?? '');
        
        $where = "1=1";
        $params = [];
        if ($search) {
             $where .= " AND (c.content LIKE :s1 OR u.username LIKE :s2)";
             $params[':s1'] = "%$search%";
             $params[':s2'] = "%$search%";
        }
        
        $sqlCount = "SELECT COUNT(*) FROM comments c LEFT JOIN users u ON c.user_id = u.id WHERE $where";
        $total = $db->query($sqlCount, $params)->fetchColumn();
        $pageCount = ceil($total/$limit);
        
        $sql = "SELECT c.*, p.title as post_title, u.username as author_name, u.avatar 
                FROM comments c 
                LEFT JOIN posts p ON c.post_id = p.id 
                LEFT JOIN users u ON c.user_id = u.id
                WHERE $where
                ORDER BY c.created_at DESC LIMIT $limit OFFSET $offset";
        $comments = $db->query($sql, $params)->fetchAll();
        
        $this->view('admin/comments/index', [
            'comments' => $comments,
            'currentPage' => $page,
            'pageCount' => $pageCount,
            'search' => $search
        ]);
    }

    public function create()
    {
        $db = Database::getInstance(config('db'));
        $posts = $db->query("SELECT id, title FROM posts ORDER BY created_at DESC")->fetchAll();
        $this->view('admin/comments/create', ['posts' => $posts]);
    }

    public function store()
    {
        $post_id = $_POST['post_id'];
        $content = trim($_POST['content']);
        $status = $_POST['status'] ?? 'approved';
        
        if(empty($content)) {
            $_SESSION['error'] = '评论内容不能为空';
            header('Location: ' . url('/admin/comments/create'));
            exit;
        }

        $db = Database::getInstance(config('db'));
        $sql = "INSERT INTO comments (post_id, user_id, content, status, created_at) VALUES (:pid, :uid, :content, :status, NOW())";
        
        $db->query($sql, [
            ':pid' => $post_id,
            ':uid' => $_SESSION['user_id'],
            ':content' => $content,
            ':status' => $status
        ]);
        
        $_SESSION['success'] = '评论添加成功！';
        header('Location: ' . url('/admin/comments'));
    }

    public function edit($id)
    {
        $db = Database::getInstance(config('db'));
        $comment = $db->query("SELECT * FROM comments WHERE id = :id", [':id' => $id])->fetch();
        
        if(!$comment) {
            $_SESSION['error'] = '评论不存在';
            header('Location: ' . url('/admin/comments'));
            exit;
        }
        
        $this->view('admin/comments/edit', ['comment' => $comment]);
    }

    public function update($id)
    {
        $content = trim($_POST['content']);
        $status = $_POST['status'];
        
        $db = Database::getInstance(config('db'));
        $db->query("UPDATE comments SET content = :content, status = :status WHERE id = :id", [
            ':id' => $id,
            ':content' => $content,
            ':status' => $status
        ]);
        
        $_SESSION['success'] = '评论更新成功！';
        header('Location: ' . url('/admin/comments'));
    }

    public function delete($id)
    {
        $db = Database::getInstance(config('db'));
        $db->query("DELETE FROM comments WHERE id = :id", [':id' => $id]);
        $_SESSION['success'] = '评论已删除';
        header('Location: ' . url('/admin/comments'));
    }
}
