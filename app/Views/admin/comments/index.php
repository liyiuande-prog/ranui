<?php require APP_PATH . '/Views/admin/header.php'; ?>
<?php require APP_PATH . '/Views/admin/sidebar.php'; ?>

<div class="md:pl-64 flex flex-col min-h-screen transition-all duration-300">
    <header class="h-16 bg-white/80 backdrop-blur-md border-b border-gray-100 flex items-center justify-between px-4 md:px-8 sticky top-0 z-40">
        <div class="flex items-center gap-4">
            <button onclick="toggleSidebar()" class="md:hidden text-gray-500 hover:text-black focus:outline-none">
                <i class="fas fa-bars text-xl"></i>
            </button>
            <h2 class="text-lg font-bold text-gray-800">评论互动</h2>
        </div>
        <a href="<?= url('/admin/comments/create') ?>" class="bg-black text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-gray-800 transition-colors">
             <i class="fas fa-plus mr-2"></i> 添加评论
        </a>
    </header>

    <main class="p-8 flex-1">
        <!-- Search -->
        <form class="mb-6 flex gap-3" method="GET">
            <div class="relative flex-1 max-w-sm">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" name="q" value="<?= e($search ?? '') ?>" placeholder="搜索评论 (内容/用户)..." class="w-full bg-white border border-gray-100 rounded-xl py-2 pl-10 pr-4 text-sm focus:ring-black focus:border-black outline-none transition-all shadow-sm">
            </div>
            <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-black transition-colors">
                搜索
            </button>
        </form>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-400 uppercase bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 font-bold w-16">ID</th>
                        <th class="px-6 py-4 font-bold">用户 Author</th>
                        <th class="px-6 py-4 font-bold max-w-xs">内容 Content</th>
                        <th class="px-6 py-4 font-bold">文章 Post</th>
                        <th class="px-6 py-4 font-bold">状态</th>
                        <th class="px-6 py-4 font-bold text-right">时间</th>
                        <th class="px-6 py-4 font-bold text-right">操作</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php foreach($comments as $c): ?>
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4 text-gray-500 font-mono text-xs"><?= $c['id'] ?></td>
                        <td class="px-6 py-4">
                             <div class="flex items-center gap-3">
                                <?php if(!empty($c['avatar'])): ?>
                                    <img src="<?= e($c['avatar']) ?>" class="w-8 h-8 rounded-full border border-gray-100">
                                <?php else: ?>
                                    <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold text-gray-500">
                                        <?= mb_substr($c['author_name'] ?? 'U', 0, 1) ?>
                                    </div>
                                <?php endif; ?>
                                <span class="font-bold text-gray-800"><?= e($c['author_name'] ?? 'Guest') ?></span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-600 max-w-xs truncate" title="<?= e($c['content']) ?>">
                            <?= e($c['content']) ?>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-blue-600 font-medium"><?= e(mb_substr($c['post_title'] ?? 'Deleted Post', 0, 20)) ?>...</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border 
                                <?= $c['status'] === 'approved' ? 'bg-green-50 text-green-600 border-green-100' : 'bg-yellow-50 text-yellow-600 border-yellow-100' ?>">
                                <?= $c['status'] === 'approved' ? '已批准' : '待审核' ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-gray-500 text-xs">
                            <?= date('M d, H:i', strtotime($c['created_at'])) ?>
                        </td>
                        <td class="px-6 py-4 text-right">
                             <div class="flex items-center justify-end gap-3">
                                <a href="<?= url('/admin/comments/edit/' . $c['id']) ?>" class="text-gray-400 hover:text-blue-600 transition-colors">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <button onclick="postAction('<?= url('/admin/comments/delete/' . $c['id']) ?>', '确定删除评论？')" class="text-gray-400 hover:text-red-600 transition-colors">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>

        <!-- Pagination -->
        <?php if(($pageCount ?? 1) > 1): ?>
        <div class="mt-6 flex justify-center gap-2">
            <?php for($i=1; $i<=$pageCount; $i++): ?>
                <a href="?page=<?= $i ?>&q=<?= e($search ?? '') ?>" class="w-8 h-8 flex items-center justify-center rounded-lg text-sm font-bold transition-colors <?= ($currentPage ?? 1) == $i ? 'bg-black text-white' : 'bg-white text-gray-500 hover:bg-gray-100' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </main>
</div>

<?php require APP_PATH . '/Views/admin/footer.php'; ?>
