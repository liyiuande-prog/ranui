<?php
// Handle installation POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['db_host'])) {
    require __DIR__ . '/process.php';
    exit;
}

// Function to check if installed
function is_installed() {
    return file_exists(__DIR__ . '/../install.lock');
}

// Check if already installed, redirect to step 4 (security warning)
if (is_installed() && !isset($_GET['step'])) {
    header('Location: /install?step=4');
    exit;
}

$step = $_GET['step'] ?? 1;
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RanUI Blog 安装程序</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">

<div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden">
    <!-- Header -->
    <div class="bg-black text-white p-6">
        <h1 class="text-2xl font-bold"><i class="fas fa-rocket mr-2"></i> RanUI Blog 安装向导</h1>
        <p class="text-gray-400 text-sm mt-1">几分钟即可完成部署</p>
    </div>

    <!-- Steps -->
    <div class="flex border-b border-gray-100">
        <div class="flex-1 p-4 text-center <?= $step == 1 ? 'text-black font-bold border-b-2 border-black' : 'text-gray-400' ?>">
            1. 环境检测
        </div>
        <div class="flex-1 p-4 text-center <?= $step == 2 ? 'text-black font-bold border-b-2 border-black' : 'text-gray-400' ?>">
            2. 数据库配置
        </div>
        <div class="flex-1 p-4 text-center <?= $step == 3 ? 'text-black font-bold border-b-2 border-black' : 'text-gray-400' ?>">
            3. 安装完成
        </div>
        <div class="flex-1 p-4 text-center <?= $step == 4 ? 'text-black font-bold border-b-2 border-black' : 'text-gray-400' ?>">
            4. 安全提示
        </div>
    </div>

    <!-- Content -->
    <div class="p-8">
        <?php if ($step == 1): ?>
            <h2 class="text-xl font-bold mb-6">环境检测</h2>
            <ul class="space-y-3 mb-8">
                <?php
                $phpVersion = phpversion();
                $pdo = extension_loaded('pdo_mysql');
                $gd = extension_loaded('gd');
                $curl = extension_loaded('curl');
                
                $checks = [
                    'PHP 版本 >= 7.4' => version_compare($phpVersion, '7.4.0', '>='),
                    'PDO MySQL 扩展' => $pdo,
                    'GD 图形库' => $gd,
                    'CURL 扩展' => $curl,
                    'Config 目录可写' => is_writable(__DIR__ . '/../app/Config')
                ];
                
                $allOk = true;
                foreach ($checks as $name => $ok) {
                    if (!$ok) $allOk = false;
                    echo "<li class='flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-100'>";
                    echo "<span>{$name}</span>";
                    echo $ok ? "<span class='text-green-600 font-bold'><i class='fas fa-check'></i> 通过</span>" : "<span class='text-red-500 font-bold'><i class='fas fa-times'></i> 失败</span>";
                    echo "</li>";
                }
                ?>
            </ul>

            <div class="text-right">
                <?php if ($allOk): ?>
                    <a href="?step=2" class="inline-block bg-black text-white px-6 py-3 rounded-lg font-bold hover:opacity-80 transition">下一步 <i class="fas fa-arrow-right ml-2"></i></a>
                <?php else: ?>
                    <button disabled class="bg-gray-300 text-white px-6 py-3 rounded-lg font-bold cursor-not-allowed">请修复环境问题</button>
                <?php endif; ?>
            </div>

        <?php elseif ($step == 2): ?>
            <h2 class="text-xl font-bold mb-6">数据库配置</h2>
            <form action="install.php?action=install" method="POST" class="space-y-4" onsubmit="return startInstall(this)">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">数据库主机</label>
                        <input type="text" name="db_host" value="127.0.0.1" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-black focus:border-transparent outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">数据库端口</label>
                        <input type="text" name="db_port" value="3306" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-black focus:border-transparent outline-none">
                    </div>
                </div>
                <div>
                     <label class="block text-sm font-bold text-gray-700 mb-1">数据库名</label>
                     <input type="text" name="db_name" value="ranui" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-black focus:border-transparent outline-none">
                </div>
                <div>
                     <label class="block text-sm font-bold text-gray-700 mb-1">数据库用户</label>
                     <input type="text" name="db_user" value="root" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-black focus:border-transparent outline-none">
                </div>
                <div>
                     <label class="block text-sm font-bold text-gray-700 mb-1">数据库密码</label>
                     <input type="password" name="db_pass" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-black focus:border-transparent outline-none">
                </div>

                <hr class="border-gray-100 my-4">

                <h3 class="font-bold text-lg mb-2">管理员账号</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">管理员用户名</label>
                        <input type="text" name="admin_user" value="admin" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-black focus:border-transparent outline-none">
                    </div>
                     <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">管理员密码</label>
                        <input type="text" name="admin_pass" value="admin123" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-black focus:border-transparent outline-none">
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit" id="installBtn" class="w-full bg-black text-white px-6 py-3 rounded-lg font-bold hover:opacity-80 transition flex items-center justify-center">
                        <span>开始安装</span>
                        <i class="fas fa-hammer ml-2"></i>
                    </button>
                </div>
                
                <!-- Install Log -->
                <div id="installLog" class="hidden mt-4 p-4 bg-gray-900 text-green-400 font-mono text-xs rounded-lg h-48 overflow-y-auto mb-4"></div>
            </form>

            <script>
                function startInstall(form) {
                    const btn = document.getElementById('installBtn');
                    const log = document.getElementById('installLog');
                    
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> 安装中...';
                    log.classList.remove('hidden');
                    log.innerHTML = '> 开始连接数据库...\n';

                    const formData = new FormData(form);

                   fetch('/install', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        const reader = response.body.getReader();
                        const decoder = new TextDecoder();
                        
                        function read() {
                            reader.read().then(({done, value}) => {
                                if (done) {
                                    return;
                                }
                                const text = decoder.decode(value);
                                // Parse custom log format
                                const lines = text.split('\n');
                                lines.forEach(line => {
                                    if(line.trim() === '') return;
                                    try {
                                        const json = JSON.parse(line);
                                        if (json.status === 'log') {
                                            log.innerHTML += '> ' + json.msg + '\n';
                                            log.scrollTop = log.scrollHeight;
                                        } else if (json.status === 'success') {
                                             window.location.href = '?step=3&admin=' + json.admin;
                                        } else if (json.status === 'error') {
                                            log.innerHTML += '> [ERROR] ' + json.msg + '\n';
                                            btn.disabled = false;
                                            btn.innerHTML = '重试安装';
                                            alert('安装出错: ' + json.msg);
                                        }
                                    } catch(e) {
                                        // Ignore raw text if any
                                    }
                                });
                                read();
                            });
                        }
                        read();
                    })
                    .catch(err => {
                        log.innerHTML += '> [Network Error] ' + err + '\n';
                        btn.disabled = false;
                        btn.innerHTML = '重试安装';
                    });

                    return false;
                }
            </script>

        <?php elseif ($step == 3): ?>
            <div class="text-center py-8">
                <div class="w-20 h-20 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-6 text-4xl text-white shadow-lg shadow-green-500/30">
                    <i class="fas fa-check"></i>
                </div>
                <h2 class="text-3xl font-bold mb-2">安装成功!</h2>
                <p class="text-gray-500 mb-8">RanUI Blog 已成功部署到您的服务器。</p>

                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 text-left mb-8">
                    <h3 class="font-bold text-yellow-800 mb-2"><i class="fas fa-exclamation-triangle mr-2"></i>重要安全提示</h3>
                    <p class="text-sm text-yellow-700 mb-2">为了系统安全，请务必执行以下操作：</p>
                    <ul class="list-disc list-inside text-sm text-yellow-700 space-y-1 ml-2">
                        <li>删除 <b>/install</b> 目录 (必须删除才能访问首页)</li>
                        <li>修改默认管理员密码</li>
                    </ul>
                </div>

                <div class="grid grid-cols-2 gap-4">
                     <a href="/admin" class="block p-4 bg-gray-50 border border-gray-200 rounded-xl hover:border-black transition group">
                        <div class="font-bold text-gray-800 group-hover:text-black">后台管理</div>
                        <div class="text-xs text-gray-500 mt-1">/admin</div>
                    </a>
                    <a href="/" class="block p-4 bg-gray-50 border border-gray-200 rounded-xl hover:border-black transition group">
                        <div class="font-bold text-gray-800 group-hover:text-black">访问首页</div>
                        <div class="text-xs text-gray-500 mt-1">/</div>
                    </a>
                </div>
            </div>
            
            <?php
             // Check if user came from success
             if(isset($_GET['admin'])) {
                 echo "<div class='mt-4 text-center text-sm text-gray-500'>管理员账号: ".htmlspecialchars($_GET['admin'])." (密码为您设置的密码)</div>";
             }
            ?>
        
        <?php elseif ($step == 4): ?>
            <?php
            // Try to read admin info from database
            $adminInfo = null;
            try {
                $configFile = __DIR__ . '/../app/Config/config.php';
                if (file_exists($configFile)) {
                    $config = require $configFile;
                    if (isset($config['db'])) {
                        $dsn = "mysql:host={$config['db']['host']};dbname={$config['db']['name']};charset=utf8mb4";
                        $pdo = new PDO($dsn, $config['db']['user'], $config['db']['pass']);
                        $stmt = $pdo->query("SELECT username, email FROM users WHERE role='admin' LIMIT 1");
                        $adminInfo = $stmt->fetch(PDO::FETCH_ASSOC);
                    }
                }
            } catch (Exception $e) {
                // Ignore errors
            }
            ?>
            <div class="text-center py-8">
                <div class="w-20 h-20 bg-red-500 rounded-full flex items-center justify-center mx-auto mb-6 text-4xl text-white shadow-lg shadow-red-500/30">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h2 class="text-3xl font-bold mb-2 text-red-600">安全警告</h2>
                <p class="text-gray-500 mb-8">系统已安装完成，但检测到安装目录仍然存在</p>

                <?php if ($adminInfo): ?>
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 text-left mb-6">
                    <h3 class="font-bold text-blue-800 mb-3"><i class="fas fa-user-shield mr-2"></i>管理员账号信息</h3>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <span class="text-sm text-gray-600 w-24">用户名：</span>
                            <code class="bg-blue-100 px-3 py-1 rounded text-blue-900 font-mono"><?= htmlspecialchars($adminInfo['username']) ?></code>
                        </div>
                        <div class="flex items-center">
                            <span class="text-sm text-gray-600 w-24">邮箱：</span>
                            <code class="bg-blue-100 px-3 py-1 rounded text-blue-900 font-mono"><?= htmlspecialchars($adminInfo['email']) ?></code>
                        </div>
                        <div class="flex items-center">
                            <span class="text-sm text-gray-600 w-24">密码：</span>
                            <span class="text-sm text-gray-500">您在安装时设置的密码</span>
                        </div>
                        <div class="flex items-center">
                            <span class="text-sm text-gray-600 w-24">后台地址：</span>
                            <code class="bg-blue-100 px-3 py-1 rounded text-blue-900 font-mono">/admin</code>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="bg-red-50 border-2 border-red-300 rounded-xl p-8 text-left mb-8">
                    <h3 class="font-bold text-red-800 mb-4 text-lg"><i class="fas fa-shield-alt mr-2"></i>重要安全警告</h3>
                    <p class="text-red-700 mb-4 leading-relaxed">
                        检测到根目录安装文件夹 <code class="bg-red-200 px-2 py-1 rounded font-mono">/install</code> 仍然存在。
                    </p>
                    <p class="text-red-700 mb-4 font-bold">
                        为了系统安全，请立即手动删除该目录，然后刷新页面访问首页。
                    </p>
                    <div class="bg-white border border-red-200 rounded-lg p-4 mt-4">
                        <p class="text-sm text-gray-600 mb-2"><i class="fas fa-terminal mr-2"></i>删除命令：</p>
                        <code class="block bg-gray-900 text-green-400 p-3 rounded font-mono text-sm">
                            rm -rf /Applications/EServer/www/ranui.test/install
                        </code>
                    </div>
                </div>

                <div class="text-sm text-gray-500 mb-6">
                    <p>删除 <b>/install</b> 目录后，点击下方按钮即可访问网站。</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                     <a href="/admin" class="block p-4 bg-gradient-to-r from-black to-gray-800 text-white rounded-xl hover:opacity-90 transition group shadow-lg">
                        <div class="font-bold"><i class="fas fa-cog mr-2"></i>后台管理</div>
                        <div class="text-xs opacity-75 mt-1">/admin</div>
                    </a>
                    <a href="/" class="block p-4 bg-gradient-to-r from-blue-600 to-blue-500 text-white rounded-xl hover:opacity-90 transition group shadow-lg">
                        <div class="font-bold"><i class="fas fa-home mr-2"></i>访问首页</div>
                        <div class="text-xs opacity-75 mt-1">/</div>
                    </a>
                </div>
                
                <div class="mt-4 text-xs text-gray-400">
                    <i class="fas fa-info-circle mr-1"></i>提示：删除 /install 目录前，这些链接将无法正常访问
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
