<?php require APP_PATH . '/Views/admin/header.php'; ?>
<?php require APP_PATH . '/Views/admin/sidebar.php'; ?>

<div class="md:pl-64 flex flex-col min-h-screen transition-all duration-300">
    <header class="h-16 bg-white/80 backdrop-blur-md border-b border-gray-100 flex items-center justify-between px-4 md:px-8 sticky top-0 z-40">
        <div class="flex items-center gap-4">
            <button onclick="toggleSidebar()" class="md:hidden text-gray-500 hover:text-black focus:outline-none">
                <i class="fas fa-bars text-xl"></i>
            </button>
            <h2 class="text-lg font-bold text-gray-800">系统设置</h2>
        </div>
        <button type="submit" form="optForm" class="bg-black text-white px-6 py-2 rounded-lg text-sm font-bold hover:bg-gray-800 transition-colors shadow-lg shadow-black/20">
            <i class="fas fa-save mr-2"></i> 保存设置
        </button>
    </header>

    <main class="p-8 flex-1 max-w-4xl mx-auto w-full">
        <form id="optForm" action="<?= url('/admin/options/update') ?>" method="POST" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?= \Core\Csrf::generate() ?>">
            
            <!-- Basic Site Info -->
            <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm space-y-6">
                <div class="flex items-center gap-2 mb-4 border-b border-gray-100 pb-4">
                    <i class="fas fa-globe text-gray-400"></i>
                    <h3 class="font-bold text-gray-800">站点基本信息</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 ml-1">站点标题 Site Title</label>
                        <input type="text" name="site_title" value="<?= e($options['site_title'] ?? '') ?>" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-100 focus:bg-white focus:border-black focus:ring-0 transition-all font-bold">
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 ml-1">站点描述 Description</label>
                    <textarea name="site_description" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-100 focus:bg-white focus:border-black focus:ring-0 transition-all h-24 resize-none"><?= e($options['site_description'] ?? '') ?></textarea>
                </div>
                
                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 ml-1">关键词 Keywords</label>
                    <input type="text" name="site_keywords" value="<?= e($options['site_keywords'] ?? '') ?>" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-100 focus:bg-white focus:border-black focus:ring-0 transition-all">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 ml-1">Logo URL</label>
                        <input type="text" name="site_logo" value="<?= e($options['site_logo'] ?? '') ?>" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-100 focus:bg-white focus:border-black focus:ring-0 transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 ml-1">Favicon URL</label>
                        <input type="text" name="site_ico" value="<?= e($options['site_ico'] ?? '') ?>" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-100 focus:bg-white focus:border-black focus:ring-0 transition-all">
                    </div>
                </div>
            </div>

            <!-- Contact & Social -->
            <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm space-y-6">
                <div class="flex items-center gap-2 mb-4 border-b border-gray-100 pb-4">
                    <i class="fas fa-share-alt text-gray-400"></i>
                    <h3 class="font-bold text-gray-800">联系与社交 (Social)</h3>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                     <div class="space-y-2">
                         <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 ml-1">GitHub URL</label>
                         <input type="text" name="social_github" value="<?= e($options['social_github'] ?? '') ?>" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-100 focus:bg-white focus:border-black focus:ring-0 transition-all">
                    </div>
                     <div class="space-y-2">
                         <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 ml-1">Bilibili URL</label>
                         <input type="text" name="social_bilibili" value="<?= e($options['social_bilibili'] ?? '') ?>" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-100 focus:bg-white focus:border-black focus:ring-0 transition-all">
                    </div>
                     <div class="space-y-2">
                         <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 ml-1">QQ</label>
                         <input type="text" name="social_qq" value="<?= e($options['social_qq'] ?? '') ?>" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-100 focus:bg-white focus:border-black focus:ring-0 transition-all">
                    </div>
                     <div class="space-y-2">
                         <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 ml-1">微信 WeChat</label>
                         <input type="text" name="social_wechat" value="<?= e($options['social_wechat'] ?? '') ?>" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-100 focus:bg-white focus:border-black focus:ring-0 transition-all">
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 ml-1">ICP 备案号</label>
                    <input type="text" name="site_icp" value="<?= e($options['site_icp'] ?? '') ?>" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-100 focus:bg-white focus:border-black focus:ring-0 transition-all">
                </div>
            </div>

            <!-- Upload Settings -->
             <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm space-y-6">
                <div class="flex items-center gap-2 mb-4 border-b border-gray-100 pb-4">
                    <i class="fas fa-cloud-upload-alt text-gray-400"></i>
                    <h3 class="font-bold text-gray-800">上传设置 (Upload)</h3>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                     <div class="space-y-2">
                         <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 ml-1">图片上传限制 (MB)</label>
                         <input type="number" name="upload_max_size_image" value="<?= e($options['upload_max_size_image'] ?? '2') ?>" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-100 focus:bg-white focus:border-black focus:ring-0 transition-all font-mono font-bold">
                         <p class="text-[10px] text-gray-400">默认 2MB。建议不超过 10MB。</p>
                    </div>
                     <div class="space-y-2">
                         <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 ml-1">视频上传限制 (MB)</label>
                         <input type="number" name="upload_max_size_video" value="<?= e($options['upload_max_size_video'] ?? '20') ?>" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-100 focus:bg-white focus:border-black focus:ring-0 transition-all font-mono font-bold">
                         <p class="text-[10px] text-gray-400">默认 20MB。受限于服务器 PHP 配置 (upload_max_filesize)。</p>
                    </div>
                </div>
            </div>

            <!-- Site Status -->
             <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm space-y-6">
                <div class="flex items-center gap-2 mb-4 border-b border-gray-100 pb-4">
                    <i class="fas fa-server text-gray-400"></i>
                    <h3 class="font-bold text-gray-800">站点状态 (Status)</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100">
                        <div>
                            <h4 class="font-bold text-gray-800">维护模式 (Maintenance)</h4>
                            <p class="text-xs text-gray-400 mt-1">开启后，前台将仅显示维护页面。</p>
                        </div>
                        <div class="space-x-4">
                             <label class="inline-flex items-center cursor-pointer gap-2">
                                <input type="radio" name="site_maintenance" value="1" class="form-radio text-black focus:ring-black" <?= ($options['site_maintenance'] ?? '0') == '1' ? 'checked' : '' ?>>
                                <span class="text-xs font-bold">开启</span>
                            </label>
                             <label class="inline-flex items-center cursor-pointer gap-2">
                                <input type="radio" name="site_maintenance" value="0" class="form-radio text-black focus:ring-black" <?= ($options['site_maintenance'] ?? '0') == '0' ? 'checked' : '' ?>>
                                <span class="text-xs font-bold">关闭</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100">
                         <div>
                            <h4 class="font-bold text-gray-800">完全关闭 (Closed)</h4>
                            <p class="text-xs text-gray-400 mt-1">开启后，站点将彻底无法访问。</p>
                        </div>
                        <div class="space-x-4">
                             <label class="inline-flex items-center cursor-pointer gap-2">
                                <input type="radio" name="site_closed" value="1" class="form-radio text-black focus:ring-black" <?= ($options['site_closed'] ?? '0') == '1' ? 'checked' : '' ?>>
                                <span class="text-xs font-bold">开启</span>
                            </label>
                             <label class="inline-flex items-center cursor-pointer gap-2">
                                <input type="radio" name="site_closed" value="0" class="form-radio text-black focus:ring-black" <?= ($options['site_closed'] ?? '0') == '0' ? 'checked' : '' ?>>
                                <span class="text-xs font-bold">关闭</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </main>
</div>

<?php require APP_PATH . '/Views/admin/footer.php'; ?>
