<?php

namespace App\Controllers\Admin;

use App\Models\Post;
use App\Models\Category;
use Core\Database;

class PostController extends BaseController
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
            $where .= " AND p.title LIKE :search";
            $params[':search'] = "%$search%";
        }
        
        // Count
        $sqlCount = "SELECT COUNT(*) FROM posts p WHERE $where";
        $total = $db->query($sqlCount, $params)->fetchColumn();
        $pageCount = ceil($total / $limit);
        
        $sql = "SELECT p.*, c.name as category_name, u.username as author_name 
                FROM posts p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN users u ON p.user_id = u.id 
                WHERE $where
                ORDER BY p.created_at DESC LIMIT $limit OFFSET $offset";
        $posts = $db->query($sql, $params)->fetchAll();
        
        $this->view('admin/posts/index', [
            'posts' => $posts,
            'currentPage' => $page,
            'pageCount' => $pageCount,
            'search' => $search
        ]);
    }

    public function create()
    {
        $categoryModel = new Category();
        $categories = $categoryModel->getAll();
        $this->view('admin/posts/create', ['categories' => $categories]);
    }

    public function store()
    {
        $title = trim($_POST['title']);
        $content = $_POST['content'];
        $category_id = $_POST['category_id'];
        $description = $_POST['description'] ?? '';
        $slug = $_POST['slug'] ?? '';
        $cover_image = $_POST['cover_image'] ?? '';
        $status = $_POST['status'] ?? 'published';
        
        if ($slug) {
            $slug = preg_replace('/[^a-z0-9\-_]/i', '-', $slug); // Basic slugify
            $slug = trim($slug, '-');
        }
        $is_pinned = isset($_POST['is_pinned']) ? 1 : 0;
        
        if (empty($title)) {
            $_SESSION['error'] = '文章标题不能为空';
            header('Location: ' . url('/admin/posts/create'));
            exit;
        }

        $db = Database::getInstance(config('db'));
        $sql = "INSERT INTO posts (user_id, category_id, title, slug, description, content, cover_image, status, is_pinned, created_at) 
                VALUES (:uid, :cid, :title, :slug, :desc, :content, :cover, :status, :pinned, NOW())";
        
        $db->query($sql, [
            ':uid' => $_SESSION['user_id'],
            ':cid' => $category_id,
            ':title' => $title,
            ':slug' => $slug,
            ':desc' => $description,
            ':content' => $content,
            ':cover' => $cover_image,
            ':status' => $status,
            ':pinned' => $is_pinned
        ]);
        
        $postId = $db->getPDO()->lastInsertId();
        
        // Trigger Hook
        \Core\Hook::listen('post_saved', ['id' => $postId, 'data' => $_POST]);
        
        // Link Uploaded Files (Prevent cleanup)
        $this->linkUploadedFiles($content, $cover_image);
        
        $_SESSION['success'] = '文章发布成功！';
        header('Location: ' . url('/admin/posts'));
    }

    public function edit($id)
    {
        $db = Database::getInstance(config('db'));
        $sql = "SELECT * FROM posts WHERE id = :id";
        $post = $db->query($sql, [':id' => $id])->fetch();
        
        if (!$post) {
            $_SESSION['error'] = '文章不存在';
            header('Location: ' . url('/admin/posts'));
            exit;
        }
        
        $categoryModel = new Category();
        $categories = $categoryModel->getAll();
        
        $this->view('admin/posts/edit', ['post' => $post, 'categories' => $categories]);
    }

     public function update($id)
    {
        $title = trim($_POST['title']);
        $content = $_POST['content'];
        $category_id = $_POST['category_id'];
        $description = $_POST['description'] ?? '';
        $slug = $_POST['slug'] ?? '';
        $cover_image = $_POST['cover_image'] ?? '';
        $status = $_POST['status'] ?? 'published';
        
        if ($slug) {
            $slug = preg_replace('/[^a-z0-9\-_]/i', '-', $slug); // Basic slugify
            $slug = trim($slug, '-');
        }
        $is_pinned = isset($_POST['is_pinned']) ? 1 : 0;
        
        $db = Database::getInstance(config('db'));

        // Fetch Old Status for Notification
        $oldPost = $db->query("SELECT status, user_id FROM posts WHERE id = :id", [':id' => $id])->fetch();

        $sql = "UPDATE posts SET title=:title, content=:content, category_id=:cid, description=:desc, slug=:slug, cover_image=:cover, status=:status, is_pinned=:pinned, updated_at=NOW() WHERE id=:id";
        
        $db->query($sql, [
            ':id' => $id,
            ':title' => $title,
            ':content' => $content,
            ':cid' => $category_id,
            ':desc' => $description,
            ':slug' => $slug,
            ':cover' => $cover_image,
            ':status' => $status,
            ':pinned' => $is_pinned
        ]);

        // Trigger Hook
        \Core\Hook::listen('post_saved', ['id' => $id, 'data' => $_POST]);
        
        // Link Uploaded Files (Prevent cleanup)
        $this->linkUploadedFiles($content, $cover_image);
        
        // Notify
        if ($oldPost && class_exists('Plugins\Ran_Notice\Service')) {
             require_once ROOT_PATH . '/plugins/Ran_Notice/Service.php';
             
             // Approved (Pending -> Published)
             if ($oldPost['status'] != 'published' && $status == 'published') {
                 \Plugins\Ran_Notice\Service::send($oldPost['user_id'], 'post_approved', [
                     'post_title' => $title
                 ]);
             }
             
             // Rejected
             if ($status == 'rejected' && $oldPost['status'] != 'rejected') {
                 \Plugins\Ran_Notice\Service::send($oldPost['user_id'], 'post_rejected', [
                     'post_title' => $title,
                     'reason' => '内容不符合规范或被管理员驳回' // Default reason
                 ]);
             }
        }
        
        $_SESSION['success'] = '文章更新成功！';
        header('Location: ' . url('/admin/posts'));
    }

    public function delete($id)
    {
        $db = Database::getInstance(config('db'));
        $db->query("DELETE FROM posts WHERE id = :id", [':id' => $id]);
        $_SESSION['success'] = '文章已删除';
        header('Location: ' . url('/admin/posts'));
    }

    private function linkUploadedFiles($content, $cover)
    {
        $paths = [];
        
        // Extract from Cover
        if ($cover && strpos($cover, 'p=') !== false) {
             preg_match('/[?&]p=([^&]+)/', $cover, $m);
             if (isset($m[1])) $paths[] = urldecode($m[1]);
        }
        
        // Extract from Content
        preg_match_all('/[?&]p=([^&"\'\s\)]+)/', $content, $matches);
        if (!empty($matches[1])) {
            foreach($matches[1] as $p) {
                $paths[] = urldecode($p);
            }
        }
        
        if (empty($paths)) return;
        
        $paths = array_unique($paths);
        $db = Database::getInstance(config('db'));
        
        // Update is_linked
        $sql = "UPDATE system_uploads SET is_linked = 1 WHERE path = :path";
        $stmt = $db->getPDO()->prepare($sql);
        
        foreach ($paths as $path) {
            $stmt->execute([':path' => $path]);
        }
    }
}
