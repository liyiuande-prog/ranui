<!-- Mobile Backdrop -->
<div id="sidebar-backdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden backdrop-blur-sm transition-opacity opacity-0 pointer-events-none" style="opacity: 0;"></div>

<aside id="sidebar" class="w-64 bg-[#1a1a1a] text-white flex flex-col h-screen fixed left-0 top-0 border-r border-[#2a2a2a] z-50 transform -translate-x-full md:translate-x-0 transition-transform duration-300 shadow-2xl md:shadow-none">
    <div class="h-16 flex items-center px-6 border-b border-[#2a2a2a]">
        <span class="text-xl font-extrabold tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-white to-gray-400">RanUI Admin.</span>
    </div>

    <nav class="flex-1 p-3 space-y-2 overflow-y-auto custom-scrollbar select-none">
        
        <!-- Dashboard -->
        <a href="<?= url('/admin/dashboard') ?>" class="flex items-center gap-3 px-3 py-3 rounded-xl text-gray-400 hover:text-white hover:bg-white/5 transition-all group">
            <div class="w-5 h-5 flex items-center justify-center">
                <i class="fas fa-home text-sm group-hover:text-white transition-colors"></i>
            </div>
            <span class="font-medium">仪表盘</span>
        </a>

        <!-- Notifications -->
        <?php
        $unreadCount = 0;
        if (is_plugin_active('Ran_Notice')) {
            $db = \Core\Database::getInstance(config('db'));
            try {
                $unreadCount = $db->query("SELECT COUNT(*) FROM user_notifications WHERE user_id = ? AND is_read = 0", [$_SESSION['user']['id']])->fetchColumn();
            } catch(\Exception $e) {}
        }
        ?>
        <a href="<?= url('/admin/notifications') ?>" class="flex items-center justify-between px-3 py-3 rounded-xl text-gray-400 hover:text-white hover:bg-white/5 transition-all group mb-4">
            <div class="flex items-center gap-3">
                <div class="w-5 h-5 flex items-center justify-center">
                    <i class="fas fa-bell text-sm group-hover:text-white transition-colors"></i>
                </div>
                <span class="font-medium">系统通知</span>
            </div>
            <?php if ($unreadCount > 0): ?>
                <span class="bg-blue-600 text-white text-[10px] px-1.5 py-0.5 rounded-full font-bold animate-pulse"><?= $unreadCount ?></span>
            <?php endif; ?>
        </a>




        <!-- 1. Content Management -->
        <div>
            <button onclick="toggleSubmenu('menu-content')" class="w-full flex items-center justify-between px-3 py-3 rounded-xl text-gray-400 hover:text-white hover:bg-white/5 transition-all group">
                <div class="flex items-center gap-3">
                    <div class="w-5 h-5 flex items-center justify-center">
                        <i class="fas fa-layer-group text-sm group-hover:text-white transition-colors"></i>
                    </div>
                    <span class="font-medium">内容管理</span>
                </div>
                <i id="menu-content-arrow" class="fas fa-chevron-down text-xs transition-transform duration-200"></i>
            </button>
            <div id="menu-content" class="hidden pl-3 mt-1 space-y-1">
                <a href="<?= url('/admin/posts') ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-500 hover:text-white hover:bg-white/5 transition-all">
                    <span class="w-1.5 h-1.5 rounded-full bg-gray-600"></span>
                    <span>文章管理</span>
                </a>
                <a href="<?= url('/admin/categories') ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-500 hover:text-white hover:bg-white/5 transition-all">
                    <span class="w-1.5 h-1.5 rounded-full bg-gray-600"></span>
                    <span>分类目录</span>
                </a>
                <a href="<?= url('/admin/comments') ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-500 hover:text-white hover:bg-white/5 transition-all">
                   <span class="w-1.5 h-1.5 rounded-full bg-gray-600"></span>
                    <span>评论互动</span>
                </a>
                <!-- Hook -->
                <?php \Core\Hook::listen('admin_sidebar_content'); ?>
            </div>
        </div>

        <!-- 2. User Management -->
        <div>
            <button onclick="toggleSubmenu('menu-user')" class="w-full flex items-center justify-between px-3 py-3 rounded-xl text-gray-400 hover:text-white hover:bg-white/5 transition-all group">
                <div class="flex items-center gap-3">
                    <div class="w-5 h-5 flex items-center justify-center">
                        <i class="fas fa-users text-sm group-hover:text-white transition-colors"></i>
                    </div>
                    <span class="font-medium">用户管理</span>
                </div>
                <i id="menu-user-arrow" class="fas fa-chevron-down text-xs transition-transform duration-200"></i>
            </button>
            <div id="menu-user" class="hidden pl-3 mt-1 space-y-1">
                <a href="<?= url('/admin/users') ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-500 hover:text-white hover:bg-white/5 transition-all">
                    <span class="w-1.5 h-1.5 rounded-full bg-gray-600"></span>
                    <span>用户列表</span>
                </a>
                <!-- Hook -->
                <?php \Core\Hook::listen('admin_sidebar_user'); ?>
                <?php \Core\Hook::listen('admin_sidebar_users'); ?>
            </div>
        </div>

        <!-- Title Hook (Separate Menu) -->
        <?php \Core\Hook::listen('admin_sidebar_title'); ?>

        <!-- Merchant Hook -->
        <?php \Core\Hook::listen('admin_sidebar_merchant'); ?>

        <!-- 3. Extension Management -->
        <div>
            <button onclick="toggleSubmenu('menu-extension')" class="w-full flex items-center justify-between px-3 py-3 rounded-xl text-gray-400 hover:text-white hover:bg-white/5 transition-all group">
                <div class="flex items-center gap-3">
                    <div class="w-5 h-5 flex items-center justify-center">
                        <i class="fas fa-cube text-sm group-hover:text-white transition-colors"></i>
                    </div>
                    <span class="font-medium">扩展管理</span>
                </div>
                <i id="menu-extension-arrow" class="fas fa-chevron-down text-xs transition-transform duration-200"></i>
            </button>
            <div id="menu-extension" class="hidden pl-3 mt-1 space-y-1">
                <a href="<?= url('/admin/themes') ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-500 hover:text-white hover:bg-white/5 transition-all">
                    <span class="w-1.5 h-1.5 rounded-full bg-gray-600"></span>
                    <span>主题配置</span>
                </a>
                <a href="<?= url('/admin/plugins') ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-500 hover:text-white hover:bg-white/5 transition-all">
                    <span class="w-1.5 h-1.5 rounded-full bg-gray-600"></span>
                    <span>插件管理</span>
                </a>
                <!-- Hook -->
                <?php \Core\Hook::listen('admin_sidebar_extension'); ?>
            </div>
        </div>

        <!-- 3.5 Finance Hook -->
        <?php \Core\Hook::listen('admin_sidebar_finance'); ?>
        <!-- MiniApp Hook -->
        <?php \Core\Hook::listen('admin_sidebar_miniapp_v2'); ?>

        <!-- Main Hook (Top Level Plugins) -->
        <?php \Core\Hook::listen('admin_sidebar_main'); ?>

        <!-- 4. System Management -->
        <div>
            <button onclick="toggleSubmenu('menu-system')" class="w-full flex items-center justify-between px-3 py-3 rounded-xl text-gray-400 hover:text-white hover:bg-white/5 transition-all group">
                <div class="flex items-center gap-3">
                    <div class="w-5 h-5 flex items-center justify-center">
                        <i class="fas fa-sliders-h text-sm group-hover:text-white transition-colors"></i>
                    </div>
                    <span class="font-medium">系统管理</span>
                </div>
                <i id="menu-system-arrow" class="fas fa-chevron-down text-xs transition-transform duration-200"></i>
            </button>
            <div id="menu-system" class="hidden pl-3 mt-1 space-y-1">
                <a href="<?= url('/admin/options') ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-500 hover:text-white hover:bg-white/5 transition-all">
                    <span class="w-1.5 h-1.5 rounded-full bg-gray-600"></span>
                    <span>系统设置</span>
                </a>
                <!-- Hook -->
                <?php \Core\Hook::listen('admin_sidebar_system'); ?>
            </div>
        </div>

    </nav>

    <script>
        function toggleSubmenu(id) {
            // Close others
            const allMenus = document.querySelectorAll('[id^="menu-"]');
            allMenus.forEach(menu => {
                if (menu.id === id) return;
                // Only close submenus (ignore arrows/buttons, targeting div containers)
                if (menu.tagName === 'DIV' && !menu.classList.contains('hidden') && !menu.id.includes('arrow')) {
                     menu.classList.add('hidden');
                     // Find associated arrow
                     const arrow = document.getElementById(menu.id + '-arrow');
                     if(arrow) arrow.classList.remove('rotate-180');
                }
            });

            const el = document.getElementById(id);
            const arrow = document.getElementById(id + '-arrow');
            if (el.classList.contains('hidden')) {
                el.classList.remove('hidden');
                arrow.classList.add('rotate-180');
            } else {
                el.classList.add('hidden');
                arrow.classList.remove('rotate-180');
            }
        }
        
        // Auto-open based on current URL (Simple logic)
        document.addEventListener('DOMContentLoaded', () => {
            const currentPath = window.location.pathname;
            if (currentPath.includes('/posts') || currentPath.includes('/categories') || currentPath.includes('/comments') || currentPath.includes('/circles')) {
                toggleSubmenu('menu-content');
            } else if (currentPath.includes('/users') && !currentPath.includes('/titles')) {
                toggleSubmenu('menu-user');
            } else if (currentPath.includes('/themes') || currentPath.includes('/plugins')) {
                toggleSubmenu('menu-extension');
            } else if (currentPath.includes('/miniapp')) {
                toggleSubmenu('menu-miniapp');
            } else if (currentPath.includes('/options')) {
                toggleSubmenu('menu-system');
            }
        });
    </script>
    
    <div class="p-3 border-t border-[#2a2a2a]">
        <a href="<?= url('/admin/users/edit/' . ($_SESSION['user_id'] ?? ($_SESSION['user']['id'] ?? 1))) ?>" class="block group">
            <div class="flex items-center gap-3 px-3 py-3 mb-2 rounded-lg bg-[#252525] border border-[#333] group-hover:bg-[#333] transition-colors">
                 <img src="<?= e($_SESSION['user']['avatar'] ?? '/assets/default-avatar.png') ?>" class="w-8 h-8 rounded-full bg-gray-500">
                 <div class="overflow-hidden">
                     <p class="text-xs font-bold text-white truncate"><?= e($_SESSION['user']['username'] ?? 'Admin') ?></p>
                     <p class="text-[10px] text-gray-500 truncate">Administrator (Edit)</p>
                 </div>
            </div>
        </a>
        <div class="grid grid-cols-2 gap-2">
            <a href="<?= url('/') ?>" target="_blank" class="flex items-center justify-center gap-2 px-2 py-2 rounded-lg text-gray-500 hover:text-gray-300 hover:bg-white/5 transition-colors text-xs font-medium bg-[#252525]">
                <i class="fas fa-globe"></i> 前台
            </a>
            <a href="<?= url('/admin/logout') ?>" class="flex items-center justify-center gap-2 px-2 py-2 rounded-lg text-red-500 hover:text-red-400 hover:bg-red-500/10 transition-colors text-xs font-medium bg-[#252525]">
                <i class="fas fa-power-off"></i> 退出
            </a>
        </div>
    </div>
</aside>
