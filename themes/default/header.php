<!DOCTYPE html>
<html lang="zh-CN" >
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php 
        ob_start();
        
        // Prepare context for hooks
        $hookContext = [
            'post' => isset($post) ? $post : null,
            'title' => isset($page_title) ? $page_title : null,
            'keywords' => isset($page_keywords) ? $page_keywords : null,
            'description' => isset($page_description) ? $page_description : null
        ];
        
        \Core\Hook::listen('theme_head_meta', $hookContext);
        $seoOutput = ob_get_clean();
        
        if (!empty($seoOutput)) {
            echo $seoOutput;
        } else {
            // Default Fallback
            $site_name = e(get_option('site_title', 'RanUI Blog'));
            if (isset($post['title'])) {
                $page_title = e($post['title']) . ' - ' . $site_name;
            } elseif (isset($page_title)) {
                $page_title = e($page_title) . ' - ' . $site_name;
            } else {
                $page_title = $site_name;
            }
            echo "<title>$page_title</title>\n";
            echo '    <meta name="keywords" content="' . e(get_option('site_keywords', 'blog, php, mvc')) . '">' . "\n";
            echo '    <meta name="description" content="' . e(get_option('site_description', 'A minimalist blog framework.')) . '">' . "\n";
        }
    ?>
    <?php
    $currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $ogTitle = isset($page_title) ? $page_title : 'RanUI';
    // Clean title for attribute
    $ogTitle = strip_tags($ogTitle);
    $ogDesc = isset($page_description) ? $page_description : (get_option('site_description', 'A minimalist blog framework.'));
    $ogImage = isset($post['cover_image']) ? $post['cover_image'] : '/assets/og-default.png'; 
    ?>
<link rel="canonical" href="<?= $currentUrl ?>">
    <meta property="og:url" content="<?= $currentUrl ?>">
    <meta property="og:image" content="<?= e($ogImage) ?>">
    <meta property="og:type" content="website">
    <script src="/assets/css/tailwindcss.css"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;800&family=Noto+Sans+SC:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.6/purify.min.js"></script>
    
    <!-- ... (Tailwind config skipped for brevity, keeping existing) ... -->
    <script>
        tailwind.config = {
            darkMode: 'class', // Enable dark mode
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'Noto Sans SC', 'sans-serif'],
                    },
                    colors: {
                        ink: {
                            900: '#111111', // 主标题
                            500: '#666666', // 次要文本
                            100: '#E5E5E5', // 边框
                        },
                        accent: {
                            DEFAULT: '#2563EB', // 克莱因蓝
                            hover: '#1d4ed8'
                        }
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-out',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0', transform: 'translateY(10px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>
    <script>
        // Init Theme
        if (localStorage.theme === 'dark') {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
        
        function toggleTheme() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.theme = 'light';
            } else {
                document.documentElement.classList.add('dark');
                localStorage.theme = 'dark';
            }
        }
        function setTheme(mode) {
            if (mode === 'dark') {
                document.documentElement.classList.add('dark');
                localStorage.theme = 'dark';
            } else {
                document.documentElement.classList.remove('dark');
                localStorage.theme = 'light';
            }
            updateThemeUI();
        }

        function toggleTheme() {
           // Keep for backward compat or if we add a quick toggle elsewhere
           if (document.documentElement.classList.contains('dark')) {
               setTheme('light');
           } else {
               setTheme('dark');
           }
        }

        function updateThemeUI() {
            const isDark = document.documentElement.classList.contains('dark');
            const label = document.getElementById('theme-text-label');
            
            if (label) {
                if (isDark) {
                    label.innerText = '主题：深色';
                } else {
                    label.innerText = '主题：浅色';
                }
            }
        }

        // Run initially
        document.addEventListener('DOMContentLoaded', updateThemeUI);
    </script>
    <style>
        /* 隐藏滚动条但保留功能 */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        
        /* 图片灰度悬停效果 */
        .img-grayscale { filter: grayscale(100%); transition: filter 0.5s ease; }
        .group:hover .img-grayscale { filter: grayscale(0%); }
    </style>

    <!-- SEO & OG -->
    
</head>
<body class="bg-white text-ink-900 dark:bg-[#0a0a0a] dark:text-gray-100 font-sans antialiased selection:bg-accent selection:text-white transition-colors duration-300" >

    <!-- ================= 导航栏 ================= -->
    <nav class="fixed top-0 w-full bg-white/95 dark:bg-[#0a0a0a]/95 backdrop-blur-sm border-b border-ink-100 dark:border-white/10 z-50 h-20 flex items-center transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-6 w-full flex justify-between items-center">
            
            <!-- Logo & 搜索 -->
            <div class="flex items-center gap-10">
                <a href="<?= url('/') ?>" class="text-2xl font-extrabold tracking-tighter dark:text-white transition-colors">
                    <?php if($logo = get_option('site_logo')): ?>
                    <img src="<?= e($logo) ?>" alt="<?= e(get_option('site_title', 'RanUI')) ?>" class="h-16 w-auto object-contain dark:invert transition-all duration-300">
                    <?php else: ?>
                    <?= e(get_option('site_title', 'RanUI')) ?>
                    <?php endif; ?>
                </a>
            </div>

            <!-- 右侧菜单 -->
            <div class="flex items-center gap-6">

                <!-- Search Trigger -->
                <button onclick="toggleSearchOverlay()" class="w-10 h-10 rounded-full bg-gray-50 dark:bg-white/5 flex items-center justify-center hover:bg-gray-100 dark:hover:bg-white/10 transition-colors group">
                    <i class="fas fa-search text-gray-400 group-hover:text-ink-900 dark:group-hover:text-white transition-colors"></i>
                </button>

                <!-- 搜索覆盖层 (Search Overlay) -->
                <div id="search-overlay" class="fixed top-20 left-0 w-full bg-white/95 dark:bg-[#0a0a0a]/95 backdrop-blur-md border-b border-gray-100 dark:border-white/5 shadow-xl hidden animate-fade-in origin-top z-40 transition-all duration-300 transform">
                    <div class="max-w-3xl mx-auto px-6 py-8">
                        
                        <!-- Search Form -->
                        <form id="global-search-form" action="<?= url('/search') ?>" method="GET" onsubmit="handleSearchSubmit(event)">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400 text-lg"></i>
                                </div>
                                <input type="text" id="search-input" name="q" autocomplete="off" class="w-full bg-gray-50 dark:bg-white/5 border-2 border-transparent focus:border-ink-900 dark:focus:border-white/20 rounded-2xl py-4 pl-12 pr-4 text-lg font-bold text-ink-900 dark:text-white placeholder-gray-400 outline-none transition-all" placeholder="搜索有趣的帖子...">
                                <input type="hidden" name="type" id="search-type" value="post">
                            </div>
                        
                            <!-- Type Toggles -->
                            <div class="flex gap-4 mt-4">
                                <button type="button" onclick="setSearchType('post')" id="btn-type-post" class="px-4 py-1.5 rounded-full text-sm font-bold bg-ink-900 text-white dark:bg-white dark:text-black transition-all">搜帖子</button>
                                <button type="button" onclick="setSearchType('user')" id="btn-type-user" class="px-4 py-1.5 rounded-full text-sm font-bold bg-gray-100 text-gray-500 dark:bg-white/5 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-white/10 transition-all">搜用户</button>
                            </div>
                        </form>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-8">
                            <!-- History -->
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest">历史搜索</h4>
                                    <button onclick="clearSearchHistory()" class="text-xs text-gray-400 hover:text-red-500"><i class="fas fa-trash-alt"></i></button>
                                </div>
                                <div id="search-history-container" class="flex flex-wrap gap-2">
                                    <!-- JS Populated -->
                                    <span class="text-xs text-gray-400 italic">暂无搜索记录</span>
                                </div>
                            </div>
                            
                            <!-- Guess -->
                            <div>
                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">猜你想搜</h4>
                                <div class="flex flex-wrap gap-2">
                                    <?php 
                                        $guesses = [];
                                        try {
                                            $db = \Core\Database::getInstance(config('db'));
                                            $guesses = $db->query("SELECT name FROM tags ORDER BY count DESC LIMIT 8")->fetchAll();
                                        } catch (\Exception $e) {}
                                        
                                        if (empty($guesses)) {
                                            // Fallback if no tags
                                            $guesses = [['name' => 'RanUI'], ['name' => '教程']];
                                        }

                                        foreach($guesses as $g): 
                                    ?>
                                    <a href="<?= url('/search?q=' . urlencode($g['name'])) ?>" class="px-3 py-1 bg-gray-50 dark:bg-white/5 hover:bg-gray-100 dark:hover:bg-white/10 rounded-lg text-xs font-medium text-ink-900 dark:text-gray-300 transition-colors">
                                        <?= e($g['name']) ?>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <script>
                    function toggleSearchOverlay() {
                        const overlay = document.getElementById('search-overlay');
                        overlay.classList.toggle('hidden');
                        if(!overlay.classList.contains('hidden')) {
                            document.getElementById('search-input').focus();
                            renderSearchHistory();
                        }
                    }

                    function setSearchType(type) {
                        document.getElementById('search-type').value = type;
                        const input = document.getElementById('search-input');
                        const btnPost = document.getElementById('btn-type-post');
                        const btnUser = document.getElementById('btn-type-user');
                        
                        if (type === 'post') {
                            input.placeholder = "搜索有趣的帖子...";
                            btnPost.className = "px-4 py-1.5 rounded-full text-sm font-bold bg-ink-900 text-white dark:bg-white dark:text-black transition-all";
                            btnUser.className = "px-4 py-1.5 rounded-full text-sm font-bold bg-gray-100 text-gray-500 dark:bg-white/5 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-white/10 transition-all";
                        } else {
                            input.placeholder = "搜索用户昵称/UID...";
                            btnUser.className = "px-4 py-1.5 rounded-full text-sm font-bold bg-ink-900 text-white dark:bg-white dark:text-black transition-all";
                            btnPost.className = "px-4 py-1.5 rounded-full text-sm font-bold bg-gray-100 text-gray-500 dark:bg-white/5 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-white/10 transition-all";
                        }
                        input.focus();
                    }

                    // History Logic (LocalStorage)
                    function handleSearchSubmit(e) {
                        const val = document.getElementById('search-input').value.trim();
                        if (val) {
                            let history = JSON.parse(localStorage.getItem('ran_search_history') || '[]');
                            // Remove duplicate if exists
                            history = history.filter(h => h !== val);
                            // Add to front
                            history.unshift(val);
                            // Limit to 10
                            if (history.length > 10) history.pop();
                            localStorage.setItem('ran_search_history', JSON.stringify(history));
                        }
                    }

                    function renderSearchHistory() {
                        const container = document.getElementById('search-history-container');
                        let history = JSON.parse(localStorage.getItem('ran_search_history') || '[]');
                        
                        if (history.length === 0) {
                            container.innerHTML = '<span class="text-xs text-gray-400 italic">暂无搜索记录</span>';
                            return;
                        }
                        
                        container.innerHTML = '';
                        history.forEach(h => {
                            const link = document.createElement('a');
                            link.href = "<?= url('/search?q=') ?>" + encodeURIComponent(h);
                            link.className = "px-3 py-1 bg-gray-50 dark:bg-white/5 hover:bg-gray-100 dark:hover:bg-white/10 rounded-lg text-xs font-medium text-gray-600 dark:text-gray-400 transition-colors flex items-center gap-2 group";
                            link.innerHTML = `<span>${h}</span> <span onclick="removeFromHistory(event, '${h}')" class="opacity-0 group-hover:opacity-100 hover:text-red-500 px-1 transition-opacity">×</span>`;
                            container.appendChild(link);
                        });
                    }

                    function removeFromHistory(e, val) {
                        e.preventDefault(); 
                        e.stopPropagation();
                        let history = JSON.parse(localStorage.getItem('ran_search_history') || '[]');
                        history = history.filter(h => h !== val);
                        localStorage.setItem('ran_search_history', JSON.stringify(history));
                        renderSearchHistory();
                    }

                    function clearSearchHistory() {
                        localStorage.removeItem('ran_search_history');
                        renderSearchHistory();
                    }
                    
                    // Close on Escape
                    document.addEventListener('keydown', function(e) {
                        if(e.key === "Escape") {
                            document.getElementById('search-overlay').classList.add('hidden');
                        }
                    });

                    // Close button inside overlay
                    // We can also add a click outside listener, but it needs to be careful not to close on open
                    document.addEventListener('click', function(e) {
                         const overlay = document.getElementById('search-overlay');
                         // Trigger button
                         const trigger = e.target.closest('button[onclick="toggleSearchOverlay()"]');
                         // Content
                         const content = e.target.closest('#search-overlay > div');
                         // Click on overlay background (outside content)
                         const isOverlayBg = (e.target.id === 'search-overlay');

                         if (isOverlayBg) {
                             overlay.classList.add('hidden');
                         }
                    });
                </script>
                
                <?php 
                // Hide Upload Button on Write Page
                $isWritePage = (strpos($_SERVER['REQUEST_URI'] ?? '', '/write') !== false);
                ?>

                <?php if(isset($_SESSION['user'])): ?>
                <!-- Upload Button -->
                <?php if (!$isWritePage): ?>
                <a href="<?= url('/write') ?>" class="hidden md:flex items-center gap-2 bg-black text-white px-5 py-2.5 rounded-full font-bold text-sm hover:opacity-80 transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                    <i class="fas fa-plus"></i> <span class="hidden lg:inline">发布</span>
                </a>
                <?php endif; ?>

                <?php if(function_exists('is_plugin_active') && is_plugin_active('Ran_Notice')): ?>
                <!-- Notification -->
                <?php 
                    // Quick check for unread count
                    $unreadCount = 0;
                    if(isset($_SESSION['user'])) {
                        $db = \Core\Database::getInstance(config('db'));
                        // Check if table exists to avoid crash if plugin disabled
                        try {
                            $unreadCount = $db->query("SELECT count(*) FROM user_notifications WHERE user_id = ? AND is_read = 0", [$_SESSION['user']['id']])->fetchColumn();
                        } catch (\Exception $e) {}
                    }
                ?>
                <a href="<?= url('/notice') ?>" class="w-10 h-10 rounded-full  text-gray-600 flex items-center justify-center transition-colors relative group">
                    <i class="fas fa-bell"></i>
                    <?php if($unreadCount > 0): ?>
                    <span class="absolute top-0 right-0 w-3 h-3 bg-red-500 border-2 border-white rounded-full"></span>
                    <?php endif; ?>
                </a>
                <?php endif; ?>

                <!-- User Dropdown -->
                <div class="relative group z-50">
                    <button class="flex items-center gap-3 focus:outline-none">
                        <div class="relative">
                            <img src="<?= e($_SESSION['user']['avatar'] ?? '/assets/default-avatar.png') ?>" class="w-10 h-10 rounded-full object-cover border border-gray-100 dark:border-white/10">
                            <?= (function_exists('is_plugin_active') && is_plugin_active('Ran_Name')) ? \Plugins\Ran_Name\Plugin::renderVerifyIcon($_SESSION['user']['id'], 'w-3 h-3') : '' ?>
                        </div>
                        <span class="hidden md:block <?= (function_exists('is_plugin_active') && is_plugin_active('Ran_Vip')) ? \Plugins\Ran_Vip\Plugin::getVipColor($_SESSION['user']['id'], 'font-bold text-sm text-ink-900 dark:text-gray-100') : 'font-bold text-sm text-ink-900 dark:text-gray-100' ?> max-w-[100px] truncate"><?= e($_SESSION['user']['username'] ?? $_SESSION['user']['username']) ?></span>
                        <i class="fas fa-chevron-down text-xs text-gray-400 group-hover:rotate-180 transition-transform"></i>
                    </button>
                    
                    <!-- Dropdown Menu -->
                    <div class="absolute right-0 top-full mt-2 w-72 bg-white dark:bg-[#151515] rounded-xl border border-gray-100 dark:border-white/10 shadow-xl py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all transform origin-top-right z-50">
                        <?php 
                            // Fetch fresh stats
                            $uid = $_SESSION['user']['id'];
                            $db = \Core\Database::getInstance(config('db'));
                            $freshUser = $db->query("SELECT * FROM users WHERE id = ?", [$uid])->fetch();
                            $points = $freshUser['points'] ?? 0;
                            $balance = $freshUser['balance'] ?? '0.00';
                            $exp = $freshUser['exp'] ?? 0;
                            
                            $isVip = false;
                            if(function_exists('is_plugin_active') && is_plugin_active('Ran_Vip')) {
                                $isVip = \Plugins\Ran_Vip\Plugin::isVip($uid);
                            }
                            
                            $gradeName = 'Lv.0';
                            $progress = 0;
                            if(function_exists('is_plugin_active') && is_plugin_active('Ran_Grade')) {
                                $grade = \Plugins\Ran_Grade\Plugin::getLevel($uid);
                                $gradeName = $grade['name'];
                                
                                // Calculate Progress
                                $nextGrade = $db->query("SELECT * FROM grades WHERE exp_required > ? ORDER BY exp_required ASC LIMIT 1", [$exp])->fetch();
                                if ($nextGrade) {
                                    $currentLevelExp = $grade['exp_required']; 
                                    $nextLevelExp = $nextGrade['exp_required'];
                                    $range = $nextLevelExp - $currentLevelExp;
                                    $gained = $exp - $currentLevelExp;
                                    $progress = ($range > 0) ? min(100, max(0, ($gained / $range) * 100)) : 100;
                                } else {
                                    $progress = 100; // Max level
                                }
                            }
                        ?>
                        <div class="px-4 py-4 border-b border-gray-100 dark:border-white/10 mb-2 bg-gray-50/50 dark:bg-white/5">
                            <div class="flex items-center gap-3 mb-3">
                                <img src="<?= e($_SESSION['user']['avatar'] ?? '/assets/default-avatar.png') ?>" class="w-12 h-12 rounded-full border-2 border-white dark:border-white/10 shadow-sm">
                                <div class="overflow-hidden">
                                     <p class="<?= (function_exists('is_plugin_active') && is_plugin_active('Ran_Vip')) ? \Plugins\Ran_Vip\Plugin::getVipColor($_SESSION['user']['id'], 'font-bold text-ink-900 dark:text-white') : 'font-bold text-ink-900 dark:text-white' ?> truncate text-base"><?= e($_SESSION['user']['username'] ?? $_SESSION['user']['username']) ?></p>
                                     <div class="flex items-center gap-2 mt-1 flex-wrap">
                                         <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-ink-900 text-white dark:bg-white dark:text-black">
                                            <?= $gradeName ?>
                                         </span>
                                         <?php if($isVip): ?>
                                             <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-ink-900 text-white dark:bg-white dark:text-black flex items-center">
                                                <i class="fas fa-crown mr-1"></i>VIP
                                             </span>
                                         <?php else: ?>
                                             <?php if(function_exists('is_plugin_active') && is_plugin_active('Ran_Vip')): ?>
                                             <a href="<?= url('/vip/plans') ?>" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-gray-200 text-gray-500 hover:text-black transition-colors">
                                                开通VIP
                                             </a>
                                             <?php endif; ?>
                                         <?php endif; ?>
                                         <?php if (class_exists('Plugins\Ran_Title\Plugin')) \Plugins\Ran_Title\Plugin::renderTitle($_SESSION['user']['id']); ?>
                                     </div>
                                </div>
                            </div>
                            
                            <!-- Stats Grid -->
                            <div class="grid grid-cols-3 gap-2 mt-2 text-center mb-3">
                                <?php if(function_exists('is_plugin_active') && is_plugin_active('Ran_Integral')): ?>
                                <div class="bg-white dark:bg-white/5 rounded-lg p-2 border border-gray-100 dark:border-white/5">
                                    <div class="text-[10px] text-gray-400 mb-0.5"><?= e(get_option('integral_currency_name', '积分')) ?></div>
                                    <div class="font-bold text-ink-900 dark:text-white text-xs truncate"><?= $points ?></div>
                                </div>
                                <?php endif; ?>

                                <?php if(function_exists('is_plugin_active') && is_plugin_active('Ran_Wallet')): ?>
                                <div class="bg-white dark:bg-white/5 rounded-lg p-2 border border-gray-100 dark:border-white/5">
                                    <div class="text-[10px] text-gray-400 mb-0.5">余额</div>
                                    <div class="font-bold text-ink-900 dark:text-white text-xs truncate"><?= $balance ?></div>
                                </div>
                                <?php endif; ?>

                                <?php if(function_exists('is_plugin_active') && is_plugin_active('Ran_Grade')): ?>
                                <div class="bg-white dark:bg-white/5 rounded-lg p-2 border border-gray-100 dark:border-white/5">
                                    <div class="text-[10px] text-gray-400 mb-0.5">经验</div>
                                    <div class="font-bold text-ink-900 dark:text-white text-xs truncate"><?= $exp ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if(function_exists('is_plugin_active') && is_plugin_active('Ran_Grade')): ?>
                            <!-- EXP Progress Bar -->
                            <div class="w-full h-1.5 bg-gray-200 dark:bg-white/10 rounded-full overflow-hidden relative">
                                <div class="absolute top-0 left-0 h-full bg-gradient-to-r from-blue-400 to-blue-600 rounded-full transition-all duration-500" style="width: <?= $progress ?>%"></div>
                            </div>
                            <div class="flex justify-between mt-1 text-[10px] text-gray-400">
                                <span>当前进度</span>
                                <span><?= floor($progress) ?>%</span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <a href="<?= url('/write') ?>" class="block md:hidden px-4 py-2.5 text-sm text-ink-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-white/5 hover:text-ink-900 dark:hover:text-white transition-colors">
                            <i class="fas fa-plus  w-5 text-center"></i> 发布
                        </a>

                        
                        <a href="<?= url('/my') ?>" class="block px-4 py-2.5 text-sm text-ink-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-white/5 hover:text-ink-900 dark:hover:text-white transition-colors">
                            <i class="fas fa-user w-5 text-center"></i> 个人资料
                        </a>

                        <!-- Nested Theme Menu -->
                        <div class="relative group/theme px-2 py-1">
                            <button class="w-full flex items-center justify-between px-2 py-2.5 text-sm text-ink-500 dark:text-gray-400 bg-gray-50 dark:bg-white/5 hover:bg-gray-100 dark:hover:bg-white/10 rounded-lg transition-colors group-hover/theme:text-ink-900 dark:group-hover/theme:text-white">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-moon w-5 text-center"></i>
                                    <span id="theme-text-label">主题：浅色</span>
                                </div>
                                <i class="fas fa-chevron-right text-xs"></i>
                            </button>

                            <!-- Submenu -->
                            <div class="absolute left-[calc(100%+8px)] top-0 w-40 bg-white dark:bg-[#151515] rounded-xl border border-gray-100 dark:border-white/10 shadow-xl py-2 invisible opacity-0 group-hover/theme:visible group-hover/theme:opacity-100 transition-all transform origin-top-left">
                                <button onclick="setTheme('dark')" class="w-full text-left px-4 py-2.5 text-sm text-ink-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-white/5 hover:text-ink-900 dark:hover:text-white transition-colors flex justify-between items-center group/item">
                                    <span>深色</span>
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-gray-800 text-white dark:bg-gray-700">内测</span>
                                </button>
                                <button onclick="setTheme('light')" class="w-full text-left px-4 py-2.5 text-sm text-ink-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-white/5 hover:text-ink-900 dark:hover:text-white transition-colors">
                                    <span>浅色</span>
                                </button>
                            </div>
                        </div>
                        
                        <div class="border-t border-gray-100 dark:border-white/10 my-1"></div>
                        
                        <a href="<?= url('/logout') ?>" class="block px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/10 transition-colors">
                            <i class="fas fa-sign-out-alt w-5 text-center"></i> 退出登录
                        </a>
                    </div>
                </div>
                <?php else: ?>
                <a href="<?= url('/login') ?>" class="md:flex items-center gap-2 bg-black text-white px-5 py-2.5 rounded-full font-bold text-sm hover:opacity-80 transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5">开始创作</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>