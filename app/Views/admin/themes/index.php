<?php require APP_PATH . '/Views/admin/header.php'; ?>
<?php require APP_PATH . '/Views/admin/sidebar.php'; ?>

<div class="md:pl-64 flex flex-col min-h-screen transition-all duration-300">
    <header class="h-16 bg-white/80 backdrop-blur-md border-b border-gray-100 flex items-center justify-between px-4 md:px-8 sticky top-0 z-40">
        <div class="flex items-center gap-4">
            <button onclick="toggleSidebar()" class="md:hidden text-gray-500 hover:text-black focus:outline-none">
                <i class="fas fa-bars text-xl"></i>
            </button>
            <h2 class="text-lg font-bold text-gray-800">主题配置</h2>
        </div>
    </header>

    <main class="p-8 flex-1">
        <div class="space-y-6">
            <?php foreach($themes as $theme): ?>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col md:flex-row hover:shadow-md transition-shadow">
                <!-- Preview -->
                <div class="md:w-64 h-48 md:h-auto bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center text-gray-400 group relative shrink-0">
                     <i class="fas fa-palette text-5xl opacity-50"></i>
                     <div class="absolute inset-0 bg-black/5 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                         <span class="text-xs font-bold uppercase tracking-widest text-black/50">Preview</span>
                     </div>
                </div>
                
                <div class="p-6 flex-1 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <!-- Info -->
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="font-bold text-xl text-gray-800 capitalize"><?= e($theme['name']) ?> Theme</h3>
                            <span class="px-2 py-1 rounded bg-gray-100 text-[10px] font-bold text-gray-500">v<?= e($theme['version']) ?></span>
                            <?php if($theme['is_active']): ?>
                            <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full font-bold flex items-center gap-1">
                                <i class="fas fa-check-circle"></i> Active
                            </span>
                            <?php endif; ?>
                        </div>
                        <p class="text-sm text-gray-400 mb-2">
                            Path: <span class="font-mono bg-gray-50 px-2 py-0.5 rounded text-gray-500">/themes/<?= e($theme['name']) ?></span>
                        </p>
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex flex-col gap-3 min-w-[160px]">
                        <?php if(!$theme['is_active']): ?>
                            <button onclick="postAction('<?= url('/admin/themes/activate/' . $theme['id']) ?>')" class="bg-black text-white font-bold py-2.5 px-6 rounded-xl hover:opacity-80 transition-opacity text-center text-sm w-full block">
                                启用 (Activate)
                            </button>
                        <?php endif; ?>
                        
                        <a href="<?= url('/admin/themes/editor?theme=' . $theme['name']) ?>" class="bg-gray-100 text-gray-600 font-bold py-2.5 px-6 rounded-xl hover:bg-gray-200 transition-colors text-center text-sm flex items-center justify-center gap-2">
                             <i class="fas fa-code"></i> 修改代码
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>
</div>

<?php require APP_PATH . '/Views/admin/footer.php'; ?>
