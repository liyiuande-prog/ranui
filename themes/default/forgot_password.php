<?php $this->render('header'); ?>

<div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-[#0a0a0a] py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white dark:bg-[#151515] p-8 rounded-2xl shadow-xl border border-gray-100 dark:border-white/5 animate-fade-in relative overflow-hidden">
        
        <!-- Header -->
        <div class="text-center">
            <h2 class="text-3xl font-extrabold text-ink-900 dark:text-white mb-2">
                重置密码
            </h2>
            <p class="text-sm text-ink-500 dark:text-gray-400">
                请输入您的邮箱以获取验证码重置密码
            </p>
        </div>

        <form class="mt-8 space-y-6" id="form-reset" method="POST" action="<?= url('/auth/reset-password') ?>">
            <input type="hidden" name="csrf_token" value="<?= \Core\Csrf::generate() ?>">
            <div class="rounded-md shadow-sm -space-y-px">
                
                <!-- Email -->
                <div class="relative group mb-4">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-envelope text-gray-400 group-focus-within:text-ink-900 dark:group-focus-within:text-white transition-colors"></i>
                    </div>
                    <input id="email-input" name="email" type="email" required 
                           class="appearance-none rounded-xl relative block w-full pl-10 px-3 py-3 border border-gray-300 dark:border-white/10 placeholder-gray-500 text-ink-900 dark:text-white rounded-t-md focus:outline-none focus:ring-ink-500 focus:border-ink-500 focus:z-10 sm:text-sm bg-transparent transition-all" 
                           placeholder="邮箱地址">
                </div>
                
                <!-- Code -->
                <div class="flex gap-2 mb-6">
                    <div class="relative group flex-1">
                         <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-key text-gray-400 group-focus-within:text-ink-900 transition-colors"></i>
                         </div>
                         <input name="code" type="text" required 
                                class="appearance-none rounded-xl relative block w-full pl-10 px-3 py-3 border border-gray-300 dark:border-white/10 placeholder-gray-500 text-ink-900 dark:text-white focus:outline-none focus:ring-ink-500 focus:border-ink-500 focus:z-10 sm:text-sm bg-transparent transition-all" 
                                placeholder="6位验证码">
                    </div>
                    <button type="button" onclick="sendVerificationCode(this)" class="w-32 bg-gray-100 dark:bg-white/10 text-ink-900 dark:text-white text-sm font-bold rounded-xl hover:bg-gray-200 dark:hover:bg-white/20 transition-colors border border-transparent focus:outline-none">
                        获取验证码
                    </button>
                </div>
                
                <!-- New Password -->
                <div class="relative group mb-4">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                         <i class="fas fa-lock text-gray-400 group-focus-within:text-ink-900 dark:group-focus-within:text-white transition-colors"></i>
                    </div>
                    <input name="password" type="password" required 
                           class="appearance-none rounded-xl relative block w-full pl-10 px-3 py-3 border border-gray-300 dark:border-white/10 placeholder-gray-500 text-ink-900 dark:text-white focus:outline-none focus:ring-ink-500 focus:border-ink-500 focus:z-10 sm:text-sm bg-transparent transition-all" 
                           placeholder="新密码 (至少6位)">
                </div>

            </div>

            <?php if (isset($error)): ?>
                <div class="bg-red-50 dark:bg-red-900/20 text-red-500 text-sm p-3 rounded-lg flex items-center gap-2">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= e($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="bg-green-50 dark:bg-green-900/20 text-green-500 text-sm p-3 rounded-lg flex items-center gap-2">
                    <i class="fas fa-check-circle"></i>
                    <?= e($success) ?>
                </div>
            <?php endif; ?>

            <div>
                <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-bold rounded-xl text-white bg-ink-900 hover:bg-ink-800 dark:bg-white dark:text-black dark:hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-ink-500 transition-all transform hover:scale-[1.02]">
                    重置密码
                </button>
            </div>
            
            <div class="flex items-center justify-center text-sm">
                <a href="<?= url('/login') ?>" class="font-medium text-ink-500 hover:text-ink-900 dark:text-gray-400 dark:hover:text-white transition-colors">
                    <i class="fas fa-arrow-left mr-1"></i> 返回登录
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    function sendVerificationCode(btn) {
        const emailInput = document.getElementById('email-input');
        const email = emailInput.value.trim();
        
        if (!email) {
            alert('请输入邮箱地址');
            return;
        }
        
        btn.disabled = true;
        const originalText = btn.innerText;
        btn.innerText = '发送中...';
        
        fetch('<?= url('/auth/send-code') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'email=' + encodeURIComponent(email)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let seconds = 60;
                btn.innerText = seconds + 's';
                const timer = setInterval(() => {
                    seconds--;
                    btn.innerText = seconds + 's';
                    if (seconds <= 0) {
                        clearInterval(timer);
                        btn.disabled = false;
                        btn.innerText = '获取验证码';
                    }
                }, 1000);
            } else {
                alert(data.message);
                btn.disabled = false;
                btn.innerText = originalText;
            }
        })
        .catch(err => {
            alert('发送失败，请稍后重试');
            btn.disabled = false;
            btn.innerText = originalText;
        });
    }
</script>

<?php $this->render('footer'); ?>
