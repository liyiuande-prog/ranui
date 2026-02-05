<?php

namespace App\Controllers\Home;

use Core\Controller;
use Core\Database;

class UserController extends Controller
{
    public function updateProfile()
    {
        $this->requireLogin();
        
        $token = $_POST['csrf_token'] ?? '';
        if (empty($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
             $this->jsonResponse(['success' => false, 'message' => 'CSRF Token Invalid']);
             return;
        }
        
        // Hook: Check Punishment
        try {
            \Core\Hook::listen('user_can_update_profile', $_SESSION['user_id']);
        } catch (\Exception $e) {
             $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
             return;
        }

        $username = trim($_POST['username'] ?? '');
        $avatar = trim($_POST['avatar'] ?? '');
        
        $updates = [];
        $params = [];
        
        if (!empty($username)) {
            // 1. Format Check
            if(!preg_match('/^[a-zA-Z0-9_\-\x{4e00}-\x{9fa5}]+$/u', $username)) {
                 $this->jsonResponse(['success' => false, 'message' => '用户名包含非法字符']);
                 return;
            }
            
            // 2. Uniqueness Check (if changed)
            if ($username !== $_SESSION['user']['username']) {
                $db = Database::getInstance(config('db'));
                $exist = $db->query("SELECT id FROM users WHERE username = ? AND id != ?", [$username, $_SESSION['user_id']])->fetch();
                if ($exist) {
                     $this->jsonResponse(['success' => false, 'message' => '该用户名已被占用']);
                     return;
                }
            }
            
            $updates[] = "username = ?";
            $params[] = $username;
            $_SESSION['user']['username'] = $username;
        }

        if (isset($_POST['bio'])) {
             $bio = trim($_POST['bio']);
             if (mb_strlen($bio) > 200) $bio = mb_substr($bio, 0, 200);
             
             $updates[] = "bio = ?";
             $params[] = $bio;
             $_SESSION['user']['bio'] = $bio;
        }
        
        if (!empty($avatar)) {
            $updates[] = "avatar = ?";
            $params[] = $avatar;
            $_SESSION['user']['avatar'] = $avatar;
        }
        
        if (empty($updates)) {
             $this->jsonResponse(['success' => false, 'message' => '没有变更内容']);
             return;
        }
        
        $params[] = $_SESSION['user_id'];
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
        
        $db = Database::getInstance(config('db'));
        $db->query($sql, $params);
        
        $this->jsonResponse(['success' => true, 'message' => '资料更新成功']);
    }
    
    public function updatePassword()
    {
        $this->requireLogin();
        
        $user = $_SESSION['user'];
        $db = Database::getInstance(config('db'));
        $dbUser = $db->query("SELECT * FROM users WHERE id = ?", [$user['id']])->fetch();
        
        $passwordSet = isset($dbUser['password_set']) ? (int)$dbUser['password_set'] : 1; 
        
        $oldPassword = $_POST['old_password'] ?? '';
        $token = $_POST['csrf_token'] ?? '';

        if (empty($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
             $this->jsonResponse(['success' => false, 'message' => 'Security Token Mismatch (CSRF)']);
             return;
        }
        $newPassword = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        
        if (strlen($newPassword) < 6) {
             $this->jsonResponse(['success' => false, 'message' => '新密码至少 6 位']);
             return;
        }
        
        if ($newPassword !== $confirm) {
             $this->jsonResponse(['success' => false, 'message' => '两次新密码不一致']);
             return;
        }
        
        if ($passwordSet === 1) {
            if (empty($oldPassword)) {
                 $this->jsonResponse(['success' => false, 'message' => '请输入当前密码']);
                 return;
            }
            if (!password_verify($oldPassword, $dbUser['password'])) {
                 $this->jsonResponse(['success' => false, 'message' => '当前密码错误']);
                 return;
            }
        }
        
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $db->query("UPDATE users SET password = ?, password_set = 1 WHERE id = ?", [$newHash, $user['id']]);
        
        // Notification: Password Changed
        if (class_exists('Plugins\Ran_Notice\Service')) {
             require_once ROOT_PATH . '/plugins/Ran_Notice/Service.php';
             \Plugins\Ran_Notice\Service::send($user['id'], 'password_changed', [
                 'time' => date('Y-m-d H:i:s')
             ]);
        }
        
        $this->jsonResponse(['success' => true, 'message' => '密码修改成功']);
    }

    public function updateEmail()
    {
        $this->requireLogin();
        
        $token = $_POST['csrf_token'] ?? '';
        if (empty($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
             $this->jsonResponse(['success' => false, 'message' => 'CSRF Token Invalid']);
             return;
        }

        $email = trim($_POST['email'] ?? '');
        $code = trim($_POST['code'] ?? '');
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->jsonResponse(['success' => false, 'message' => '请输入有效的邮箱地址']);
        }

        // 1. Verify Code
        $db = Database::getInstance(config('db'));
        $record = $db->query("SELECT * FROM email_codes WHERE email = ? AND code = ? AND expires_at > ?", [$email, $code, time()])->fetch();
        
        if (!$record) {
             $this->jsonResponse(['success' => false, 'message' => '验证码错误或已过期']);
        }

        // 2. Check if Email is taken
        $exist = $db->query("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $_SESSION['user_id']])->fetch();
        if ($exist) {
             $this->jsonResponse(['success' => false, 'message' => '该邮箱已被其他账号使用']);
        }

        // 3. Update
        $db->query("UPDATE users SET email = ? WHERE id = ?", [$email, $_SESSION['user_id']]);
        
        // Consumer Code
        $db->query("DELETE FROM email_codes WHERE email = ?", [$email]);
        
        $_SESSION['user']['email'] = $email;

        // 4. Task Check (Bind Email)
        if (function_exists('is_plugin_active') && is_plugin_active('Ran_Task') && class_exists('Plugins\Ran_Task\Service')) {
            require_once ROOT_PATH . '/plugins/Ran_Task/Service.php';
            \Plugins\Ran_Task\Service::check($_SESSION['user_id'], 'bind_email');
        }

        // 5. Notification
        if (function_exists('is_plugin_active') && is_plugin_active('Ran_Notice') && class_exists('Plugins\Ran_Notice\Service')) {
            require_once ROOT_PATH . '/plugins/Ran_Notice/Service.php';
            \Plugins\Ran_Notice\Service::send($_SESSION['user_id'], 'email_changed', [
                'new_email' => $email
            ]);
        }
        
        $this->jsonResponse(['success' => true, 'message' => '邮箱绑定成功']);
    }
    
    public function my()
    {
        if (!isset($_SESSION['user'])) {
             header("Location: /login");
             exit;
        }

        $db = Database::getInstance(config('db'));
        $user = $db->query("SELECT * FROM users WHERE id = ?", [$_SESSION['user']['id']])->fetch();
        
        if ($user) {
            $_SESSION['user'] = $user;
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $this->view('my', [
            'user' => $user ?: $_SESSION['user'],
            'page_title' => '个人中心',
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    public function profile($id)
    {
        $db = Database::getInstance(config('db'));
        $user = $db->query("SELECT * FROM users WHERE id = ?", [$id])->fetch();
        if (!$user) {
            echo "User not found";
            return;
        }
        
        $postModel = new \App\Models\Post();
        
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $posts = $postModel->getByUserId($user['id'], $limit, $offset);

        if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
            if (empty($posts)) exit;
            $this->view('user', ['profile_user' => $user, 'posts' => $posts, 'ajax' => true]);
            exit;
        }

        $this->view('user', [
            'page_title' => $user['username'] . ' - 个人主页',
            'profile_user' => $user,
            'posts' => $posts
        ]);
    }

    public function search()
    {
        $this->requireLogin();

        $q = trim($_GET['q'] ?? '');
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $db = Database::getInstance(config('db'));
        
        $where = "WHERE 1=1";
        $params = [];
        
        if ($q !== '') {
            $where .= " AND username LIKE ?";
            $params[] = $q . '%';
        }

        $users = $db->query("SELECT id, username, avatar FROM users $where ORDER BY username ASC LIMIT $limit OFFSET $offset", $params)->fetchAll();
        
        $this->jsonResponse(['success' => true, 'data' => $users]);
    }

    private function requireLogin() {
        if (!isset($_SESSION['user'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => '未登录']);
            exit;
        }
    }
    
    public function getData()
    {
        $this->requireLogin();
        $user = $_SESSION['user'];
        $db = Database::getInstance(config('db'));
        
        $type = $_GET['type'] ?? '';
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $data = [];
        $total = 0;
        
        if ($type === 'works') {
            $total = $db->query("SELECT COUNT(*) as c FROM posts WHERE user_id = ?", [$user['id']])->fetch()['c'];
            if ($total > 0) {
                $data = $db->query("SELECT id, title, created_at, view_count, status FROM posts WHERE user_id = ? ORDER BY created_at DESC LIMIT $limit OFFSET $offset", [$user['id']])->fetchAll();
            }
        } elseif ($type === 'comments') {
            $total = $db->query("SELECT COUNT(*) as c FROM comments WHERE user_id = ?", [$user['id']])->fetch()['c'];
            if ($total > 0) {
                $data = $db->query("SELECT c.content, c.created_at, p.id as post_id, p.title as post_title FROM comments c LEFT JOIN posts p ON c.post_id = p.id WHERE c.user_id = ? ORDER BY c.created_at DESC LIMIT $limit OFFSET $offset", [$user['id']])->fetchAll();
            }
        } elseif ($type === 'likes') {
            $total = $db->query("SELECT COUNT(*) as c FROM post_likes WHERE user_id = ?", [$user['id']])->fetch()['c'];
            if ($total > 0) {
                $data = $db->query("SELECT p.id, p.title, p.created_at FROM post_likes l JOIN posts p ON l.post_id = p.id WHERE l.user_id = ? ORDER BY l.created_at DESC LIMIT $limit OFFSET $offset", [$user['id']])->fetchAll();
            }
        } elseif ($type === 'logs') {
             $hasWallet = function_exists('is_plugin_active') && is_plugin_active('Ran_Wallet');
             $hasIntegral = function_exists('is_plugin_active') && is_plugin_active('Ran_Integral');
             
             if (!$hasWallet && !$hasIntegral) {
                 $this->jsonResponse(['success'=>true, 'data'=>[], 'pagination' => ['total_pages' => 0]]);
                 return;
             }
             
             $sqls = [];
             $params = [];
             
             if ($hasWallet) {
                 $sqls[] = "SELECT id, CONVERT(description USING utf8mb4) as description, amount, CONVERT(type USING utf8mb4) as type, created_at, 'balance' as asset_type FROM wallet_logs WHERE user_id = ?";
                 $params[] = $user['id'];
             }
             if ($hasIntegral) {
                 $sqls[] = "SELECT id, CONVERT(description USING utf8mb4) as description, amount, CONVERT(type USING utf8mb4) as type, created_at, 'points' as asset_type FROM integral_logs WHERE user_id = ?";
                 $params[] = $user['id'];
             }
             
             if ($hasWallet) {
                 try {
                    $total += $db->query("SELECT COUNT(*) as c FROM wallet_logs WHERE user_id = ?", [$user['id']])->fetch()['c'];
                 } catch(\Exception $e){}
             }
             if ($hasIntegral) {
                 try{
                    $total += $db->query("SELECT COUNT(*) as c FROM integral_logs WHERE user_id = ?", [$user['id']])->fetch()['c'];
                 } catch(\Exception $e){}
             }
             
             if ($total > 0 && count($sqls) > 0) {
                 $unionSql = implode(" UNION ALL ", $sqls);
                 $querySql = "$unionSql ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
                 try {
                    $data = $db->query($querySql, $params)->fetchAll();
                 } catch(\Exception $e) {}
             }
        }
        
        $this->jsonResponse([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($total / $limit),
                'total_items' => $total,
                'limit' => $limit
            ]
        ]);
    }

    private function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    // --- Account Cancellation Logic ---

    public function requestAccountCancel()
    {
        $this->requireLogin();
        
        $token = $_POST['csrf_token'] ?? '';
        if (empty($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
             echo "Security Token Invalid";
             return;
        }

        $userId = $_SESSION['user_id'];

        try {
            \Core\Hook::listen('user_can_cancel_account', $userId);
        } catch (\Exception $e) {
            echo "<script>alert('无法注销: " . addslashes($e->getMessage()) . "'); history.back();</script>";
            return;
        }

        $db = Database::getInstance(config('db'));
        
        $db->query("CREATE TABLE IF NOT EXISTS `account_cancellations` (
            `user_id` int(11) NOT NULL,
            `status` varchar(20) NOT NULL DEFAULT 'pending',
            `request_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `execute_at` datetime NOT NULL,
            PRIMARY KEY (`user_id`)
        )");

        // 7 Days Regret Period
        $executeAt = date('Y-m-d H:i:s', time() + 86400 * 7);
        
        $db->query("REPLACE INTO account_cancellations (user_id, status, execute_at) VALUES (?, 'pending', ?)", [$userId, $executeAt]);
        $db->query("UPDATE users SET status = 'cancellation_pending' WHERE id = ?", [$userId]);
        
        // --- Add Notification Here ---
        if (function_exists('is_plugin_active') && is_plugin_active('Ran_Notice') && class_exists('Plugins\Ran_Notice\Service')) {
             require_once ROOT_PATH . '/plugins/Ran_Notice/Service.php';
             // If account_cancel_request template exists, fine. If not, it might fail or send default.
             // Usually Service handles graceful failure if template missing.
             \Plugins\Ran_Notice\Service::send($userId, 'account_cancel_request', ['date' => $executeAt]);
        }
        
        if (session_status() == PHP_SESSION_NONE) session_start();
        session_destroy();
        
        header("Location: /login?error=" . urlencode("已提交注销申请。请查收邮件通知。您有7天后悔期，如需恢复请登录并选择‘放弃注销’。"));
        exit;
    }

    public function handleAccountCancel()
    {
        if (empty($_POST['action']) || empty($_POST['csrf_token'])) {
            header("Location: /login");
            exit;
        }

        if (class_exists('Core\Captcha')) {
            $points = $_POST['captcha_points'] ?? '';
            if (empty($points) || !\Core\Captcha::check($points)) {
                 header("Location: /login?error=" . urlencode("验证码错误，请重试"));
                 exit;
            }
        }

        if (session_status() == PHP_SESSION_NONE) session_start();
        
        $userId = $_SESSION['temp_user_id'] ?? null;
        if (!$userId) {
             header("Location: /login?error=" . urlencode("会话已过期，请重新登录"));
             exit;
        }

        $db = Database::getInstance(config('db'));
        $action = $_POST['action'];

        if ($action === 'revoke') {
            $db->query("UPDATE users SET status = 'active' WHERE id = ?", [$userId]);
            $db->query("DELETE FROM account_cancellations WHERE user_id = ?", [$userId]);
            
            $user = $db->query("SELECT * FROM users WHERE id = ?", [$userId])->fetch();
            $_SESSION['user'] = $user;
            $_SESSION['user_id'] = $userId;
            unset($_SESSION['temp_user_id']);
            unset($_SESSION['cancel_account_pending']);
            
            header("Location: /");
            exit;

        } elseif ($action === 'confirm') {
            
            // Check Calm-down Period
            $cancelRequest = $db->query("SELECT * FROM account_cancellations WHERE user_id = ?", [$userId])->fetch();
            
            if (!$cancelRequest) {
                 // Should not happen if status is pending
                 header("Location: /login?error=" . urlencode("注销申请不存在或已撤销"));
                 exit;
            }
            
            if (strtotime($cancelRequest['execute_at']) > time()) {
                 $daysLeft = ceil((strtotime($cancelRequest['execute_at']) - time()) / 86400);
                 header("Location: /login?error=" . urlencode("还在冷静期内，无法立即注销。请等待 {$daysLeft} 天后自动执行，或现在撤销申请。"));
                 exit;
            }

            // --- Send Notification BEFORE Delete ---
            if (function_exists('is_plugin_active') && is_plugin_active('Ran_Notice') && class_exists('Plugins\Ran_Notice\Service')) {
                require_once ROOT_PATH . '/plugins/Ran_Notice/Service.php';
                \Plugins\Ran_Notice\Service::send($userId, 'account_coke_success'); // "Coke" implies removal
            }

            $db->query("DELETE FROM users WHERE id = ?", [$userId]);
            $db->query("DELETE FROM account_cancellations WHERE user_id = ?", [$userId]);
            $db->query("DELETE FROM user_tokens WHERE user_id = ?", [$userId]);
            $db->query("DELETE FROM posts WHERE user_id = ?", [$userId]);
            $db->query("DELETE FROM comments WHERE user_id = ?", [$userId]);
             
            try { $db->query("DELETE FROM user_auths WHERE user_id = ?", [$userId]); } catch(\Exception $e) {}
            try { $db->query("DELETE FROM user_notifications WHERE user_id = ?", [$userId]); } catch(\Exception $e) {}
            try { $db->query("DELETE FROM user_punishments WHERE user_id = ?", [$userId]); } catch(\Exception $e) {}
            try { $db->query("DELETE FROM punishment_defense_logs WHERE user_id = ?", [$userId]); } catch(\Exception $e) {}
            
            session_destroy();
            header("Location: /login?success=" . urlencode("账号已成功注销。江湖再见！"));
            exit;
        }
    }
}
