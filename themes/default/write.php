<?php $this->render('header'); ?>

<?php
if (function_exists('is_plugin_active') && is_plugin_active('Ran_Grade') && class_exists('Plugins\Ran_Grade\Plugin')) {
    // 1. Basic Post Permission
    if (!\Plugins\Ran_Grade\Plugin::checkPermission($_SESSION['user']['id'], 'write_post')) {
        echo '<div class="pt-32 pb-20 px-6 max-w-7xl mx-auto text-center"><div class="bg-red-50 dark:bg-red-900/10 p-10 rounded-3xl"><i class="fas fa-lock text-4xl text-red-500 mb-4"></i><h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-2">权限不足</h2><p class="text-gray-500 dark:text-gray-400">您的等级不足，无法发布文章。请多多互动（评论、点赞）提升等级至 Lv.1 后再试。</p></div></div>';
        $this->render('footer');
        exit;
    }
    
    // 2. Advanced Permissions
    $uid = $_SESSION['user']['id'];
    $gradeInfo = \Plugins\Ran_Grade\Plugin::getLevel($uid);
    $level = $gradeInfo['level'];
    
    $isVip = false;
    if (class_exists('\Plugins\Ran_Vip\Plugin')) {
        $isVip = \Plugins\Ran_Vip\Plugin::isVip($uid);
    }
    
    // Thresholds: Image=Lv.2, Video=Lv.3, Dmooji=Lv.4. VIP overrides all.
    $canPostImage = $isVip || $level >= 2;
    $canPostVideo = $isVip || $level >= 3;
    $canUseDmooji = $isVip || $level >= 4;
} else {
    $canPostImage = true;
    $canPostVideo = true;
    $canUseDmooji = true;
}

// Inject CSS to hide Editor Buttons if restricted
echo '<style>';
if (!$canPostImage) echo 'label[title="Image"] { display: none !important; }';
if (!$canPostVideo) echo 'label[title="Video"] { display: none !important; }';
echo '</style>';
?>
<main class="pt-32 pb-20 px-6 w-full max-w-7xl mx-auto">
    <div class="mb-10 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-extrabold text-ink-900 dark:text-gray-100">开始写作</h1>
            <p class="text-ink-500 dark:text-gray-400 mt-2">分享你的故事与见解</p>
        </div>
        <div>
             <button type="submit" form="writeForm" class="bg-ink-900 text-white dark:bg-white dark:text-black px-8 py-3 rounded-full font-bold hover:scale-105 transition-transform shadow-lg shadow-ink-900/20">
                <i class="fas fa-paper-plane mr-2"></i> 发布文章
            </button>
        </div>
    </div>
    <form id="writeForm" action="<?= url('/post/store') ?>" method="POST" class="space-y-8">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left: Editor -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Title -->
                <div class="space-y-2">
                    <input type="text" name="title" class="w-full px-0 py-4 bg-transparent border-0 border-b-2 border-gray-100 dark:border-white/10 focus:border-gray-100 dark:focus:border-white/10 focus:ring-0 outline-none transition-all text-3xl font-extrabold placeholder-gray-300 dark:placeholder-gray-600 text-ink-900 dark:text-white" placeholder="请输入文章标题..." required>
                </div>
                
                <!-- Editor Container -->
                <!-- Shared Editor Component -->
                <?php
                    $name = 'content';
                    $id = 'editor';
                    $content = '';
                    $placeholder = '开始撰写您的精彩内容 (支持 Markdown)...';
                    $enableMentions = true;
                    require APP_PATH . '/Views/components/editor.php';
                ?>
            </div>
            
            <!-- Right: Settings -->
            <div class="space-y-6">
                
                <!-- Category Status or Circle Info -->
                <div class="bg-white dark:bg-white/5 p-8 rounded-3xl border border-gray-100 dark:border-white/10 shadow-sm space-y-6">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-sliders-h text-ink-400"></i>
                        <h3 class="font-bold text-ink-900 dark:text-gray-100">发布设置</h3>
                    </div>
                    
                    <div class="space-y-2">
                        <?php if(is_plugin_active('Ran_Circle')): ?>
                            <label class="block text-xs font-bold text-ink-400 dark:text-gray-500 uppercase tracking-wider">发布到圈子</label>
                            <?php if(!empty($circle_id)): ?>
                                <?php 
                                    $cid = (int)$circle_id;
                                    $cName = '未知圈子';
                                    $db = \Core\Database::getInstance(config('db'));
                                    $circle = $db->query("SELECT name FROM circles WHERE id = ?", [$cid])->fetch();
                                    if($circle) $cName = $circle['name'];
                                ?>
                                <div class="px-4 py-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-300 font-bold border border-blue-100 dark:border-blue-900/30">
                                    <i class="fas fa-bullseye mr-2"></i> <?= htmlspecialchars($cName) ?>
                                </div>
                                <input type="hidden" name="circle_id" value="<?= $cid ?>">
                            <?php else: ?>
                                <div class="relative">
                                    <select name="circle_id" class="w-full pl-4 pr-10 py-3 rounded-xl bg-gray-50 dark:bg-black/20 border border-transparent focus:bg-white dark:focus:bg-black focus:border-ink-900 dark:focus:border-white focus:ring-0 text-sm font-medium transition-all appearance-none cursor-pointer text-ink-900 dark:text-white">
                                        <option value="0">请选择圈子...</option>
                                        <?php if(!empty($circles)): ?>
                                            <?php foreach($circles as $c): ?>
                                                <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <div class="absolute right-4 top-3.5 pointer-events-none text-ink-400">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <label class="block text-xs font-bold text-ink-400 dark:text-gray-500 uppercase tracking-wider">文章分类</label>
                            <div class="relative">
                                <select name="category_id" class="w-full pl-4 pr-10 py-3 rounded-xl bg-gray-50 dark:bg-black/20 border border-transparent focus:bg-white dark:focus:bg-black focus:border-ink-900 dark:focus:border-white focus:ring-0 text-sm font-medium transition-all appearance-none cursor-pointer text-ink-900 dark:text-white" required>
                                    <option value="">请选择分类...</option>
                                    <?php if(!empty($categories)): ?>
                                        <?php foreach($categories as $cat): ?>
                                            <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <div class="absolute right-4 top-3.5 pointer-events-none text-ink-400">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Meta Info -->
                <div class="bg-white dark:bg-white/5 p-8 rounded-3xl border border-gray-100 dark:border-white/10 shadow-sm space-y-6">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-search text-ink-400"></i>
                        <h3 class="font-bold text-ink-900 dark:text-gray-100">元数据</h3>
                    </div>
                
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-ink-400 dark:text-gray-500 uppercase tracking-wider">文章摘要</label>
                        <textarea name="description" class="w-full px-4 py-3 rounded-xl bg-gray-50 dark:bg-black/20 border border-transparent focus:bg-white dark:focus:bg-black focus:border-ink-900 dark:focus:border-white focus:ring-0 text-sm font-medium transition-all h-32 resize-none text-ink-900 dark:text-white" placeholder="简单的介绍这篇文章..."></textarea>
                    </div>
                    
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-ink-400 dark:text-gray-500 uppercase tracking-wider">文章标签</label>
                        <input type="text" name="tags" class="w-full px-4 py-3 rounded-xl bg-gray-50 dark:bg-black/20 border border-transparent focus:bg-white dark:focus:bg-black focus:border-ink-900 dark:focus:border-white focus:ring-0 text-sm font-medium transition-all text-ink-900 dark:text-white" placeholder="React, Vue3, 教程 (用逗号分隔)">
                        <p class="text-[10px] text-gray-400">使用逗号分隔多个标签，最多添加 5 个</p>
                    </div>
                    
                <!-- Cover Image -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-ink-400 dark:text-gray-500 uppercase tracking-wider">封面图链接</label>
                    <?php if($canPostImage): ?>
                    <input type="text" name="cover_image" class="w-full px-4 py-3 rounded-xl bg-gray-50 dark:bg-black/20 border border-transparent focus:bg-white dark:focus:bg-black focus:border-ink-900 dark:focus:border-white focus:ring-0 text-sm font-medium transition-all text-ink-900 dark:text-white" placeholder="https://...">
                    <?php else: ?>
                    <div class="w-full px-4 py-3 rounded-xl bg-gray-100 dark:bg-white/5 border border-transparent text-sm font-medium text-gray-400 cursor-not-allowed flex items-center justify-between">
                         <span>封面设置不可用</span>
                         <span class="text-xs bg-gray-200 dark:bg-white/10 px-2 py-1 rounded">需 Lv.2 或 VIP</span>
                    </div>
                    <?php endif; ?>
                </div>
                </div>
                
                
                <?php \Core\Hook::listen('theme_write_extra_settings'); ?>
                

                <?php \Core\Hook::listen('theme_write_bounty', $post ?? []); ?>



            
            <!-- Plugin Hook: Bottom Settings -->
            <?php \Core\Hook::listen('theme_write_settings_bottom', $post ?? []); ?>
            

            
            </div>
        </div>
    </form>
</main>

<!-- Custom Editor Logic -->
<!-- Custom Editor Logic included via component -->

<script>
// 标题验证 - 防止只有空格
document.getElementById('writeForm').addEventListener('submit', function(e) {
    const titleInput = this.querySelector('input[name="title"]');
    const title = titleInput.value.trim();
    
    if (title === '') {
        e.preventDefault();
        alert('标题不能为空或只包含空格,请输入有效的标题内容');
        titleInput.focus();
        return false;
    }
    // 验证圈子或分类选择
    <?php if(is_plugin_active('Ran_Circle')): ?>
    const circleSelect = this.querySelector('select[name="circle_id"]');
    if (circleSelect && circleSelect.value == '0') {
        e.preventDefault();
        alert('请选择一个要发布的圈子');
        circleSelect.focus();
        return false;
    }
    <?php else: ?>
    const categorySelect = this.querySelector('select[name="category_id"]');
    if (categorySelect && categorySelect.value == '') {
        e.preventDefault();
        alert('请选择一个文章分类');
        categorySelect.focus();
        return false;
    }
    <?php endif; ?>
});
</script>

<?php $this->render('footer'); ?>
