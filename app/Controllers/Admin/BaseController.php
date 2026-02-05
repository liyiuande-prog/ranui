<?php

namespace App\Controllers\Admin;

use Core\Controller;

class BaseController extends Controller
{
    public function __construct($config)
    {
        parent::__construct($config);
        $this->checkAuth();
    }

    protected function checkAuth()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Auto Login via Token
        if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
            $this->tryAutoLogin();
        }

        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . url('/admin/login'));
            exit;
        }

        // --- Security Fix: Token Binding Verification ---
        // Ensure the session token matches the database (Revoke access if token deleted/expired)
        if (isset($_SESSION['auth_token'])) {
            try {
                $db = \Core\Database::getInstance($this->getConfig('db'));
                $valid = $db->query("SELECT id FROM user_tokens WHERE token = :token AND user_id = :uid AND expires_at > NOW()", [
                    ':token' => $_SESSION['auth_token'],
                    ':uid' => $_SESSION['user_id']
                ])->fetch();

                if (!$valid) {
                    // Token invalid (Revoked or Expired)
                    session_destroy();
                    header('Location: ' . url('/admin/login?error=SessionExpired'));
                    exit;
                }
            } catch (\Exception $e) {
                // DB Error, fail safe
            }
        } else {
             // Optional: Enforce token presence for Admins (Force logout if no token found)
             // session_destroy(); 
             // header('Location: ' . url('/admin/login'));
             // exit;
        }

        // Restrict Access: Only 'admin' role allowed
        if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'admin') {
            // Logged in but not admin
            header('Location: ' . url('/')); // Redirect to homepage
            exit;
        }

        // --- Global CSRF Protection ---
        $this->checkCsrf();
    }

    protected function checkCsrf()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
             // 1. Check if token exists in session
             if (empty($_SESSION['csrf_token'])) {
                 // Try to regenerate or fail
                 // In admin context, session should be active.
                 \Core\Log::error("CSRF Error: No token in session for user " . ($_SESSION['user_id'] ?? 'guest'));
                 die('Security Error: Session invalid or expired. Please refresh the page.');
             }

             // 2. Check token in request
             $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
             
             if (empty($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
                 \Core\Log::warning("CSRF Failure user:{$_SESSION['user_id']} ip:" . ($_SERVER['REMOTE_ADDR']??''));
                 
                 if ($this->isAjax()) {
                     header('Content-Type: application/json');
                     echo json_encode(['error' => 'CSRF Token Mismatch']);
                     exit;
                 }
                 
                 die('Security Warning: CSRF Token Verification Failed. Please refresh the page and try again.');
             }
        }
    }
    
    protected function isAjax() {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') 
                  || (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    }

    protected function tryAutoLogin()
    {
        try {
            $token = $_COOKIE['remember_token'];
            $db = \Core\Database::getInstance($this->getConfig('db'));
            
            $sql = "SELECT u.* FROM user_tokens t 
                    JOIN users u ON t.user_id = u.id 
                    WHERE t.token = :token AND t.expires_at > NOW()";
            
            $user = $db->query($sql, [':token' => $token])->fetch();

            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user'] = $user;
                $_SESSION['auth_token'] = $token; // Bind Token to Session
                // Optional: Rotate token here for better security
            }
        } catch (\Exception $e) {
            // Ignore error
        }
    }
}
