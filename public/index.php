<?php

/**
 * RanUI Blog Entry Point
 */

// Define absolute path constants
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CORE_PATH', ROOT_PATH . '/core');
define('PUBLIC_PATH', __DIR__);

// Load Configuration
$config = require APP_PATH . '/Config/config.php';

// Load Helpers
require CORE_PATH . '/helpers.php';

// Simple Autoloader
spl_autoload_register(function ($class) {
    // Prefix mapping
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

// Run the Application
try {
    $app = new Core\App($config);
    $app->run();
} catch (Throwable $e) {
    // Log the error
    if (class_exists('Core\Log')) {
        \Core\Log::error($e->getMessage(), ['trace' => $e->getTraceAsString(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
    }
    
    // API Error Handling
    if (strpos($_SERVER['REQUEST_URI'], '/api/') === 0) {
         header('Content-Type: application/json');
         header('HTTP/1.1 200 OK');
         echo json_encode([
             'code' => 500, 
             'msg' => 'System Error: ' . $e->getMessage(),
             'data' => $config['debug'] ? [
                 'file' => $e->getFile(),
                 'line' => $e->getLine(),
                 'trace' => explode("\n", $e->getTraceAsString())
             ] : null
         ]);
         exit;
    }

    // Display error
    header('HTTP/1.1 500 Internal Server Error');
    if ($config['debug']) {
        echo '<div style="font-family: monospace; background: #f8d7da; padding: 20px; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px;">';
        echo "<h2 style='margin-top:0'>Application Error</h2>";
        echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . " (Line: " . $e->getLine() . ")</p>";
        echo "<details><summary style='cursor:pointer; font-weight:bold'>Stack Trace</summary><pre style='overflow:auto; margin-top:10px'>" . htmlspecialchars($e->getTraceAsString()) . "</pre></details>";
        echo '</div>';
    } else {
        // Friendly 500 Page
        $siteTitle = 'Error';
        try { $siteTitle = defined('APP_PATH') ? 'System Error' : 'Error'; } catch(Exception $x){}
        
        echo <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>500 Internal Server Error</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background: #f9fafb; height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; color: #374151; }
        .container { text-align: center; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); max-width: 500px; width: 90%; }
        h1 { font-size: 24px; font-weight: 800; color: #111; margin-bottom: 10px; }
        p { color: #6b7280; font-size: 16px; line-height: 1.5; margin-bottom: 25px; }
        .btn { display: inline-block; background-color: #000; color: #fff; padding: 10px 20px; border-radius: 9999px; text-decoration: none; font-weight: 600; font-size: 14px; transition: opacity 0.2s; }
        .btn:hover { opacity: 0.8; }
        .icon { font-size: 48px; margin-bottom: 20px; display: block; }
    </style>
</head>
<body>
    <div class="container">
        <span class="icon">🚧</span>
        <h1>系统暂时不可用</h1>
        <p>服务器遇到了一些问题，我们已记录并正在紧急修复中。<br>请稍后刷新重试。</p>
        <a href="/" class="btn">返回首页</a>
    </div>
</body>
</html>
HTML;
    }
}
