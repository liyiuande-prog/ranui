<?php require APP_PATH . '/Views/admin/header.php'; ?>
<?php require APP_PATH . '/Views/admin/sidebar.php'; ?>

<div class="md:pl-64 flex flex-col min-h-screen transition-all duration-300">
    <!-- Topbar -->
    <header class="h-16 bg-white/80 backdrop-blur-md border-b border-gray-100 flex items-center justify-between px-4 md:px-8 sticky top-0 z-40">
        <div class="flex items-center gap-4">
            <button onclick="toggleSidebar()" class="md:hidden text-gray-500 hover:text-black focus:outline-none">
                <i class="fas fa-bars text-xl"></i>
            </button>
            <h2 class="text-lg font-bold text-gray-800">系统通知</h2>
        </div>
        <div class="flex items-center gap-4">
            <a href="<?= url('/admin') ?>" class="text-sm font-bold text-gray-500 hover:text-black">
                返回仪表盘
            </a>
        </div>
    </header>

    <main class="p-6 md:p-8 flex-1">
        <div class="max-w-4xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-800">通知中心</h1>
                <span class="text-xs text-gray-400">最近 50 条消息</span>
            </div>

            <div class="space-y-4">
                <?php if (empty($notifications)): ?>
                    <div class="bg-white rounded-2xl p-12 border border-gray-100 shadow-sm text-center">
                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-bell-slash text-gray-300 text-2xl"></i>
                        </div>
                        <p class="text-gray-500">暂无任何通知</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($notifications as $notice): ?>
                        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition-all <?= $notice['is_read'] ? 'opacity-75' : 'border-l-4 border-l-blue-500' ?>">
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center shrink-0">
                                    <i class="fas <?= $notice['is_read'] ? 'fa-envelope-open' : 'fa-envelope' ?>"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2 mb-1">
                                        <h3 class="font-bold text-gray-800 truncate"><?= e($notice['title']) ?></h3>
                                        <span class="text-[10px] text-gray-400 tabular-nums shrink-0"><?= date('Y-m-d H:i', strtotime($notice['created_at'])) ?></span>
                                    </div>
                                    <p class="text-sm text-gray-500 leading-relaxed mb-3"><?= e($notice['content']) ?></p>
                                    
                                    <div class="flex items-center gap-3">
                                        <?php if (!empty($notice['link'])): ?>
                                            <a href="<?= url('/admin/notifications/read/' . $notice['id']) ?>" class="text-xs font-bold text-blue-600 hover:bg-blue-50 px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1.5">
                                                立即查看 <i class="fas fa-arrow-right text-[10px]"></i>
                                            </a>
                                        <?php elseif (!$notice['is_read']): ?>
                                            <a href="<?= url('/admin/notifications/read/' . $notice['id']) ?>" class="text-xs font-bold text-gray-500 hover:bg-gray-100 px-3 py-1.5 rounded-lg transition-colors">
                                                标记已读
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php require APP_PATH . '/Views/admin/footer.php'; ?>
