<?php require APP_PATH . '/Views/admin/header.php'; ?>
<?php require APP_PATH . '/Views/admin/sidebar.php'; ?>

<div class="md:pl-64 flex flex-col min-h-screen transition-all duration-300">
    <header class="h-16 bg-white/80 backdrop-blur-md border-b border-gray-100 flex items-center justify-between px-4 md:px-8 sticky top-0 z-40">
        <div class="flex items-center gap-4">
            <button onclick="toggleSidebar()" class="md:hidden text-gray-500 hover:text-black focus:outline-none">
                <i class="fas fa-bars text-xl"></i>
            </button>
            <h2 class="text-lg font-bold text-gray-800">分类目录</h2>
        </div>
        <div>
            <a href="<?= url('/admin/categories/create') ?>" class="bg-black text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-gray-800 transition-colors">
                <i class="fas fa-plus mr-2"></i> 新建分类
            </a>
        </div>
    </header>

    <main class="p-8 flex-1">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-400 uppercase bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 font-bold w-16">ID</th>
                        <th class="px-6 py-4 font-bold">名称 Name</th>
                        <th class="px-6 py-4 font-bold">别名 Slug</th>
                        <th class="px-6 py-4 font-bold">文章数</th>
                        <th class="px-6 py-4 font-bold text-right">操作</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php foreach($categories as $c): ?>
                    <tr class="hover:bg-gray-50/50 transition-colors group">
                        <td class="px-6 py-4 text-gray-500 font-mono text-xs"><?= $c['id'] ?></td>
                        <td class="px-6 py-4 font-bold text-gray-800">
                            <?= e($c['name']) ?>
                            <?php if(!empty($c['description'])): ?>
                                <p class="text-xs text-gray-400 font-normal mt-1"><?= e($c['description']) ?></p>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-gray-500 font-mono text-xs"><?= e($c['slug']) ?></td>
                        <td class="px-6 py-4">
                             <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-600">
                                <?= $c['post_count'] ?> 篇
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-3">
                                <a href="<?= url('/admin/categories/edit/' . $c['id']) ?>" class="text-gray-400 hover:text-blue-600 transition-colors">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <a href="<?= url('/category/' . $c['id']) ?>" target="_blank" class="text-gray-400 hover:text-green-600 transition-colors">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button onclick="postAction('<?= url('/admin/categories/delete/' . $c['id']) ?>', '确定删除分类？')" class="text-gray-400 hover:text-red-600 transition-colors">
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
    </main>
</div>

<?php require APP_PATH . '/Views/admin/footer.php'; ?>
