<?php require APP_PATH . '/Views/admin/header.php'; ?>
<?php require APP_PATH . '/Views/admin/sidebar.php'; ?>
<?php 
$unreadCount = 0;
if (is_plugin_active('Ran_Notice')) {
    $db = \Core\Database::getInstance(config('db'));
    // Secondary check for table existence to be safe
    try {
        $unreadCount = $db->query("SELECT COUNT(*) FROM user_notifications WHERE user_id = ? AND is_read = 0", [$_SESSION['user']['id']])->fetchColumn();
    } catch (\Exception $e) {
        $unreadCount = 0;
    }
}
?>

<div class="md:pl-64 flex flex-col min-h-screen transition-all duration-300">
    <!-- Topbar -->
    <header class="h-16 bg-white/80 backdrop-blur-md border-b border-gray-100 flex items-center justify-between px-4 md:px-8 sticky top-0 z-40">
        <div class="flex items-center gap-4">
            <button onclick="toggleSidebar()" class="md:hidden text-gray-500 hover:text-black focus:outline-none">
                <i class="fas fa-bars text-xl"></i>
            </button>
            <h2 class="text-lg font-bold text-gray-800">仪表盘 (Dashboard)</h2>
        </div>
        <div class="flex items-center gap-4">
            <a href="<?= url('/admin/notifications') ?>" class="relative group p-2 rounded-full hover:bg-gray-100 transition-colors">
                <i class="fas fa-bell text-gray-400 group-hover:text-blue-500"></i>
                <?php if (($unreadCount ?? 0) > 0): ?>
                    <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 border-2 border-white rounded-full"></span>
                <?php endif; ?>
            </a>
            <a href="<?= url('/') ?>" target="_blank" class="text-sm font-bold text-gray-500 hover:text-black flex items-center gap-2">
                <i class="fas fa-external-link-alt"></i> 访问站点
            </a>
            <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-purple-500 to-pink-500 flex items-center justify-center text-white font-bold text-xs ring-2 ring-white shadow-lg">
                <?= strtoupper(substr($_SESSION['user']['username'] ?? 'A', 0, 1)) ?>
            </div>
        </div>
    </header>

    <main class="p-6 md:p-8 flex-1">
        
        <!-- Header / Welcome -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">早安, <?= e($_SESSION['user']['username']) ?></h1>
                <p class="text-gray-500 text-sm mt-1">今天也是充满活力的一天，来看看系统运行状况吧。</p>
            </div>
            <div class="flex items-center gap-3">
                 <span class="text-xs font-mono bg-gray-100 text-gray-500 px-3 py-1.5 rounded-full">v1.2.0</span>
                 <div class="h-8 w-px bg-gray-200"></div>
                 <span class="text-xs text-green-500 font-bold flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span> 运行正常
                 </span>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
            
            <!-- Main Content (Left 3 cols) -->
            <div class="xl:col-span-3 space-y-8">
                
                <!-- 1. Core Stats (Compact 4-col) -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
                        <div class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">今日营收</div>
                        <div class="flex items-end justify-between">
                            <span class="text-2xl font-black text-gray-800">¥<?= number_format($revenue_today, 2) ?></span>
                            <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center">
                                <i class="fas fa-wallet text-green-500 text-sm"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
                        <div class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">文章</div>
                        <div class="flex items-end justify-between">
                            <span class="text-2xl font-black text-gray-800"><?= number_format($stats['posts']) ?></span>
                            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                                <i class="fas fa-file-alt text-blue-500 text-sm"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
                        <div class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">用户</div>
                        <div class="flex items-end justify-between">
                            <span class="text-2xl font-black text-gray-800"><?= number_format($stats['users'] ?? 0) ?></span>
                            <div class="w-8 h-8 rounded-lg bg-orange-50 flex items-center justify-center">
                                <i class="fas fa-users text-orange-500 text-sm"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
                        <div class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">评论</div>
                        <div class="flex items-end justify-between">
                            <span class="text-2xl font-black text-gray-800"><?= number_format($stats['comments']) ?></span>
                            <div class="w-8 h-8 rounded-lg bg-pink-50 flex items-center justify-center">
                                <i class="fas fa-comments text-pink-500 text-sm"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Pending Tasks (Critical Section) -->
                <?php if (!empty($pending)): ?>
                <div>
                     <div class="flex items-center gap-2 mb-4 px-1">
                        <i class="fas fa-bell text-gray-400"></i>
                        <h3 class="font-bold text-gray-700">业务处理 <span class="text-xs font-normal text-gray-400 ml-2">待您审批</span></h3>
                     </div>
                     <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3">
                        <?php if (isset($pending['withdraw']) && $pending['withdraw'] > 0): ?>
                        <a href="<?= url('/admin/finance/withdraw') ?>" class="bg-white p-3 rounded-xl border border-red-100 shadow-sm hover:shadow-md transition-all group flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full bg-red-50 text-red-500 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                <i class="fas fa-university text-xs"></i>
                            </div>
                            <span class="text-[10px] text-gray-500 font-bold whitespace-nowrap">提现申请</span>
                            <span class="text-sm font-black text-red-600 mt-1"><?= $pending['withdraw'] ?></span>
                        </a>
                        <?php endif; ?>

                        <?php if (isset($pending['auth']) && $pending['auth'] > 0): ?>
                        <a href="<?= url('/admin/users?status=auth_pending') ?>" class="bg-white p-3 rounded-xl border border-yellow-100 shadow-sm hover:shadow-md transition-all group flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full bg-yellow-50 text-yellow-500 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                <i class="fas fa-id-card text-xs"></i>
                            </div>
                            <span class="text-[10px] text-gray-500 font-bold whitespace-nowrap">实名审核</span>
                            <span class="text-sm font-black text-yellow-600 mt-1"><?= $pending['auth'] ?></span>
                        </a>
                        <?php endif; ?>

                        <?php if (isset($pending['circle']) && $pending['circle'] > 0): ?>
                        <a href="<?= url('/admin/circles?status=0') ?>" class="bg-white p-3 rounded-xl border border-blue-100 shadow-sm hover:shadow-md transition-all group flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                <i class="fas fa-circle-notch text-xs"></i>
                            </div>
                            <span class="text-[10px] text-gray-500 font-bold whitespace-nowrap">圈子入驻</span>
                            <span class="text-sm font-black text-blue-600 mt-1"><?= $pending['circle'] ?></span>
                        </a>
                        <?php endif; ?>

                        <?php if (isset($pending['integral_order']) && $pending['integral_order'] > 0): ?>
                        <a href="<?= url('/admin/integral/orders?status=0') ?>" class="bg-white p-3 rounded-xl border border-purple-100 shadow-sm hover:shadow-md transition-all group flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full bg-purple-50 text-purple-500 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                <i class="fas fa-shipping-fast text-xs"></i>
                            </div>
                            <span class="text-[10px] text-gray-500 font-bold whitespace-nowrap">积分发货</span>
                            <span class="text-sm font-black text-purple-600 mt-1"><?= $pending['integral_order'] ?></span>
                        </a>
                        <?php endif; ?>

                        <?php if (isset($pending['merchant']) && $pending['merchant'] > 0): ?>
                        <a href="<?= url('/admin/merchants?status=pending') ?>" class="bg-white p-3 rounded-xl border border-indigo-100 shadow-sm hover:shadow-md transition-all group flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-500 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                <i class="fas fa-store text-xs"></i>
                            </div>
                            <span class="text-[10px] text-gray-500 font-bold whitespace-nowrap">商家申请</span>
                            <span class="text-sm font-black text-indigo-600 mt-1"><?= $pending['merchant'] ?></span>
                        </a>
                        <?php endif; ?>

                        <?php if (isset($pending['link']) && $pending['link'] > 0): ?>
                        <a href="<?= url('/admin/links?status=0') ?>" class="bg-white p-3 rounded-xl border border-emerald-100 shadow-sm hover:shadow-md transition-all group flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                <i class="fas fa-link text-xs"></i>
                            </div>
                            <span class="text-[10px] text-gray-500 font-bold whitespace-nowrap">友情链接</span>
                            <span class="text-sm font-black text-emerald-600 mt-1"><?= $pending['link'] ?></span>
                        </a>
                        <?php endif; ?>

                        <?php if (isset($pending['appeal']) && $pending['appeal'] > 0): ?>
                        <a href="<?= url('/admin/punish/appeals') ?>" class="bg-white p-3 rounded-xl border border-orange-100 shadow-sm hover:shadow-md transition-all group flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full bg-orange-50 text-orange-500 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                <i class="fas fa-exclamation-triangle text-xs"></i>
                            </div>
                            <span class="text-[10px] text-gray-500 font-bold whitespace-nowrap">处罚申诉</span>
                            <span class="text-sm font-black text-orange-600 mt-1"><?= $pending['appeal'] ?></span>
                        </a>
                        <?php endif; ?>
                     </div>
                </div>
                <?php endif; ?>

                <!-- 2. Plugins Grid (Functional Modules) -->
                <div>
                     <div class="flex items-center gap-2 mb-4 px-1">
                        <i class="fas fa-layer-group text-gray-400"></i>
                        <h3 class="font-bold text-gray-700">功能概览</h3>
                     </div>
                     <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <?php
                        $pluginDir = ROOT_PATH . '/plugins';
                        if (is_dir($pluginDir)) {
                            $plugins = array_filter(glob($pluginDir . '/*'), 'is_dir');
                            foreach ($plugins as $path) {
                                $name = basename($path);
                                
                                // Check if plugin is active before loading widget
                                if (function_exists('is_plugin_active') && !is_plugin_active($name)) {
                                    continue;
                                }

                                $className = "Plugins\\$name\\Plugin";
                                if (class_exists($className)) {
                                    $widgetFile = $path . '/views/admin/widget.php';
                                    if (file_exists($widgetFile)) include $widgetFile;
                                }
                            }
                        }
                        ?>
                     </div>
                </div>

                <!-- 3. Recent Posts & Charts Row -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Recent Posts -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="font-bold text-gray-800">最新发布</h3>
                            <a href="<?= url('/admin/posts') ?>" class="text-xs font-bold bg-gray-50 hover:bg-black hover:text-white px-3 py-1.5 rounded-lg transition-colors">管理文章</a>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="text-gray-400 font-medium border-b border-gray-50">
                                    <tr>
                                        <th class="pb-3 pl-2">标题</th>
                                        <th class="pb-3 w-16">状态</th>
                                        <th class="pb-3 text-right pr-2 w-24">时间</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    <?php foreach($recent_posts as $post): ?>
                                    <tr class="group hover:bg-gray-50 transition-colors">
                                        <td class="py-3 pl-2 font-medium text-gray-700 group-hover:text-black truncate max-w-[200px]">
                                            <?= e($post['title']) ?>
                                        </td>
                                        <td class="py-3">
                                            <span class="text-xs text-gray-500"><?= $post['status'] == 'published' ? '发布' : '草稿' ?></span>
                                        </td>
                                        <td class="py-3 text-right pr-2 text-gray-400 text-xs tabular-nums">
                                            <?= date('m/d H:i', strtotime($post['created_at'])) ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Revenue Chart -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-bold text-gray-800">营收走势 (7天)</h3>
                            <div class="text-xs text-green-500 font-bold">¥<?= number_format(array_sum($chart_data['revenue'] ?? []), 2) ?></div>
                        </div>
                        <div style="height: 200px; position: relative;">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- 4. Traffic Chart (Wide) -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h3 class="font-bold text-gray-800 mb-6">访问统计 (PV)</h3>
                    <div style="height: 250px; position: relative;">
                        <canvas id="trafficChart"></canvas>
                    </div>
                </div>

            </div>

            <!-- Right Sidebar (1 col) -->
            <div class="space-y-6">
                
                <!-- Quick Actions -->
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                    <h3 class="font-bold text-gray-800 mb-4 text-sm">快捷入口</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <a href="<?= url('/admin/posts/create') ?>" class="flex flex-col items-center justify-center p-3 rounded-xl bg-gray-50 hover:bg-blue-50 hover:text-blue-600 transition-colors gap-2 text-gray-600">
                            <i class="fas fa-feather-alt text-lg"></i>
                            <span class="text-xs font-bold">写文章</span>
                        </a>
                        <a href="<?= url('/admin/themes') ?>" class="flex flex-col items-center justify-center p-3 rounded-xl bg-gray-50 hover:bg-purple-50 hover:text-purple-600 transition-colors gap-2 text-gray-600">
                            <i class="fas fa-paint-brush text-lg"></i>
                            <span class="text-xs font-bold">主题</span>
                        </a>
                        <a href="<?= url('/admin/users') ?>" class="flex flex-col items-center justify-center p-3 rounded-xl bg-gray-50 hover:bg-orange-50 hover:text-orange-600 transition-colors gap-2 text-gray-600">
                            <i class="fas fa-user-plus text-lg"></i>
                            <span class="text-xs font-bold">用户</span>
                        </a>
                        <a href="<?= url('/admin/options') ?>" class="flex flex-col items-center justify-center p-3 rounded-xl bg-gray-50 hover:bg-gray-200 hover:text-black transition-colors gap-2 text-gray-600">
                            <i class="fas fa-sliders-h text-lg"></i>
                            <span class="text-xs font-bold">设置</span>
                        </a>
                    </div>
                </div>

                <!-- System Info Card -->
                <div class="bg-gray-900 text-gray-300 p-6 rounded-2xl shadow-lg relative overflow-hidden">
                    <div class="relative z-10 space-y-4">
                        <h3 class="text-white font-bold mb-4 flex items-center gap-2">
                            <i class="fas fa-server"></i> 系统信息
                        </h3>
                        
                        <div class="flex justify-between items-center text-sm border-b border-white/10 pb-2">
                            <span>服务器</span>
                            <span class="font-mono text-white"><?= php_uname('s') ?></span>
                        </div>
                        <div class="flex justify-between items-center text-sm border-b border-white/10 pb-2">
                            <span>PHP 版本</span>
                            <span class="font-mono text-white"><?= PHP_VERSION ?></span>
                        </div>
                        <div class="flex justify-between items-center text-sm border-b border-white/10 pb-2">
                            <span>数据库</span>
                            <span class="font-mono text-white">MySQL</span>
                        </div>
                        <div class="flex justify-between items-center text-sm text-center">
                             <div class="text-xs text-gray-500 w-full pt-2">Powered by RanUI Framework</div>
                        </div>
                    </div>
                    
                    <!-- Decor -->
                    <div class="absolute -bottom-10 -right-10 w-32 h-32 bg-indigo-600/20 rounded-full blur-2xl"></div>
                </div>

            </div>
        </div>

    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctxTraffic = document.getElementById('trafficChart').getContext('2d');
        const trafficChart = new Chart(ctxTraffic, {
            type: 'line',
            data: {
                labels: <?= json_encode($chart_data['labels'] ?? []) ?>,
                datasets: [{
                    label: '访问量 (PV)',
                    data: <?= json_encode($chart_data['visits'] ?? []) ?>,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.05)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [5, 5], drawBorder: false } },
                    x: { grid: { display: false } }
                }
            }
        });

        const ctxRevenue = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(ctxRevenue, {
            type: 'bar',
            data: {
                labels: <?= json_encode($chart_data['labels'] ?? []) ?>,
                datasets: [{
                    label: '营收 (元)',
                    data: <?= json_encode($chart_data['revenue'] ?? []) ?>,
                    backgroundColor: '#10b981',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [5, 5], drawBorder: false } },
                    x: { grid: { display: false } }
                }
            }
        });
    });
    </script>
    
    <?php require APP_PATH . '/Views/admin/footer.php'; ?>
