<?php require APP_PATH . '/Views/admin/header.php'; ?>
<?php require APP_PATH . '/Views/admin/sidebar.php'; ?>

<div class="md:pl-64 flex flex-col min-h-screen transition-all duration-300">
    <header class="h-16 bg-white/80 backdrop-blur-md border-b border-gray-100 flex items-center justify-between px-4 md:px-8 sticky top-0 z-40">
        <div class="flex items-center gap-4">
            <button onclick="toggleSidebar()" class="md:hidden text-gray-500 hover:text-black focus:outline-none mr-2">
                <i class="fas fa-bars text-xl"></i>
            </button>
             <a href="<?= url('/admin/categories') ?>" class="text-gray-400 hover:text-black transition-colors"><i class="fas fa-arrow-left"></i></a>
             <h2 class="text-lg font-bold text-gray-800">编辑分类</h2>
        </div>
    </header>

    <main class="p-8 flex-1 max-w-2xl mx-auto w-full">
        <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm">
            <form action="<?= url('/admin/categories/update/' . $category['id']) ?>" method="POST" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?= \Core\Csrf::generate() ?>">
                
                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 ml-1">名称 Name</label>
                    <input type="text" name="name" value="<?= e($category['name']) ?>" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-100 focus:bg-white focus:border-black focus:ring-0 transition-all font-bold" required>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 ml-1">别名 Slug</label>
                    <input type="text" name="slug" value="<?= e($category['slug']) ?>" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-100 focus:bg-white focus:border-black focus:ring-0 transition-all font-mono text-sm">
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 ml-1">描述 Description</label>
                    <textarea name="description" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-100 focus:bg-white focus:border-black focus:ring-0 transition-all h-32 resize-none"><?= e($category['description']) ?></textarea>
                </div>

                <button type="submit" class="w-full bg-black text-white font-bold py-3 rounded-xl hover:bg-gray-800 transition-all">保存修改</button>
            </form>
        </div>
    </main>
</div>

<?php require APP_PATH . '/Views/admin/footer.php'; ?>
