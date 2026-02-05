<?php if(empty($ajax)): ?>
<?php $this->render('header'); ?>

<main class="max-w-7xl mx-auto px-6 pt-32 pb-20 flex flex-col lg:flex-row gap-16 min-h-screen">

    <!-- Left: Content (Posts) -->
    <div class="lg:w-[65%] w-full">
        <div class="flex items-center justify-between mb-8 border-b border-ink-100 dark:border-white/10 pb-4">
            <h2 class="text-2xl font-bold text-ink-900 dark:text-gray-100">
                发布的文章 <span class="text-sm font-normal text-ink-500 dark:text-gray-500 ml-2"><?= count($posts) ?> 篇</span>
            </h2>
            <!-- Layout Switcher -->
            <div class="flex gap-2">
                 <button onclick="switchLayout('list')" id="btn-list" class="p-2 rounded hover:bg-gray-100 dark:hover:bg-white/10 text-ink-900 dark:text-gray-200 transition-colors" title="列表视图">
                    <i class="fas fa-list"></i>
                </button>
                <button onclick="switchLayout('grid')" id="btn-grid" class="p-2 rounded hover:bg-gray-100 dark:hover:bg-white/10 text-gray-400 transition-colors" title="朋友圈视图">
                    <i class="fas fa-th-large"></i>
                </button>
            </div>
        </div>

        <div id="articles-list" class="flex flex-col gap-8">
            <?php if(empty($posts)): ?>
                <div class="text-center py-20 text-ink-400 dark:text-gray-600">
                    <p class="font-medium">暂无文章</p>
                </div>
            <?php else: ?>
<?php endif; ?>
            <?php foreach($posts as $post): ?>
                <article class="article-item group animate-fade-in border-b border-ink-100 dark:border-white/10 pb-12 last:border-0" data-layout="list">
                    <!-- 列表样式 (List View) -->
                    <div class="list-view-content flex flex-row gap-4 sm:gap-8">
                        <div class="flex-1 flex flex-col justify-center min-w-0">
                            <div class="flex items-center gap-2 mb-2">
                                <a href="<?= url('/user/' . $post['user_id']) ?>" class="relative block shrink-0">
                                    <img src="<?= e($profile_user['avatar'] ?? '/assets/default-avatar.png') ?>" class="w-10 h-10 rounded-full object-cover">
                                    <?= (function_exists('is_plugin_active') && is_plugin_active('Ran_Name')) ? \Plugins\Ran_Name\Plugin::renderVerifyIcon($post['user_id'], 'w-2 h-2 !bottom-0 !right-0') : '' ?>
                                </a>
                                <div class="flex flex-col">
                                    <a href="<?= url('/user/' . $post['user_id']) ?>" class="<?= (function_exists('is_plugin_active') && is_plugin_active('Ran_Vip')) ? \Plugins\Ran_Vip\Plugin::getVipColor($profile_user['id'] ?? $post['user_id'], 'hover:text-accent transition-colors text-xs font-medium dark:text-gray-300') : 'hover:text-accent transition-colors text-xs font-medium dark:text-gray-300' ?>"><?= e($profile_user['username'] ?? '') ?></a>
                                    <span class="text-[10px] text-gray-400"><?= date('m月d日', strtotime($post['created_at'])) ?></span>
                                </div>
                            </div>
                            <a href="<?= url('/' . $post['id'] . '.html') ?>" class="block">
                                <h3 class="text-lg md:text-2xl font-bold mb-2 group-hover:underline decoration-2 underline-offset-4 decoration-accent dark:text-gray-100 line-clamp-2 leading-snug">
                                    <?= e($post['title']) ?>
                                    <?php \Core\Hook::listen('theme_post_title_suffix', $post); ?>
                                </h3>
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
                            <img src="<?= e($post['cover_image']) ?>" class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500">
                        </a>
                        <?php endif; ?>
                    </div>

                    <!-- 朋友圈样式 (Grid/Moments View) -->
                    <div class="grid-view-content hidden flex-col gap-3">
                        <div class="flex items-start gap-3">
                             <a href="<?= url('/user/' . $post['user_id']) ?>" class="relative block shrink-0 mt-1">
                                <img src="<?= e($profile_user['avatar'] ?? '/assets/default-avatar.png') ?>" class="w-10 h-10 rounded-lg object-cover">
                                <?= (function_exists('is_plugin_active') && is_plugin_active('Ran_Name')) ? \Plugins\Ran_Name\Plugin::renderVerifyIcon($post['user_id'], 'w-3 h-3 !bottom-[2px] !right-[2px]') : '' ?>
                            </a>
                            <div class="flex-1 min-w-0">
                                 <div class="flex items-center justify-between mb-1">
                                     <a href="<?= url('/user/' . $post['user_id']) ?>" class="<?= (function_exists('is_plugin_active') && is_plugin_active('Ran_Vip')) ? \Plugins\Ran_Vip\Plugin::getVipColor($profile_user['id'] ?? $post['user_id'], 'font-bold text-ink-900 dark:text-gray-100 text-sm') : 'font-bold text-ink-900 dark:text-gray-100 text-sm' ?> hover:text-accent"><?= e($profile_user['username'] ?? '') ?></a>
                                 </div>
                                 <div class="text-xs text-ink-500 dark:text-gray-400 mb-2">
                                    <p class="mb-2"><?= e($post['description'] ?: $post['title']) ?></p>
                                    
                                    <?php if(!empty($post['cover_image'])): ?>
                                     <div class="block w-full max-w-[200px] h-32 bg-gray-100 dark:bg-white/5 rounded-lg overflow-hidden mb-2 cursor-zoom-in group-image">
                                        <img src="<?= e($post['cover_image']) ?>" class="w-full h-full object-cover" onclick="openImagePreview(event, this.src)">
                                    </div>
                                    <?php else: ?>
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
                                                <a href="<?= url('/c/' . $post['circle_slug']) ?>" class="text-accent"><?= e($post['circle_name']) ?></a>
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
<?php if(empty($ajax)): ?>
            <?php endif; ?>
        </div>

        
        <!-- 加载更多按钮 -->
        <div class="pt-12 flex justify-center" id="load-more-container">
            <button onclick="loadMore()" id="load-more-btn" class="border border-ink-100 dark:border-white/20 text-ink-500 dark:text-gray-400 px-8 py-3 rounded-full text-sm hover:border-ink-900 hover:text-ink-900 dark:hover:border-white dark:hover:text-white transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-ink-100">
                加载更多文章
            </button>
        </div>
        
        <!-- 无更多数据提示 (默认隐藏) -->
        <div id="no-more-data" class="hidden pt-12 text-center text-ink-500 dark:text-gray-500 text-sm">
            没有更多内容了
        </div>
    </div>

    <!-- Right: Sidebar (Profile Info) -->
    <aside class="lg:w-[35%] w-full">
        <div class="sticky top-32 space-y-8">
            <!-- Profile Card -->
            <div class="text-center md:text-left">
                <div class="relative inline-block mb-6 mx-auto md:mx-0">
                    <img src="<?= e($profile_user['avatar'] ?? '/assets/default-avatar.png') ?>" class="w-32 h-32 rounded-full border-4 border-white dark:border-[#151515] shadow-lg block">
                    <?= (function_exists('is_plugin_active') && is_plugin_active('Ran_Name')) ? \Plugins\Ran_Name\Plugin::renderVerifyIcon($profile_user['id'], 'w-8 h-8') : '' ?>
                </div>
                
                <h1 class="text-3xl font-extrabold <?= (function_exists('is_plugin_active') && is_plugin_active('Ran_Vip')) ? \Plugins\Ran_Vip\Plugin::getVipColor($profile_user['id'], 'text-ink-900 dark:text-white') : 'text-ink-900 dark:text-white' ?> mb-2">
                    <?= e($profile_user['username'] ?? $profile_user['username']) ?>
                    <?php if (class_exists('Plugins\Ran_Title\Plugin')) \Plugins\Ran_Title\Plugin::renderTitle($profile_user['id']); ?>
                </h1>
                <div class="flex items-center justify-center md:justify-start gap-2 mb-4">
                    <?php 
                        $uidDisplay = !empty($profile_user['uid']) ? $profile_user['uid'] : '@' . $profile_user['username'];
                        $isLh = false;
                        if (!empty($profile_user['uid']) && function_exists('is_plugin_active') && is_plugin_active('Ran_Lianghao')) {
                            $isLh = \Plugins\Ran_Lianghao\Plugin::checkIsLianghao($profile_user['uid']);
                        }
                        
                        if ($isLh) {
                             \Plugins\Ran_Lianghao\Plugin::renderBadge($profile_user['uid']);
                        } else {
                             echo '<span class="font-medium text-ink-500 dark:text-gray-400">' . e($uidDisplay) . '</span>';
                        }
                    ?>
                </div>
                <!-- Bio Display -->
                <div class="text-sm text-gray-500 dark:text-gray-400 mb-6 leading-relaxed max-w-sm">
                    <?= e($profile_user['bio'] ?? '') ?: '这家伙很懒，什么也没留下' ?>
                </div>
                <div class="flex flex-wrap gap-2 justify-center md:justify-start mb-8">
                    <span class="px-3 py-1 bg-gray-100 dark:bg-white/10 text-ink-500 dark:text-gray-400 text-xs font-bold rounded-full">
                         加入于 <?= date('Y', strtotime($profile_user['created_at'])) ?>
                    </span>
                                            <?php \Core\Hook::listen('theme_follow_stats', $profile_user['id']); ?>

                        <?php \Core\Hook::listen('theme_follow_button', $profile_user['id']); ?>

                        <?php 
                        $db = \Core\Database::getInstance(config('db'));
                        $msgActive = $db->query("SELECT is_active FROM plugins WHERE name = 'Ran_Messages'")->fetch();
                        if ($msgActive && $msgActive['is_active'] == 1 && isset($_SESSION['user']['id']) && $_SESSION['user']['id'] != $profile_user['id']): 
                        ?>
                        <a href="<?= url('/messages/chat/' . $profile_user['id']) ?>" class="ml-2 bg-blue-600 text-white text-[10px] font-bold px-3 py-1 rounded-full hover:bg-blue-700 transition-all flex items-center gap-1">
                            <i class="far fa-comment-dots"></i> 私信
                        </a>
                        <?php endif; ?>

                </div>
                
                <!-- Hook: User Reward -->
                <?php \Core\Hook::listen('theme_user_reward', $profile_user); ?>
                
                <!-- Hook: Follow Stats & Button -->

                
                <!-- Stats -->
                <div class="grid grid-cols-3 gap-4 py-6 border-t border-b border-ink-100 dark:border-white/10">
                    <div class="text-center">
                        <span class="block font-bold text-xl text-ink-900 dark:text-white"><?= count($posts) ?></span>
                        <span class="text-xs text-ink-500 dark:text-gray-500 uppercase">文章</span>
                    </div>
                    <div class="text-center">
                        <span class="block font-bold text-xl text-ink-900 dark:text-white">0</span>
                        <span class="text-xs text-ink-500 dark:text-gray-500 uppercase">评论</span>
                    </div>
                    <div class="text-center">
                        <span class="block font-bold text-xl text-ink-900 dark:text-white">0</span>
                        <span class="text-xs text-ink-500 dark:text-gray-500 uppercase">获赞</span>
                    </div>
                </div>
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
            
            while (tempDiv.firstChild) {
                const child = tempDiv.firstChild;
                if (child.nodeType === 1) { // Element node
                     updateArticleLayout(child, currentLayout);
                     container.appendChild(child);
                } else {
                    container.appendChild(child);
                }
            }

            btn.innerText = '加载更多';
            btn.disabled = false;
        }
    })
    .catch(err => {
        console.error(err);
        btn.innerText = '加载失败';
        btn.disabled = false;
    });
}

function switchLayout(layout) {
    const listBtn = document.getElementById('btn-list');
    const gridBtn = document.getElementById('btn-grid');
    const container = document.getElementById('articles-list');
    const articles = container.querySelectorAll('.article-item');
    
    // Save preference
    localStorage.setItem('articleLayout', layout);
    
    // Update Buttons
    if (layout === 'list') {
        if(listBtn) {
            listBtn.classList.remove('text-gray-400');
            listBtn.classList.add('text-ink-900', 'dark:text-gray-200');
        }
        if(gridBtn) {
            gridBtn.classList.add('text-gray-400');
            gridBtn.classList.remove('text-ink-900', 'dark:text-gray-200');
        }
        
        container.classList.remove('grid', 'grid-cols-1', 'gap-4');
        container.classList.add('flex', 'flex-col', 'gap-8');
    } else {
        if(gridBtn) {
            gridBtn.classList.remove('text-gray-400');
            gridBtn.classList.add('text-ink-900', 'dark:text-gray-200');
        }
        if(listBtn) {
            listBtn.classList.add('text-gray-400');
            listBtn.classList.remove('text-ink-900', 'dark:text-gray-200');
        }
        
        container.classList.remove('flex', 'flex-col', 'gap-8');
        container.classList.add('flex', 'flex-col', 'gap-8'); 
    }
    
    articles.forEach(article => {
        updateArticleLayout(article, layout);
    });
}

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
        article.classList.add('border-b', 'border-gray-100', 'dark:border-white/5', 'pb-8'); 
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
