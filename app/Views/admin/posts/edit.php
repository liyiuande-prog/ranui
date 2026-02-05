<?php require APP_PATH . '/Views/admin/header.php'; ?>
<?php require APP_PATH . '/Views/admin/sidebar.php'; ?>

<div class="md:pl-64 flex flex-col min-h-screen transition-all duration-300">
    <header class="h-16 bg-white/80 backdrop-blur-md border-b border-gray-100 flex items-center justify-between px-4 md:px-8 sticky top-0 z-40">
        <div class="flex items-center gap-4">
            <button onclick="toggleSidebar()" class="md:hidden text-gray-500 hover:text-black focus:outline-none mr-2">
                <i class="fas fa-bars text-xl"></i>
            </button>
             <a href="<?= url('/admin/posts') ?>" class="text-gray-400 hover:text-black transition-colors"><i class="fas fa-arrow-left"></i></a>
             <h2 class="text-lg font-bold text-gray-800">编辑文章</h2>
        </div>
        <div class="flex items-center gap-4">
             <a href="<?= url('/' . $post['id'] . '.html') ?>" target="_blank" class="text-gray-500 hover:text-black text-sm font-bold transition-colors">
                <i class="fas fa-external-link-alt mr-1"></i> 预览
            </a>
            <button type="submit" form="postForm" class="bg-black text-white px-6 py-2 rounded-lg text-sm font-bold hover:bg-gray-800 transition-colors shadow-lg shadow-black/20">
                <i class="fas fa-save mr-2"></i> 保存修改
            </button>
        </div>
    </header>

    <main class="p-8 flex-1 max-w-6xl mx-auto w-full">
        <form id="postForm" action="<?= url('/admin/posts/update/' . $post['id']) ?>" method="POST" class="space-y-8">
            <input type="hidden" name="csrf_token" value="<?= \Core\Csrf::generate() ?>">
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left: Editor -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="space-y-2">
                        <input type="text" name="title" value="<?= e($post['title']) ?>" class="w-full px-0 py-4 bg-transparent border-0 border-b-2 border-gray-100 focus:border-black focus:ring-0 transition-all text-3xl font-extrabold placeholder-gray-300" placeholder="输入文章标题..." required>
                    </div>
                    
                    <?php
                        $name = 'content';
                        $id = 'editor';
                        $content = $post['content'] ?? '';
                        $placeholder = '开始撰写您的精彩内容 (Markdown supported)...';
                        require APP_PATH . '/Views/components/editor.php';
                    ?>
                </div>
                
                <!-- Right: Settings -->
                <div class="space-y-6">
                    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-5">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="fas fa-sliders-h text-gray-400"></i>
                            <h3 class="font-bold text-gray-800 text-sm">基本设置</h3>
                        </div>
                        
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider">分类 Category</label>
                            <select name="category_id" class="w-full px-3 py-2.5 rounded-lg bg-gray-50 border-transparent focus:bg-white focus:border-black focus:ring-0 text-sm font-medium transition-all">
                                <?php foreach($categories as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= $c['id'] == $post['category_id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider">发布状态 Status</label>
                            <select name="status" class="w-full px-3 py-2.5 rounded-lg bg-gray-50 border-transparent focus:bg-white focus:border-black focus:ring-0 text-sm font-medium transition-all">
                                <option value="published" <?= $post['status'] == 'published' ? 'selected' : '' ?>>立即发布 (Published)</option>
                                <option value="draft" <?= $post['status'] == 'draft' ? 'selected' : '' ?>>存为草稿 (Draft)</option>
                            </select>
                        </div>
                        
                        <div class="flex items-center gap-3 pt-2">
                             <input type="checkbox" name="is_pinned" id="is_pinned" value="1" <?= ($post['is_pinned'] ?? 0) ? 'checked' : '' ?> class="w-4 h-4 rounded border-gray-300 text-black focus:ring-black/20">
                             <label for="is_pinned" class="text-sm font-bold text-gray-700 select-none cursor-pointer">
                                 置顶推荐 (Pin to Top)
                             </label>
                        </div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-5">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="fas fa-search text-gray-400"></i>
                            <h3 class="font-bold text-gray-800 text-sm">SEO & 媒体</h3>
                        </div>
                        
                         <div class="space-y-2">
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider">URL 别名 Slug</label>
                            <input type="text" name="slug" value="<?= e($post['slug'] ?? '') ?>" class="w-full px-3 py-2.5 rounded-lg bg-gray-50 border-transparent focus:bg-white focus:border-black focus:ring-0 text-sm font-medium transition-all" placeholder="article-slug-url">
                        </div>
                        
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider">摘要 Description</label>
                            <textarea name="description" class="w-full px-3 py-2.5 rounded-lg bg-gray-50 border-transparent focus:bg-white focus:border-black focus:ring-0 text-sm font-medium transition-all h-24 resize-none" placeholder="100字以内的文章摘要..."><?= e($post['description'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider">封面图 Cover URL</label>
                            <input type="text" name="cover_image" value="<?= e($post['cover_image'] ?? '') ?>" class="w-full px-3 py-2.5 rounded-lg bg-gray-50 border-transparent focus:bg-white focus:border-black focus:ring-0 text-sm font-medium transition-all" placeholder="https://example.com/image.jpg">
                        </div>
                    </div>
                    
                    <!-- Plugin Extension Point -->
                    <?php \Core\Hook::listen('theme_write_extra_settings'); ?>
                    
                    <!-- Ran_Bounty Hook -->
                    <?php \Core\Hook::listen('theme_write_bounty', $post ?? []); ?>
                    
                    <!-- Plugin Hook: Bottom Settings -->
                    <?php \Core\Hook::listen('theme_write_settings_bottom', $post ?? []); ?>

                    <div class="pt-4 border-t border-gray-100">
                         <button type="button" onclick="postAction('<?= url('/admin/posts/delete/' . $post['id']) ?>', '确定要删除这篇文章吗？')" class="block w-full text-center text-red-500 text-sm font-bold hover:text-red-700 transition-colors">删除文章</button>
                    </div>
                </div>
            </div>
        </form>
    </main>
</div>

<!-- Custom Editor Logic -->
<!-- Custom Editor Logic included via component -->

<script>
// 标题验证 - 防止只有空格
document.getElementById('postForm').addEventListener('submit', function(e) {
    const titleInput = this.querySelector('input[name="title"]');
    const title = titleInput.value.trim();
    
    if (title === '') {
        e.preventDefault();
        alert('标题不能为空或只包含空格,请输入有效的标题内容');
        titleInput.focus();
        return false;
    }
});
</script>

<?php require APP_PATH . '/Views/admin/footer.php'; ?>
