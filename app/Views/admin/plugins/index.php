<?php require APP_PATH . '/Views/admin/header.php'; ?>
<?php require APP_PATH . '/Views/admin/sidebar.php'; ?>

<div class="md:pl-64 flex flex-col min-h-screen transition-all duration-300">
    <header class="h-16 bg-white/80 backdrop-blur-md border-b border-gray-100 flex items-center justify-between px-4 md:px-8 sticky top-0 z-40">
        <div class="flex items-center gap-4">
            <button onclick="toggleSidebar()" class="md:hidden text-gray-500 hover:text-black focus:outline-none">
                <i class="fas fa-bars text-xl"></i>
            </button>
            <h2 class="text-lg font-bold text-gray-800">插件管理</h2>
        </div>
    </header>

    <main class="p-8 flex-1">
        <!-- Search Box -->
        <div class="mb-6 flex gap-3">
            <div class="relative flex-1 max-w-sm">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" id="pluginSearch" placeholder="搜索插件 (名称/描述)..." class="w-full bg-white border border-gray-100 rounded-xl py-2 pl-10 pr-4 text-sm focus:ring-black focus:border-black outline-none transition-all shadow-sm">
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-400 uppercase bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 font-bold">插件名称 Name</th>
                        <th class="px-6 py-4 font-bold">版本 Version</th>
                        <th class="px-6 py-4 font-bold">安装时间 Installed</th>
                        <th class="px-6 py-4 font-bold text-right">状态 Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php foreach($plugins as $p): ?>
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center shrink-0">
                                <i class="fas fa-puzzle-piece"></i>
                            </div>
                            <div class="flex flex-col">
                                <span class="font-bold text-gray-800 text-sm"><?= e($p['displayName'] ?? $p['name']) ?></span>
                                <span class="text-xs text-gray-500 line-clamp-1" title="<?= e($p['description'] ?? '') ?>">
                                    <?= e($p['description'] ?? '暂无描述') ?>
                                </span>
                                <span class="text-[10px] text-gray-400 font-mono mt-0.5"><?= e($p['name']) ?></span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-500 font-mono text-xs">v<?= e($p['version']) ?></td>
                        <td class="px-6 py-4 text-gray-500 text-xs text-center">
                             <?= $p['installed_at'] ? date('Y-m-d', strtotime($p['installed_at'])) : '<span class="text-gray-300">-</span>' ?>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end items-center gap-4">
                                <?php if ($p['is_installed']): ?>
                                    <!-- Toggle Status -->
                                    <?php if($p['is_active']): ?>
                                        <button onclick="postAction('<?= url('/admin/plugins/toggle/' . $p['id']) ?>')" class="inline-flex items-center gap-1 text-green-600 hover:text-green-700 text-xs font-bold bg-green-50 px-3 py-1.5 rounded-full border border-green-100 transition-colors cursor-pointer">
                                            <i class="fas fa-check-circle"></i> 运行中
                                        </button>
                                    <?php else: ?>
                                        <button onclick="postAction('<?= url('/admin/plugins/toggle/' . $p['id']) ?>')" class="inline-flex items-center gap-1 text-gray-400 hover:text-gray-600 text-xs font-bold bg-gray-50 px-3 py-1.5 rounded-full border border-gray-100 transition-colors cursor-pointer">
                                            <i class="fas fa-pause-circle"></i> 已停用
                                        </button>
                                    <?php endif; ?>

                                    <!-- Uninstall -->
                                    <button onclick="postAction('<?= url('/admin/plugins/uninstall/' . $p['id']) ?>', '确定要卸载此插件吗？此操作将删除数据库中的插件记录。')" class="text-gray-300 hover:text-red-500 transition-colors" title="卸载插件">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                <?php else: ?>
                                    <!-- Install Button -->
                                    <button onclick="postAction('<?= url('/admin/plugins/install/' . $p['name']) ?>')" class="bg-black text-white px-4 py-1.5 rounded-lg text-xs font-bold hover:bg-gray-800 transition-colors shadow-lg shadow-black/10">
                                        <i class="fas fa-download mr-1"></i> 安装
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($plugins)): ?>
                        <tr><td colspan="4" class="px-6 py-12 text-center text-gray-400 italic">plugins/ 目录下未发现插件。</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>
        
        <div class="mt-6 p-4 bg-gray-50 rounded-xl border border-gray-100 text-xs text-gray-500">
             <i class="fas fa-info-circle mr-1"></i> 提示：将插件文件夹上传至 <code>/plugins/</code> 目录，刷新此页面即可自动识别并安装。
        </div>
    </main>
</div>
<script>
document.getElementById('pluginSearch').addEventListener('input', function(e) {
    const term = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(term) ? '' : 'none';
    });
});
</script>
<?php require APP_PATH . '/Views/admin/footer.php'; ?>
