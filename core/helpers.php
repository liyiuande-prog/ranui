<?php

use Core\Database;

/**
 * Global Helper Functions
 */

if (!function_exists('dd')) {
    function dd(...$vars)
    {
        foreach ($vars as $v) {
            echo "<pre>";
            var_dump($v);
            echo "</pre>";
        }
        die(1);
    }
}

if (!function_exists('config')) {
    function config($key, $default = null)
    {
        global $config; // Access header config
        // Support dot notation e.g. 'db.host'
        $keys = explode('.', $key);
        $value = $config;
        
        foreach ($keys as $k) {
            if (isset($value[$k])) {
                $value = $value[$k];
            } else {
                return $default;
            }
        }
        return $value;
    }
}

if (!function_exists('get_option')) {
    /**
     * Get system option from database
     */
    function get_option($name, $default = null)
    {
        // Simple caching via static variable to avoid repeated queries in one request
        static $options = null;
        
        if ($options === null) {
            try {
                // Try to load all autoload options at once
                $db = Database::getInstance(config('db'));
                $stmt = $db->query("SELECT option_name, option_value FROM options WHERE autoload = 1");
                $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
                $options = $rows ?: []; 
            } catch (Exception $e) {
                $options = [];
            }
        }

        if (array_key_exists($name, $options)) {
            return $options[$name];
        }

        // If not autoloaded, try to fetch one-off (optional optimization)
        // For now, return default
        return $default;
    }
}

if (!function_exists('update_option')) {
    /**
     * Update or add system option
     */
    function update_option($name, $value, $autoload = 1)
    {
        $db = Database::getInstance(config('db'));
        $sql = "INSERT INTO options (option_name, option_value, autoload) 
                VALUES (:name, :value, :autoload) 
                ON DUPLICATE KEY UPDATE option_value = :value_update";
        
        $db->query($sql, [
            ':name' => $name,
            ':value' => $value,
            ':autoload' => $autoload,
            ':value_update' => $value
        ]);
        
        return true;
    }
}

if (!function_exists('theme_url')) {
    function theme_url($path = '')
    {
        // Assuming themes are in public/themes or we rely on rewrite to serve files from root/themes
        // If public/index.php is root, then themes/ is parallel to public/ ? 
        // No, current structure has themes/ in root. 
        // web server should point to public/ usually.
        // If so, themes assets must be in public/themes or we rely on special routing/rewrite.
        // let's assume valid URL path.
        $theme = config('theme', 'default');
        return "/themes/{$theme}/" . ltrim($path, '/');
    }
}

if (!function_exists('url')) {
    function url($path = '')
    {
        // Basic URL builder
        $base = rtrim(config('base_url', ''), '/');
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('view')) {
    // Helper to render view inside controllers/views easily
    function view($path, $data = []) {
        // ... (implementation or reference to Controller->view if possible)
        // Since this is a global helper, ideally it shouldn't be responsible for rendering logic tied to object context unless we use a singleton View system.
        // For now, let's keep it simple or remove if unused.
    }
}

if (!function_exists('e')) {
    /**
     * Escape HTML special characters for XSS protection
     */
    function e($string)
    {
        return htmlspecialchars((string) $string, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('is_plugin_active')) {
    /**
     * Check if a plugin is active
     */
    function is_plugin_active($pluginName)
    {
        static $activePlugins = null;
        
        if ($activePlugins === null) {
            try {
                $db = Database::getInstance(config('db'));
                $stmt = $db->query("SELECT name FROM plugins WHERE is_active = 1");
                $activePlugins = $stmt->fetchAll(PDO::FETCH_COLUMN);
            } catch (Exception $e) {
                $activePlugins = [];
            }
        }

        return in_array($pluginName, $activePlugins);
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirect to a new URL
     */
    function redirect($url, $statusCode = 302)
    {
        header('Location: ' . $url, true, $statusCode);
        exit();
    }
}

if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}
