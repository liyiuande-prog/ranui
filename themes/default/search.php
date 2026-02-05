<?php 
if (!function_exists('highlight_kw')) {
    function highlight_kw($text, $query) {
        if (empty($query) || empty($text)) return e($text);
        $escapedText = e($text);
        $escapedQuery = e($query);
        return preg_replace(
            '/(' . preg_quote($escapedQuery, '/') . ')/iu', 
            '<mark class="bg-yellow-200 dark:bg-yellow-900/50 text-ink-900 dark:text-gray-100 rounded px-0.5 font-bold">$1</mark>', 
            $escapedText
        );
    }
}
?>
<?php if(empty($ajax)): ?>
<?php $this->render('header'); ?>
<main class="max-w-7xl mx-auto px-6 py-32 min-h-screen">
    <div class="mb-12 text-center">
        <h1 class="text-3xl font-bold mb-8 text-ink-900 dark:text-gray-100 tracking-tight">搜索 RanUI</h1>
        <form action="<?= url('/search') ?>" method="GET" class="max-w-2xl mx-auto relative group">
            <input type="text" name="q" value="<?= e($q) ?>" placeholder="搜索文章、评论或用户..." class="w-full pl-14 pr-6 py-4 bg-white dark:bg-white/5 border-2 border-gray-100 dark:border-white/10 rounded-full text-lg focus:border-ink-900 dark:focus:border-white focus:ring-0 transition-colors shadow-sm group-hover:shadow-md dark:text-gray-100 dark:placeholder-gray-500">
            <i class="fas fa-search absolute left-6 top-1/2 -translate-y-1/2 text-ink-400 dark:text-gray-500 text-lg"></i>
             <!-- Maintain type if searching again? Probably not, default to post/all is simpler unless hidden input -->
             <?php if(isset($_GET['type'])): ?><input type="hidden" name="type" value="<?= e($_GET['type']) ?>"><?php endif; ?>
        </form>
    </div>

    <?php if(!empty($q)): ?>
        <div class="flex border-b border-ink-100 dark:border-white/10 mb-8 overflow-x-auto no-scrollbar">
            <?php 
            $tabs = [
                'post' => '文章',
                'comment' => '评论',
                'user' => '用户'
            ];
            if(function_exists('is_plugin_active') && is_plugin_active('Ran_Integral')) $tabs['product'] = '商品';
            ?>
            <?php foreach($tabs as $key => $label): ?>
                <a href="<?= url('/search?q=' . urlencode($q) . '&type=' . $key) ?>" 
                   class="px-6 py-4 border-b-2 font-medium transition-colors whitespace-nowrap <?= $type === $key ? 'border-ink-900 text-ink-900 dark:border-white dark:text-white' : 'border-transparent text-ink-500 hover:text-ink-900 dark:text-gray-500 dark:hover:text-gray-300' ?>">
                   <?= $label ?>
                </a>
            <?php endforeach; ?>
        </div>
        
        <div class="flex justify-end gap-4 mb-8 text-sm">
             <div class="flex items-center gap-2">
                 <span class="text-ink-500 dark:text-gray-500">排序:</span>
                 <select onchange="updateParams('sort', this.value)" class="bg-transparent border-none font-bold text-ink-900 dark:text-gray-100 focus:ring-0 cursor-pointer py-0 pl-0 pr-8">
                     <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?> class="text-black">最新</option>
                     <option value="hot" <?= $sort == 'hot' ? 'selected' : '' ?> class="text-black">热门</option>
                 </select>
             </div>
             <?php if($type === 'post'): ?>
             <div class="flex items-center gap-2">
                 <span class="text-ink-500 dark:text-gray-500">时间:</span>
                 <select onchange="updateParams('time', this.value)" class="bg-transparent border-none font-bold text-ink-900 dark:text-gray-100 focus:ring-0 cursor-pointer py-0 pl-0 pr-8">
                     <option value="all" <?= $time == 'all' ? 'selected' : '' ?> class="text-black">全部</option>
                     <option value="day" <?= $time == 'day' ? 'selected' : '' ?> class="text-black">一天内</option>
                     <option value="week" <?= $time == 'week' ? 'selected' : '' ?> class="text-black">一周内</option>
                 </select>
             </div>
             <?php if(isset($_SESSION['user'])): ?>
             <div class="flex items-center gap-2">
                 <span class="text-ink-500 dark:text-gray-500">范围:</span>
                 <select onchange="updateParams('scope', this.value)" class="bg-transparent border-none font-bold text-ink-900 dark:text-gray-100 focus:ring-0 cursor-pointer py-0 pl-0 pr-8">
                     <option value="all" <?= ($scope ?? 'all') == 'all' ? 'selected' : '' ?> class="text-black">全部</option>
                     <option value="follow" <?= ($scope ?? 'all') == 'follow' ? 'selected' : '' ?> class="text-black">关注</option>
                 </select>
             </div>
             <?php endif; ?>
             <?php endif; ?>
        </div>
        
        <?php
            $gridClass = 'flex flex-col';
            if ($type === 'comment') $gridClass = 'grid grid-cols-1 md:grid-cols-2 gap-6';
            if ($type === 'user' || $type === 'product') $gridClass = 'grid grid-cols-2 md:grid-cols-4 gap-6';
        ?>
        <div id="search-results" class="<?= $gridClass ?>">
<?php endif; ?>
            <?php if(!empty($results)): ?>
                <?php foreach($results as $item): ?>
                    <?php if ($type === 'post'): $post = $item; ?>
                        <article class="flex flex-col md:flex-row gap-6 group border-b border-ink-100 dark:border-white/10 pb-8 mb-8 last:border-0 last:mb-0 last:pb-0">
                           <?php if($post['cover_image']): ?>
                           <a href="<?= url('/' . $post['id'] . '.html') ?>" class="w-full md:w-48 h-32 shrink-0 bg-gray-100 dark:bg-white/10 rounded-xl overflow-hidden block">
                               <img src="<?= e($post['cover_image']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                           </a>
                           <?php endif; ?>
                           <div class="flex-1">
                               <a href="<?= url('/' . $post['id'] . '.html') ?>">
                                   <h4 class="text-xl font-bold text-ink-900 dark:text-gray-100 mb-2 group-hover:text-accent transition-colors">
                                       <?= highlight_kw($post['title'], $q) ?>
                                       <?php \Core\Hook::listen('theme_post_title_suffix', $post); ?>
                                   </h4>
                               </a>
                               <p class="text-sm text-ink-500 dark:text-gray-400 line-clamp-2 leading-relaxed mb-3"><?= highlight_kw($post['description'], $q) ?></p>
                               <div class="flex items-center gap-3 text-xs text-ink-400 dark:text-gray-500">
                                   <a href="<?= url('/user/' . $post['user_id']) ?>" class="relative block">
                                       <img src="<?= e($post['author_avatar']) ?>" class="w-5 h-5 rounded-full">
                                       <?= (function_exists('is_plugin_active') && is_plugin_active('Ran_Name')) ? \Plugins\Ran_Name\Plugin::renderVerifyIcon($post['user_id'], 'w-2 h-2 !bottom-0 !right-0') : '' ?>
                                   </a>
                                   <a href="<?= url('/user/' . $post['user_id']) ?>" class="hover:text-accent transition-colors"><?= e($post['author_name']) ?></a>
                                   <span>·</span>
                                   <span><?= date('M d', strtotime($post['created_at'])) ?></span>
                                   <span>·</span>
                                   <span class="flex items-center gap-1"><i class="far fa-eye"></i> <?= number_format($post['view_count'] ?? 0) ?></span>
                                   <button onclick="toggleLike(<?= $post['id'] ?>, this)" class="flex items-center gap-1 hover:text-red-500 transition-colors group-like ml-2" title="点赞">
                                        <i class="far fa-heart transition-transform group-active:scale-125"></i>
                                        <span class="like-count"><?= number_format($post['like_count'] ?? 0) ?></span>
                                   </button>
                               </div>
                           </div>
                        </article>
                    <?php elseif ($type === 'comment'): $comment = $item; ?>
                        <div class="bg-gray-50 dark:bg-white/5 p-6 rounded-2xl border border-transparent hover:border-gray-200 dark:hover:border-white/20 transition-colors">
                           <div class="flex gap-3 mb-3">
                                <div class="relative shrink-0">
                                    <img src="<?= e($comment['avatar'] ?? '/assets/default-avatar.png') ?>" class="w-8 h-8 rounded-full object-cover">
                                    <?= (function_exists('is_plugin_active') && is_plugin_active('Ran_Name')) ? \Plugins\Ran_Name\Plugin::renderVerifyIcon($comment['user_id'], 'w-3 h-3 !bottom-0 !right-0') : '' ?>
                                </div>
                               <div class="text-sm">
                                   <span class="font-bold text-ink-900 dark:text-gray-100"><?= e($comment['username']) ?></span>
                                   <span class="text-ink-400 dark:text-gray-500 text-xs">发表于</span>
                                   <a href="<?= url('/' . $comment['post_id'] . '.html') ?>" class="text-accent hover:underline font-medium block truncate max-w-[200px]">
                                       <?= highlight_kw($comment['post_title'], $q) ?>
                                   </a>
                               </div>
                           </div>
                           <p class="text-ink-600 dark:text-gray-300 italic text-sm line-clamp-3">"<?= highlight_kw($comment['content'], $q) ?>"</p>
                        </div>
                    <?php elseif ($type === 'user'): $user = $item; ?>
                        <a href="<?= url('/user/' . $user['id']) ?>" class="flex items-center gap-4 p-4 border border-gray-100 dark:border-white/10 rounded-xl hover:shadow-lg hover:-translate-y-1 transition-all bg-white dark:bg-white/5 group">
                            <div class="relative">
                                <img src="<?= e($user['avatar'] ?? '/assets/default-avatar.png') ?>" class="w-12 h-12 rounded-full border-2 border-gray-50 dark:border-white/10 group-hover:border-accent/50 transition-colors">
                                <?= (function_exists('is_plugin_active') && is_plugin_active('Ran_Name')) ? \Plugins\Ran_Name\Plugin::renderVerifyIcon($user['id'], 'w-4 h-4 !bottom-0 !right-0') : '' ?>
                            </div>
                            <div class="overflow-hidden">
                                <p class="font-bold text-ink-900 dark:text-gray-100 truncate group-hover:text-accent transition-colors"><?= highlight_kw($user['username'], $q) ?></p>
                                <span class="px-2 py-0.5 bg-gray-100 dark:bg-white/10 text-[10px] font-bold uppercase tracking-wider rounded text-ink-500 dark:text-gray-400"><?= e($user['role'] ?? 'User') ?></span>
                                <!-- Hook: Follow Button -->
                                <?php \Core\Hook::listen('theme_follow_button', $user['id']); ?>
                            </div>
                        </a>
                    <?php elseif ($type === 'product'): $product = $item; ?>
                        <a href="<?= url('/integral/goods/' . $product['id']) ?>" class="group bg-white dark:bg-white/5 rounded-xl overflow-hidden border border-gray-100 dark:border-white/10 hover:shadow-lg transition-all">
                            <div class="relative w-full pb-[100%] bg-gray-100 dark:bg-white/10">
                                <img src="<?= e($product['image']) ?>" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            </div>
                            <div class="p-4">
                                <h4 class="font-bold text-ink-900 dark:text-gray-100 truncate mb-1"><?= highlight_kw($product['name'], $q) ?></h4>
                                <div class="flex items-baseline gap-1 text-accent font-bold">
                                    <?= number_format($product['points_price']) ?> 积分
                                    <?php if($product['money'] > 0): ?>
                                        <span class="text-xs text-ink-400">+ ¥<?= $product['money'] ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                 <?php if(empty($ajax)): ?>
                 <div class="col-span-full text-center py-20 text-ink-400 dark:text-gray-600">
                     <div class="w-16 h-16 bg-gray-50 dark:bg-white/5 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                        <i class="far fa-sad-tear"></i>
                     </div>
                     <p>未找到相关结果</p>
                 </div>
                 <?php endif; ?>
            <?php endif; ?>
<?php if(empty($ajax)): ?>
        </div>
        
        <?php if(!empty($results)): ?>
        <div class="pt-12 flex justify-center" id="load-more-container">
            <button onclick="loadMore()" id="load-more-btn" class="border border-ink-100 dark:border-white/20 text-ink-500 dark:text-gray-400 px-8 py-3 rounded-full text-sm hover:border-ink-900 hover:text-ink-900 dark:hover:border-white dark:hover:text-white transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-ink-100">
                加载更多
            </button>
             <div id="no-more-data" class="hidden pt-12 text-center text-ink-500 text-sm">没有更多了</div>
        </div>
        <?php endif; ?>

    <?php endif; ?>
</main>
<?php $this->render('footer'); ?>
<script>
function updateParams(key, value) {
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set(key, value);
    urlParams.set('page', 1); // Reset page
    window.location.search = urlParams.toString();
}

let page = 1;
function loadMore() {
    const btn = document.getElementById('load-more-btn');
    const container = document.getElementById('search-results');
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
            container.insertAdjacentHTML('beforeend', html);
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
</script>
<script>
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
