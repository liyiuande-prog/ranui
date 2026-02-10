<?php

namespace App\Controllers\Home;

use Core\Controller;
use App\Models\User;
use Core\Database;

class AuthController extends Controller
{
    public function login()
    {
        if (isset($_SESSION['user'])) {
            header('Location: ' . url('/'));
            exit;
        }
        
        // Pass error/success inputs if any
        $data = [
            'app_name' => get_option('site_title', 'RanUI Blog'),
            'title' => '登录',
            'username' => $_GET['username'] ?? '',
            'error' => $_GET['error'] ?? '',
            'success' => $_GET['success'] ?? ''
        ];
        $this->view('login', $data);
    }
    
    public function authenticate()
    {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // --- Security Upgrade: RSA Decryption ---
        // If password is long (encrypted base64), try to decrypt
        if (strlen($password) > 100) {
            try {
                $privateKeyPath = ROOT_PATH . '/core/private_key.pem';
                if (file_exists($privateKeyPath)) {
                    $privateKey = openssl_pkey_get_private(file_get_contents($privateKeyPath));
                    if ($privateKey) {
                        $decrypted = '';
                        // JSEncrypt uses PKCS1 padding
                        openssl_private_decrypt(base64_decode($password), $decrypted, $privateKey);
                        if ($decrypted) {
                            $password = $decrypted;
                        }
                    }
                }
            } catch (\Exception $e) {
                // Decryption failed, treat as wrong password
                \Core\Log::error("RSA Decryption Failed: " . $e->getMessage());
            }
        }
        $remember = isset($_POST['remember']);

        // 1. Rate Limiting
        $limitMsg = $this->checkRateLimit();
        if ($limitMsg) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => $limitMsg]);
                return;
            }
            $this->view('login', ['error' => $limitMsg, 'username' => $username, 'title' => '登录']);
            return;
        }

        // 2. CSRF Check
        if (!\Core\Csrf::check($_POST['csrf_token'] ?? '')) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => '安全令牌无效，请刷新页面重试']);
                return;
            }
            $this->view('login', ['error' => '安全令牌无效，请刷新页面重试', 'username' => $username, 'title' => '登录']);
            return;
        }


        // 3. Click Captcha Check
        if (get_option('login_captcha_enable', '1') == '1' && class_exists('Core\Captcha')) {
            $points = $_POST['captcha_points'] ?? '';
            if (empty($points) || !\Core\Captcha::check($points)) {
                if ($this->isAjax()) {
                    $this->json(['success' => false, 'message' => '验证码验证失败，请重新尝试']);
                    return;
                }
                $this->view('login', ['error' => '验证码验证失败，请重新尝试', 'username' => $username, 'title' => '登录']);
                return;
            }
        }

        if (empty($username) || empty($password)) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => '账号和密码不能为空']);
                return;
            }
            $this->view('login', ['error' => '账号和密码不能为空', 'username' => $username, 'title' => '登录']);
            return;
        }

        $userModel = new User();
        // Support login by Username OR Email OR UID
        $db = Database::getInstance(config('db'));
        $candidates = $db->query("SELECT * FROM users WHERE username = ? OR email = ? OR uid = ?", [$username, $username, $username])->fetchAll();
        
        $user = null;
        if ($candidates) {
            foreach ($candidates as $candidate) {
                if (password_verify($password, $candidate['password'])) {
                    $user = $candidate;
                    break;
                }
            }
        }

        if ($user) {
            // Check Account Status for Cancellation
            if (isset($user['status']) && $user['status'] === 'cancellation_pending') {
                $_SESSION['temp_user_id'] = $user['id'];
                $_SESSION['cancel_account_pending'] = true;
                header('Location: ' . url('/login')); // Redirect to same page to show modal
                exit;
            }

             // Check Account Status for Ban
             if (isset($user['status']) && $user['status'] === 'banned') {
                 $this->view('login', ['error' => '该账号已被封禁', 'username' => $username, 'title' => '登录']);
                 return;
             }
             if (isset($user['status']) && $user['status'] === 'deleted') {
                 $this->view('login', ['error' => '该账号已注销', 'username' => $username, 'title' => '登录']);
                 return;
             }

            // Success
            $this->clearFailure();
            session_regenerate_id(true);
            
            $hookResult = \Core\Hook::listen('auth_login_after_verify', $user);
            $isAppActive = function_exists('is_plugin_active') && is_plugin_active('Ran_App');

            if ($isAppActive && isset($hookResult['action']) && $hookResult['action'] === '2fa_required') {
                $_SESSION['2fa_pending_uid'] = $user['id'];
                $_SESSION['2fa_pending_remember'] = $remember; 
                $_SESSION['2fa_pending_type'] = 'home';
                
                if ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') || isset($_POST['ajax'])) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'action' => '2fa_required', 'message' => '需要进行二次验证']);
                    exit;
                }

                header('Location: ' . url('/auth/2fa/verify'));
                exit;
            }

            $_SESSION['user'] = $user;
            $_SESSION['user_id'] = $user['id'];

            // Handle Remember Me (Secure Token)
            if ($remember) {
                $this->createRememberToken($user['id']);
            }
            
            // Hook: Check Login Punishment (Ran_Punish)
            $punishError = \Core\Hook::listen('auth_login_check', ['user_id' => $user['id']]);
            
            if (is_string($punishError)) {
                session_destroy();
                $this->view('login', ['error' => $punishError, 'username' => $username, 'title' => '登录']);
                return;
            }

            // Hook: Login Success
            \Core\Hook::listen('auth_login_success', ['user_id' => $user['id']]);
            
            // --- Security Log & Device Check ---
            $this->logAccess($user['id'], 'login_success', 1, 'Login via Password');
            $this->checkDevice($user['id']);

            // Task Check: Daily Login
            if (function_exists('is_plugin_active') && is_plugin_active('Ran_Task') && class_exists('Plugins\Ran_Task\Service')) {
                require_once ROOT_PATH . '/plugins/Ran_Task/Service.php';
                \Plugins\Ran_Task\Service::check($user['id'], 'daily_login');
            }

            if ($this->isAjax()) {
                $this->json(['success' => true, 'redirect' => url('/')]);
                return;
            }

            header('Location: ' . url('/'));
            exit;
        }

        // Failure
        $attempts = $this->recordFailure();
        $remaining = 5 - $attempts;
        $msg = ($remaining > 0) 
            ? "账号或密码错误。剩余次数: {$remaining}" 
            : "尝试次数过多，请稍后再试。";
            
        // --- Security Log ---
        // Log "0" as user_id for unknown user, but record username attempted
        $this->logAccess(0, 'login_failed', 0, "Username: $username, IP: " . $this->getIp());

        if ($this->isAjax()) {
            $this->json(['success' => false, 'message' => $msg]);
            return;
        }

        $this->view('login', ['error' => $msg, 'username' => $username, 'title' => '登录']);
    }

    private function isAjax() {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') || isset($_POST['ajax']);
    }

    private function json($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public function register()
    {
        if (isset($_SESSION['user'])) {
            header('Location: ' . url('/'));
            exit;
        }

        $data = [
            'app_name' => get_option('site_title', 'RanUI Blog'),
            'title' => '注册'
        ];
        $this->view('register', $data);
    }

    public function store()
    {
        // CSRF Check
        if (!\Core\Csrf::check($_POST['csrf_token'] ?? '')) {
             $this->view('register', ['error' => '页面已过期，请刷新重试 (CSRF)', 'title' => '注册']);
             return;
        }

        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['password_confirm'] ?? '';

        // Basic Validation
        if (empty($username) || empty($email) || empty($password)) {
             $this->view('register', ['error' => '所有字段都必须填写', 'username' => $username, 'email' => $email, 'title' => '注册']);
             return;
        }

        if (strlen($password) < 6) {
             $this->view('register', ['error' => '密码长度至少需 6 位', 'username' => $username, 'email' => $email, 'title' => '注册']);
             return;
        }

        if ($password !== $confirm) {
             $this->view('register', ['error' => '两次输入的密码不一致', 'username' => $username, 'email' => $email, 'title' => '注册']);
             return;
        }

        $userModel = new User();
        if ($userModel->findByUsername($username)) {
            $this->view('register', ['error' => '该用户名已被占用', 'username' => $username, 'email' => $email, 'title' => '注册']);
             return;
        }

        if ($userModel->findByEmail($email)) {
            $this->view('register', ['error' => '该邮箱已被注册', 'username' => $username, 'email' => $email, 'title' => '注册']);
             return;
        }

        // Create User
        $db = Database::getInstance(config('db'));
        $pdo = $db->getPDO();
        
        try {
            $pdo->beginTransaction();

            $sql = "SELECT CAST(t1.uid AS UNSIGNED) + 1 AS next_id
                    FROM users t1
                    WHERE NOT EXISTS (
                        SELECT 1 FROM users t2 WHERE CAST(t2.uid AS UNSIGNED) = CAST(t1.uid AS UNSIGNED) + 1
                    )
                    AND CAST(t1.uid AS UNSIGNED) >= 10000
                    ORDER BY next_id ASC
                    LIMIT 1";
            
            $gapUid = $db->query($sql)->fetchColumn();
            $nextUid = $gapUid ? (string)$gapUid : '10001';

            while (true) {
                try {
                    $inPool = $db->query("SELECT uid FROM lianghao_pool WHERE uid = ?", [$nextUid])->fetch();
                    if ($inPool) {
                        $nextUid = (string)((int)$nextUid + 1);
                        continue;
                    }
                } catch (\Exception $e) {}
                
                $inUsers = $db->query("SELECT id FROM users WHERE uid = ?", [$nextUid])->fetch();
                if ($inUsers) {
                    $nextUid = (string)((int)$nextUid + 1);
                    continue;
                }
                break;
            }

            $newUser = [
                'uid' => $nextUid,
                'username' => $username,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role' => 'user'
            ];

            $newId = $userModel->create($newUser);
            
            // --- Invitation Logic ---
            if (isset($_COOKIE['ran_invite_code'])) {
                $code = $_COOKIE['ran_invite_code'];
                $inviter = $db->query("SELECT id, username FROM users WHERE invite_code = ?", [$code])->fetch();
                
                if ($inviter) {
                    $db->query("UPDATE users SET inviter_id = ? WHERE id = ?", [$inviter['id'], $newId]);
                    
                    if (function_exists('is_plugin_active') && is_plugin_active('Ran_Task') && class_exists('Plugins\Ran_Task\Service')) {
                        require_once ROOT_PATH . '/plugins/Ran_Task/Service.php';
                        \Plugins\Ran_Task\Service::check($inviter['id'], 'invite_user', ['invitee_name' => $username]);
                    }
                }
            }
            
            $pdo->commit();
            
            // --- Security Log ---
            $this->logAccess($newId, 'register_success', 1, "Username: $username");
            
            // Redirect to Login with success
            header('Location: ' . url('/login?success=注册成功，请登录&username=' . urlencode($username)));
            exit;
        } catch (\Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $this->view('register', ['error' => '注册失败，请稍后重试', 'username' => $username, 'email' => $email, 'title' => '注册']);
        }
    }

    public function logout()
    {
        // Clear Token
        if (isset($_COOKIE['remember_token'])) {
            $token = $_COOKIE['remember_token'];
            $db = Database::getInstance(config('db'));
            $db->query("DELETE FROM user_tokens WHERE token = :token", [':token' => $token]);
            setcookie('remember_token', '', time() - 3600, '/');
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
        header('Location: ' . url('/'));
        exit;
    }

    // --- Private Security Helpers ---

    private function createRememberToken($userId)
    {
        $token = bin2hex(random_bytes(32)); // 64 chars
        $expires = date('Y-m-d H:i:s', time() + 86400 * 7); // 7 Days
        $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

        $db = Database::getInstance(config('db'));
        $db->query("INSERT INTO user_tokens (user_id, token, user_agent, expires_at) VALUES (:uid, :token, :ua, :exp)", [
            ':uid' => $userId,
            ':token' => $token,
            ':ua' => $ua,
            ':exp' => $expires
        ]);

        // Secure Cookie (HttpOnly)
        setcookie('remember_token', $token, time() + 86400 * 7, '/', '', false, true);
    }

    private function getIp() {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    private function checkRateLimit() {
        $ip = $this->getIp();
        $db = Database::getInstance(config('db'));
        
        try {
            $pdo = $db->getPDO();
            // Try explicit SELECT first using raw PDO
            $stmt = $pdo->prepare("SELECT * FROM login_fails WHERE ip = ?");
            $stmt->execute([$ip]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($row) {
                if ($row['locked_until'] > time()) {
                    $wait = ceil(($row['locked_until'] - time()) / 60);
                    return "安全警告：尝试次数过多，请 {$wait} 分钟后再试。";
                }
                // Unlock if expired
                if ($row['locked_until'] > 0 && $row['locked_until'] < time()) {
                     $this->clearFailure(); 
                }
            }
        } catch (\PDOException $e) {
            // Table seemingly doesn't exist or DB error. 
            // In strict environment, we just skip rate limiting rather than crashing.
            // DO NOT try to CREATE TABLE here if it risks crashing constraints.
            return null;
        }
        return null;
    }

    private function recordFailure() {
        $ip = $this->getIp();
        $db = Database::getInstance(config('db'));
        
        try {
            $pdo = $db->getPDO();
            
            // Check existing with raw PDO
            $stmt = $pdo->prepare("SELECT * FROM login_fails WHERE ip = ?");
            $stmt->execute([$ip]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            $newAttempts = ($row ? $row['attempts'] : 0) + 1;
            $lockedUntil = 0;
            
            if ($newAttempts >= 5) {
                $lockedUntil = time() + 300; // 5 min lock
            }
            // ... (Simple logic for 10 attempts)
            if ($newAttempts >= 10) {
                 $lockedUntil = time() + 300; 
            }
    
            if ($row) {
                $up = $pdo->prepare("UPDATE login_fails SET attempts = ?, locked_until = ? WHERE ip = ?");
                $up->execute([$newAttempts, $lockedUntil, $ip]);
            } else {
                $in = $pdo->prepare("INSERT INTO login_fails (ip, attempts, locked_until) VALUES (?, ?, ?)");
                $in->execute([$ip, $newAttempts, $lockedUntil]);
            }
            
            return $newAttempts;
        } catch (\PDOException $e) {
            // Failsafe: if table missing, return max attempts to be safe? Or 1?
            // Let's return 1 so they can keep trying (Fail Open) or 5 to Block?
            // Fail open seems friendlier if system is broken.
            return 1;
        }
    }

    private function clearFailure() {
        $ip = $this->getIp();
        $db = Database::getInstance(config('db'));
        try {
            $pdo = $db->getPDO();
            $stmt = $pdo->prepare("DELETE FROM login_fails WHERE ip = ?");
            $stmt->execute([$ip]);
        } catch (\PDOException $e) {}
    }

    // --- Verification Code Logic ---

    public function sendCode()
    {
        header('Content-Type: application/json');
        
        $email = trim($_POST['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => '邮箱格式不正确']);
            exit;
        }

        $ip = $this->getIp();
        $db = Database::getInstance(config('db'));

        // 1. Ensure Table Exists
        $db->query("CREATE TABLE IF NOT EXISTS `email_codes` (
            `email` varchar(255) NOT NULL,
            `code` varchar(10) NOT NULL,
            `ip` varchar(45) NOT NULL,
            `created_at` int(11) NOT NULL,
            `expires_at` int(11) NOT NULL,
            KEY `k_email` (`email`),
            KEY `k_ip` (`ip`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // 2. Check Rate Limit (User IP or Email within last 60s)
        $recent = $db->query("SELECT created_at FROM email_codes WHERE (email = ? OR ip = ?) AND created_at > ?", [$email, $ip, time() - 60])->fetch();
        
        if ($recent) {
            echo json_encode(['success' => false, 'message' => '发送过于频繁，请 60 秒后再试']);
            exit;
        }

        // 3. Generate Code
        $code = (string)rand(100000, 999999);
        $now = time();
        $expires = $now + 600; // 10 minutes

        // 4. Send Email via Ran_Email Plugin
        if (function_exists('is_plugin_active') && is_plugin_active('Ran_Email') && class_exists('Plugins\Ran_Email\Service')) {
            $service = new \Plugins\Ran_Email\Service();
            $userExists = (new User())->findByEmail($email);
            $subject = $userExists ? '登录验证码 - RanUI' : '注册验证码 - RanUI';
            $body = "<h2>您的验证码是：<span style='color:blue'>{$code}</span></h2><p>有效期10分钟，请勿告诉他人。</p>";
            
            $result = $service->send($email, $subject, $body);
            if ($result !== true) {
                echo json_encode(['success' => false, 'message' => '邮件发送失败: ' . $result]);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'message' => '邮件服务未启用']);
            exit;
        }

        // 5. Store Logic (Insert new record)
        $db->query("INSERT INTO email_codes (email, code, ip, created_at, expires_at) VALUES (?, ?, ?, ?, ?)", [$email, $code, $ip, $now, $expires]);
        
        echo json_encode(['success' => true, 'message' => '验证码发送成功']);
        exit;
    }

    public function loginWithCode()
    {
        // CSRF Check (Assuming form has csrf_token)
        if (!\Core\Csrf::check($_POST['csrf_token'] ?? '')) {
             $this->view('login', ['error' => '页面已过期，请刷新重试 (CSRF)', 'title' => '登录']);
             return;
        }

        $email = trim($_POST['email'] ?? '');
        $code = trim($_POST['code'] ?? '');
        $inviteCode = trim($_POST['invite_code'] ?? '');
        
        if (empty($email) || empty($code)) {
            $this->view('login', ['error' => '邮箱和验证码不能为空', 'username' => '', 'title' => '登录']);
            return;
        }

        $db = Database::getInstance(config('db'));
        
        // 1. Validate Code
        $record = $db->query("SELECT * FROM email_codes WHERE email = ? AND code = ? AND expires_at > ?", [$email, $code, time()])->fetch();
        
        if (!$record) {
             $this->view('login', ['error' => '验证码无效或已过期', 'username' => '', 'title' => '登录']);
             return;
        }

        // 2. Consume Code
        $db->query("DELETE FROM email_codes WHERE email = ?", [$email]);

        // 3. User Logic
        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if (!$user) {
            // --- Auto Register Flow ---
            
             // Check Invite Code Requirement
            if (function_exists('is_plugin_active') && is_plugin_active('Ran_Invite')) {
                if (empty($inviteCode)) {
                    $this->view('login', ['error' => '本站开启了邀请注册，请输入邀请码', 'username' => '', 'title' => '注册']);
                    return;
                }
                
                $invRow = $db->query("SELECT * FROM invite_codes WHERE code = ? AND status = 0", [$inviteCode])->fetch();
                if (!$invRow) {
                    $this->view('login', ['error' => '邀请码无效或已被使用', 'username' => '', 'title' => '注册']);
                    return;
                }
                $validInviteRow = $invRow;
            }

            $username = explode('@', $email)[0];
            while ($userModel->findByUsername($username)) {
                $username .= rand(0, 9);
            }
            
            $password = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
            $pdo = $db->getPDO();

            try {
                $pdo->beginTransaction();
                
                 // Mark Invite Code Used
                if (isset($validInviteRow)) {
                    $db->query("UPDATE invite_codes SET status = 1, used_time = NOW() WHERE id = ?", [$validInviteRow['id']]);
                }
                
                $maxUid = $db->query("SELECT MAX(CAST(uid AS UNSIGNED)) FROM users WHERE uid REGEXP '^[0-9]+$' FOR UPDATE")->fetchColumn();
                $nextUid = $maxUid ? (string)($maxUid + 1) : '10001';
                if ((int)$nextUid < 10001) $nextUid = '10001';

                $newUser = [
                    'uid' => $nextUid,
                    'username' => $username,
                    'email' => $email,
                    'password' => $password,
                    'role' => 'user',
                    'password_set' => 0 // Flag: Password not set by user
                ];
                
                // Ensure column exists (Lazy Migration)
                $checkPassSet = $db->query("SHOW COLUMNS FROM users LIKE 'password_set'")->fetch();
                if (!$checkPassSet) {
                        $db->query("ALTER TABLE users ADD COLUMN password_set TINYINT DEFAULT 1");
                }

                $newId = $userModel->create($newUser);
                $user = $userModel->findByEmail($email);
                
                // --- Security Log ---
                $this->logAccess($newId, 'register_auto', 1, 'Auto Register via Email Code');

                $pdo->commit();

                // Notification: Welcome Register
                if (function_exists('is_plugin_active') && is_plugin_active('Ran_Notice') && class_exists('Plugins\Ran_Notice\Service')) {
                    require_once ROOT_PATH . '/plugins/Ran_Notice/Service.php';
                    \Plugins\Ran_Notice\Service::send($user['id'], 'welcome_register', [
                        'site_name' => get_option('site_name', 'RanUI')
                    ]);
                }

                // Send "Set Password" Email
                if (function_exists('is_plugin_active') && is_plugin_active('Ran_Email') && class_exists('Plugins\Ran_Email\Service')) {
                    $service = new \Plugins\Ran_Email\Service();
                    $subject = '为了您的账号安全，请设置密码 - RanUI';
                    $settingsUrl = url('/user/my'); // To be updated to new route
                    $body = "<h2>欢迎加入 RanUI!</h2>
                                <p>您已通过验证码自动注册登录。为了您的账号安全，我们建议您立即设置一个固定密码。</p>
                                <p>请访问个人中心进行设置：<a href='{$settingsUrl}'>{$settingsUrl}</a></p>";
                    try {
                        $service->send($email, $subject, $body);
                    } catch (\Exception $ignore) {}
                }

            } catch (\Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $this->view('login', ['error' => '自动注册失败: ' . $e->getMessage(), 'username' => '', 'title' => '登录']);
                return;
            }
        }
        
        // --- Status Checks (Fixed: Moved OUTSIDE creation block) ---
        if (isset($user['status'])) {
            if ($user['status'] === 'cancellation_pending') {
                $_SESSION['temp_user_id'] = $user['id'];
                $_SESSION['cancel_account_pending'] = true;
                header('Location: ' . url('/login'));
                exit;
            }
            if ($user['status'] === 'banned') {
                $this->view('login', ['error' => '该账号已被封禁', 'username' => '', 'title' => '登录']);
                return;
            }
            if ($user['status'] === 'deleted') {
                $this->view('login', ['error' => '该账号已注销', 'username' => '', 'title' => '登录']);
                return;
            }
        }

        // 4. Login Session
        $_SESSION['user'] = $user;
        $_SESSION['user_id'] = $user['id'];
        session_regenerate_id(true);
        
        // --- Security Log ---
        $this->logAccess($user['id'], 'login_code_success', 1, 'Login via Email Code');
        $this->checkDevice($user['id']);

        // Task Check: Daily Login
        if (function_exists('is_plugin_active') && is_plugin_active('Ran_Task') && class_exists('Plugins\Ran_Task\Service')) {
            require_once ROOT_PATH . '/plugins/Ran_Task/Service.php';
            \Plugins\Ran_Task\Service::check($user['id'], 'daily_login');
        }

        header('Location: ' . url('/'));
        exit;
    }

    public function forgotPassword()
    {
        $this->view('forgot_password', ['title' => '重置密码']);
    }

    public function resetPassword()
    {
        $email = trim($_POST['email'] ?? '');
        $code = trim($_POST['code'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($code) || empty($password)) {
             $this->view('forgot_password', ['error' => '所有字段都必须填写']);
             return;
        }

        if (strlen($password) < 6) {
             $this->view('forgot_password', ['error' => '新密码长度至少需要 6 位']);
             return;
        }

        $db = Database::getInstance(config('db'));

        // 1. Verify Code
        $record = $db->query("SELECT * FROM email_codes WHERE email = ? AND code = ? AND expires_at > ?", [$email, $code, time()])->fetch();
        if (!$record) {
             $this->view('forgot_password', ['error' => '验证码无效或已过期']);
             return;
        }

        // 2. Check User
        $userModel = new User();
        $user = $userModel->findByEmail($email);
        if (!$user) {
             $this->view('forgot_password', ['error' => '该邮箱未注册账号']);
             return;
        }

        // 3. Update Password
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        
        // Ensure column exists (Lazy Migration check)
        $checkPassSet = $db->query("SHOW COLUMNS FROM users LIKE 'password_set'")->fetch();
        if (!$checkPassSet) {
             $db->query("ALTER TABLE users ADD COLUMN password_set TINYINT DEFAULT 1");
        }

        $db->query("UPDATE users SET password = ?, password_set = 1 WHERE id = ?", [$newHash, $user['id']]);

        // Notification: Password Changed
        if (function_exists('is_plugin_active') && is_plugin_active('Ran_Notice') && class_exists('Plugins\Ran_Notice\Service')) {
             require_once ROOT_PATH . '/plugins/Ran_Notice/Service.php';
             \Plugins\Ran_Notice\Service::send($user['id'], 'password_changed', [
                 'time' => date('Y-m-d H:i:s')
             ]);
        }

        // 4. Consume Code
        $db->query("DELETE FROM email_codes WHERE email = ?", [$email]);

        // 5. Redirect
        header('Location: ' . url('/login?success=密码重置成功，请使用新密码登录&username=' . urlencode($user['username'])));
        exit;
    }

    // --- QR Login Logic (Web Side) ---

    private function ensureQrTable() {
        $db = Database::getInstance(config('db'));
        $db->query("CREATE TABLE IF NOT EXISTS `qr_login_sessions` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `session_id` varchar(64) NOT NULL,
            `user_id` int(11) DEFAULT NULL,
            `status` tinyint(4) DEFAULT '0' COMMENT '0:pending, 1:scanned, 2:confirmed, 3:expired',
            `ip` varchar(45) DEFAULT NULL,
            `user_agent` varchar(255) DEFAULT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `expires_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `session_id` (`session_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    public function qrSession() {
        header('Content-Type: application/json');
        $this->ensureQrTable();
        
        $session_id = bin2hex(random_bytes(16)) . time();
        $expires = date('Y-m-d H:i:s', time() + 300); // 5 minutes
        
        $db = Database::getInstance(config('db'));
        $db->query("INSERT INTO qr_login_sessions (session_id, ip, user_agent, expires_at) VALUES (?, ?, ?, ?)", [
            $session_id,
            $this->getIp(),
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            $expires
        ]);
        
        echo json_encode([
            'success' => true,
            'data' => [
                'session_id' => $session_id,
                'qr_url' => url('/auth/qr/login?sid=' . $session_id), // URL for App to recognize
                'expires_in' => 300
            ]
        ]);
        exit;
    }

    public function qrCheck() {
        header('Content-Type: application/json');
        $sid = $_GET['sid'] ?? '';
        if (!$sid) {
            echo json_encode(['success' => false, 'message' => 'Missing session ID']);
            exit;
        }
        
        $db = Database::getInstance(config('db'));
        $session = $db->query("SELECT * FROM qr_login_sessions WHERE session_id = ?", [$sid])->fetch();
        
        if (!$session) {
            echo json_encode(['success' => false, 'message' => 'Invalid session']);
            exit;
        }
        
        // Handle Expiration
        if (strtotime($session['expires_at']) < time()) {
            if ($session['status'] != 3) {
                $db->query("UPDATE qr_login_sessions SET status = 3 WHERE id = ?", [$session['id']]);
            }
            echo json_encode(['success' => true, 'status' => 'expired']);
            exit;
        }
        
        if ($session['status'] == 2 && $session['user_id']) {
            // Confirmed, Perform Login
            $userModel = new User();
            $user = $userModel->find($session['user_id']);
            if ($user) {
                if (session_status() === PHP_SESSION_NONE) session_start();
                $_SESSION['user'] = $user;
                $_SESSION['user_id'] = $user['id'];
                
                echo json_encode([
                    'success' => true, 
                    'status' => 'confirmed', 
                    'redirect' => url('/'),
                    'msg' => 'Login Successful'
                ]);
                
                $db->query("DELETE FROM qr_login_sessions WHERE session_id = ?", [$sid]); 
                exit;
            }
        }
        
        $statusMap = [0 => 'pending', 1 => 'scanned', 2 => 'confirmed', 3 => 'expired'];
        echo json_encode([
            'success' => true,
            'status' => $statusMap[$session['status']] ?? 'pending'
        ]);
        exit;
    }

    // --- Security Phase 2: Risk Control & Logging ---

    private function logAccess($userId, $action, $status = 1, $details = '')
    {
        try {
            $db = Database::getInstance(config('db'));
            $pdo = $db->getPDO();
            $ip = $this->getIp();
            $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
            $deviceHash = md5($ip . $ua); // Simple device fingerprint
            
            // Raw PDO Insert
            $stmt = $pdo->prepare("INSERT INTO access_logs (user_id, action, ip, user_agent, device_hash, status, details) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $action, $ip, $ua, $deviceHash, $status, $details]);
        } catch (\PDOException $e) { 
            // Logging should not break login flow
            // \Core\Log::error("Access Log Failed: " . $e->getMessage()); 
        }
    }

    private function checkDevice($userId)
    {
        try {
            $db = Database::getInstance(config('db'));
            $pdo = $db->getPDO();
            $ip = $this->getIp();
            $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
            $deviceHash = md5($ip . $ua);
            
            $stmt = $pdo->prepare("SELECT id FROM user_devices WHERE user_id = ? AND device_hash = ?");
            $stmt->execute([$userId, $deviceHash]);
            $exists = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$exists) {
                // New Device Found
                $ins = $pdo->prepare("INSERT INTO user_devices (user_id, device_hash, user_agent, is_trusted, last_login) VALUES (?, ?, ?, 1, NOW())");
                $ins->execute([$userId, $deviceHash, $ua]);
                
                // Trigger Warning Notification
                if (function_exists('is_plugin_active') && is_plugin_active('Ran_Email') && class_exists('Plugins\Ran_Email\Service')) {
                    $userModel = new User();
                    $user = $userModel->find($userId);
                    if ($user && $user['email']) {
                        $service = new \Plugins\Ran_Email\Service();
                        $time = date('Y-m-d H:i:s');
                        $subject = '【安全提醒】新设备登录通知 - RanUI';
                        $body = "<h2>您的账号在新设备上登录</h2>
                                 <p>时间：{$time}</p>
                                 <p>IP地址：{$ip}</p>
                                 <p>设备：{$ua}</p>
                                 <p style='color:red'>如果不是您本人的操作，请立即修改密码！</p>";
                        // Async send or fire and forget
                         try { $service->send($user['email'], $subject, $body); } catch(\Exception $e){}
                    }
                }
            } else {
                // Update Last Login
                $up = $pdo->prepare("UPDATE user_devices SET last_login = NOW() WHERE id = ?");
                $up->execute([$exists['id']]);
            }
        } catch (\PDOException $e) { }
    }
}
