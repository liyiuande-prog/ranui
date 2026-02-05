<?php
/**
 * RanUI Cron Entry Point
 * Location: /core/cron.php
 * Setup: * * * * * /absolute/path/to/php /absolute/path/to/core/cron.php
 */

// Define absolute path constants
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CORE_PATH', ROOT_PATH . '/core');

// Load Configuration
$config = require APP_PATH . '/Config/config.php';

// Load Helpers
require CORE_PATH . '/helpers.php';

// Simple Autoloader (Synchronized with index.php)
spl_autoload_register(function ($class) {
    $prefixes = [
        'App\\' => APP_PATH . '/',
        'Core\\' => CORE_PATH . '/',
        'Plugins\\' => ROOT_PATH . '/plugins/',
    ];

    foreach ($prefixes as $prefix => $base_dir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }

        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});

use Core\Schedule;
use Core\Hook;

// 1. Load Plugins
\Core\PluginManager::load();

// 2. Register Core System Tasks
Schedule::register('core_cleanup_tokens', 'daily', function() use ($config) {
    try {
        $db = \Core\Database::getInstance($config['db']);
        $db->query("DELETE FROM user_tokens WHERE expires_at < NOW()");
    } catch (\Exception $e) {
        error_log("Cron Task Error (cleanup_tokens): " . $e->getMessage());
    }
});

Schedule::register('core_cleanup_ratelimit', 'daily', function() {
    $dir = ROOT_PATH . '/storage/ratelimit';
    if (is_dir($dir)) {
        $files = glob($dir . '/*.txt');
        $now = time();
        foreach ($files as $file) {
            if ($now - filemtime($file) > 86400) {
                @unlink($file);
            }
        }
    }
});

Schedule::register('core_cleanup_uploads', 'hourly', [\Core\Upload::class, 'cleanupOrphanedFiles']);

// 3. Trigger Global Hook for plugins to add their tasks
Hook::listen('system_schedule_register');

// 4. Run Schedule
Schedule::run();

echo "[" . date('Y-m-d H:i:s') . "] Cron tasks executed.\n";
