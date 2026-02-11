<?php if(empty($ajax)): ?>
<?php $this->render('header'); ?>

    <!-- ================= 主体内容 ================= -->
    <main class="max-w-7xl mx-auto px-6 pt-24 md:pt-32 pb-4 md:pb-20 flex flex-col lg:flex-row gap-16">

        <!-- 左侧：内容流 (65%) -->
        <div class="lg:w-[65%] w-full">
            

            <!-- 切换 Tabs -->
            <?php 
                $activeTab = $_GET['tab'] ?? 'recommend';
                
                // Validate active tab against plugin status
                if ($activeTab === 'follow' && (!function_exists('is_plugin_active') || !is_plugin_active('Ran_Follow'))) {
                    $activeTab = 'recommend';
                }

                $tabs = [
                    'recommend' => '推荐',
                    'newest' => '最新',
                    'new_comment' => '新评',
                ];
                
                if (function_exists('is_plugin_active') && is_plugin_active('Ran_Follow')) {
                    $tabs['follow'] = '关注';
                }
                if (function_exists('is_plugin_active') && is_plugin_active('Ran_MoYu')) {
                    $tabs['moyu'] = '摸鱼';
                }
            ?>
            <div class="flex items-center justify-between border-b border-ink-100 dark:border-white/10 mb-4 md:mb-12">
                <div class="flex gap-8">
                    <?php foreach($tabs as $key => $label): ?>
                        <a href="<?= url('/?tab=' . $key) ?>" 
                           class="pb-4 border-b-2 font-medium text-sm transition-colors <?= $activeTab === $key ? 'border-ink-900 text-ink-900 font-bold dark:border-white dark:text-white' : 'border-transparent text-ink-500 hover:text-ink-900 dark:text-gray-400 dark:hover:text-gray-200' ?>">
                            <?= $label ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                <!-- Layout Switcher -->
                <div class="flex gap-2 pb-4">
                     <button onclick="toggleLayout()" id="btn-layout-toggle" class="p-2 rounded hover:bg-gray-100 dark:hover:bg-white/10 text-ink-900 dark:text-gray-200 transition-colors" title="切换视图">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
            </div>

            <!-- 文章列表容器 -->
            <div id="articles-list" class="flex flex-col min-h-[500px] gap-8">
                <?php if (!empty($pinned_post)): ?>
                    <article class="article-item group animate-fade-in border-b border-ink-100 dark:border-white/10 pb-12 last:border-0" data-layout="list">
                        <!-- 列表样式 (List View) -->
                        <div class="list-view-content flex flex-row gap-4 sm:gap-8">
                            <div class="flex-1 flex flex-col justify-center min-w-0">
                                <div class="flex items-center gap-2 mb-2">
                                    <a href="<?= url('/user/' . $pinned_post['user_id']) ?>" class="relative block shrink-0">
                                        <img src="<?= e($pinned_post['author_avatar']) ?>" class="w-10 h-10 rounded-full object-cover">
                                        <?= (function_exists('is_plugin_active') && is_plugin_active('Ran_Name')) ? \Plugins\Ran_Name\Plugin::renderVerifyIcon($pinned_post['user_id'], 'w-2 h-2 !bottom-0 !right-0') : '' ?>
                                    </a>
                                    <div class="flex flex-col">
                                        <div class="flex items-center gap-1">
                                            <a href="<?= url('/user/' . $pinned_post['user_id']) ?>" class="<?= (function_exists('is_plugin_active') && is_plugin_active('Ran_Vip')) ? \Plugins\Ran_Vip\Plugin::getVipColor($pinned_post['user_id'], 'hover:text-accent transition-colors text-xs font-medium dark:text-gray-300') : 'hover:text-accent transition-colors text-xs font-medium dark:text-gray-300' ?>"><?= e($pinned_post['author_name']) ?></a>
                                        </div>
                                        <span class="text-[10px] text-gray-400"><?= date('m月d日', strtotime($pinned_post['created_at'])) ?></span>
                                    </div>
                                    <?php \Core\Hook::listen('theme_follow_button', $pinned_post['user_id']); ?>
                                    <span class="text-[10px] font-bold text-accent border border-accent/20 px-1.5 py-0.5 rounded">置顶</span>
                                    <?php if (class_exists('Plugins\Ran_Title\Plugin')) \Plugins\Ran_Title\Plugin::renderTitle($pinned_post['user_id']); ?>
                                </div>
                                <a href="<?= url('/' . $pinned_post['id'] . '.html') ?>" class="block">
                                    <h2 class="text-lg md:text-2xl font-bold mb-2 decoration-2 underline-offset-4 decoration-accent dark:text-gray-100 line-clamp-2 leading-snug">
                                        <?= e($pinned_post['title']) ?>
                                        <?php \Core\Hook::listen('theme_post_title_suffix', $pinned_post); ?>
                                    </h2>
                                </a>
                                <p class="text-ink-500 dark:text-gray-400 text-sm md:text-base leading-relaxed line-clamp-2 md:line-clamp-3 mb-4 hidden sm:block">
                                    <?= e($pinned_post['description']) ?>
                                </p>
                                <div class="flex items-center justify-between mt-auto">
                                    <div class="flex items-center gap-3 text-[10px] sm:text-xs text-ink-500 dark:text-gray-500">
                                        <?php 
                                            $showCircle = function_exists('is_plugin_active') && is_plugin_active('Ran_Circle') && !empty($pinned_post['circle_name']);
                                        ?>
                                        <?php if($showCircle): ?>
                                            <a href="<?= url('/c/' . $pinned_post['circle_slug']) ?>" class="bg-gray-100 dark:bg-white/10 px-2 py-0.5 rounded text-ink-900 dark:text-gray-200 hover:text-accent transition-colors"><?= e($pinned_post['circle_name']) ?></a>
                                        <?php elseif(!empty($pinned_post['category_name'])): ?>
                                            <span class="bg-gray-100 dark:bg-white/10 px-2 py-0.5 rounded text-ink-900 dark:text-gray-200"><?= e($pinned_post['category_name']) ?></span>
                                        <?php endif; ?>
                                        <span class="bg-gray-100 dark:bg-white/10 px-2 py-0.5 rounded text-ink-900 dark:text-gray-200 hidden sm:inline"><?= e($pinned_post['read_time']) ?></span>
                                        <span class="flex items-center gap-1"><i class="far fa-eye"></i> <?= number_format($pinned_post['view_count']) ?></span>
                                        <button onclick="toggleLike(<?= $pinned_post['id'] ?>, this)" class="flex items-center gap-1 hover:text-red-500 transition-colors group-like" title="点赞">
                                            <i class="far fa-heart transition-transform group-active:scale-125"></i> 
                                            <span class="like-count"><?= number_format($pinned_post['like_count'] ?? 0) ?></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php if(!empty($pinned_post['cover_image'])): ?>
                            <a href="<?= url('/' . $pinned_post['id'] . '.html') ?>" class="w-28 h-20 sm:w-48 sm:h-32 shrink-0 bg-gray-100 dark:bg-white/5 overflow-hidden block rounded-xl">
                                <img src="<?= e($pinned_post['cover_image']) ?>" class="w-full h-full object-cover transition-all duration-500 ease-out">
                            </a>
                            <?php endif; ?>
                        </div>

                         <!-- 朋友圈样式 (Grid/Moments View) -->
                        <div class="grid-view-content hidden flex-col gap-3">
                            <div class="flex items-start gap-3">
                                 <a href="<?= url('/user/' . $pinned_post['user_id']) ?>" class="relative block shrink-0 mt-1">
                                    <img src="<?= e($pinned_post['author_avatar']) ?>" class="w-10 h-10 rounded-lg object-cover">
                                    <?= (function_exists('is_plugin_active') && is_plugin_active('Ran_Name')) ? \Plugins\Ran_Name\Plugin::renderVerifyIcon($pinned_post['user_id'], 'w-3 h-3 !bottom-[2px] !right-[2px]') : '' ?>
                                </a>
                                <div class="flex-1 min-w-0">
                                     <div class="flex items-center justify-between mb-1">
                                         <div class="flex items-center gap-2">
                                             <a href="<?= url('/user/' . $pinned_post['user_id']) ?>" class="<?= (function_exists('is_plugin_active') && is_plugin_active('Ran_Vip')) ? \Plugins\Ran_Vip\Plugin::getVipColor($pinned_post['user_id'], 'font-bold text-ink-900 dark:text-gray-100 text-sm') : 'font-bold text-ink-900 dark:text-gray-100 text-sm' ?> hover:text-accent"><?= e($pinned_post['author_name']) ?></a>
                                             <?php if (class_exists('Plugins\Ran_Title\Plugin')) \Plugins\Ran_Title\Plugin::renderTitle($pinned_post['user_id']); ?>
                                             <span class="text-[10px] font-bold text-accent border border-accent/20 px-1.5 py-0.5 rounded">置顶</span>
                                         </div>
                                         <?php \Core\Hook::listen('theme_follow_button', $pinned_post['user_id']); ?>
                                     </div>
                                     <div class="text-xs text-ink-500 dark:text-gray-400 mb-2">
                                        <p class="mb-2"><?= e($pinned_post['description'] ?: $pinned_post['title']) ?></p>
                                        
                                        <?php if(!empty($pinned_post['cover_image'])): ?>
                                         <div class="block w-full max-w-[200px] h-32 bg-gray-100 dark:bg-white/5 rounded-lg overflow-hidden mb-2 cursor-zoom-in group-image">
                                            <img src="<?= e($pinned_post['cover_image']) ?>" class="w-full h-full object-cover" onclick="openImagePreview(event, this.src)">
                                        </div>
                                        <?php else: ?>
                                         <a href="<?= url('/' . $pinned_post['id'] . '.html') ?>" class="block bg-gray-50 dark:bg-white/5 p-2 rounded text-ink-900 dark:text-gray-200 text-sm font-medium mb-2 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors">
                                             <i class="fas fa-link text-xs mr-1 opacity-50"></i> <?= e($pinned_post['title']) ?>
                                             <?php \Core\Hook::listen('theme_post_title_suffix', $pinned_post); ?>
                                         </a>
                                        <?php endif; ?>

                                        <div class="flex items-center justify-between text-[10px] text-gray-400 mt-2">
                                            <div class="flex items-center gap-3">
                                                <span><?= date('m月d日', strtotime($pinned_post['created_at'])) ?></span>
                                                <?php 
                                                    $showCircle = function_exists('is_plugin_active') && is_plugin_active('Ran_Circle') && !empty($pinned_post['circle_name']);
                                                ?>
                                                <?php if($showCircle): ?>
                                                    <a href="<?= url('/c/' . $pinned_post['circle_slug']) ?>" class="text-accent"><?= e($pinned_post['circle_name']) ?></a>
                                                <?php elseif(!empty($pinned_post['category_name'])): ?>
                                                    <span class="text-accent"><?= e($pinned_post['category_name']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <button onclick="toggleLike(<?= $pinned_post['id'] ?>, this)" class="flex items-center gap-1 hover:text-red-500 transition-colors group-like" title="点赞">
                                                    <i class="far fa-heart transition-transform group-active:scale-125"></i>
                                                    <span class="like-count"><?= number_format($pinned_post['like_count'] ?? 0) ?></span>
                                                </button>
                                            </div>
                                        </div>
                                     </div>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endif; ?>
                <?php endif; ?>
                <?php if (empty($posts)): ?>
                    <div class="text-center  text-ink-500 dark:text-gray-500">
                        <?php if ($activeTab === 'follow'): ?>
                            <?php if (!isset($_SESSION['user']['id'])): ?>
                                <div class="flex flex-col items-center gap-4">
                                    <div class="w-16 h-16 bg-gray-100 dark:bg-white/5 rounded-full flex items-center justify-center text-2xl text-gray-400">
                                        <i class="fas fa-lock"></i>
                                    </div>
                                    <h3 class="text-lg font-bold text-ink-900 dark:text-white">尚未登录</h3>
                                    <p class="text-sm text-gray-400 max-w-xs mb-2">登录后即可查看关注作者的最新动态</p>
                                    <a href="<?= url('/login') ?>" class="px-6 py-2 bg-ink-900 dark:bg-white text-white dark:text-black rounded-full font-bold text-sm hover:opacity-90 transition-opacity">立即登录</a>
                                </div>
                            <?php else: ?>
                                <div class="flex flex-col items-center gap-4">
                                    <div class="w-16 h-16 bg-gray-100 dark:bg-white/5 rounded-full flex items-center justify-center text-2xl text-gray-400">
                                        <i class="fas fa-user-plus"></i>
                                    </div>
                                    <h3 class="text-lg font-bold text-ink-900 dark:text-white">暂无关注动态</h3>
                                    <p class="text-sm text-gray-400 max-w-xs">快去探索并关注感兴趣的作者吧</p>
                                    <a href="<?= url('/?tab=recommend') ?>" class="px-6 py-2 border border-ink-200 dark:border-white/20 rounded-full font-bold text-sm hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">去探索</a>
                                </div>
                            <?php endif; ?>
                        <?php elseif ($activeTab === 'moyu' && function_exists('is_plugin_active') && is_plugin_active('Ran_MoYu')): ?>
                             <!-- MoYu Tab Content (AJAX Loaded) -->
                             <!-- Feed Container -->
                             <div id="moyu-feed-container" class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full">
                                    <div class="md:col-span-2 flex flex-col items-center gap-4 text-center py-12" id="moyu-placeholder">
                                        <div class="w-16 h-16 bg-blue-50 dark:bg-blue-900/10 rounded-full flex items-center justify-center text-2xl text-blue-400 animate-pulse">
                                            <i class="fas fa-coffee"></i>
                                        </div>
                                        <h3 class="text-lg font-bold text-ink-900 dark:text-white">摸鱼时间</h3>
                                        <p class="text-sm text-gray-400 max-w-xs transition-opacity" id="moyu-loading-text">正在从全网搜集最新技术文章...</p>
                                        <button onclick="openMoyuApply()" class="text-xs text-blue-500 hover:underline mt-2">申请收录我的博客</button>
                                    </div>
                             </div>

                             <script>
                                let moyuPage = 1;
                                let isLoadingMoyu = false;

                                document.addEventListener('DOMContentLoaded', function() {
                                    // Initial load PAGE 1
                                    loadMoyuPage(1);
                                });

                                function loadMoyuPage(page) {
                                    if(isLoadingMoyu) return;
                                    isLoadingMoyu = true;
                                    moyuPage = page;
                                    
                                    const container = document.getElementById('moyu-feed-container');

                                    // Scroll to top of list if not first load ?
                                    // container.scrollIntoView({ behavior: 'smooth' });

                                    fetch('/moyu/list?ajax=1&page=' + page)
                                        .then(r => r.text())
                                        .then(html => {
                                            const placeholder = document.getElementById('moyu-placeholder');
                                            
                                            if(html.trim()) {
                                                // Replace ENTIRE content (no append for pagination style)
                                                container.innerHTML = html;
                                            } else {
                                                if(page === 1) {
                                                     document.getElementById('moyu-loading-text').innerText = '暂时没有摸鱼内容，请稍后再来';
                                                }
                                            }
                                        })
                                        .catch(err => {
                                            console.error(err);
                                            const txt = document.getElementById('moyu-loading-text');
                                            if(txt) txt.innerText = '加载失败，请刷新重试';
                                        })
                                        .finally(() => {
                                            isLoadingMoyu = false;
                                        });
                                }

                                function openMoyuApply() {
                                    <?php if(!isset($_SESSION['user'])): ?>
                                        window.location.href = '/login?redirect=' + encodeURIComponent(window.location.href);
                                        return;
                                    <?php endif; ?>
                                    
                                    document.getElementById('moyu-apply-modal').showModal();
                                }

                                function submitMoyuApply(e) {
                                    e.preventDefault();
                                    const btn = e.target.querySelector('button[type="submit"]');
                                    const name = document.getElementById('apply-name').value;
                                    const url = document.getElementById('apply-url').value;
                                    
                                    const originalText = btn.innerText;
                                    btn.disabled = true;
                                    btn.innerText = '提交中...';
                                    
                                    const form = new FormData();
                                    form.append('name', name);
                                    form.append('url', url);
                                    
                                    fetch('/moyu/apply', {
                                        method: 'POST',
                                        body: form
                                    })
                                    .then(r => r.json())
                                    .then(res => {
                                        if(res.success) {
                                            alert(res.message);
                                            document.getElementById('moyu-apply-modal').close();
                                            e.target.reset();
                                        } else {
                                            alert(res.message || '提交失败');
                                        }
                                    })
                                    .catch(err => {
                                        alert('网络错误');
                                    })
                                    .finally(() => {
                                        btn.disabled = false;
                                        btn.innerText = originalText;
                                    });
                                }
                             </script>

                             <!-- Apply Modal -->
                             <dialog id="moyu-apply-modal" class="backdrop:bg-black/40 rounded-xl p-0 w-full max-w-md shadow-2xl">
                                <div class="p-6">
                                    <h3 class="font-bold text-lg mb-4 text-ink-900">申请收录博客</h3>
                                    <form onsubmit="submitMoyuApply(event)" class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">博客名称</label>
                                            <input type="text" id="apply-name" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-0" placeholder="例如：我的技术小站" required>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">RSS/Atom 地址</label>
                                            <input type="url" id="apply-url" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-0" placeholder="https://example.com/feed" required>
                                            <p class="text-xs text-gray-400 mt-1">必须是有效的 RSS 订阅链接</p>
                                        </div>
                                        <div class="flex justify-end gap-2 mt-6">
                                            <button type="button" onclick="document.getElementById('moyu-apply-modal').close()" class="px-4 py-2 text-gray-500 hover:bg-gray-100 rounded-lg transition-colors">取消</button>
                                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">提交申请</button>
                                        </div>
                                    </form>
                                </div>
                             </dialog>
                        <?php else: ?>
                            <div class="flex flex-col items-center gap-4">
                                 <div class="w-16 h-16 bg-gray-100 dark:bg-white/5 rounded-full flex items-center justify-center text-2xl text-gray-400">
                                    <i class="far fa-folder-open"></i>
                                </div>
                                <p>这里还什么都没有~</p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <article class="article-item group animate-fade-in border-b border-ink-100 dark:border-white/10 pb-12 last:border-0" data-layout="list">
                            <!-- 列表样式 (List View) -->
                            <div class="list-view-content flex flex-row gap-4 sm:gap-8">
                                <div class="flex-1 flex flex-col justify-center min-w-0">
                                    <div class="flex items-center gap-2 mb-2">
                                        <a href="<?= url('/user/' . $post['user_id']) ?>" class="relative block shrink-0">
                                            <img src="<?= e($post['author_avatar']) ?>" class="w-5 h-5 rounded-full object-cover">
                                            <?= (function_exists('is_plugin_active') && is_plugin_active('Ran_Name')) ? \Plugins\Ran_Name\Plugin::renderVerifyIcon($post['user_id'], 'w-2 h-2 !bottom-0 !right-0') : '' ?>
                                        </a>
                                        <div class="flex flex-col">
                                            <div class="flex items-center gap-1">
                                                <a href="<?= url('/user/' . $post['user_id']) ?>" class="<?= (function_exists('is_plugin_active') && is_plugin_active('Ran_Vip')) ? \Plugins\Ran_Vip\Plugin::getVipColor($post['user_id'], 'hover:text-accent transition-colors text-xs font-medium dark:text-gray-300') : 'hover:text-accent transition-colors text-xs font-medium dark:text-gray-300' ?>"><?= e($post['author_name']) ?></a>
                                                <?php if (class_exists('Plugins\Ran_Title\Plugin')) \Plugins\Ran_Title\Plugin::renderTitle($post['user_id']); ?>
                                            </div>
                                            <span class="text-[10px] text-gray-400"><?= date('m月d日', strtotime($post['created_at'])) ?></span>
                                        </div>
                                        <?php \Core\Hook::listen('theme_follow_button', $post['user_id']); ?>
                                    </div>
                                    <a href="<?= url('/' . $post['id'] . '.html') ?>" class="block">
                                        <h2 class="text-lg md:text-2xl font-bold mb-2 decoration-2 underline-offset-4 decoration-accent dark:text-gray-100 line-clamp-2 leading-snug">
                                            <?= e($post['title']) ?>
                                            <?php \Core\Hook::listen('theme_post_title_suffix', $post); ?>
                                        </h2>
                                    </a>
                                    <p class="text-ink-500 dark:text-gray-400 text-sm md:text-base leading-relaxed line-clamp-2 md:line-clamp-3 mb-4 hidden sm:block">
                                        <?= e($post['description']) ?>
                                    </p>
                                    <div class="flex items-center justify-between mt-auto">
                                        <div class="flex items-center gap-3 text-[10px] sm:text-xs text-ink-500 dark:text-gray-500">
                                            <?php 
                                                $showCircle = function_exists('is_plugin_active') && is_plugin_active('Ran_Circle') && !empty($post['circle_name']);
                                            ?>
                                            <?php if($showCircle): ?>
                                                <a href="<?= url('/c/' . $post['circle_slug']) ?>" class="bg-gray-100 dark:bg-white/10 px-2 py-0.5 rounded text-ink-900 dark:text-gray-200 hover:text-accent transition-colors"><?= e($post['circle_name']) ?></a>
                                            <?php elseif(!empty($post['category_name'])): ?>
                                                <span class="bg-gray-100 dark:bg-white/10 px-2 py-0.5 rounded text-ink-900 dark:text-gray-200"><?= e($post['category_name']) ?></span>
                                            <?php endif; ?>
                                            <span class="bg-gray-100 dark:bg-white/10 px-2 py-0.5 rounded text-ink-900 dark:text-gray-200 hidden sm:inline"><?= e($post['read_time']) ?></span>
                                            <span class="flex items-center gap-1"><i class="far fa-eye"></i> <?= number_format($post['view_count']) ?></span>
                                            <button onclick="toggleLike(<?= $post['id'] ?>, this)" class="flex items-center gap-1 hover:text-red-500 transition-colors group-like" title="点赞">
                                                <i class="far fa-heart transition-transform group-active:scale-125"></i>
                                                <span class="like-count"><?= number_format($post['like_count'] ?? 0) ?></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <?php if(!empty($post['cover_image'])): ?>
                                <a href="<?= url('/' . $post['id'] . '.html') ?>" class="w-28 h-20 sm:w-48 sm:h-32 shrink-0 bg-gray-100 dark:bg-white/5 overflow-hidden block rounded-xl">
                                    <img src="<?= e($post['cover_image']) ?>" class="w-full h-full object-cover transition-all duration-500 ease-out">
                                </a>
                                <?php endif; ?>
                            </div>

                             <!-- 朋友圈样式 (Grid/Moments View) - Initially Hidden via CSS -->
                            <div class="grid-view-content hidden flex-col gap-3">
                                <div class="flex items-start gap-3">
                                     <a href="<?= url('/user/' . $post['user_id']) ?>" class="relative block shrink-0 mt-1">
                                        <img src="<?= e($post['author_avatar']) ?>" class="w-10 h-10 rounded-lg object-cover">
                                        <?= (function_exists('is_plugin_active') && is_plugin_active('Ran_Name')) ? \Plugins\Ran_Name\Plugin::renderVerifyIcon($post['user_id'], 'w-3 h-3 !bottom-[2px] !right-[2px]') : '' ?>
                                    </a>
                                    <div class="flex-1 min-w-0">
                                         <div class="flex items-center justify-between mb-1">
                                             <div class="flex items-center gap-1">
                                                 <a href="<?= url('/user/' . $post['user_id']) ?>" class="<?= (function_exists('is_plugin_active') && is_plugin_active('Ran_Vip')) ? \Plugins\Ran_Vip\Plugin::getVipColor($post['user_id'], 'font-bold text-ink-900 dark:text-gray-100 text-sm') : 'font-bold text-ink-900 dark:text-gray-100 text-sm' ?> hover:text-accent"><?= e($post['author_name']) ?></a>
                                                 <?php if (class_exists('Plugins\Ran_Title\Plugin')) \Plugins\Ran_Title\Plugin::renderTitle($post['user_id']); ?>
                                             </div>
                                             <?php \Core\Hook::listen('theme_follow_button', $post['user_id']); ?>
                                         </div>
                                         <div class="text-xs text-ink-500 dark:text-gray-400 mb-2">
                                            <p class="mb-2"><?= e($post['description'] ?: $post['title']) ?></p>
                                            
                                            <?php if(!empty($post['cover_image'])): ?>
                                             <div class="block w-full max-w-[200px] h-32 bg-gray-100 dark:bg-white/5 rounded-lg overflow-hidden mb-2 cursor-zoom-in group-image">
                                                <img src="<?= e($post['cover_image']) ?>" class="w-full h-full object-cover" onclick="openImagePreview(event, this.src)">
                                            </div>
                                            <?php else: ?>
                                            <!-- Pure Text Link Style -->
                                             <a href="<?= url('/' . $post['id'] . '.html') ?>" class="block bg-gray-50 dark:bg-white/5 p-2 rounded text-ink-900 dark:text-gray-200 text-sm font-medium mb-2 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors">
                                                 <i class="fas fa-link text-xs mr-1 opacity-50"></i> <?= e($post['title']) ?>
                                                 <?php \Core\Hook::listen('theme_post_title_suffix', $post); ?>
                                             </a>
                                            <?php endif; ?>

                                            <div class="flex items-center justify-between text-[10px] text-gray-400 mt-2">
                                                <div class="flex items-center gap-3">
                                                    <span><?= date('m月d日', strtotime($post['created_at'])) ?></span>
                                                    <?php 
                                                        $showCircle = function_exists('is_plugin_active') && is_plugin_active('Ran_Circle') && !empty($post['circle_name']);
                                                    ?>
                                                    <?php if($showCircle): ?>
                                                        <a href="<?= url('/circle/' . $post['circle_slug']) ?>" class="text-accent"><?= e($post['circle_name']) ?></a>
                                                    <?php elseif(!empty($post['category_name'])): ?>
                                                        <span class="text-accent"><?= e($post['category_name']) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="flex items-center gap-3">
                                                    <button onclick="toggleLike(<?= $post['id'] ?>, this)" class="flex items-center gap-1 hover:text-red-500 transition-colors group-like" title="点赞">
                                                        <i class="far fa-heart transition-transform group-active:scale-125"></i>
                                                        <span class="like-count"><?= number_format($post['like_count'] ?? 0) ?></span>
                                                    </button>
                                                </div>
                                            </div>
                                         </div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php if(empty($ajax)): ?>
            </div>

            <!-- 加载更多按钮 -->
            <?php if(count($posts) >= 10): ?>
            <div class="flex justify-center" id="load-more-container">
                <button onclick="loadMore()" id="load-more-btn" class="border border-ink-100 dark:border-white/20 text-ink-500 dark:text-gray-400 px-8 py-3 rounded-full text-sm hover:border-ink-900 hover:text-ink-900 dark:hover:border-white dark:hover:text-white transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-ink-100">
                    加载更多文章
                </button>
            </div>
            <?php endif; ?>
            
            <!-- 无更多数据提示 (默认隐藏) -->
            <div id="no-more-data" class="hidden pt-12 text-center text-ink-500 dark:text-gray-500 text-sm">
                没有更多内容了
            </div>
        </div>

        <!-- 右侧：侧边栏 (35%) -->
        <aside class="lg:w-[35%] w-full hidden lg:block space-y-12">
            
            
            <!-- Widget: 站点统计 (Site Stats) -->
            <div>
               <div class="flex items-center gap-2 mb-6">
                    <span class="w-1 h-4 bg-ink-900 dark:bg-white rounded-full"></span>
                    <h3 class="font-bold text-ink-900 dark:text-gray-100">站点数据</h3>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-gray-50 dark:bg-white/5 p-4 rounded-xl text-center hover:bg-white dark:hover:bg-white/10 hover:shadow-lg transition-all border border-transparent hover:border-gray-100 dark:hover:border-white/5">
                        <div class="text-xs text-gray-400 mb-1">文章总数</div>
                        <div class="text-xl font-black text-ink-900 dark:text-gray-100 font-mono"><?= $site_stats['post_count'] ?? 0 ?></div>
                    </div>
                     <div class="bg-gray-50 dark:bg-white/5 p-4 rounded-xl text-center hover:bg-white dark:hover:bg-white/10 hover:shadow-lg transition-all border border-transparent hover:border-gray-100 dark:hover:border-white/5">
                        <div class="text-xs text-gray-400 mb-1">运行天数</div>
                        <div class="text-xl font-black text-ink-900 dark:text-gray-100 font-mono"><?= $site_stats['running_days'] ?? 0 ?></div>
                    </div>
                     <div class="bg-gray-50 dark:bg-white/5 p-4 rounded-xl text-center hover:bg-white dark:hover:bg-white/10 hover:shadow-lg transition-all border border-transparent hover:border-gray-100 dark:hover:border-white/5">
                        <div class="text-xs text-gray-400 mb-1">注册用户</div>
                        <div class="text-xl font-black text-ink-900 dark:text-gray-100 font-mono"><?= $site_stats['user_count'] ?? 0 ?></div>
                    </div>
                     <div class="bg-gray-50 dark:bg-white/5 p-4 rounded-xl text-center hover:bg-white dark:hover:bg-white/10 hover:shadow-lg transition-all border border-transparent hover:border-gray-100 dark:hover:border-white/5">
                        <div class="text-xs text-gray-400 mb-1">总评论</div>
                        <div class="text-xl font-black text-ink-900 dark:text-gray-100 font-mono"><?= $site_stats['comment_count'] ?? 0 ?></div>
                    </div>
                </div>
            </div>
           

            <!-- Widget: 热门标签 (Hot Tags) -->
            <div>
                 <div class="flex items-center gap-2 mb-6">
                    <span class="w-1 h-4 bg-ink-900 dark:bg-white rounded-full"></span>
                    <h3 class="font-bold text-ink-900 dark:text-gray-100">热门标签</h3>
                </div>
                <div class="flex flex-wrap gap-2">
                    <?php if(!empty($hot_tags)): ?>
                        <?php foreach($hot_tags as $index => $tag): ?>
                            <a href="<?= url('/?tag=' . urlencode($tag['slug'])) ?>" class="px-3 py-1.5 bg-white dark:bg-white/5 border border-gray-100 dark:border-white/5 rounded-lg text-xs font-bold text-ink-500 hover:text-white hover:bg-ink-900 dark:hover:bg-accent transition-all duration-300 shadow-sm hover:shadow-md <?= $index < 3 ? 'border-l-2 border-l-ink-900 dark:border-l-white' : '' ?>">
                                # <?= e($tag['name']) ?>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-xs text-gray-400 py-2">暂无热门标签</div>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Widget: 最新评论 -->
            <div>
                <div class="flex items-center gap-2 mb-6">
                    <span class="w-1 h-4 bg-ink-900 dark:bg-white rounded-full"></span>
                    <h3 class="font-bold text-ink-900 dark:text-gray-100">最新评论</h3>
                </div>
                <div class="space-y-6">
                    <?php if(!empty($latest_comments)): ?>
                        <?php foreach($latest_comments as $comment): ?>
                        <?php $commentUser = $comment['username'] ?? 'Guest'; ?>
                        <div class="group relative pl-4 border-l-2 border-gray-100 dark:border-white/10 hover:border-ink-900 dark:hover:border-white transition-colors">
                             <div class="flex items-center justify-between mb-2">
                                 <div class="flex items-center gap-2">
                                     <a href="<?= url('/user/' . $comment['user_id']) ?>" class="shrink-0 relative">
                                        <img src="<?= e($comment['avatar'] ?? '/assets/default-avatar.png') ?>" class="w-5 h-5 rounded-full object-cover">
                                     </a>
                                     <a href="<?= url('/user/' . $comment['user_id']) ?>" class="<?= (function_exists('is_plugin_active') && is_plugin_active('Ran_Vip')) ? \Plugins\Ran_Vip\Plugin::getVipColor($comment['user_id'], 'text-xs font-bold text-ink-900 dark:text-gray-200') : 'text-xs font-bold text-ink-900 dark:text-gray-200' ?> hover:text-accent"><?= e($commentUser) ?></a>
                                 </div>
                                 <span class="text-[10px] text-gray-400"><?= date('m-d', strtotime($comment['created_at'])) ?></span>
                             </div>
                             
                             <div class="text-xs text-gray-500 dark:text-gray-400 mb-2 line-clamp-2">
                                 回复 <a href="<?= url('/' . $comment['post_id'] . '.html') ?>" class="text-ink-900 dark:text-gray-300 font-medium hover:underline">《<?= e($comment['post_title']) ?>》</a>
                             </div>
                             
                             <a href="<?= url('/' . $comment['post_id'] . '.html') ?>" class="block text-sm text-ink-700 dark:text-gray-300 leading-relaxed bg-gray-50 dark:bg-white/5 p-3 rounded-xl rounded-tl-none hover:bg-gray-100 dark:hover:bg-white/10 transition-colors">
                                 <?= e($comment['content']) ?>
                             </a>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-xs text-ink-500 text-center py-4">暂时没有评论</div>
                    <?php endif; ?>
                </div>
                </div>
            </div>


             <!-- Widget: 靓号抢购 (Lucky UID) -->
             <?php
                if(function_exists('is_plugin_active') && is_plugin_active('Ran_Lianghao')):
                    $db = \Core\Database::getInstance(config('db'));
                    try {
                        // Query Integral Goods directly to show what is actually LISTED in the Mall
                        // We look for goods that have 'lianghao_uid' in their extra_data JSON
                        $rawGoods = $db->query("SELECT * FROM integral_goods WHERE extra_data LIKE '%lianghao_uid%' ORDER BY price DESC LIMIT 8")->fetchAll();
                        
                        $haoList = [];
                        foreach($rawGoods as $good) {
                            $extra = json_decode($good['extra_data'] ?? '', true);
                            if(isset($extra['lianghao_uid'])) {
                                $haoList[] = [
                                    'uid' => $extra['lianghao_uid'],
                                    'price' => $good['price'], // Points Price
                                    'id' => $good['id']
                                ];
                            }
                        }
                    } catch(\Exception $e) { $haoList = []; }
                    
                    // Fetch Integral Name Logic
                    $currencyName = '积分';
                    try {
                        $currencyNameRow = $db->query("SELECT option_value FROM options WHERE option_name = 'integral_currency_name'")->fetch();
                        if ($currencyNameRow) $currencyName = $currencyNameRow['option_value'];
                    } catch(\Exception $e) {}
                    
                    if(!empty($haoList)):
            ?>
            <div>
                 <div class="flex items-center gap-2 mb-6">
                    <span class="w-1 h-4 bg-ink-900 dark:bg-white rounded-full"></span>
                    <h3 class="font-bold text-ink-900 dark:text-gray-100">靓号抢购</h3>
                </div>
                
                <div class="grid grid-cols-4 gap-4">
                    <?php foreach($haoList as $hao): ?>
                    <div class="flip-card">
                        <div class="flip-card-inner">
                            <div class="flip-card-front bg-gray-50 dark:bg-white/5 p-2 flex-col">
                                <span class="text-gray-600 dark:text-gray-400 font-bold font-mono"><?= $hao['uid'] ?></span>
                            </div>
                            <!-- Back links to Mall Goods Detail or Mall Index -->
                            <a href="<?= url('/integral/goods/' . $hao['id']) ?>" class="flip-card-back bg-gradient-to-br from-pink-500 to-purple-600 p-2 flex-col" style="--tw-gradient-to: #000000 var(--tw-gradient-to-position);--tw-gradient-from: #818181 var(--tw-gradient-from-position);">
                                <span class="text-white font-bold text-xs"><?= number_format($hao['price']) ?></span>
                                <span class="text-white/80 text-[10px]"><?= $currencyName ?></span>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
             <?php endif; endif; ?>

            <!-- Widget: 合作伙伴 (Partner Grid) -->
            <div>
                <div class="flex items-center gap-2 mb-6">
                    <span class="w-1 h-4 bg-ink-900 dark:bg-white rounded-full"></span>
                    <h3 class="font-bold text-ink-900 dark:text-gray-100">合作伙伴</h3>
                </div>
                
                <style>
                    .flip-card {
                        perspective: 1000px;
                        height: 60px;
                    }
                    .flip-card-inner {
                        position: relative;
                        width: 100%;
                        height: 100%;
                        transition: transform 0.6s;
                        transform-style: preserve-3d;
                    }
                    .flip-card:hover .flip-card-inner {
                        transform: rotateY(180deg);
                    }
                    .flip-card-front, .flip-card-back {
                        position: absolute;
                        width: 100%;
                        height: 100%;
                        backface-visibility: hidden;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        border-radius: 0.5rem;
                    }
                    .flip-card-back {
                        transform: rotateY(180deg);
                    }
                </style>
            </div>

             <!-- Widget: 最新注册 (New Registered Users) -->
             <div>
                <div class="flex items-center gap-2 mb-6">
                    <span class="w-1 h-4 bg-ink-900 dark:bg-white rounded-full"></span>
                    <h3 class="font-bold text-ink-900 dark:text-gray-100">最新注册</h3>
                </div>
                 <div class="space-y-4">
                     <?php if(!empty($new_users)): ?>
                         <?php foreach($new_users as $user): ?>
                         <div class="flex items-center gap-3">
                             <a href="<?= url('/user/' . $user['id']) ?>" class="w-10 h-10 rounded-xl overflow-hidden bg-gray-100 dark:bg-white/10 shrink-0">
                                 <img src="<?= e($user['avatar']) ?>" alt="<?= e($user['username']) ?>" class="w-full h-full object-cover">
                             </a>
                             <div class="flex-1 min-w-0">
                                 <div class="flex items-center justify-between mb-0.5">
                                     <h4 class="font-bold text-ink-900 dark:text-gray-100 text-sm truncate">
                                         <a href="<?= url('/user/' . $user['id']) ?>" class="<?= (function_exists('is_plugin_active') && is_plugin_active('Ran_Vip')) ? \Plugins\Ran_Vip\Plugin::getVipColor($user['id'], '') : '' ?>"><?= e($user['username']) ?></a>
                                     </h4>
                                     <span class="text-[10px] text-gray-400 shrink-0"><?= date('m-d', strtotime($user['created_at'])) ?></span>
                                 </div>
                                 <div class="flex items-center gap-2">
                                     <span class="text-[10px] text-green-500 truncate"></span>
                                 </div>
                             </div>
                         </div>
                         <?php endforeach; ?>
                     <?php else: ?>
                        <div class="text-center text-xs text-gray-400 py-4">暂无新用户</div>
                     <?php endif; ?>
                 </div>
            </div>
        </aside>
    </main>


<?php $this->render('footer'); ?>

<!-- Image Preview Modal -->
<div id="image-preview-modal" class="fixed inset-0 z-[9999] bg-black/90 hidden flex items-center justify-center opacity-0 transition-opacity duration-300" onclick="closeImagePreview()">
    <div class="relative max-w-[90vw] max-h-[90vh]">
        <img id="preview-image" src="" alt="Preview" class="max-w-full max-h-[90vh] object-contain rounded-lg shadow-2xl">
        <button onclick="closeImagePreview()" class="absolute -top-12 right-0 text-white hover:text-gray-300 transition-colors">
            <i class="fas fa-times text-2xl"></i>
        </button>
    </div>
</div>

<script>
let page = 1;

function openImagePreview(event, src) {
    event.preventDefault();
    event.stopPropagation();
    
    const modal = document.getElementById('image-preview-modal');
    const img = document.getElementById('preview-image');
    
    img.src = src;
    modal.classList.remove('hidden');
    // Small delay to allow display:block to apply before opacity transition
    setTimeout(() => {
        modal.classList.remove('opacity-0');
    }, 10);
    document.body.style.overflow = 'hidden'; // Prevent scrolling
}

function closeImagePreview() {
    const modal = document.getElementById('image-preview-modal');
    modal.classList.add('opacity-0');
    
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = ''; // Restore scrolling
    }, 300);
}

function loadMore() {
    const btn = document.getElementById('load-more-btn');
    const container = document.getElementById('articles-list');
    const noMore = document.getElementById('no-more-data');
    
    if(!btn) return;
    
    btn.innerText = '加载中...';
    btn.disabled = true;
    
    page++;
    
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('page', page);
    urlParams.set('ajax', 1);
    
    fetch('?' + urlParams.toString())
    .then(res => res.text())
    .then(html => {
        if (!html.trim()) {
            btn.parentElement.classList.add('hidden');
            noMore.classList.remove('hidden');
        } else {
            // Append HTML but need to process it to respect current layout
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;
            
            // Check current layout
            const currentLayout = localStorage.getItem('articleLayout') || 'list';
            
            // Adjust new items if necessary (this logic mainly depends on CSS classes handles by switchLayout)
            // But we need to ensure the HTML structure matches. 
            // The server returns the same structure, JS handles visibility.
            // So we just append and then re-apply layout to ensure consistency
            
            while (tempDiv.firstChild) {
                const child = tempDiv.firstChild;
                if (child.nodeType === 1) { // Element node
                     updateArticleLayout(child, currentLayout);
                     container.appendChild(child);
                } else {
                    container.appendChild(child);
                }
            }
            
            btn.innerText = '加载更多文章';
            btn.disabled = false;
        }
    })
    .catch(err => {
        console.error(err);
        btn.innerText = '加载失败';
        btn.disabled = false;
    });
}

function toggleLayout() {
    const currentLayout = localStorage.getItem('articleLayout') || 'list';
    const newLayout = currentLayout === 'list' ? 'grid' : 'list';
    switchLayout(newLayout);
}

function switchLayout(layout) {
    const toggleBtn = document.getElementById('btn-layout-toggle');
    const toggleIcon = toggleBtn ? toggleBtn.querySelector('i') : null;
    const container = document.getElementById('articles-list');
    const articles = container.querySelectorAll('.article-item');
    
    // Save preference
    localStorage.setItem('articleLayout', layout);
    
    // Update Button Icon
    if (toggleIcon) {
        if (layout === 'list') {
            toggleIcon.className = 'fas fa-list';
            toggleBtn.title = '当前：列表视图';
            
            container.classList.remove('grid', 'grid-cols-1', 'gap-4');
            container.classList.add('flex', 'flex-col', 'gap-8');
        } else {
            toggleIcon.className = 'fas fa-th-large';
            toggleBtn.title = '当前：朋友圈视图';
            
            container.classList.remove('flex', 'flex-col', 'gap-8');
            container.classList.add('flex', 'flex-col', 'gap-8'); 
        }
    }
    
    articles.forEach(article => {
        updateArticleLayout(article, layout);
    });
}

// Initial check to set icon correctly on load
document.addEventListener('DOMContentLoaded', () => {
    const savedLayout = localStorage.getItem('articleLayout') || 'list';
    const toggleBtn = document.getElementById('btn-layout-toggle');
    if (toggleBtn) {
         const icon = toggleBtn.querySelector('i');
         if (savedLayout === 'list') {
             icon.className = 'fas fa-list';
         } else {
             icon.className = 'fas fa-th-large';
         }
    }
});

function updateArticleLayout(article, layout) {
    const listView = article.querySelector('.list-view-content');
    const gridView = article.querySelector('.grid-view-content');
    
    if (layout === 'list') {
        if(listView) listView.classList.remove('hidden');
        if(listView) listView.classList.add('flex');
        if(gridView) gridView.classList.add('hidden');
        if(gridView) gridView.classList.remove('flex');
        
        article.classList.add('border-b', 'border-ink-100', 'dark:border-white/10', 'pb-12');
        article.classList.remove('border-0', 'pb-4');
    } else {
        if(listView) listView.classList.add('hidden');
        if(listView) listView.classList.remove('flex');
        if(gridView) gridView.classList.remove('hidden');
        if(gridView) gridView.classList.add('flex');
        
        article.classList.remove('border-b', 'border-ink-100', 'dark:border-white/10', 'pb-12');
        article.classList.add('border-b', 'border-gray-100', 'dark:border-white/5', 'pb-8'); // Moments separator
    }
}

// Initial Load
document.addEventListener('DOMContentLoaded', () => {
    const savedLayout = localStorage.getItem('articleLayout') || 'list';
    switchLayout(savedLayout);
});
function toggleLike(postId, btn) {
    if(btn.dataset.processing) return;
    btn.dataset.processing = "1";

    const data = new FormData();
    data.append('post_id', postId);

    fetch('/post/like', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: data
    })
    .then(r => r.json())
    .then(res => {
        delete btn.dataset.processing;
        if(res.code === 401) {
            window.location.href = '/login';
            return;
        }
        if(res.status === 'success') {
            const icon = btn.querySelector('i');
            const countSpan = btn.querySelector('.like-count');
            
            if(res.action === 'liked') {
                icon.classList.remove('far');
                icon.classList.add('fas', 'text-red-500');
                btn.classList.add('text-red-500');
            } else {
                icon.classList.remove('fas', 'text-red-500');
                icon.classList.add('far');
                btn.classList.remove('text-red-500');
            }
            if(countSpan) countSpan.innerText = res.count;
        } else {
            alert(res.message);
        }
    })
    .catch(e => {
        console.error(e);
        delete btn.dataset.processing;
    });
}
</script>
<?php endif; ?>
