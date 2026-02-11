<?php
// Stream Output
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

// Disable buffering
if (function_exists('apache_setenv')) {
    apache_setenv('no-gzip', 1);
}
@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);
for ($i = 0; $i < ob_get_level(); $i++) { ob_end_flush(); }
ob_implicit_flush(1);

function sendLog($msg, $status='log', $data=[]) {
    $out = array_merge(['status' => $status, 'msg' => $msg], $data);
    echo json_encode($out) . "\n";
    flush();
    if($status === 'error') exit;
}

// 1. Check Params
$host = $_POST['db_host'] ?? '127.0.0.1';
$port = $_POST['db_port'] ?? '3306';
$dbname = $_POST['db_name'] ?? 'ranui';
$user = $_POST['db_user'] ?? 'root';
$pass = $_POST['db_pass'] ?? '';
$adminUser = $_POST['admin_user'] ?? 'admin';
$adminPass = $_POST['admin_pass'] ?? 'admin123';

try {
    // 2. Connect DB
    sendLog("正在连接数据库 {$host}:{$port}...");
    $dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    sendLog("数据库连接成功!");

    // 3. Create Database
    sendLog("正在创建数据库/切换数据库 `{$dbname}`...");
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbname`");
    sendLog("数据库准备就绪");

    // 4. Update Config File
    sendLog("正在写入配置文件...");
    $configFile = __DIR__ . '/../app/Config/config.php';
    
    // Read template or create new
    // Read template or create new
    $configContent = "<?php

return [
    'app_name' => 'RanUI Blog',
    'base_url' => (isset(\$_SERVER['HTTPS']) && \$_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . (\$_SERVER['HTTP_HOST'] ?? ''),
    'debug' => true,
    
    // Database Configuration
    'db' => [
        'host' => '{$host}',
        'name' => '{$dbname}',
        'user' => '{$user}',
        'pass' => '{$pass}',
        'charset' => 'utf8mb4'
    ],
    
    // Theme Configuration
    'theme' => 'default',
];
";
    if (file_put_contents($configFile, $configContent) === false) {
        throw new Exception("无法写入配置文件，请检查 app/Config 目录权限");
    }
    sendLog("配置文件写入成功");

    // 5. Import SQL
    sendLog("开始导入数据表...");
    $sqlFile = __DIR__ . '/../ranui_db.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("找不到 ranui_db.sql 文件");
    }

    $sql = file_get_contents($sqlFile);
    // Remove comments to verify clean sql? No, PDO can handle it mostly but better split
    // Simple split by ; is dangerous if ; exists in content. 
    // But since this is a specific dump, let's try raw exec first if it supports multiple queries
    // PDO might not support multiple queries in one exec call depending on driver settings.
    // Let's safe-split.
    
    // Simple SQL splitter for dump files
    $lines = file($sqlFile);
    $templine = '';
    
    foreach ($lines as $line) {
        if (substr($line, 0, 2) == '--' || $line == '')
            continue;
            
        $templine .= $line;
        if (substr(trim($line), -1, 1) == ';') {
            try {
                $pdo->exec($templine);
                // Extract table name for log
                if (preg_match('/CREATE TABLE (?:IF NOT EXISTS )?`?(\w+)`?/', $templine, $m)) {
                    sendLog("创建表: {$m[1]}");
                }
            } catch (Exception $e) {
                // Ignore "Table already exists" or duplicate entry if reinstalling?
                // Warning only
                sendLog("SQL警告: " . $e->getMessage());
            }
            $templine = '';
        }
    }
    sendLog("数据表导入完成");

    // 6. Create Admin Account
    sendLog("正在配置管理员账号...");
    $hash = password_hash($adminPass, PASSWORD_DEFAULT);
    
    // Check if admin exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$adminUser]);
    if ($stmt->fetch()) {
        $pdo->prepare("UPDATE users SET password = ?, role = 'admin', status = 'active' WHERE username = ?")
            ->execute([$hash, $adminUser]);
        sendLog("管理员账号已存在，密码已重置");
    } else {
        $pdo->prepare("INSERT INTO users (uid, username, email, password, role, status, created_at) VALUES (?, ?, ?, ?, 'admin', 'active', NOW())")
            ->execute(['10001', $adminUser, 'admin@ranui.com', $hash]);
        sendLog("管理员账号创建成功");
    }

    // 7. Generate Lock File
    file_put_contents(__DIR__ . '/../install.lock', 'INSTALLED ON ' . date('Y-m-d H:i:s'));
    sendLog("生成安装锁文件...");

    sendLog("安装全部完成！", "success", ['admin' => $adminUser]);

} catch (Exception $e) {
    sendLog($e->getMessage(), 'error');
}
