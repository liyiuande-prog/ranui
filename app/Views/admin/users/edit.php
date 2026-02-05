<?php require APP_PATH . '/Views/admin/header.php'; ?>
<?php require APP_PATH . '/Views/admin/sidebar.php'; ?>

<div class="md:pl-64 flex flex-col min-h-screen transition-all duration-300">
    <header class="h-16 bg-white/80 backdrop-blur-md border-b border-gray-100 flex items-center justify-between px-4 md:px-8 sticky top-0 z-40">
        <div class="flex items-center gap-4">
            <button onclick="toggleSidebar()" class="md:hidden text-gray-500 hover:text-black focus:outline-none mr-2">
                <i class="fas fa-bars text-xl"></i>
            </button>
             <a href="<?= url('/admin/users') ?>" class="text-gray-400 hover:text-black transition-colors"><i class="fas fa-arrow-left"></i></a>
             <h2 class="text-lg font-bold text-gray-800">编辑用户</h2>
        </div>
    </header>

    <main class="p-8 flex-1 max-w-2xl mx-auto w-full">
        <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm">
            <form action="<?= url('/admin/users/update/' . $user['id']) ?>" method="POST" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?= \Core\Csrf::generate() ?>">
                
                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 ml-1">用户唯一标识 UID (Read Only)</label>
                    <input type="text" value="<?= e($user['uid']) ?>" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-100 text-gray-400 font-bold cursor-not-allowed" disabled>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 ml-1">用户名 Username (Read Only)</label>
                    <input type="text" value="<?= e($user['username']) ?>" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-100 text-gray-400 font-bold cursor-not-allowed" disabled>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 ml-1">邮箱 Email</label>
                    <input type="email" name="email" value="<?= e($user['email']) ?>" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-100 focus:bg-white focus:border-black focus:ring-0 transition-all">
                </div>


                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 ml-1">修改密码 Password</label>
                    <input type="password" name="password" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-100 focus:bg-white focus:border-black focus:ring-0 transition-all" placeholder="留空则保持不变">
                </div>
                
                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 ml-1">角色 Role</label>
                    <select name="role" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-100 focus:bg-white focus:border-black focus:ring-0 transition-all font-bold">
                        <option value="editor" <?= $user['role'] == 'editor' ? 'selected' : '' ?>>编辑 (Editor)</option>
                        <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>管理员 (Admin)</option>
                    </select>
                </div>

                <button type="submit" class="w-full bg-black text-white font-bold py-3 rounded-xl hover:bg-gray-800 transition-all">保存修改</button>
            </form>
        </div>

        <?php \Core\Hook::listen('admin_users_edit_view', $user); ?>
    </main>
</div>

<?php require APP_PATH . '/Views/admin/footer.php'; ?>
