<?php

namespace App\Controllers\Admin;

use Core\Database;

class UserController extends BaseController
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
             $where .= " AND (username LIKE :s1 OR email LIKE :s2)";
             $params[':s1'] = "%$search%";
             $params[':s2'] = "%$search%";
        }
        
        $sqlCount = "SELECT COUNT(*) FROM users WHERE $where";
        $total = $db->query($sqlCount, $params)->fetchColumn();
        $pageCount = ceil($total/$limit);
        
        $sql = "SELECT * FROM users WHERE $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
        $users = $db->query($sql, $params)->fetchAll();
        
        $this->view('admin/users/index', [
            'users' => $users,
            'currentPage' => $page,
            'pageCount' => $pageCount,
            'search' => $search
        ]);
    }

    public function create()
    {
        $this->view('admin/users/create');
    }

    public function store()
    {
        $uid = trim($_POST['uid']);
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $role = $_POST['role'];
        
        if(empty($uid) || empty($username) || empty($password)) {
             $_SESSION['error'] = 'UID、用户名和密码不能为空';
             header('Location: ' . url('/admin/users/create'));
             exit;
        }
        
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
             $_SESSION['error'] = '邮箱格式不正确';
             header('Location: ' . url('/admin/users/create'));
             exit;
        }
        
        if(!preg_match('/^[a-zA-Z0-9_\-\x{4e00}-\x{9fa5}]+$/u', $username)) {
             $_SESSION['error'] = '用户名包含非法字符';
             header('Location: ' . url('/admin/users/create'));
             exit;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        $db = Database::getInstance(config('db'));
        $sql = "INSERT INTO users (uid, username, email, password, role, created_at) 
                VALUES (:uid, :u, :e, :p, :r, NOW())";
        
        $db->query($sql, [
            ':uid' => $uid,
            ':u' => $username,
            ':e' => $email,
            ':p' => $hash,
            ':r' => $role
        ]);
        
        $_SESSION['success'] = "用户 {$username} 创建成功！";
        header('Location: ' . url('/admin/users'));
    }

    public function edit($id)
    {
        $db = Database::getInstance(config('db'));
        $user = $db->query("SELECT * FROM users WHERE id = :id", [':id' => $id])->fetch();
        if(!$user) {
            $_SESSION['error'] = '用户不存在';
            header('Location: ' . url('/admin/users'));
            exit;
        }
        $this->view('admin/users/edit', ['user' => $user]);
    }

    public function update($id)
    {
        $email = trim($_POST['email']);
        $role = $_POST['role'];
        $password = $_POST['password'];
        
        $db = Database::getInstance(config('db'));
        
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
             $_SESSION['error'] = '邮箱格式不正确';
             header('Location: ' . url('/admin/users')); // Simplified redirect
             exit;
        }

        if(!empty($password)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $db->query("UPDATE users SET email=:e, role=:r, password=:p WHERE id=:id", [
                ':e'=>$email, ':r'=>$role, ':p'=>$hash, ':id'=>$id
            ]);
        } else {
            $db->query("UPDATE users SET email=:e, role=:r WHERE id=:id", [
                ':e'=>$email, ':r'=>$role, ':id'=>$id
            ]);
        }
        
        $_SESSION['success'] = '用户信息更新成功！';
        header('Location: ' . url('/admin/users'));
    }

    public function delete($id)
    {
        if(isset($_SESSION['user_id']) && $id == $_SESSION['user_id']) {
             $_SESSION['error'] = '不能删除自己';
             header('Location: ' . url('/admin/users'));
             exit;
        }

        $db = Database::getInstance(config('db'));
        $db->query("DELETE FROM users WHERE id=:id", [':id'=>$id]);
        $_SESSION['success'] = '用户已删除';
        header('Location: ' . url('/admin/users'));
    }
}
