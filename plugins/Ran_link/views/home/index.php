<div class="max-w-7xl mx-auto px-6 py-12 pt-28 min-h-screen">
    <div class="mb-12 text-center">
        <h1 class="text-4xl font-extrabold text-ink-900 dark:text-white mb-4">友情链接</h1>
        <p class="text-gray-500 dark:text-gray-400">Friend Links & Partners</p>
    </div>
    
    <?php if(isset($_GET['success'])): ?>
    <div class="bg-green-50 text-green-700 px-4 py-3 rounded-xl mb-8 border border-green-100 text-center text-sm font-bold">
        <i class="fas fa-check-circle mr-2"></i> 提交成功，请等待管理员审核。
    </div>
    <?php endif; ?>

    <!-- Links Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6 mb-16">
        <?php foreach($links as $link): ?>
        <a href="<?= e($link['url']) ?>" target="_blank" class="flex items-center p-4 bg-white dark:bg-[#111] border border-gray-100 dark:border-white/5 rounded-xl hover:shadow-lg dark:hover:shadow-black/50 hover:border-black dark:hover:border-white transition-all group">
            <div class="w-10 h-10 rounded-full bg-gray-50 dark:bg-white/10 flex items-center justify-center text-lg font-bold text-gray-400 group-hover:bg-black group-hover:text-white dark:group-hover:bg-white dark:group-hover:text-black transition-colors">
                <?= mb_substr($link['name'], 0, 1) ?>
            </div>
            <div class="ml-4 overflow-hidden">
                <h3 class="font-bold text-ink-900 dark:text-white truncate"><?= e($link['name']) ?></h3>
                <p class="text-xs text-gray-400 truncate"><?= e($link['url']) ?></p>
            </div>
        </a>
        <?php endforeach; ?>

        <!-- Add Link Button -->
        <button onclick="document.getElementById('apply_modal').classList.remove('hidden')" class="flex items-center p-4 bg-gray-50 dark:bg-white/5 border border-dashed border-gray-300 dark:border-gray-700 rounded-xl hover:bg-white hover:border-black dark:hover:bg-white/10 dark:hover:border-white transition-all group text-left">
            <div class="w-10 h-10 rounded-full bg-white dark:bg-black/20 flex items-center justify-center text-lg font-bold text-gray-400 group-hover:bg-black group-hover:text-white dark:group-hover:bg-white dark:group-hover:text-black transition-colors">
                <i class="fas fa-plus"></i>
            </div>
            <div class="ml-4 overflow-hidden">
                <h3 class="font-bold text-gray-500 group-hover:text-ink-900 dark:group-hover:text-white transition-colors">申请友链</h3>
                <p class="text-xs text-gray-400">点击提交贵站信息</p>
            </div>
        </button>
    </div>

    <!-- Apply Modal -->
    <div id="apply_modal" class="fixed inset-0 z-50 hidden">
        <!-- Backdrop -->
        <div onclick="document.getElementById('apply_modal').classList.add('hidden')" class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity"></div>
        
        <!-- Modal Content -->
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-md p-4">
            <div class="bg-white dark:bg-[#1a1a1a] rounded-3xl p-8 shadow-2xl relative border border-gray-100 dark:border-white/10">
                <button onclick="document.getElementById('apply_modal').classList.add('hidden')" class="absolute top-4 right-4 text-gray-400 hover:text-black dark:hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
                
                <h2 class="text-2xl font-bold text-ink-900 dark:text-white mb-2 text-center">申请友链</h2>
                <p class="text-gray-500 dark:text-gray-400 text-sm mb-8 text-center">请确保贵站已添加本站链接。</p>
                
                <form action="<?= url('/links/apply') ?>" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">站点名称</label>
                        <input type="text" name="name" required class="w-full bg-gray-50 dark:bg-black/20 border-gray-200 dark:border-white/10 rounded-xl px-4 py-3 text-sm focus:ring-black dark:focus:ring-white focus:border-black dark:focus:border-white transition-all" placeholder="Title">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">站点地址</label>
                        <input type="url" name="url" required class="w-full bg-gray-50 dark:bg-black/20 border-gray-200 dark:border-white/10 rounded-xl px-4 py-3 text-sm focus:ring-black dark:focus:ring-white focus:border-black dark:focus:border-white transition-all" placeholder="https://">
                    </div>
                    <button type="submit" class="w-full bg-black text-white dark:bg-white dark:text-black px-4 py-3.5 rounded-xl font-bold hover:scale-[1.02] transition-transform shadow-lg shadow-black/10">
                        提交申请
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
