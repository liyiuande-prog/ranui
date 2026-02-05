<?php $this->render('header'); ?>

<main class="max-w-7xl mx-auto px-6 pt-32 pb-20 flex flex-col lg:flex-row gap-16">
    <!-- themes/default/post.php -->
    <div class="lg:w-[65%] w-full">
        <article>
            <!-- Title -->
            <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight leading-tight mb-6 text-ink-900 dark:text-gray-100">
                <?= e($post['title']) ?>
                <!-- Hook: Title Suffix (e.g. Bounty Icon) -->
                <?php \Core\Hook::listen('theme_post_title_suffix', $post); ?>
            </h1>

            <!-- Meta Info -->
            <div class="flex flex-wrap items-center gap-y-3 gap-x-4 mb-8 text-sm text-ink-500 dark:text-gray-400">
                <!-- Category/Circle Badge -->
                <?php 
                    $showCircle = function_exists('is_plugin_active') && is_plugin_active('Ran_Circle') && !empty($post['circle_name']);
                ?>
                <?php if($showCircle): ?>
                    <a href="<?= url('/c/' . $post['circle_slug']) ?>" class="px-3 py-1 rounded-full bg-gray-100 dark:bg-white/10 hover:bg-ink-900 hover:text-white dark:hover:bg-white dark:hover:text-black transition-colors font-bold text-xs">
                        <?= e($post['circle_name']) ?>
                    </a>
                <?php elseif(!empty($post['category_name'])): ?>
                    <a href="<?= url('/?category=' . $post['category_id']) ?>" class="px-3 py-1 rounded-full bg-gray-100 dark:bg-white/10 hover:bg-ink-900 hover:text-white dark:hover:bg-white dark:hover:text-black transition-colors font-bold text-xs">
                        <?= e($post['category_name']) ?>
                    </a>
                <?php endif; ?>
                
                <div class="flex items-center gap-1">
                     <i class="far fa-clock text-xs opacity-70"></i>
                     <span><?= date('m月d日', strtotime($post['created_at'])) ?></span>
                </div>
                
                <div class="flex items-center gap-1">
                     <i class="fas fa-book-open text-xs opacity-70"></i>
                     <span><?= e($post['read_time']) ?></span>
                </div>
                
                <div class="flex items-center gap-1">
                     <i class="far fa-eye text-xs opacity-70"></i>
                     <span><?= number_format($post['view_count']) ?> 阅读</span>
                </div>
                
                <button onclick="toggleLike(<?= $post['id'] ?>, this)" class="flex items-center gap-1 hover:text-red-500 transition-colors group-like ml-auto md:ml-0" title="点赞">
                    <i class="far fa-heart transition-transform group-active:scale-125"></i> 
                    <span><?= number_format($post['like_count'] ?? 0) ?> 点赞</span>
                </button>
            </div>

            <!-- Description (Lead Paragraph) -->
            <?php if(!empty($post['description'])): ?>
                <p class="text-lg md:text-xl text-ink-500 dark:text-gray-300 leading-relaxed font-normal mb-8 border-l-4 border-ink-900 dark:border-white pl-4 italic bg-gray-50 dark:bg-white/5 py-4 pr-4 rounded-r-xl">
                    <?= e($post['description']) ?>
                </p>
            <?php endif; ?>

            <!-- Mobile Author Info (Visible only on small screens) -->
            <div class="lg:hidden flex items-center gap-4 mb-8 pb-8 border-b border-ink-100 dark:border-white/10">
                <a href="<?= url('/user/' . $post['user_id']) ?>" class="relative block">
                    <img src="<?= e($post['author_avatar']) ?>" alt="<?= e($post['author_name']) ?>" class="w-12 h-12 rounded-full border border-ink-100 dark:border-white/10">
                    <?= (function_exists('is_plugin_active') && is_plugin_active('Ran_Name')) ? \Plugins\Ran_Name\Plugin::renderVerifyIcon($post['user_id']) : '' ?>
                </a>
                <div>
                    <div class="flex items-center flex-wrap gap-2">
                        <a href="<?= url('/user/' . $post['user_id']) ?>" class="<?= (function_exists('is_plugin_active') && is_plugin_active('Ran_Vip')) ? \Plugins\Ran_Vip\Plugin::getVipColor($post['user_id'], 'font-bold text-ink-900 dark:text-gray-100') : 'font-bold text-ink-900 dark:text-gray-100' ?> hover:text-accent transition-colors"><?= e($post['author_name']) ?></a>
                        <?php if (class_exists('Plugins\Ran_Title\Plugin')) \Plugins\Ran_Title\Plugin::renderTitle($post['user_id']); ?>
                        <?php \Core\Hook::listen('theme_post_author_suffix', $post); ?>
                    </div>
                    <p class="text-xs text-ink-500 dark:text-gray-400 line-clamp-1"><?= e($post['author_bio'] ?? '这家伙很懒，什么也没留下') ?></p>
                </div>
            </div>

            <!-- Content (Rendered via JS) -->
            <!-- Custom Styles for this Post -->
            <style>
                /* Beautiful Headings */
                #parsed-content h1, #parsed-content h2, #parsed-content h3 { position: relative; color: #1a202c; margin-top: 2em; margin-bottom: 1em; font-weight: 800; letter-spacing: -0.025em; scroll-margin-top: 120px; }
                .dark #parsed-content h1, .dark #parsed-content h2, .dark #parsed-content h3 { color: #f7fafc; }
                
                #parsed-content h1 { font-size: 2.25rem; line-height: 2.5rem; padding-bottom: 0.5rem; border-bottom: 2px solid #e2e8f0; }
                .dark #parsed-content h1 { border-color: #2d3748; }
                
                #parsed-content h2 { font-size: 1.875rem; line-height: 2.25rem; padding-left: 1rem; border-left: 4px solid #000; }
                .dark #parsed-content h2 { border-left-color: #fff; }
                
                #parsed-content h3 { font-size: 1.5rem; line-height: 2rem; color: #4a5568; }
                .dark #parsed-content h3 { color: #cbd5e0; }

                /* Links */
                #parsed-content a { color: #3b82f6; text-decoration: none; border-bottom: 1px solid #3b82f6; transition: all 0.2s; font-weight: 500; }
                #parsed-content a:hover { color: #2563eb; border-bottom-width: 2px; }
                .dark #parsed-content a { color: #60a5fa; border-color: #60a5fa; }
                .dark #parsed-content a:hover { color: #93c5fd; }
                
                /* Strong & Em */
                #parsed-content strong { color: #000; font-weight: 700; background: linear-gradient(120deg, #84fab0 0%, #8fd3f4 100%); background-repeat: no-repeat; background-size: 100% 0.2em; background-position: 0 88%; transition: background-size 0.25s ease-in; }
                .dark #parsed-content strong { color: #fff; background: linear-gradient(120deg, #2b6cb0 0%, #2c5282 100%); }
                #parsed-content strong:hover { background-size: 100% 88%; }
                
                #parsed-content em { font-style: italic; color: #718096; font-family: serif; font-size: 1.1em; }
                .dark #parsed-content em { color: #a0aec0; }
                
                /* Adaptive Images */
                #parsed-content img { 
                    display: block; 
                    max-width: 100%; 
                    height: auto; /* Maintains Aspect Ratio (1:1, 4:3, 16:9, etc.) */
                    margin: 2rem auto; 
                    border-radius: 12px; 
                    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); 
                    transition: transform 0.3s ease;
                }
                #parsed-content img:hover { transform: scale(1.01); }
                
                /* Code Blocks */
                #parsed-content pre { margin: 2rem 0; padding: 0; border-radius: 12px; overflow: hidden; background: #282c34; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
                #parsed-content code.hljs { padding: 1.5rem; font-family: 'Fira Code', 'Menlo', monospace; font-size: 0.9em; line-height: 1.7; }
                
                /* Video */
                #parsed-content video { width: 100%; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
                
                /* Image Grid Styles */
                .img-grid-container {
                    display: grid;
                    gap: 0.5rem;
                    margin: 2rem 0;
                    width: 100%;
                    grid-template-columns: repeat(3, 1fr);
                }
                .img-grid-2, .img-grid-4 { grid-template-columns: repeat(2, 1fr) !important; }

                .img-grid-item {
                    position: relative;
                    padding-bottom: 100%; /* Square Aspect Ratio */
                    overflow: hidden;
                    border-radius: 8px;
                    cursor: zoom-in;
                }
                
                .img-grid-item img {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                    margin: 0 !important;
                    border-radius: 0 !important;
                    box-shadow: none !important;
                    transition: transform 0.3s ease;
                }
                .img-grid-item:hover img {
                    transform: scale(1.05);
                }
                
                /* Single Image Adaptive Container */
                .img-single-container {
                    width: 100%;
                    margin: 2rem 0;
                    border-radius: 12px;
                    overflow: hidden;
                    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
                    background: #f3f4f6; /* Placeholder color */
                }
                .dark .img-single-container { background: #2d3748; }
                .img-single-container img {
                    margin: 0 !important;
                    width: 100% !important;
                    height: 100% !important;
                    object-fit: cover;
                    border-radius: 0 !important;
                    box-shadow: none !important;
                }
            </style>
            
            <!-- Hook: Post Content Before (e.g. Vote) -->
            <?php \Core\Hook::listen('theme_post_content_before', $post); ?>
            
            <!-- Hook: Bounty -->
            <?php \Core\Hook::listen('theme_post_bounty', $post); ?>
            
            <div id="parsed-content" class="prose prose-lg prose-slate dark:prose-invert max-w-none text-ink-900 dark:text-gray-200 leading-relaxed mb-20 min-h-[200px]">
                <div class="animate-pulse space-y-4">
                    <div class="h-4 bg-gray-200 dark:bg-white/10 rounded w-3/4"></div>
                    <div class="h-4 bg-gray-200 dark:bg-white/10 rounded"></div>
                    <div class="h-4 bg-gray-200 dark:bg-white/10 rounded w-5/6"></div>
                </div>
            </div>
            
            <!-- Tags Section -->
            <?php if(!empty($tags)): ?>
            <div class="flex flex-wrap gap-2 mb-12">
                <?php foreach($tags as $tag): ?>
                <a href="<?= url('/?tag=' . urlencode($tag['slug'])) ?>" class="inline-flex items-center gap-1.5 px-4 py-2 bg-gray-100 dark:bg-white/5 hover:bg-gray-200 dark:hover:bg-white/10 rounded-full text-sm font-medium text-ink-700 dark:text-gray-300 transition-colors border border-transparent hover:border-gray-300 dark:hover:border-white/20">
                    <i class="fas fa-tag text-xs text-ink-400"></i>
                    <?= e($tag['name']) ?>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <!-- Hook: Lucky Draw -->
            <?php \Core\Hook::listen('theme_post_lucky', $post); ?>

            <!-- Hook: Post Reward -->
            <?php \Core\Hook::listen('theme_post_reward', $post); ?>
        </article>
        
        <!-- Comments Section -->
        <div class="border-t border-ink-100 dark:border-white/10 pt-16">
            <h3 class="text-2xl font-bold text-ink-900 dark:text-gray-100 mb-10">评论 (<?= count($comments) ?>)</h3>

            <!-- Comment Form -->
            <div class="mb-14">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <form action="<?= url('/comment/add') ?>" method="POST" class="group relative">
                        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                        <input type="hidden" name="parent_id" id="comment-parent-id" value="0">
                        
                        <!-- Reply Indicator -->
                        <div id="reply-badge" class="hidden absolute -top-8 left-14 bg-gray-100 dark:bg-white/10 px-3 py-1 rounded-t-lg text-xs text-gray-500 flex items-center gap-2">
                            <span>回复 <b id="reply-user" class="text-ink-900 dark:text-white"></b></span>
                            <button type="button" onclick="cancelReply()" class="hover:text-red-500"><i class="fas fa-times"></i></button>
                        </div>

                        <div class="flex gap-4 items-start">
                            <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-white/20 flex-shrink-0 overflow-hidden">
                                <img src="<?= e($_SESSION['user']['avatar'] ?? '/assets/default-avatar.png') ?>" class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1">
                                <textarea id="comment-editor" name="content" class="w-full bg-gray-50 dark:bg-white/5 border border-transparent dark:border-white/10 rounded-2xl p-4 text-ink-900 dark:text-gray-200 placeholder-ink-400 focus:bg-white dark:focus:bg-black focus:border-ink-200 dark:focus:border-white/20 focus:ring-0 transition-all resize-y min-h-[120px]" placeholder="写下您的想法..."></textarea>
                                <div class="flex justify-between items-center mt-3">
                                    <button type="button" onclick="toggleAtList(this)" class="p-2 hover:bg-gray-200 dark:hover:bg-white/10 rounded-full transition-colors text-gray-500" title="@用户">
                                        <i class="fas fa-at"></i>
                                    </button>
                                    <button type="submit" class="bg-ink-900 text-white dark:bg-white dark:text-black px-6 py-2.5 rounded-full text-sm font-bold hover:bg-accent dark:hover:bg-gray-200 transition-colors shadow-lg shadow-ink-900/10">
                                        发布评论
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <script>
                        function replyTo(id, username) {
                            document.getElementById('comment-parent-id').value = id;
                            document.getElementById('reply-user').innerText = '@' + username;
                            document.getElementById('reply-badge').classList.remove('hidden');
                            document.getElementById('comment-editor').focus();
                            document.getElementById('comment-editor').placeholder = '回复 @' + username + '...';
                        }
                        function cancelReply() {
                            document.getElementById('comment-parent-id').value = 0;
                            document.getElementById('reply-badge').classList.add('hidden');
                            document.getElementById('comment-editor').placeholder = '写下您的想法...';
                        }
                    </script>
                <?php else: ?>
                    <div class="bg-gray-50 dark:bg-white/5 rounded-2xl p-10 text-center border-2 border-dashed border-gray-200 dark:border-white/10">
                        <div class="mb-5 text-ink-300 dark:text-gray-600">
                            <i class="far fa-comments text-4xl"></i>
                        </div>
                        <h4 class="font-bold text-ink-900 dark:text-gray-100 mb-2">参与讨论</h4>
                        <p class="text-ink-500 dark:text-gray-400 text-sm mb-8">登录后即可分享您的独到见解</p>
                        <div class="flex justify-center gap-4">
                            <a href="<?= url('/login') ?>" class="bg-ink-900 text-white dark:bg-white dark:text-black px-8 py-2.5 rounded-full text-sm font-bold hover:bg-ink-700 dark:hover:bg-gray-200 transition-colors shadow-lg shadow-ink-900/20">
                                登录
                            </a>
                            <a href="<?= url('/login') ?>" class="bg-white text-ink-900 dark:bg-transparent dark:text-white border border-gray-200 dark:border-white/20 px-8 py-2.5 rounded-full text-sm font-bold hover:border-ink-900 dark:hover:border-white transition-colors">
                                注册账号
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="space-y-8">
                <?php 
                // Build Tree
                $commentGroups = [];
                $allComments = []; // Map ID -> Comment for lookups
                foreach($comments as $c) {
                    $pid = $c['parent_id'] ?? 0;
                    $commentGroups[$pid][] = $c;
                    $allComments[$c['id']] = $c;
                }
                
                // Helper to get all descendants flattened
                $getDescendants = function($parentId) use (&$getDescendants, $commentGroups) {
                    $descendants = [];
                    if (isset($commentGroups[$parentId])) {
                        foreach ($commentGroups[$parentId] as $child) {
                            $descendants[] = $child;
                            $descendants = array_merge($descendants, $getDescendants($child['id']));
                        }
                    }
                    return $descendants;
                };

                // Render Root Comments
                if(isset($commentGroups[0])):
                    foreach($commentGroups[0] as $root):
                        $rootUser = $root['username'] ?? 'Guest';
                        $rootAvatar = $root['avatar'] ?? '/assets/default-avatar.png';
                        $descendants = $getDescendants($root['id']);
                ?>
                    <!-- Root Comment Item -->
                    <div class="flex gap-4 group" id="comment-<?= $root['id'] ?>" data-user-id="<?= $root['user_id'] ?>">
                        <a href="<?= url('/user/' . $root['user_id']) ?>" class="relative shrink-0 block w-10 h-10">
                            <img src="<?= e($rootAvatar) ?>" class="w-full h-full rounded-full object-cover block ring-2 ring-transparent group-hover:ring-gray-100 dark:group-hover:ring-white/10 transition-all">
                            <?= (function_exists('is_plugin_active') && is_plugin_active('Ran_Name')) ? \Plugins\Ran_Name\Plugin::renderVerifyIcon($root['user_id'], 'w-3 h-3', 'bottom: -2px; right: -2px;') : '' ?>
                        </a>
                        <div class="flex-1">
                            <!-- Root Content -->
                            <div class="mb-2">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <a href="<?= url('/user/' . $root['user_id']) ?>" class="<?= (function_exists('is_plugin_active') && is_plugin_active('Ran_Vip')) ? \Plugins\Ran_Vip\Plugin::getVipColor($root['user_id'], 'font-bold text-ink-900 dark:text-gray-200') : 'font-bold text-ink-900 dark:text-gray-200' ?> hover:text-accent transition-colors"><?= e($rootUser) ?></a>
                                    <?php if (class_exists('Plugins\Ran_Title\Plugin')) \Plugins\Ran_Title\Plugin::renderTitle($root['user_id']); ?>
                                </div>
                                <div class="flex items-center gap-3 mt-1">
                                    <span class="text-xs text-ink-400"><?= date('m-d H:i', strtotime($root['created_at'])) ?></span>
                                    <?php if(isset($_SESSION['user_id'])): ?>
                                    <button onclick="replyTo(<?= $root['id'] ?>, '<?= e($rootUser) ?>')" class="text-xs font-bold text-gray-400 hover:text-ink-900 dark:hover:text-white transition-colors cursor-pointer">
                                        回复
                                    </button>
                                    <?php endif; ?>
                                    
                                    <!-- Bounty Accept Button (Direct Injection) -->
                                    <?php
                                    if (isset($_SESSION['user']['id']) && class_exists('\Plugins\Ran_Bounty\Plugin')) {
                                        $postAuthorId = $post['user_id']; // Variable from compiled template or scope
                                        $isPostAuthor = ($_SESSION['user']['id'] == $postAuthorId);
                                        $isNotMe = ($_SESSION['user']['id'] != $root['user_id']);
                                        
                                        if ($isPostAuthor && $isNotMe) {
                                            // Check bounty status (Cached or Simple Check)
                                            // We can optimize this by fetching bounty once at top of file, but for now:
                                            static $bountyStatus = null;
                                            if ($bountyStatus === null) {
                                                $bountyData = \Plugins\Ran_Bounty\Plugin::getBounty($post['id']);
                                                $bountyStatus = $bountyData ? $bountyData['status'] : -1;
                                            }
                                            
                                            // Status 0 = Active
                                            if ($bountyStatus === 0) {
                                                echo '<button onclick="acceptAnswer('.$root['id'].', '.$root['user_id'].')" class="text-xs font-bold bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400 px-3 py-1 rounded-full ml-2 hover:bg-yellow-200 dark:hover:bg-yellow-900/50 transition-colors cursor-pointer flex items-center gap-1"><i class="fas fa-check-circle"></i> 采纳为最佳</button>';
                                            }
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                            <p class="text-ink-500 dark:text-gray-400 leading-relaxed text-sm mb-3">
                                <?= e($root['content']) ?>
                            </p>

                            <!-- Child Comments Container (Flattened) -->
                            <?php if (!empty($descendants)): ?>
                            <div class="bg-gray-50 dark:bg-white/5 rounded-xl p-4 space-y-5 mt-3">
                                <?php foreach($descendants as $child): 
                                    $childUser = $child['username'] ?? 'Guest';
                                    $childAvatar = $child['avatar'] ?? '/assets/default-avatar.png';
                                    
                                    // Check if replying to someone specific inside the thread
                                    $replyTargetUser = null;
                                    if ($child['parent_id'] != $root['id'] && isset($allComments[$child['parent_id']])) {
                                        $replyTargetUser = $allComments[$child['parent_id']]['username'] ?? '';
                                    }
                                ?>
                                <div class="flex gap-3" id="comment-<?= $child['id'] ?>">
                                    <a href="<?= url('/user/' . $child['user_id']) ?>" class="relative shrink-0 block w-8 h-8">
                                        <img src="<?= e($childAvatar) ?>" class="w-full h-full rounded-full object-cover block">
                                        <?= (function_exists('is_plugin_active') && is_plugin_active('Ran_Name')) ? \Plugins\Ran_Name\Plugin::renderVerifyIcon($child['user_id'], 'w-3 h-3', 'bottom: -2px; right: -2px;') : '' ?>
                                    </a>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <a href="<?= url('/user/' . $child['user_id']) ?>" class="<?= (function_exists('is_plugin_active') && is_plugin_active('Ran_Vip')) ? \Plugins\Ran_Vip\Plugin::getVipColor($child['user_id'], 'font-bold text-sm text-ink-900 dark:text-gray-200') : 'font-bold text-sm text-ink-900 dark:text-gray-200' ?> hover:text-accent transition-colors"><?= e($childUser) ?></a>
                                            <?php if (class_exists('Plugins\Ran_Title\Plugin')) \Plugins\Ran_Title\Plugin::renderTitle($child['user_id']); ?>
                                            
                                            <span class="text-xs text-ink-400 ml-auto"><?= date('m-d H:i', strtotime($child['created_at'])) ?></span>
                                        </div>
                                        
                                        <p class="text-ink-500 dark:text-gray-400 leading-relaxed text-sm">
                                            <?php if($replyTargetUser): ?>
                                            <span class="text-ink-900 dark:text-white font-bold text-xs mr-1">回复 @<?= e($replyTargetUser) ?> :</span>
                                            <?php endif; ?>
                                            <?= e($child['content']) ?>
                                        </p>
                                        
                                        <?php if(isset($_SESSION['user_id'])): ?>
                                        <div class="mt-1 flex items-center gap-2">
                                            <button onclick="replyTo(<?= $child['id'] ?>, '<?= e($childUser) ?>')" class="text-xs font-bold text-gray-400 hover:text-ink-900 dark:hover:text-white transition-colors cursor-pointer">
                                                回复
                                            </button>
                                            
                                            <!-- Bounty Accept Button (Direct Injection Child) -->
                                            <?php
                                            if (class_exists('\Plugins\Ran_Bounty\Plugin')) {
                                                // Variables reused from root scope: $post, $bountyStatus
                                                // Variables dependent: $isPostAuthor, $isNotMe
                                                $isNotMeChild = ($_SESSION['user']['id'] != $child['user_id']);
                                                
                                                if ($isPostAuthor && $isNotMeChild && isset($bountyStatus) && $bountyStatus === 0) {
                                                    echo '<button onclick="acceptAnswer('.$child['id'].', '.$child['user_id'].')" class="text-xs font-bold bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400 px-2 py-0.5 rounded-full ml-1 hover:bg-yellow-200 dark:hover:bg-yellow-900/50 transition-colors cursor-pointer flex items-center gap-1"><i class="fas fa-check-circle"></i> 采纳</button>';
                                                }
                                            }
                                            ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-ink-500 dark:text-gray-500 italic">暂无评论。</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right: Sidebar -->
    <aside class="lg:w-[35%] hidden w-full lg:block space-y-12">
        <!-- Author Widget -->
        <div class="text-center px-4">
            <!-- Avatar -->
            <a href="<?= url('/user/' . $post['user_id']) ?>" class="inline-block relative group">
                <img src="<?= e($post['author_avatar']) ?>" alt="<?= e($post['author_name']) ?>" class="w-24 h-24 rounded-full shadow-lg object-cover transition-transform duration-300 group-hover:scale-110">
                <?= (function_exists('is_plugin_active') && is_plugin_active('Ran_Name')) ? \Plugins\Ran_Name\Plugin::renderVerifyIcon($post['user_id'], 'w-6 h-6') : '' ?>
            </a>

            <!-- Name & Info -->
            <div class="mt-4 mb-2">
                <h3 class="font-black text-xl text-ink-900 dark:text-white flex items-center justify-center gap-2">
                    <a href="<?= url('/user/' . $post['user_id']) ?>" class="<?= (function_exists('is_plugin_active') && is_plugin_active('Ran_Vip')) ? \Plugins\Ran_Vip\Plugin::getVipColor($post['user_id'], '') : '' ?> hover:text-blue-600 transition-colors"><?= e($post['author_name']) ?></a>
                    <?php if (class_exists('Plugins\Ran_Title\Plugin')) \Plugins\Ran_Title\Plugin::renderTitle($post['user_id']); ?>
                    <?php \Core\Hook::listen('theme_post_author_suffix', $post); ?>
                </h3>
            </div>
            
            <!-- Bio -->
            <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed mb-6 italic">
                 <?= e($post['author_bio']) ?: '专注分享 ' . e($post['category_name']) . ' 的深度见解。' ?>
            </p>

            <!-- Follow Button Hook -->
            <div class="flex justify-center mb-4">
                 <?php \Core\Hook::listen('theme_follow_button', $post['user_id']); ?>
                 <?php 
                        $db = \Core\Database::getInstance(config('db'));
                        $msgActive = $db->query("SELECT is_active FROM plugins WHERE name = 'Ran_Messages'")->fetch();
                        if ($msgActive && $msgActive['is_active'] == 1 && isset($_SESSION['user']['id']) && $_SESSION['user']['id'] != $post['user_id']): 
                        ?>
                        <a href="<?= url('/messages/chat/' . $post['user_id']) ?>" class="ml-2 bg-blue-600 text-white text-[10px] font-bold px-3 py-1 rounded-full hover:bg-blue-700 transition-all flex items-center gap-1">
                            <i class="far fa-comment-dots"></i> 私信
                        </a>
                        <?php endif; ?>
            </div>
        </div>

        <!-- Related Posts Widget -->
        <?php if(!empty($related_posts)): ?>
        <div>
            <div class="flex items-center gap-2 mb-6">
                <span class="w-1 h-4 bg-ink-900 dark:bg-white rounded-full"></span>
                <h3 class="font-bold text-ink-900 dark:text-gray-100">相关推荐</h3>
            </div>
            <div class="space-y-6">
                <?php foreach($related_posts as $rpost): ?>
                <div class="group flex gap-4 items-start">
                    <div class="flex-1">
                        <a href="<?= url('/' . $rpost['id'] . '.html') ?>" class="block font-bold text-ink-900 dark:text-gray-100 group-hover:text-accent transition-colors leading-snug mb-2 line-clamp-2">
                            <?= e($rpost['title']) ?>
                        </a>
                        <div class="flex items-center gap-2">
                            <a href="<?= url('/user/' . $rpost['author_name']) ?>" class="relative block shrink-0">
                                <img src="<?= e($rpost['author_avatar']) ?>" class="w-4 h-4 rounded-full">
                            </a>
                            <span class="text-[10px] text-ink-400 dark:text-gray-500">
                                <?= e($rpost['author_name']) ?>
                            </span>
                            <span class="text-[10px] text-gray-400">·</span>
                            <span class="text-[10px] text-gray-400"><?= date('m-d', strtotime($rpost['created_at'])) ?></span>
                        </div>
                    </div>
                    <?php if(!empty($rpost['cover_image'])): ?>
                    <a href="<?= url('/' . $rpost['id'] . '.html') ?>" class="w-20 h-14 shrink-0 rounded-lg bg-gray-100 dark:bg-white/10 overflow-hidden">
                        <img src="<?= e($rpost['cover_image']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    </a>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Author's Other Posts Widget -->
        <?php if(!empty($author_posts)): ?>
        <div>
            <div class="flex items-center gap-2 mb-6">
                <span class="w-1 h-4 bg-ink-900 dark:bg-white rounded-full"></span>
                <h3 class="font-bold text-ink-900 dark:text-gray-100">作者其他文章</h3>
            </div>
            <div class="space-y-6">
                <?php foreach($author_posts as $apost): ?>
                <div class="group flex gap-4 items-start">
                    <div class="flex-1">
                        <a href="<?= url('/' . $apost['id'] . '.html') ?>" class="block font-bold text-ink-900 dark:text-gray-100 group-hover:text-accent transition-colors leading-snug mb-2 line-clamp-2">
                            <?= e($apost['title']) ?>
                        </a>
                        <div class="flex items-center gap-2 text-[10px] text-gray-400">
                            <i class="far fa-eye"></i>
                            <span><?= number_format($apost['view_count']) ?></span>
                            <span>·</span>
                            <span><?= date('m-d', strtotime($apost['created_at'])) ?></span>
                        </div>
                    </div>
                    <?php if(!empty($apost['cover_image'])): ?>
                    <a href="<?= url('/' . $apost['id'] . '.html') ?>" class="w-20 h-14 shrink-0 rounded-lg bg-gray-100 dark:bg-white/10 overflow-hidden">
                        <img src="<?= e($apost['cover_image']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    </a>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tag Cloud Widget -->
        <?php if(!empty($tags)): ?>
        <div>
            <div class="flex items-center gap-2 mb-6">
                <span class="w-1 h-4 bg-ink-900 dark:bg-white rounded-full"></span>
                <h3 class="font-bold text-ink-900 dark:text-gray-100">文章标签</h3>
            </div>
            <div class="flex flex-wrap gap-2">
                <?php foreach($tags as $tag): ?>
                <a href="<?= url('/?tag=' . urlencode($tag['slug'])) ?>" class="px-3 py-1.5 bg-white dark:bg-white/5 border border-gray-100 dark:border-white/5 rounded-lg text-xs font-bold text-ink-500 hover:text-white hover:bg-ink-900 dark:hover:bg-accent transition-all duration-300 shadow-sm hover:shadow-md">
                    # <?= e($tag['name']) ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Table of Contents Widget (JS Generated) -->
        <div id="toc-widget" class="hidden top-32">
            <h3 class="text-xs font-bold uppercase tracking-widest mb-6 text-ink-900 dark:text-gray-200">文章目录</h3>
            <div id="toc-list" class="flex flex-col gap-2 text-sm text-gray-500 dark:text-gray-400 border-l-2 border-gray-100 dark:border-white/10 pl-4 space-y-2 max-h-[calc(100vh-200px)] overflow-y-auto custom-scrollbar">
                <!-- Links will be injected here -->
            </div>
        </div>


    </aside>
</main>

<!-- Markdown Parsing & Highlighting -->
<script src="/assets/js/marked.min.js"></script>
<link rel="stylesheet" href="/assets/css/atom-one-dark.min.css">
<script src="/assets/js/highlight.min.js"></script>

<!-- ViewerJS -->
<link rel="stylesheet" href="https://lib.baomitu.com/viewerjs/1.11.6/viewer.min.css">
<script src="https://lib.baomitu.com/viewerjs/1.11.6/viewer.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Safe Decode Base64
        const b64 = "<?= base64_encode($post['content']) ?>";
        let content = b64 ? decodeURIComponent(escape(window.atob(b64))) : '';
        
        // Auto-Inject Video if Dmooji Playlist exists but video tag is missing
        if (typeof window.DmoojiConfig !== 'undefined' && window.DmoojiConfig.videos && window.DmoojiConfig.videos.length > 0) {
             if (!content.includes('<video') && !content.includes('[video')) {
                 const firstVideo = window.DmoojiConfig.videos[0];
                 // Prepend Video Tag
                 const videoTag = `<video src="${firstVideo.url}" controls class="w-full rounded-lg shadow-sm mb-6"></video>\n\n`;
                 content = videoTag + content;
             }
        }
        
        const container = document.getElementById('parsed-content');
        // --- Security Fix: Fail Safe XSS Protection ---
        const rawHtml = marked.parse(content);
        if (typeof DOMPurify !== 'undefined') {
            container.innerHTML = DOMPurify.sanitize(rawHtml);
        } else {
            console.error('DOMPurify not loaded. Rendering in safe text mode.');
            container.innerText = "Error: Security module failed to load. Content cannot be displayed.";
        }
        
        // --- Dmooji Cleanup: Remove extra videos if Dmooji is active ---
        if (typeof window.DmoojiConfig !== 'undefined' && window.DmoojiConfig.videos && window.DmoojiConfig.videos.length > 0) {
            const videos = container.querySelectorAll('video');
            // Dmooji uses the first one. Remove others to prevent duplicates/stacking.
            if (videos.length > 1) {
                for(let i = 1; i < videos.length; i++) {
                    videos[i].remove();
                }
            }
        }
        
        hljs.highlightAll();

        // --- TOC Generation ---
        const generateTOC = () => {
            const container = document.getElementById('parsed-content');
            const headers = container.querySelectorAll('h1, h2, h3');
            const tocWidget = document.getElementById('toc-widget');
            const tocList = document.getElementById('toc-list');
            
            if (headers.length < 2) return;

            tocWidget.classList.remove('hidden');
            
            headers.forEach((header, index) => {
                if (!header.id) header.id = 'heading-' + index;
                
                const link = document.createElement('a');
                link.href = '#' + header.id;
                link.innerText = header.innerText;
                link.className = 'hover:text-purple-600 dark:hover:text-white transition-colors block leading-relaxed py-0.5 border-l-2 border-transparent -ml-[18px] pl-4 hover:border-purple-600';
                
                if (header.tagName === 'H2') link.classList.add('ml-2');
                if (header.tagName === 'H3') link.classList.add('ml-4', 'text-xs');
                
                link.onclick = (e) => {
                    e.preventDefault();
                    document.getElementById(header.id).scrollIntoView({behavior: 'smooth'});
                };

                tocList.appendChild(link);
            });
            
            // Highlight on scroll
            const observer = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const id = entry.target.id;
                        document.querySelectorAll('#toc-list a').forEach(a => {
                             // Reset all
                             if(a.getAttribute('href') === '#' + id) {
                                  a.classList.add('text-purple-600', 'font-bold', '!border-purple-600');
                             } else {
                                  a.classList.remove('text-purple-600', 'font-bold', '!border-purple-600');
                             }
                        });
                    }
                });
            }, { rootMargin: '-10% 0px -70% 0px' });
            headers.forEach(h => observer.observe(h));
        };
        generateTOC();

        // --- Image Grouping Logic ---
        const processImages = () => {
            const children = Array.from(container.children);
            let groups = [];
            let currentGroup = [];
            
            for (let i = 0; i < children.length; i++) {
                const node = children[i];
                const isImgP = (node.tagName === 'P' && node.querySelector('img') && node.innerText.trim() === '');
                
                if (isImgP) {
                    const imgs = node.querySelectorAll('img');
                    if (imgs.length > 0) {
                        currentGroup.push({ node: node, imgs: Array.from(imgs) });
                    }
                } else {
                    if (currentGroup.length > 0) {
                        groups.push([...currentGroup]);
                        currentGroup = [];
                    }
                }
            }
            if (currentGroup.length > 0) groups.push(currentGroup);
            
            groups.forEach(group => {
                let allImgs = [];
                group.forEach(item => allImgs.push(...item.imgs));
                
                const count = allImgs.length;
                
                if (count > 1) {
                    const firstNode = group[0].node;
                    const gridDiv = document.createElement('div');
                    gridDiv.className = `img-grid-container img-grid-${count}`;
                    if (count === 2 || count === 4) gridDiv.classList.add('img-grid-2');
                    
                    allImgs.forEach(img => {
                        const itemDiv = document.createElement('div');
                        itemDiv.className = 'img-grid-item';
                        itemDiv.appendChild(img.cloneNode(true));
                        gridDiv.appendChild(itemDiv);
                    });
                    
                    container.insertBefore(gridDiv, firstNode);
                    group.forEach(g => g.node.remove());
                } else if (count === 1) {
                    // Single Image - Adaptive Aspect Ratio (1:1, 4:3, 16:9, 9:16)
                    const node = group[0].node;
                    const img = group[0].imgs[0]; // Original image
                    
                    const wrapper = document.createElement('div');
                    wrapper.className = 'img-single-container';
                    // Default placeholder ratio
                    wrapper.style.aspectRatio = '16/9';
                    
                    const applyRatio = (imageEl) => {
                        const w = imageEl.naturalWidth;
                        const h = imageEl.naturalHeight;
                        if(!w || !h) return;
                        
                        const r = w / h;
                        const targets = [
                            { v: 1, s: '1/1' },
                            { v: 4/3, s: '4/3' },
                            { v: 16/9, s: '16/9' },
                            { v: 9/16, s: '9/16' }
                        ];
                        
                        let best = targets[0];
                        let minDiff = Math.abs(r - best.v);
                        
                        targets.forEach(t => {
                            const diff = Math.abs(r - t.v);
                            if (diff < minDiff) {
                                minDiff = diff;
                                best = t;
                            }
                        });
                        wrapper.style.aspectRatio = best.s;
                    };

                    if(img.complete) applyRatio(img);
                    else img.onload = () => applyRatio(img);
                    
                    wrapper.appendChild(img.cloneNode(true));
                    container.replaceChild(wrapper, node);
                }
            });
        };
        processImages();

        // --- Initialize ViewerJS Logic ---
        // We init on the container so it finds all images inside, including grid ones.
        if (typeof Viewer !== 'undefined') {
            new Viewer(container, {
                url: 'src',
                toolbar: {
                    zoomIn: 4,
                    zoomOut: 4,
                    oneToOne: 4,
                    reset: 4,
                    prev: 4,
                    play: {
                        show: 4,
                        size: 'large',
                    },
                    next: 4,
                    rotateLeft: 4,
                    rotateRight: 4,
                    flipHorizontal: 4,
                    flipVertical: 4,
                },
                title: false, // Clean look
                navbar: true, // Show thumbnails
            });
        } else {
            console.error('ViewerJS library not loaded!');
        }
        
        // Enhance Code Blocks (Mac Style + Robust Copy)
        document.querySelectorAll('pre code').forEach((codeBlock) => {
            const pre = codeBlock.parentElement;
            
            // 1. Create Wrapper
            const wrapper = document.createElement('div');
            wrapper.className = 'code-wrapper my-8 rounded-xl overflow-hidden shadow-2xl bg-[#282c34]';
            
            // 2. Create Header
            const header = document.createElement('div');
            header.className = 'flex items-center justify-between px-4 py-3 bg-[#21252b] border-b border-black/20 select-none';
                    
            // 2a. Mac Dots
            const dots = document.createElement('div');
            dots.className = 'flex gap-2';
            dots.innerHTML = `
                <div class="w-3 h-3 rounded-full bg-[#ff5f56] shadow-sm"></div>
                <div class="w-3 h-3 rounded-full bg-[#ffbd2e] shadow-sm"></div>
                <div class="w-3 h-3 rounded-full bg-[#27c93f] shadow-sm"></div>
            `;
            
            // 2b. Language Label
            const langClass = Array.from(codeBlock.classList).find(c => c.startsWith('language-'));
            const langName = langClass ? langClass.replace('language-', '').toUpperCase() : 'CODE';
            const label = document.createElement('div');
            label.className = 'text-xs font-mono text-gray-500 font-bold tracking-wider';
            label.innerText = langName;
                        
            // 2c. Copy Button
            const copyBtn = document.createElement('button');
            copyBtn.className = 'group flex items-center gap-2 text-xs text-gray-400 hover:text-white transition-colors cursor-pointer focus:outline-none';
            copyBtn.innerHTML = `
                <i class="far fa-copy group-hover:scale-110 transition-transform"></i>
                <span>Copy</span>
            `;
            
            // Logic: Robust Copy
            copyBtn.onclick = () => {
                const text = codeBlock.innerText; // Get raw text
                
                // Fallback function for HTTP/Non-Secure
                const fallbackCopy = (text) => {
                    const textArea = document.createElement("textarea");
                    textArea.value = text;
                    textArea.style.position = "fixed"; // Avoid scrolling to bottom
                    document.body.appendChild(textArea);
                    textArea.focus();
                    textArea.select();
                    try {
                        const successful = document.execCommand('copy');
                        showFeedback(successful);
                    } catch (err) {
                        showFeedback(false);
                    }
                    document.body.removeChild(textArea);
                };

                const showFeedback = (success) => {
                    if (success) {
                        copyBtn.innerHTML = `<i class="fas fa-check text-green-400"></i> <span class="text-green-400">Copied!</span>`;
                        } else {
                        copyBtn.innerHTML = `<i class="fas fa-times text-red-400"></i> <span class="text-red-400">Error</span>`;
                    }
                    setTimeout(() => {
                        copyBtn.innerHTML = `<i class="far fa-copy group-hover:scale-110 transition-transform"></i> <span>Copy</span>`;
                    }, 2000);
                };

                if (navigator.clipboard) {
                    navigator.clipboard.writeText(text).then(() => showFeedback(true)).catch(() => fallbackCopy(text));
                } else {
                    fallbackCopy(text);
                }
            };
            
            // Assemble Header
            header.appendChild(dots);
            header.appendChild(label);
            header.appendChild(copyBtn);
            
            // 3. Assemble DOM
            // Insert wrapper before pre
            pre.parentNode.insertBefore(wrapper, pre);
            // Move pre into wrapper
            wrapper.appendChild(header);
            wrapper.appendChild(pre);
                
            // 4. Clean Pre Styles for integration (override existing styles)
            pre.style.margin = '0';
            pre.style.borderRadius = '0';
            pre.style.background = 'transparent';
            pre.style.boxShadow = 'none';
        });
    });
</script>

</script>

<!-- @ Mention Logic (Shared Logic Copy) -->
<div id="at-user-dropdown" class="hidden fixed z-50 w-64 bg-white dark:bg-[#1e1e1e] rounded-xl shadow-2xl border border-gray-100 dark:border-white/10 flex flex-col overflow-hidden">
    <div class="p-3 border-b border-gray-100 dark:border-white/5">
        <input type="text" id="at-search-input" placeholder="搜索用户..." class="w-full bg-gray-50 dark:bg-black/20 border-0 rounded-lg px-3 py-2 text-sm text-ink-900 dark:text-white focus:ring-1 focus:ring-black dark:focus:ring-white">
    </div>
    <div id="at-user-list" class="flex-1 overflow-y-auto max-h-60 p-2 space-y-1"></div>
</div>
<script>
// Adapted for Post.php
let atPage = 1;
let atLoading = false;
let atHasMore = true;
let atQuery = '';

function toggleAtList(btn) {
    const dropdown = document.getElementById('at-user-dropdown');
    if (dropdown.classList.contains('hidden')) {
        const rect = btn.getBoundingClientRect();
        dropdown.style.top = (rect.bottom + 5) + 'px';
        dropdown.style.left = rect.left + 'px';
        dropdown.classList.remove('hidden');
        document.getElementById('at-search-input').focus();
        loadAtUsers(true);
    } else {
        dropdown.classList.add('hidden');
    }
}
document.addEventListener('click', (e) => {
    const dropdown = document.getElementById('at-user-dropdown');
    if (!dropdown.classList.contains('hidden') && !e.target.closest('#at-user-dropdown') && !e.target.closest('button[title="@用户"]')) {
        dropdown.classList.add('hidden');
    }
});
document.getElementById('at-search-input').addEventListener('input', (e) => {
    atQuery = e.target.value;
    loadAtUsers(true);
});
document.getElementById('at-user-list').addEventListener('scroll', (e) => {
    if (e.target.scrollTop + e.target.clientHeight >= e.target.scrollHeight - 20) {
        if (!atLoading && atHasMore) loadAtUsers();
    }
});
function loadAtUsers(reset = false) {
    if (reset) { atPage = 1; atHasMore = true; document.getElementById('at-user-list').innerHTML = ''; }
    if (!atHasMore) return;
    atLoading = true;
    fetch(`/api/users/search?q=${atQuery}&page=${atPage}`).then(res => res.json()).then(res => {
        atLoading = false;
        if (res.success) {
            if (res.data.length < 10) atHasMore = false;
            atPage++;
            renderAtUsers(res.data);
        }
    });
}
function renderAtUsers(users) {
    const list = document.getElementById('at-user-list');
    if (users.length === 0 && list.children.length === 0) { list.innerHTML = '<div class="text-center text-gray-400 text-xs py-4">未找到用户</div>'; return; }
    users.forEach(u => {
        const div = document.createElement('div');
        div.className = 'flex items-center gap-2 p-2 hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg cursor-pointer transition-colors';
        div.onclick = () => insertAt(u.username);
        div.innerHTML = `<img src="${u.avatar || '/assets/default-avatar.png'}" class="w-6 h-6 rounded-full object-cover"><span class="text-sm font-bold text-gray-700 dark:text-gray-200">${u.username}</span>`;
        list.appendChild(div);
    });
}
function insertAt(username) {
    const editor = document.getElementById('comment-editor');
    if(editor) {
        const start = editor.selectionStart;
        const end = editor.selectionEnd;
        const text = ` @${username} `;
        editor.setRangeText(text, start, end, 'end');
        editor.focus();
    }
    document.getElementById('at-user-dropdown').classList.add('hidden');
}
</script>

<script>
function copyLink() {
    navigator.clipboard.writeText(window.location.href).then(() => {
        const btn = event.currentTarget || document.activeElement;
        const icon = btn.querySelector('i');
        if(icon) {
            const originalClass = icon.className;
            icon.className = 'fas fa-check';
            setTimeout(() => icon.className = originalClass, 2000);
        }
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



<!-- Ran_Dmooji Assets -->
<?php if(function_exists('is_plugin_active') && is_plugin_active('Ran_Dmooji') && class_exists('\Plugins\Ran_Dmooji\Plugin')): ?>
    <?php \Plugins\Ran_Dmooji\Plugin::renderAssets($post['id']); ?>
<?php endif; ?>

<?php $this->render('footer'); ?>
