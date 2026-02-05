<?php

namespace App\Controllers\Home;

use Core\Controller;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Category;
use Core\Database;

class PostController extends Controller
{
    public function index($id)
    {
        $postModel = new Post();
        $commentModel = new Comment();
        
        $post = $postModel->getPostById($id);
        
        if (!$post) {
            header("HTTP/1.0 404 Not Found");
            echo "<h1>404 Not Found</h1><p>文章不存在或已删除。</p>";
            return;
        }

        // View Counting logic (Spam Protection)
        $viewCookieName = 'viewed_post_' . $id;
        if (!isset($_COOKIE[$viewCookieName])) {
            // Increment view count
            $postModel->incrementViewCount($id);
            
            // Update local variable to reflect new count immediately
            $post['view_count']++; 

            // Set cookie for 24 hours to prevent refresh spam
            $isSecure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
            setcookie($viewCookieName, '1', time() + 86400, '/', '', $isSecure, true);
        }

        $comments = $commentModel->getByPostId($id);
        
        // Fetch tags for this post
        $tagModel = new \App\Models\Tag();
        $tags = $tagModel->getTagsByPostId($id);
        
        // Fetch related posts (same category)
        $relatedPosts = $postModel->getRelatedPosts($post['category_id'], $id, 5);
        
        // Fetch author's other posts
        $authorPosts = $postModel->getAuthorPosts($post['user_id'], $id, 5);
        
        $data = [
            'app_name' => get_option('site_title', 'RanUI Blog'),
            'post' => $post,
            'comments' => $comments,
            'tags' => $tags,
            'related_posts' => $relatedPosts,
            'author_posts' => $authorPosts
        ];
        
        $this->view('post', $data);
    }

    /**
     * 渲染写作页面
     */
    public function create()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: ' . url('/login'));
            exit;
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        // Hook: Check Punishment
        try {
            \Core\Hook::listen('user_can_post', $_SESSION['user_id']);
        } catch (\Exception $e) {
            echo "<script>alert('" . $e->getMessage() . "');history.back();</script>";
            return;
        }

        $categoryModel = new Category();
        $categories = $categoryModel->getAll();
        
        $circles = [];
        if (is_plugin_active('Ran_Circle')) {
            $db = Database::getInstance(config('db'));
            // Fetch all active circles
            $circles = $db->query("SELECT id, name FROM circles WHERE status = 1 ORDER BY heat DESC")->fetchAll();
        }

        $this->view('write', [
            'app_name' => get_option('site_title'),
            'categories' => $categories,
            'circles' => $circles,
            'page_title' => '撰写文章',
            'circle_id' => (int)($_GET['circle_id'] ?? 0)
        ]);
    }

    /**
     * 保存投稿
     */
    public function store()
    {
        if (!isset($_SESSION['user'])) {
             header('Location: ' . url('/login'));
             exit;
        }

        $token = $_POST['csrf_token'] ?? '';
        if (empty($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
             echo "<script>alert('页面已过期，请刷新重试 (CSRF Token Invalid)');history.back();</script>";
             return;
        }

        // Hook: Check Punishment
        try {
            \Core\Hook::listen('user_can_post', $_SESSION['user_id']);
        } catch (\Exception $e) {
            echo "<script>alert('" . $e->getMessage() . "');history.back();</script>";
            return;
        }

        $title = trim($_POST['title']);
        $content = $_POST['content'];
        $category_id = (int)($_POST['category_id'] ?? 0);
        $description = $_POST['description'] ?? '';
        $slug = $_POST['slug'] ?? '';
        $cover_image = $_POST['cover_image'] ?? '';
        $status = 'published';
        
        // --- Circle Logic ---
        $circle_id = (int)($_POST['circle_id'] ?? 0);
        if ($circle_id > 0) {
            $db = Database::getInstance(config('db'));
            $circle = $db->query("SELECT category_id FROM circles WHERE id = ?", [$circle_id])->fetch();
            if ($circle) {
                // Check membership (except for public lobby ID 1)
                if ($circle_id > 1) {
                    $isMember = $db->query("SELECT id FROM circle_members WHERE circle_id = ? AND user_id = ? AND role != 'banned'", 
                        [$circle_id, $_SESSION['user_id']])->fetch();
                    if (!$isMember) {
                         echo "<script>alert('您还不是该圈子的成员，请先加入圈子再发布内容');history.back();</script>";
                         return;
                    }
                }
                // Auto-fill category from circle
                $category_id = $circle['category_id'];
            } else {
                echo "<script>alert('所选圈子不存在');history.back();</script>";
                return;
            }
        } else {
            echo "<script>alert('请选择一个要发布的圈子');history.back();</script>";
            return;
        }
        
        if (empty($title)) {
             echo "<script>alert('请输入文章标题');history.back();</script>";
             return;
        }

        // Calculate Read Time
        $read_time = $this->calculateReadTime($content);

        $db = Database::getInstance(config('db'));
        $sql = "INSERT INTO posts (user_id, category_id, circle_id, title, slug, description, content, cover_image, read_time, status, created_at) 
                VALUES (:uid, :cid, :circle_id, :title, :slug, :desc, :content, :cover, :read_time, :status, NOW())";
        
        $db->query($sql, [
            ':uid' => $_SESSION['user_id'],
            ':cid' => $category_id,
            ':circle_id' => $circle_id,
            ':title' => $title,
            ':slug' => $slug,
            ':desc' => $description,
            ':content' => $content,
            ':cover' => $cover_image,
            ':read_time' => $read_time,
            ':status' => $status
        ]);
        
        $id = $db->getPDO()->lastInsertId();
        
        // Trigger Hook: post_created
        \Core\Hook::listen('post_created', ['id' => $id, 'title' => $title]);
        
        // Trigger Hook: post_saved (For plugins like Ran_Dmooji to save meta)
        \Core\Hook::listen('post_saved', ['id' => $id, 'data' => $_POST]);

        // Link Uploaded Files (Prevent cleanup)
        $this->linkUploadedFiles($content, $cover_image);

        // --- Process Tags ---
        $tagsInput = $_POST['tags'] ?? '';
        if (!empty($tagsInput)) {
            $tagModel = new \App\Models\Tag();
            // Handle comma (both English and Chinese)
            $tagsInput = str_replace('，', ',', $tagsInput);
            $tags = explode(',', $tagsInput);
            $tags = array_map('trim', $tags);
            $tags = array_unique(array_filter($tags));
            $tags = array_slice($tags, 0, 5); // Max 5 tags

            foreach ($tags as $tagName) {
                if (empty($tagName)) continue;

                // Check if tag exists
                $tag = $tagModel->findByName($tagName);
                if (!$tag) {
                    // Create new tag
                    // Simple slug generation: URL encode or pinyin (if available). 
                    // For now, let's use the name as slug if English, or urlencode if not.
                    // Better to have aSlugify function, but safe handling:
                    $slug = $tagName; 
                    // If slug exists (rare for name not existing), append rand
                    if ($tagModel->findBySlug($slug)) {
                        $slug .= '-' . rand(100, 999);
                    }
                    $tagId = $tagModel->create($tagName, $slug);
                } else {
                    $tagId = $tag['id'];
                }

                // Link Post and Tag
                // Check dupes first? post_tags primary key handles it but might throw error. 
                // IGNORE to be safe
                $db->query("INSERT IGNORE INTO post_tags (post_id, tag_id) VALUES (?, ?)", [$id, $tagId]);
                
                // Increment tag count
                $tagModel->incrementCount($tagId);
            }
        }
        
        // Process Mentions in Article Content
        if (is_plugin_active('Ran_Notice')) {
            $this->processMentions($content, $id, $title, 'post');
        }
        
        // Task Check: Daily Post
        if (function_exists('is_plugin_active') && is_plugin_active('Ran_Task')) {
            $taskService = ROOT_PATH . '/plugins/Ran_Task/Service.php';
            if (file_exists($taskService)) {
                require_once $taskService;
                if (class_exists('Plugins\Ran_Task\Service')) {
                    \Plugins\Ran_Task\Service::check($_SESSION['user_id'], 'daily_post');
                }
            }
        }
        
        header('Location: ' . url('/' . $id . '.html'));
        exit;
    }

    /**
     * 点赞/取消点赞
     */
    public function toggleLike()
    {
        // 1. 验证登录
        if (!isset($_SESSION['user'])) {
             if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                 header('Content-Type: application/json');
                 echo json_encode(['status' => 'error', 'message' => '请先登录', 'code' => 401]);
                 exit;
             }
             header('Location: ' . url('/login'));
             exit;
        }

        $post_id = $_POST['post_id'] ?? 0;
        
        if (empty($post_id)) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => '无效的文章ID']);
            exit;
        }
        
        $db = Database::getInstance(config('db'));
        $user_id = $_SESSION['user_id'];
        
        // 2. 检查是否已点赞
        $check = $db->query("SELECT id FROM post_likes WHERE user_id = ? AND post_id = ?", [$user_id, $post_id])->fetch();
        
        if ($check) {
            // 已点赞 -> 取消点赞
            $db->query("DELETE FROM post_likes WHERE id = ?", [$check['id']]);
            // 减少计数 (防止负数)
            $db->query("UPDATE posts SET like_count = GREATEST(like_count - 1, 0) WHERE id = ?", [$post_id]);
            $action = 'unliked';
        } else {
            // 未点赞 -> 添加点赞
            $db->query("INSERT INTO post_likes (user_id, post_id, created_at) VALUES (?, ?, NOW())", [$user_id, $post_id]);
            $db->query("UPDATE posts SET like_count = like_count + 1 WHERE id = ?", [$post_id]);
            $action = 'liked';

            // 通知作者逻辑
            $post = $db->query("SELECT user_id, title FROM posts WHERE id = ?", [$post_id])->fetch();
            if ($post && $post['user_id'] != $user_id) {
                 if (function_exists('is_plugin_active') && is_plugin_active('Ran_Notice') && file_exists(ROOT_PATH . '/plugins/Ran_Notice/Service.php')) {
                     require_once ROOT_PATH . '/plugins/Ran_Notice/Service.php';
                     if (class_exists('Plugins\Ran_Notice\Service')) {
                         \Plugins\Ran_Notice\Service::send($post['user_id'], 'post_liked', [
                             'username' => $_SESSION['user']['username'] ?? '有人',
                             'post_title' => $post['title'],
                             'link' => url('/' . $post_id . '.html')
                         ]);
                     }
                 }
            }
        }
        
        // 3.获取最新计数
        $newCount = $db->query("SELECT like_count FROM posts WHERE id = ?", [$post_id])->fetchColumn();
        
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success', 
            'action' => $action, 
            'count' => (int)$newCount
        ]);
        exit;
    }

    /**
     * Handle Comment Submission
     */
    public function addComment()
    {
        if (!isset($_SESSION['user'])) {
             header('Location: ' . url('/login'));
             exit;
        }

        $post_id = $_POST['post_id'] ?? 0;
        
        // 1. CSRF Check
        $token = $_POST['csrf_token'] ?? '';
        if (empty($token) || !\Core\Csrf::check($token)) {
             echo "<script>alert('页面已过期，请刷新重试 (CSRF Token Invalid)');history.back();</script>";
             return;
        }
        // Hook: Check Punishment
        try {
            \Core\Hook::listen('user_can_comment', $_SESSION['user_id']);
        } catch (\Exception $e) {
            echo "<script>alert('" . $e->getMessage() . "');history.back();</script>";
             return;
        }

        $content = trim($_POST['content'] ?? '');

        if (empty($post_id) || empty($content)) {
            echo "<script>alert('评论内容不能为空');history.back();</script>";
            return;
        }

        $commentModel = new Comment();
        
        // Use Model->insert if available or direct DB query
        // Based on Model.php seen earlier, it has insert method.
        // Assuming 'comments' table has columns found in Comment Model usage + created_at
        
        $data = [
            'post_id' => $post_id,
            'user_id' => $_SESSION['user_id'],
            'parent_id' => $_POST['parent_id'] ?? 0,
            'content' => $content,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Since Comment extends Model, and Model has insert, we use that.
        // However, Model::insert doesn't support complex SQL functions like NOW() easily in value binding unless handled.
        // But date('Y-m-d H:i:s') is fine for MySQL datetime.
        
        $commentModel->insert($data);
        
        // --- Notification Logic ---
        // Verify Post exists and Author is not self
        $postModel = new Post();
        $post = $postModel->getPostById($post_id);
        
        if (is_plugin_active('Ran_Notice') && file_exists(ROOT_PATH . '/plugins/Ran_Notice/Service.php')) {
            require_once ROOT_PATH . '/plugins/Ran_Notice/Service.php'; 
            
            if (class_exists('Plugins\Ran_Notice\Service')) { 
            
            $myUsername = $_SESSION['user']['username'] ?? '有人';
            $parentId = $_POST['parent_id'] ?? 0;
            $replyContent = mb_substr(strip_tags($content), 0, 50);

            // 1. Notify Post Author (if direct comment or always?)
            // If direct comment -> comment_received
        // Logic to notify
        // 1. Notify Post Author (if direct comment or always?)
            if ($post && $post['user_id'] != $_SESSION['user_id'] && $parentId == 0) {
                 \Plugins\Ran_Notice\Service::send($post['user_id'], 'comment_received', [
                    'username' => $myUsername,
                    'post_title' => $post['title'],
                    'content' => $replyContent . '...',
                    'link' => url('/' . $post_id . '.html')
                ]);
            }
            
            // 2. Notify Parent Comment Author (if reply)
            if ($parentId > 0) {
                 $db = Database::getInstance(config('db'));
                 $parent = $db->query("SELECT user_id FROM comments WHERE id = ?", [$parentId])->fetch();
                 
                 if ($parent && $parent['user_id'] != $_SESSION['user_id']) {
                      \Plugins\Ran_Notice\Service::send($parent['user_id'], 'reply_received', [
                            'username' => $myUsername,
                            'content' => $replyContent . '...',
                            'link' => url('/' . $post_id . '.html')
                      ]);
                 }
            }
        
        } // End of Plugins\Ran_Notice\Service check
        }
        
        // 3. Process Mentions
        if (is_plugin_active('Ran_Notice')) {
            $this->processMentions($content, $post_id, $post['title'], 'comment');
        }
        
        // Task Check: Daily Comment
        $taskService = ROOT_PATH . '/plugins/Ran_Task/Service.php';
        if (file_exists($taskService)) {
            require_once $taskService;
            if (class_exists('Plugins\Ran_Task\Service')) {
                \Plugins\Ran_Task\Service::check($_SESSION['user_id'], 'daily_comment');
            }
        }

        // Hook: Comment Saved (For Ran_Lucky etc.)
        \Core\Hook::listen('comment_saved', [
            'post_id' => $post_id,
            'user_id' => $_SESSION['user_id'],
            'content' => $content
        ]);

        header('Location: ' . url('/' . $post_id . '.html'));
        exit;
    }

    private function processMentions($content, $postId, $postTitle, $context = 'post')
    {
        // Use Regex to find @Username (Stop at space or end)
        preg_match_all('/@([^\s\n\r\t]+)/u', $content, $matches);
        
        if (empty($matches[1])) return;
        
        $usernames = array_unique($matches[1]);
        $db = Database::getInstance(config('db'));
        $myUsername = $_SESSION['user']['username'] ?? '有人';
        
        // Load Notice Service Once
        if (!class_exists('Plugins\Ran_Notice\Service')) {
             if(file_exists(ROOT_PATH . '/plugins/Ran_Notice/Service.php')) {
                 require_once ROOT_PATH . '/plugins/Ran_Notice/Service.php';
             } else {
                 return;
             }
        }

        foreach ($usernames as $username) {
             // Find User
             $user = $db->query("SELECT id FROM users WHERE username = ?", [$username])->fetch();
             if ($user && $user['id'] != $_SESSION['user_id']) {
                 $key = $context === 'post' ? 'post_metion' : 'comment_mention'; // Note: key matches seed (post_metion typo? User supplied: post_metion)
                 \Plugins\Ran_Notice\Service::send($user['id'], $key, [
                     'username' => $myUsername,
                     'post_title' => $postTitle,
                     'content' => ($context === 'comment') ? mb_substr(strip_tags($content), 0, 50) . '...' : '',
                     'link' => url('/' . $postId . '.html')
                 ]);
             }
        }
    }

    private function calculateReadTime($content)
    {
        // 1. Text Reading Time (350 chars/min is more realistic for mixed content)
        $text = strip_tags($content);
        $length = mb_strlen($text, 'UTF-8');
        $textMinutes = $length / 350;
        
        // 2. Image Reading Time (Approx 10 seconds per image)
        preg_match_all('/<img/i', $content, $imgMatches);
        $imgCount = count($imgMatches[0] ?? []);
        $imageMinutes = ($imgCount * 10) / 60;
        
        // 3. Video awareness (If video exists, add base 1 min)
        $videoMinutes = (stripos($content, '<video') !== false || stripos($content, '<iframe') !== false) ? 1 : 0;

        $totalMinutes = ceil($textMinutes + $imageMinutes + $videoMinutes);
        
        if ($totalMinutes < 1) $totalMinutes = 1;
        
        return $totalMinutes . ' 分钟';
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
