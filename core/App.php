<?php

namespace Core;

class App
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
        
        // Start Session
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }


        // Load Plugins
        PluginManager::load();
        
        // Load Active Theme from DB override
        try {
            $db = Database::getInstance($this->config['db']);
            $activeTheme = $db->query("SELECT name FROM themes WHERE is_active = 1 LIMIT 1")->fetchColumn();
            
            if ($activeTheme) {
                // Update local config
                $this->config['theme'] = $activeTheme;
                
                // Update global config for Helpers and Router
                global $config; 
                if (isset($config) && is_array($config)) {
                    $config['theme'] = $activeTheme;
                }
            }
        } catch (\Exception $e) {
            // Fallback to default in config if DB fails
        }
    }

    public function run()
    {
        // Global Rate Limiting
        $this->checkRateLimit();

        // Load Route Definitions
        if (file_exists(ROOT_PATH . '/web/home.php')) {
            require ROOT_PATH . '/web/home.php';
        }
        if (file_exists(ROOT_PATH . '/web/admin.php')) {
            require ROOT_PATH . '/web/admin.php';
        }
        
        $uri = $this->getRequestUri();
        $method = $_SERVER['REQUEST_METHOD'];

        // ============================================================
        // [Security Fix] Global Admin Route Guard
        // ============================================================
        if (strpos($uri, '/admin') === 0 && !in_array($uri, ['/admin/login', '/admin/logout'])) {
            
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }

            // 1. Try Auto-Login if not logged in
            if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
                try {
                    $db = Database::getInstance($this->config['db']);
                    $token = $_COOKIE['remember_token'];
                    $sql = "SELECT u.* FROM user_tokens t 
                            JOIN users u ON t.user_id = u.id 
                            WHERE t.token = :token AND t.expires_at > NOW()";
                    $user = $db->query($sql, [':token' => $token])->fetch();
                    if ($user) {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user'] = $user;
                        $_SESSION['auth_token'] = $token;
                    }
                } catch (\Exception $e) { /* Ignore */ }
            }

            // 2. Check Login Status
            if (!isset($_SESSION['user_id'])) {
                $loginUrl = function_exists('url') ? url('/admin/login') : '/admin/login';
                header('Location: ' . $loginUrl);
                exit;
            }

            // 3. Check Admin Role
            if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'admin') {
                $homeUrl = function_exists('url') ? url('/') : '/';
                header('Location: ' . $homeUrl);
                exit;
            }
        }

        try {
            Router::dispatch($uri, $method);
        } catch (\Exception $e) {
            // Handle 404
            if ($this->config['debug']) {
                echo "<h1>Error</h1><p>" . $e->getMessage() . "</p>";
            } else {
                // Show generic 404 page
                http_response_code(404);
                echo "404 Not Found";
            }
        }
    }

    protected function getRequestUri()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        

        // Treat /index.php as root
        if ($uri === '/index.php') {
            return '/';
        }
        
        return $uri ?: '/';
        return $uri ?: '/';
    }

    private function checkRateLimit()
    {
        // Smooth Rate Limiting: Token Bucket Algorithm
        // Capacity: 600 tokens
        // Refill Rate: 10 tokens/sec (600 per 60s)
        $capacity = 600;
        $window = 60;
        $refillRate = $capacity / $window; // 10 tokens/sec
        
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $cacheDir = ROOT_PATH . '/storage/ratelimit';
        
        if (!is_dir($cacheDir)) {
             @mkdir($cacheDir, 0777, true);
        }
        
        $file = $cacheDir . '/' . md5($ip) . '.txt';
        $now = time();
        
        // Default state for new IP
        $data = ['tokens' => $capacity, 'last_refill' => $now];
        
        // Open for reading and writing, creating if necessary
        $fp = fopen($file, 'c+');
        
        if (flock($fp, LOCK_EX)) {
            $content = stream_get_contents($fp);
            $json = $content ? json_decode($content, true) : null;
            
            if ($json) {
                // Calculation: Refill tokens based on time passed
                $timePassed = $now - $json['last_refill'];
                $newTokens = $json['tokens'] + ($timePassed * $refillRate);
                $data['tokens'] = min($capacity, $newTokens);
                $data['last_refill'] = $now;
            }
            
            // Consume 1 token
            if ($data['tokens'] >= 1) {
                $data['tokens'] -= 1;
            }
            
            // Write back
            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, json_encode($data));
            fflush($fp);
            flock($fp, LOCK_UN);
        }
        fclose($fp);
        
        // Block if empty
        if ($data['tokens'] < 1) {
            $retryAfter = ceil(1 / $refillRate);
            header('HTTP/1.1 429 Too Many Requests');
            header("Retry-After: $retryAfter");
            echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>429 访问受限</title></head><body><h1>访问过于频繁</h1></body></html>';
            exit;
        }
    }
}
