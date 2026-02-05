<?php

namespace App\Controllers\Admin;

use Core\Controller;
use Core\Database;

class AuthController extends Controller
{
    public function login()
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . url('/admin/dashboard'));
            exit;
        }
        $this->view('admin/login');
    }

    public function authenticate()
    {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // 1. Check Rate Limit
        $limitMsg = $this->checkRateLimit();
        if ($limitMsg) {
            $this->view('admin/login', ['error' => $limitMsg, 'username' => $username]);
            return;
        }

        // 2. CSRF Check
        $token = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || empty($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
            $this->view('admin/login', ['error' => '页面已过期，请刷新重试 (CSRF)', 'username' => $username]);
            return;
        }

        if (empty($username) || empty($password)) {
            $this->view('admin/login', ['error' => '用户名和密码不能为空']);
            return;
        }

        $db = Database::getInstance(config('db'));
        $sql = "SELECT * FROM users WHERE username = :username LIMIT 1";
        $user = $db->query($sql, [':username' => $username])->fetch();

        if ($user) {
            if (password_verify($password, $user['password'])) {
                // Login Success
                $this->clearFailure();
                session_regenerate_id(true);

                // --- Enhanced Security: Always use Token ---
                $token = bin2hex(random_bytes(32));
                $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
                $ip = $this->getIp();

                // --- 2FA Check Hook ---
                // If this Hook returns ['action' => '2fa_required'], we pause the login.
                $hookResult = \Core\Hook::listen('auth_login_after_verify', $user);
                
                if (isset($hookResult['action']) && $hookResult['action'] === '2fa_required') {
                    $_SESSION['2fa_pending_uid'] = $user['id'];
                    $_SESSION['2fa_pending_remember'] = isset($_POST['remember']);
                    $_SESSION['2fa_pending_type'] = 'admin';
                    
                    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'action' => '2fa_required', 'message' => '需要进行双重验证']);
                        exit;
                    }

                    header('Location: ' . url('/auth/2fa/verify'));
                    exit;
                }

                $isRemember = isset($_POST['remember']);
                // Remember: 30 days, Normal: 24 hours (Session default)
                $expires = $isRemember ? date('Y-m-d H:i:s', time() + 86400 * 30) : date('Y-m-d H:i:s', time() + 86400);

                // Store Token
                $db->query("INSERT INTO user_tokens (user_id, token, user_agent, expires_at) VALUES (:uid, :token, :ua, :exp)", [
                    ':uid' => $user['id'],
                    ':token' => $token,
                    ':ua' => $ua,
                    ':exp' => $expires
                ]);

                // --- Security: New Device/IP Notification ---
                $this->checkAndNotifyLogin($user, $ip, $ua);

                // Set Session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user'] = $user;
                $_SESSION['auth_token'] = $token; // Bind Session to Token
                
                if ($isRemember) {
                    setcookie('remember_token', $token, time() + 86400 * 30, '/', '', false, true);
                }
                
                header('Location: ' . url('/admin/dashboard'));
                exit;
            }
        }
        
        // Login Failed
        $attempts = $this->recordFailure();
        $remaining = 5 - $attempts;
        $msg = ($remaining > 0) 
            ? "账号或密码错误。剩余次数: {$remaining}" 
            : "尝试次数过多，账号已锁定 15 分钟。";
            
        $this->view('admin/login', ['error' => $msg, 'username' => $username]);
    }

    // --- Security Helper Methods ---

    private function getIp() {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    private function checkRateLimit() {
        $ip = $this->getIp();
        $file = APP_PATH . '/Storage/login_attempts.json';
        if (!file_exists($file)) return null;
        
        $fp = fopen($file, 'r');
        if (flock($fp, LOCK_SH)) { // Shared lock for reading
            $content = stream_get_contents($fp);
            $data = $content ? json_decode($content, true) : [];
            flock($fp, LOCK_UN);
            fclose($fp);

            if (isset($data[$ip])) {
                if ($data[$ip]['locked_until'] > time()) {
                    $wait = ceil(($data[$ip]['locked_until'] - time()) / 60);
                    return "安全警告：尝试次数过多，请 {$wait} 分钟后再试。";
                }
                // Auto unlock if time passed - Handled lazily in recordFailure or next success
            }
        } else {
             fclose($fp);
        }
        return null;
    }

    private function recordFailure() {
        $ip = $this->getIp();
        $file = APP_PATH . '/Storage/login_attempts.json';
        
        // Ensure directory exists
        $dir = dirname($file);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $attempts = 0;
        
        $fp = fopen($file, 'c+'); // Open for read/write
        if (flock($fp, LOCK_EX)) { // Exclusive lock
            $content = stream_get_contents($fp);
            $data = $content ? json_decode($content, true) : [];
            
            if (!isset($data[$ip])) {
                $data[$ip] = ['attempts' => 0, 'locked_until' => 0];
            }
            
            // Check if lock expired to reset
            if ($data[$ip]['locked_until'] > 0 && $data[$ip]['locked_until'] < time()) {
                 $data[$ip]['attempts'] = 0;
                 $data[$ip]['locked_until'] = 0;
            }
            
            $data[$ip]['attempts']++;
            $attempts = $data[$ip]['attempts'];
            
            if ($attempts >= 5) {
                $data[$ip]['locked_until'] = time() + 900; // 15 mins
            }
            
            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, json_encode($data));
            fflush($fp);
            flock($fp, LOCK_UN);
        }
        fclose($fp);
        
        return $attempts;
    }

    private function clearFailure() {
        $ip = $this->getIp();
        $file = APP_PATH . '/Storage/login_attempts.json';
        
        if (!file_exists($file)) return;

        $fp = fopen($file, 'c+');
        if (flock($fp, LOCK_EX)) {
            $content = stream_get_contents($fp);
            $data = $content ? json_decode($content, true) : [];
            
            if (isset($data[$ip])) {
                unset($data[$ip]);
                ftruncate($fp, 0);
                rewind($fp);
                fwrite($fp, json_encode($data));
                fflush($fp);
            }
            flock($fp, LOCK_UN);
        }
        fclose($fp);
    }
    
    private function checkAndNotifyLogin($user, $currentIp, $currentUa)
    {
        // Require Email Plugin
        if (!function_exists('is_plugin_active') || !is_plugin_active('Ran_Email') || !class_exists('Plugins\Ran_Email\Service')) {
            return;
        }

        $db = Database::getInstance(config('db'));
        
        // Check previous successful logins
        $lastLogin = $db->query("SELECT ip, user_agent FROM access_logs WHERE user_id = ? AND status = 1 ORDER BY id DESC LIMIT 1 OFFSET 1", [$user['id']])->fetch();
        
        $isNewDevice = true;
        
        if ($lastLogin) {
            // Simple check: Same IP or same UA?
            // (In reality, IP changes often, so maybe focus on UA or Subnet, but for high security admin, IP change is noteworthy)
            if ($lastLogin['ip'] === $currentIp) {
                $isNewDevice = false;
            }
        } else {
            // First time login ever?
            $isNewDevice = false; 
        }

        if ($isNewDevice) {
            try {
                $service = new \Plugins\Ran_Email\Service();
                $time = date('Y-m-d H:i:s');
                $subject = "【安全提醒】您的后台账号在新设备登录";
                $body = "<h2>管理员登录提醒</h2>
                        <p>您的账号 <strong>{$user['username']}</strong> 刚刚在新的 IP 地址登录。</p>
                        <ul>
                            <li><strong>登录时间：</strong> {$time}</li>
                            <li><strong>IP 地址：</strong> {$currentIp}</li>
                            <li><strong>设备信息：</strong> {$currentUa}</li>
                        </ul>
                        <p style='color:red'>如果这不是您的操作，请立即修改密码！</p>";
                
                $service->send($user['email'], $subject, $body);
            } catch (\Exception $e) {
                // Log and ignore, don't block login
                \Core\Log::error("Login Notification Failed: " . $e->getMessage());
            }
        }
    }

    public function logout()
    {
        // Clear Token from DB and Cookie
        if (isset($_COOKIE['remember_token'])) {
            try {
                $token = $_COOKIE['remember_token'];
                $db = Database::getInstance(config('db'));
                $db->query("DELETE FROM user_tokens WHERE token = :token", [':token' => $token]);
                setcookie('remember_token', '', time() - 3600, '/');
            } catch (\Exception $e) {}
        }

        session_destroy();
        header('Location: ' . url('/admin/login'));
        exit;
    }
}
