<?php

namespace Core;

class Controller
{
    protected $config;
    protected $view;

    public function __construct($config)
    {
        $this->config = $config;
        $this->view = new View($config);
    }

    protected function view($path, $data = [])
    {
        $this->view->render($path, $data);
    }
    
    // Helper to get config
    protected function getConfig($key) {
        return $this->config[$key] ?? null;
    }

    /**
     * Get Current Logged In User ID from Token
     */
    protected function getAuthId() {
        $token = $_SERVER['HTTP_TOKEN'] ?? ($_SERVER['HTTP_X_TOKEN'] ?? '');
        if (empty($token) && isset($_SERVER['HTTP_AUTHORIZATION'])) {
            if (preg_match('/Bearer\s+(.*)$/i', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
                $token = $matches[1];
            }
        }
        if (empty($token)) $token = $_REQUEST['token'] ?? ($_POST['token'] ?? ($_GET['token'] ?? ''));
        if (empty($token)) return 0;
        try {
            $databaseConfig = config('db');
            if (!$databaseConfig) return 0;
            $db = Database::getInstance($databaseConfig);
            $now = date('Y-m-d H:i:s');
            $row = $db->query("SELECT user_id FROM user_tokens WHERE token = ? AND expires_at > ?", [$token, $now])->fetch();
            return $row ? (int)$row['user_id'] : 0;
        } catch (\Throwable $e) { return 0; }
    }

    /**
     * Send JSON Response
     */
    protected function apiJson($data) {
        if (ob_get_length()) ob_clean(); 
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_PARTIAL_OUTPUT_ON_ERROR);
        exit;
    }

    /**
     * Fix Asset URLs
     */
    protected function fullUrl($url) {
        if (empty($url)) return '';
        if (strpos($url, 'http') === 0) return $url;
        
        if (strpos($url, '/') === 0) {
            $path = $url;
        } else {
            $path = '/' . $url;
        }

        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'www.geknet.com';
        
        return "{$protocol}://{$host}{$path}";
    }
}
