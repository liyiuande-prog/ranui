<?php require APP_PATH . '/Views/admin/header.php'; ?>
<?php require APP_PATH . '/Views/admin/sidebar.php'; ?>

<div class="md:pl-64 flex flex-col min-h-screen transition-all duration-300">
    <header class="h-16 bg-white/80 backdrop-blur-md border-b border-gray-100 flex items-center justify-between px-4 md:px-8 sticky top-0 z-40">
        <div class="flex items-center gap-4">
            <button onclick="toggleSidebar()" class="md:hidden text-gray-500 hover:text-black focus:outline-none">
                <i class="fas fa-bars text-xl"></i>
            </button>
            <h2 class="text-lg font-bold text-gray-800">文章管理</h2>
        </div>
        <div>
            <a href="<?= url('/admin/posts/create') ?>" class="bg-black text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-gray-800 transition-colors">
                <i class="fas fa-plus mr-2"></i> 发布文章
            </a>
        </div>
    </header>

    <main class="p-8 flex-1">
        <!-- Search -->
        <form class="mb-6 flex gap-3" method="GET">
            <div class="relative flex-1 max-w-sm">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" name="q" value="<?= e($search ?? '') ?>" placeholder="搜索文章标题..." class="w-full bg-white border border-gray-100 rounded-xl py-2 pl-10 pr-4 text-sm focus:ring-black focus:border-black outline-none transition-all shadow-sm">
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
                            <th class="px-6 py-4 font-bold">标题</th>
                            <th class="px-6 py-4 font-bold">分类</th>
                            <th class="px-6 py-4 font-bold">作者</th>
                            <th class="px-6 py-4 font-bold">状态</th>
                            <th class="px-6 py-4 font-bold text-right">发布时间</th>
                            <th class="px-6 py-4 font-bold text-right">操作</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php foreach($posts as $p): ?>
                        <tr class="hover:bg-gray-50/50 transition-colors group">
                            <td class="px-6 py-4 text-gray-500 font-mono text-xs"><?= $p['id'] ?></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <?php if(!empty($p['is_pinned'])): ?>
                                        <span class="text-xs bg-red-50 text-red-500 px-2 py-0.5 rounded border border-red-100 font-bold whitespace-nowrap">
                                            <i class="fas fa-thumbtack mr-1"></i> TOP
                                        </span>
                                    <?php endif; ?>
                                    <p class="font-bold text-gray-800 group-hover:text-blue-600 transition-colors line-clamp-1"><?= e($p['title']) ?></p>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-600"><?= e($p['category_name']) ?></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-[10px] font-bold text-gray-500">
                                        <?= mb_substr($p['author_name'] ?? 'U', 0, 1) ?>
                                    </div>
                                    <span class="text-gray-600"><?= e($p['author_name']) ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border 
                                    <?= $p['status'] === 'published' ? 'bg-green-50 text-green-600 border-green-100' : 'bg-gray-50 text-gray-500 border-gray-100' ?>">
                                    <?= $p['status'] === 'published' ? '已发布' : '草稿' ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right text-gray-500 font-mono text-xs">
                                <?= date('Y-m-d', strtotime($p['created_at'])) ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="<?= url('/admin/posts/edit/' . $p['id']) ?>" class="text-gray-400 hover:text-blue-600 transition-colors" title="编辑">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <a href="<?= url('/' . $p['id'] . '.html') ?>" target="_blank" class="text-gray-400 hover:text-green-600 transition-colors" title="预览">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button onclick="postAction('<?= url('/admin/posts/delete/' . $p['id']) ?>', '确定要删除这篇文章吗？操作不可恢复。')" class="text-gray-400 hover:text-red-600 transition-colors" title="删除">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($posts)): ?>
                        <tr><td colspan="7" class="px-6 py-12 text-center text-gray-400 italic">暂无文章，点击右上角发布。</td></tr>
                        <?php endif; ?>
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
