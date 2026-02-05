<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录 - RanUI Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-[#f8f9fa] flex items-center justify-center min-h-screen text-[#1a1a1a]">
    <div class="w-full max-w-md p-6">
        <div class="bg-white p-10 rounded-[2rem] shadow-[0_20px_40px_-15px_rgba(0,0,0,0.05)] border border-gray-100">
            <div class="text-center mb-10">
                <a href="<?= url('/') ?>" class="inline-block text-3xl font-extrabold tracking-tight mb-2">RanUI.</a>
                <p class="text-gray-500 text-sm font-medium">后台管理系统</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="bg-red-50 text-red-600 text-sm font-medium p-4 rounded-xl mb-6 text-center border border-red-100 animate-pulse">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form id="login-form" action="<?= url('/admin/login') ?>" method="POST" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?= \Core\Csrf::generate() ?>">
                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 ml-1">用户名</label>
                    <input type="text" name="username" class="w-full px-5 py-4 rounded-xl bg-gray-50 border border-gray-100 focus:bg-white focus:border-black focus:ring-0 transition-all outline-none font-medium text-lg placeholder-gray-300" placeholder="Username" required value="<?= isset($username) ? htmlspecialchars($username) : '' ?>">
                </div>
                
                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 ml-1">密码</label>
                    <input type="password" name="password" class="w-full px-5 py-4 rounded-xl bg-gray-50 border border-gray-100 focus:bg-white focus:border-black focus:ring-0 transition-all outline-none font-medium text-lg placeholder-gray-300 font-sans" placeholder="••••••••" required>
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-gray-300 text-black focus:ring-black">
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">记住我</span>
                    </label>
                    <a href="#" class="text-xs font-bold text-gray-400 hover:text-black transition-colors">忘记密码?</a>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full bg-black text-white font-bold py-4 rounded-xl hover:bg-gray-800 transition-all transform hover:-translate-y-1 hover:shadow-lg active:scale-95 duration-200">
                        登录系统
                    </button>
                </div>
            </form>
        </div>
        
        <p class="text-center mt-8 text-xs text-gray-400 font-medium">
            <a href="<?= url('/') ?>" class="hover:text-gray-600">&larr; 返回前台</a>
            <span class="mx-2">|</span>
            &copy; <?= date('Y') ?> RanUI Blog System
        </p>
    </div>

    <!-- 2FA Modal -->
    <div id="twofa-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 hidden backdrop-blur-sm animate-in fade-in duration-300">
        <div class="bg-white p-8 rounded-[2rem] shadow-2xl max-w-sm w-full mx-4 transform transition-all scale-95 border border-gray-100">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900">双重验证</h3>
                <p class="text-sm text-gray-500 mt-2">请输入您的身份验证器生成的 6 位数字验证码</p>
            </div>
            
            <div class="space-y-4">
                <input type="text" id="twofa-code" maxlength="6" class="w-full px-6 py-4 rounded-xl bg-gray-50 border border-gray-100 text-center text-3xl font-black tracking-[0.5em] focus:bg-white focus:border-blue-500 outline-none transition-all placeholder-gray-200" placeholder="000000">
                <p id="twofa-error" class="text-xs text-red-500 text-center hidden"></p>
                <button onclick="submitTwoFA()" class="w-full bg-black text-white font-bold py-4 rounded-xl hover:bg-gray-800 transition-all active:scale-95">验证并登录</button>
                <button onclick="closeTwoFAModal()" class="w-full py-2 text-xs font-bold text-gray-400 uppercase tracking-widest hover:text-gray-600 transition-colors">取消</button>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script>
        const loginForm = document.getElementById('login-form');
        
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(r => r.json())
            .then(res => {
                if (res.action === '2fa_required') {
                    openTwoFAModal();
                } else if (res.redirect) {
                    window.location.href = res.redirect;
                } else if (!res.success && res.message) {
                    alert(res.message);
                } else {
                    // If server didn't return JSON but redirected or something, we might need a fallback.
                    // But our controller now returns JSON for AJAX requests.
                    if (res.action === undefined) {
                         // Fallback to normal submit if something is wrong
                         loginForm.submit();
                    }
                }
            })
            .catch(err => {
                console.error("Login Fetch Error:", err);
                loginForm.submit(); // Force normal submit as fallback
            });
        });

        function openTwoFAModal() {
            document.getElementById('twofa-modal').classList.remove('hidden');
            document.getElementById('twofa-code').focus();
        }

        function closeTwoFAModal() {
            document.getElementById('twofa-modal').classList.add('hidden');
        }

        function submitTwoFA() {
            const code = document.getElementById('twofa-code').value;
            const errorEl = document.getElementById('twofa-error');
            
            if (code.length !== 6) {
                errorEl.innerText = '请输入 6 位数字验证码';
                errorEl.classList.remove('hidden');
                return;
            }

            fetch('<?= url('/auth/2fa/verify/ajax') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'code=' + encodeURIComponent(code)
            })
            .then(r => r.json())
            .then(res => {
                if (res.success && res.redirect) {
                    window.location.href = res.redirect;
                } else {
                    errorEl.innerText = res.message || '验证失败';
                    errorEl.classList.remove('hidden');
                }
            });
        }
    </script>
</body>
</html>
