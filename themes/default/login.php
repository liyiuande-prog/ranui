<?php $this->render('header'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jsencrypt/3.3.2/jsencrypt.min.js"></script>
<script>
    const RSA_PUBLIC_KEY = `-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEApu/lRV3Y3iDDZI3LOpC3
Qr/aaQsWimc4N8McznKU0+Jm8qBJmZ5Uv0VMFjX9vE2u9WeuTNr82wPaLouvmGX+
X4FUB3YZ+CR9SF0kGA6Ma0ejlCeEzeZSSMKQ7sM7sgASYVztkSJZ+rnSULWaxRnb
0zlcjPbcw2/SuoQ4i0/RiraviK8qvQJwsrCz+TnjS7PXKTBTSzvXF40sS1Ac8HSY
nm7BEq7VM1tfcUHphn3kQHN4RGR1Gb+Q+bH91jrYdNf+sVU2VbB6yPQVjiFjE+U/
lCXz5zKRkrCvlAUGGV9qsFt0joT2OfQAg/Xz91CpKnzfbvFa6treQ+VDKXkYbLpL
+wIDAQAB
-----END PUBLIC KEY-----`;
</script>

<div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-[#0a0a0a] pt-20 pb-10 px-4">
    
    <!-- Main Card -->
    <div class="bg-white dark:bg-[#151515] w-full max-w-md rounded-3xl shadow-2xl overflow-hidden relative border border-gray-100 dark:border-white/5">
        
        <!-- Corner Toggle (QR Code Switch) -->
        <div class="absolute top-0 right-0 cursor-pointer z-20" onclick="toggleLoginMode()" title="切换登录方式">
            <!-- Triangle Background -->
            <div class="absolute top-0 right-0 w-16 h-16 bg-gradient-to-bl from-ink-900 to-gray-800 dark:from-white dark:to-gray-200" style="clip-path: polygon(0 0, 100% 0, 100% 100%);"></div>
            <!-- Icon -->
            <i id="mode-icon" class="fas fa-qrcode absolute top-2 right-2 text-white dark:text-black text-2xl"></i>
        </div>

        <!-- Mode A: Normal Login Forms -->
        <div id="login-container" class="p-8 md:p-10 transition-all duration-300">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-extrabold text-ink-900 dark:text-white mb-2">欢迎回来</h1>
            </div>

            <?php if (isset($_SESSION['cancel_account_pending'])): ?>
            <!-- Account Cancellation Confirmation Modal (Inline) -->
            <div class="mb-6 p-6 bg-yellow-50 dark:bg-yellow-900/10 border border-yellow-200 dark:border-yellow-700/30 rounded-2xl text-center">
                 <div class="w-16 h-16 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                     <i class="fas fa-exclamation-triangle"></i>
                 </div>
                 <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">账号注销确认</h3>
                 <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                     您的账号当前处于<b>注销申请期</b>。请确认您的操作：
                 </p>
                 
                 <div class="space-y-3">
                     <button onclick="confirmCancelAccount()" class="w-full py-3 bg-red-600 text-white font-bold rounded-xl hover:bg-red-700 transition-colors shadow-lg shadow-red-500/20">
                         <i class="fas fa-trash-alt mr-2"></i> 确认立即注销 (删除所有数据)
                     </button>
                     <button onclick="revokeCancelAccount()" class="w-full py-3 bg-white dark:bg-white/10 text-gray-900 dark:text-white border border-gray-200 dark:border-white/10 font-bold rounded-xl hover:bg-gray-50 dark:hover:bg-white/20 transition-colors">
                         <i class="fas fa-undo mr-2"></i> 放弃注销 (恢复正常登录)
                     </button>
                 </div>
                 <p class="text-xs text-gray-400 mt-4">* 无论选择哪项，都需要进行安全验证。</p>
            </div>
            
            <script>
                // Override submit function for cancellation logic
                let cancelActionType = ''; // 'confirm' or 'revoke'

                function confirmCancelAccount() {
                    cancelActionType = 'confirm';
                    openCaptcha(); // Trigger captcha first
                }

                function revokeCancelAccount() {
                    cancelActionType = 'revoke';
                    openCaptcha(); // Trigger captcha first
                }

                // Hook into the captcha submit
                // Hook into the captcha submit
                // Wait for DOM to be ready so 'submitCaptcha' logic from later in the file is loaded (though we are replacing it)
                document.addEventListener('DOMContentLoaded', function() {
                    // Override the global submitCaptcha function defined at the bottom of the page
                    submitCaptcha = function() {
                        // Only intercept if we are in cancel flow
                        if (cancelActionType) {
                            const pointsStr = clickPoints.map(p => `${p.x},${p.y}`).join(',');
                            
                            // Create a hidden form to submit
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = '<?= url('/user/cancel/handle') ?>';
                            
                            const actionInput = document.createElement('input');
                            actionInput.type = 'hidden';
                            actionInput.name = 'action';
                            actionInput.value = cancelActionType;
                            
                            const captchaInput = document.createElement('input');
                            captchaInput.type = 'hidden';
                            captchaInput.name = 'captcha_points';
                            captchaInput.value = pointsStr;
                            
                            const csrfInput = document.createElement('input');
                            csrfInput.type = 'hidden';
                            csrfInput.name = 'csrf_token';
                            // Ensure we use the correct CSRF token method. 
                            // If \Core\Csrf::token() is deprecated/removed in favor of field() or similar, verify.
                            // Assuming token() returns the string token.
                            csrfInput.value = '<?= \Core\Csrf::generate() ?>';

                            form.appendChild(actionInput);
                            form.appendChild(captchaInput);
                            form.appendChild(csrfInput);
                            
                            document.body.appendChild(form);
                            form.submit();
                        } else {
                            console.log("Submit Captcha for Cancellation - No action type set");
                        }
                    }
                });
            </script>
            <?php else: ?>

            <!-- Tabs -->
            <div class="flex justify-center gap-8 mb-8 border-b border-gray-100 dark:border-white/10 relative">
                <button onclick="switchTab('password')" id="tab-password" class="pb-3 text-sm font-bold text-ink-900 dark:text-white border-b-2 border-ink-900 dark:border-white transition-all">
                    账号密码登录
                </button>
                <button onclick="switchTab('code')" id="tab-code" class="pb-3 text-sm font-medium text-gray-400 hover:text-ink-600 dark:hover:text-gray-300 border-b-2 border-transparent transition-all">
                    验证码注册
                </button>
            </div>

            <?php if(!empty($error)): ?>
            <div class="mb-6 p-3 rounded-lg bg-red-50 dark:bg-red-900/10 text-red-500 text-sm flex items-center gap-2">
                <i class="fas fa-exclamation-circle"></i> <?= e($error) ?>
            </div>
            <?php endif; ?>

            <?php if(!empty($success)): ?>
            <div class="mb-6 p-3 rounded-lg bg-green-50 dark:bg-green-900/10 text-green-600 text-sm flex items-center gap-2">
                <i class="fas fa-check-circle"></i> <?= e($success) ?>
            </div>
            <?php endif; ?>

            <!-- Form 1: Password -->
            <form id="form-password" action="<?= url('/login') ?>" method="POST" class="space-y-5 block">
                <?php echo \Core\Csrf::field(); ?>
                <div class="space-y-2">
                    <div class="relative group">
                        <span class="absolute left-4 top-3.5 text-gray-400"><i class="fas fa-user"></i></span>
                        <input type="text" name="username" value="<?= e($username ?? '') ?>" required class="w-full pl-11 pr-4 py-3 rounded-xl bg-gray-50 dark:bg-white/5 border border-transparent dark:border-white/10 focus:bg-white dark:focus:bg-black focus:border-ink-900 dark:focus:border-white focus:ring-0 outline-none transition-all font-medium dark:text-white placeholder-gray-400" placeholder="请输入账号 / 邮箱 / UID">
                    </div>
                </div>
                
                <div class="space-y-2">
                    <div class="relative group">
                        <span class="absolute left-4 top-3.5 text-gray-400"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" required class="w-full pl-11 pr-4 py-3 rounded-xl bg-gray-50 dark:bg-white/5 border border-transparent dark:border-white/10 focus:bg-white dark:focus:bg-black focus:border-ink-900 dark:focus:border-white focus:ring-0 outline-none transition-all font-medium dark:text-white placeholder-gray-400" placeholder="请输入密码">
                    </div>
                </div>

                <div class="flex justify-between items-center text-sm">
                    <div class="flex items-center">
                        <input type="checkbox" id="remember" name="remember" class="w-4 h-4 rounded border-gray-300 text-ink-900 focus:ring-ink-900">
                        <label for="remember" class="ml-2 text-gray-500 dark:text-gray-400">记住我</label>
                    </div>
                    <a href="<?= url('/forgot-password') ?>" class="text-accent hover:underline font-medium">忘记密码?</a>
                </div>

                <button type="submit" class="w-full bg-ink-900 dark:bg-white text-white dark:text-black py-3.5 rounded-xl font-bold hover:shadow-lg transition-all">
                    登录
                </button>

                <!-- OAuth Hook -->
                <?php \Core\Hook::listen('login_oauth_buttons'); ?>
            </form>

            <!-- Form 2: Code -->
            <form id="form-code" action="<?= url('/login/code') ?>" method="POST" class="space-y-5 hidden">
                 <?php echo \Core\Csrf::field(); ?>
                <div class="space-y-2">
                     <div class="relative group">
                        <span class="absolute left-4 top-3.5 text-gray-400"><i class="fas fa-envelope"></i></span>
                        <input type="email" name="email" id="email-input" required class="w-full pl-11 pr-4 py-3 rounded-xl bg-gray-50 dark:bg-white/5 border border-transparent dark:border-white/10 focus:bg-white dark:focus:bg-black focus:border-ink-900 dark:focus:border-white focus:ring-0 outline-none transition-all font-medium dark:text-white placeholder-gray-400" placeholder="请输入邮箱">
                    </div>
                </div>
                
                <div class="space-y-2">
                    <div class="flex gap-3">
                        <div class="relative group flex-1">
                            <span class="absolute left-4 top-3.5 text-gray-400"><i class="fas fa-shield-alt"></i></span>
                            <input type="text" name="code" required class="w-full pl-11 pr-4 py-3 rounded-xl bg-gray-50 dark:bg-white/5 border border-transparent dark:border-white/10 focus:bg-white dark:focus:bg-black focus:border-ink-900 dark:focus:border-white focus:ring-0 outline-none transition-all font-medium dark:text-white placeholder-gray-400" placeholder="输入验证码">
                        </div>
                        <button type="button" onclick="sendVerificationCode(this)" class="px-4 py-3 bg-gray-100 dark:bg-white/10 text-ink-900 dark:text-white rounded-xl font-bold hover:bg-gray-200 dark:hover:bg-white/20 transition-colors text-sm whitespace-nowrap min-w-[100px]">
                            获取验证码
                        </button>
                    </div>
                </div>
                
                <!-- Invite Code Hook (Plugins like Ran_Invite will inject here) -->
                <?php \Core\Hook::listen('auth_register_form'); ?>

                <button type="submit" class="w-full bg-ink-900 dark:bg-white text-white dark:text-black py-3.5 rounded-xl font-bold hover:shadow-lg transition-all">
                    注册 / 登录
                </button>
            </form>
            <?php endif; ?>
        </div>

        <!-- Mode B: QR Code Login -->
        <div id="qr-container" class="hidden p-10 flex-col items-center justify-center min-h-[400px] text-center animate-fade-in">
             <h2 id="qr-status-title" class="text-xl font-bold text-ink-900 dark:text-white mb-8">手机扫码安全登录</h2>
             
             <div class="p-2 border-2 border-gray-100 dark:border-white/10 rounded-xl mb-6 relative group bg-white dark:bg-black/20">
                 <!-- Canvas QR -->
                 <canvas id="qr-canvas" class="w-48 h-48 rounded-lg cursor-pointer transform transition-all duration-500"></canvas>
                 
                 <!-- Success Overlay (Shown when scanned but not yet confirmed) -->
                 <div id="qr-scanned-overlay" class="absolute inset-0 bg-white/95 dark:bg-black/95 flex flex-col items-center justify-center rounded-lg hidden animate-in fade-in zoom-in duration-300">
                    <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center mb-4 shadow-lg shadow-green-500/20">
                        <i class="fas fa-check text-white text-2xl"></i>
                    </div>
                    <p class="font-bold text-ink-900 dark:text-white">扫描成功</p>
                    <p class="text-xs text-gray-500 mt-1">请在手机上点击确认</p>
                 </div>

                 <!-- Overlay Interaction (Refresh) -->
                 <div id="qr-refresh-overlay" onclick="refreshQRCode()" class="absolute inset-0 bg-white/90 dark:bg-black/80 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity cursor-pointer">
                     <div class="text-center">
                         <i class="fas fa-sync text-2xl text-accent mb-2"></i>
                         <p class="text-sm font-bold text-ink-900 dark:text-white">点击刷新</p>
                     </div>
                 </div>
             </div>
             
             <div id="qr-footer-hint" class="flex items-center gap-2 text-sm text-gray-500 justify-center">
                 <i class="fas fa-mobile-alt text-lg"></i>
                 <span>打开 <span class="text-ink-900 dark:text-white font-bold">RanUI App</span> 扫一扫登录</span>
             </div>
        </div>

    </div>
</div>

<script src="<?= url('/assets/js/simple-qr.js') ?>"></script>
<script>
    function sendVerificationCode(btn) {
        const emailInput = document.getElementById('email-input');
        const email = emailInput.value.trim();
        
        if (!email) {
            alert('请输入邮箱地址');
            return;
        }
        
        // Disable button
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
                // Start Countdown
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

    function switchTab(tab) {
        const btnPass = document.getElementById('tab-password');
        const btnCode = document.getElementById('tab-code');
        const formPass = document.getElementById('form-password');
        const formCode = document.getElementById('form-code');

        if (tab === 'password') {
            btnPass.className = "pb-3 text-sm font-bold text-ink-900 dark:text-white border-b-2 border-ink-900 dark:border-white transition-all";
            btnCode.className = "pb-3 text-sm font-medium text-gray-400 hover:text-ink-600 dark:hover:text-gray-300 border-b-2 border-transparent transition-all";
            formPass.classList.remove('hidden');
            formCode.classList.add('hidden');
        } else {
            btnPass.className = "pb-3 text-sm font-medium text-gray-400 hover:text-ink-600 dark:hover:text-gray-300 border-b-2 border-transparent transition-all";
            btnCode.className = "pb-3 text-sm font-bold text-ink-900 dark:text-white border-b-2 border-ink-900 dark:border-white transition-all";
            formPass.classList.add('hidden');
            formCode.classList.remove('hidden');
        }
    }

    let qrPollTimer = null;
    let currentSid = null;

    function toggleLoginMode() {
        const loginContainer = document.getElementById('login-container');
        const qrContainer = document.getElementById('qr-container');
        const modeIcon = document.getElementById('mode-icon');

        if (qrContainer.classList.contains('hidden')) {
            // Switch to QR
            loginContainer.classList.add('hidden');
            qrContainer.classList.remove('hidden');
            qrContainer.classList.add('flex');
            modeIcon.classList.remove('fa-qrcode');
            modeIcon.classList.add('fa-desktop'); 
            
            refreshQRCode(); // Fetch and render real QR
        } else {
             // Switch to Form
            loginContainer.classList.remove('hidden');
            qrContainer.classList.add('hidden');
            qrContainer.classList.remove('flex');
            modeIcon.classList.remove('fa-desktop');
            modeIcon.classList.add('fa-qrcode');
            
            if (qrPollTimer) clearInterval(qrPollTimer);
        }
    }
    
    function renderRoundedQR(text) {
        try {
            var typeNumber = 0;
            var errorCorrectionLevel = 1; 
            var qr = new qrcode(typeNumber, errorCorrectionLevel);
            qr.addData(text);
            qr.make();
            var moduleCount = qr.getModuleCount();
            
            var canvas = document.getElementById('qr-canvas');
            var ctx = canvas.getContext('2d');
            
            var size = 200;
            var scale = window.devicePixelRatio || 1;
            canvas.width = size * scale;
            canvas.height = size * scale;
            ctx.scale(scale, scale);
            
            var cellSize = size / moduleCount;
            var isDark = document.documentElement.classList.contains('dark');
            ctx.fillStyle = isDark ? '#FFFFFF' : '#000000'; 
            
            ctx.clearRect(0,0, size, size);
            
            for (var row = 0; row < moduleCount; row++) {
                for (var col = 0; col < moduleCount; col++) {
                    if (qr.isDark(row, col)) {
                        var x = col * cellSize;
                        var y = row * cellSize;
                        ctx.beginPath();
                        var r = cellSize * 0.4; 
                        ctx.arc(x + cellSize/2, y + cellSize/2, r, 0, Math.PI * 2);
                        ctx.fill();
                    }
                }
            }
        } catch(e) {
            console.error("QR Render Error:", e);
        }
    }

    function refreshQRCode() {
        if (qrPollTimer) clearInterval(qrPollTimer);
        
        fetch('<?= url('/auth/qr/session') ?>')
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                currentSid = res.data.session_id;
                renderRoundedQR(res.data.qr_url);
                // Reset UI state
                document.getElementById('qr-scanned-overlay').classList.add('hidden');
                document.getElementById('qr-status-title').innerText = '手机扫码安全登录';
                document.getElementById('qr-canvas').classList.remove('blur-sm');
                startPolling(currentSid);
            }
        });
    }

    function startPolling(sid) {
        if (qrPollTimer) clearInterval(qrPollTimer);
        qrPollTimer = setInterval(() => {
            fetch('<?= url('/auth/qr/check') ?>?sid=' + sid)
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    if (res.status === 'confirmed' && res.redirect) {
                        console.log("Login confirmed, redirecting...");
                        clearInterval(qrPollTimer);
                        window.location.href = res.redirect;
                    } else if (res.status === 'expired') {
                        clearInterval(qrPollTimer);
                        refreshQRCode();
                    } else if (res.status === 'scanned') {
                        // Show scanned state
                        document.getElementById('qr-scanned-overlay').classList.remove('hidden');
                        document.getElementById('qr-status-title').innerText = '已扫描，待确认';
                        document.getElementById('qr-canvas').classList.add('blur-sm');
                    }
                }
            })
            .catch(err => console.error("Polling error:", err));
        }, 1000);
    }

    // Monitor Theme Changes
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === "class") {
                const qrContainer = document.getElementById('qr-container');
                if (!qrContainer.classList.contains('hidden') && currentSid) {
                    refreshQRCode(); // Better than just re-render to ensure fresh session
                }
            }
        });
    });
    
    observer.observe(document.documentElement, { attributes: true });
</script>

<!-- Captcha Modal -->
<div id="captcha-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 hidden backdrop-blur-sm">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-2xl max-w-sm w-full mx-4">
        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-2">安全验证</h3>
        <p class="text-sm text-gray-500 mb-4">请依次点击：<strong id="captcha-target" class="text-blue-500 text-lg">...</strong></p>
        
        <div class="relative w-[300px] h-[150px] mx-auto bg-gray-100 rounded-lg overflow-hidden cursor-crosshair">
            <img id="captcha-img" src="" class="w-full h-full object-cover">
            <!-- Click Markers -->
            <div id="captcha-markers" class="absolute inset-0 pointer-events-none"></div>
            <!-- Click Layer -->
            <div id="captcha-layer" class="absolute inset-0 z-10" onclick="recordClick(event)"></div>
        </div>

        <div class="flex justify-end gap-3 mt-6">
            <button onclick="closeCaptcha()" type="button" class="px-4 py-2 text-sm text-gray-500 hover:bg-gray-100 rounded-lg">取消</button>
            <button onclick="refreshCaptcha()" type="button" class="px-4 py-2 text-sm text-blue-500 hover:bg-blue-50 rounded-lg">刷新</button>
            <button onclick="submitCaptcha()" id="captcha-confirm" disabled type="button" class="px-4 py-2 text-sm bg-black text-white rounded-lg opacity-50 cursor-not-allowed">确认登录</button>
        </div>
    </div>
</div>

<script>
let clickPoints = [];
const MAX_POINTS = 4;

function openCaptcha(e) {
    if(e) e.preventDefault();
    document.getElementById('captcha-modal').classList.remove('hidden');
    refreshCaptcha();
}

function closeCaptcha() {
    document.getElementById('captcha-modal').classList.add('hidden');
    clickPoints = [];
    renderMarkers();
}

function refreshCaptcha() {
    // 1. Fetch Info to Generate New Data (and get Text)
    // Clear image first to show loading state if desired, or just wait
    document.getElementById('captcha-target').innerText = '...';
    
    fetch('<?= url('/captcha/info') ?>')
    .then(r => r.json())
    .then(d => {
        // 2. Update Text
        document.getElementById('captcha-target').innerText = d.text;
        
        // 3. Load Image (Now that session is populated)
        // Add random timestamp to force refresh
        document.getElementById('captcha-img').src = '<?= url('/captcha/image') ?>?' + Math.random();
    })
    .catch(e => {
        console.error("Captcha Error", e);
        document.getElementById('captcha-target').innerText = 'Error';
    });

    // Reset clicks
    clickPoints = [];
    renderMarkers();
    checkBtn();
}

function recordClick(e) {
    if (clickPoints.length >= MAX_POINTS) return;
    
    const rect = e.target.getBoundingClientRect();
    const x = Math.round(e.clientX - rect.left);
    const y = Math.round(e.clientY - rect.top);
    
    clickPoints.push({x, y});
    renderMarkers();
    checkBtn();
}

function renderMarkers() {
    const container = document.getElementById('captcha-markers');
    container.innerHTML = '';
    clickPoints.forEach((p, index) => {
        const marker = document.createElement('div');
        marker.className = 'absolute w-6 h-6 bg-red-500 text-white flex items-center justify-center rounded-full text-xs font-bold -ml-3 -mt-3 shadow-sm border border-white';
        marker.style.left = p.x + 'px';
        marker.style.top = p.y + 'px';
        marker.innerText = index + 1;
        container.appendChild(marker);
    });
}

function checkBtn() {
    const btn = document.getElementById('captcha-confirm');
    if (clickPoints.length === MAX_POINTS) {
        btn.disabled = false;
        btn.classList.remove('opacity-50', 'cursor-not-allowed');
    } else {
        btn.disabled = true;
        btn.classList.add('opacity-50', 'cursor-not-allowed');
    }
}

function submitCaptcha() {
    const pointsStr = clickPoints.map(p => `${p.x},${p.y}`).join(',');
    
    // Inject into form
    const form = document.getElementById('form-password');
    let input = form.querySelector('input[name="captcha_points"]');
    if (!input) {
        input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'captcha_points';
        form.appendChild(input);
    }
    input.value = pointsStr;
    
    // Close modal
    closeCaptcha();

    // Trigger form submit programmatically
    // This will trigger the event listener below
    form.dispatchEvent(new Event('submit', { cancelable: true }));
}

// Intercept Login Form
document.getElementById('form-password').addEventListener('submit', function(e) {
    e.preventDefault(); 
    
    // 1. Captcha Check
    if (!this.querySelector('input[name="captcha_points"]')) {
        openCaptcha();
        return;
    }

    // 2. Encrypt Password
    const passInput = this.querySelector('input[name="password"]');
    const plainPass = passInput.value;
    
    // Only encrypt if not already encrypted (check length or format)
    if (plainPass && plainPass.length < 100) {
        try {
            const encryptor = new JSEncrypt();
            encryptor.setPublicKey(RSA_PUBLIC_KEY);
            const encrypted = encryptor.encrypt(plainPass);
            
            if (encrypted) {
                passInput.value = encrypted; // Replace with encrypted text
                
                // Submit via AJAX for 2FA handling
                const formData = new FormData(this);
                // We don't need to append ajax=1 manually if we use X-Requested-With header, but let's keep it safe
                
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
                    } else if (res.success) {
                         window.location.reload();
                    } else {
                        // Error case
                        alert(res.message || res.error || '登录失败');
                        // Reset captcha so user can try again
                        this.querySelector('input[name="captcha_points"]').remove();
                        passInput.value = plainPass; // Restore plain password for retry
                    }
                })
                .catch(err => {
                    console.error("Login Error:", err);
                    alert('网络充值或服务器错误');
                    passInput.value = plainPass;
                });

            } else {
                alert('安全加密环境初始化失败，请刷新页面');
            }
        } catch(err) {
            console.error("Encryption Error", err);
            alert('加密模块加载失败，请检查网络');
        }
    } else {
        // Already encrypted or empty? Should not happen in normal flow.
        this.submit();
    }
});
</script>

<?php $this->render('footer'); ?>

<!-- 2FA Modal -->
<div id="twofa-modal" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 hidden backdrop-blur-md animate-in fade-in duration-300">
    <div class="bg-white dark:bg-[#1a1a1a] p-8 rounded-3xl shadow-2xl max-w-sm w-full mx-4 transform transition-all scale-95 border border-gray-100 dark:border-white/5">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">双重验证</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">请输入您的身份验证工具生成的 6 位验证码</p>
        </div>
        
        <div class="space-y-4">
            <input type="text" id="twofa-code" maxlength="6" class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-white/5 border border-gray-100 dark:border-white/10 text-center text-3xl font-black tracking-[0.5em] focus:bg-white dark:focus:bg-black focus:border-blue-500 outline-none transition-all dark:text-white placeholder-gray-200" placeholder="000000">
            <p id="twofa-error" class="text-xs text-red-500 text-center hidden"></p>
            <button onclick="submitTwoFA()" class="w-full bg-ink-900 dark:bg-white text-white dark:text-black font-bold py-4 rounded-2xl hover:opacity-90 transition-all active:scale-95 shadow-xl shadow-blue-500/10">验证并登录</button>
            <button onclick="closeTwoFAModal()" class="w-full py-2 text-xs font-bold text-gray-400 uppercase tracking-widest hover:text-gray-600 dark:hover:text-gray-200 transition-colors">取消</button>
        </div>
    </div>
</div>

<script>
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
        })
        .catch(err => {
            console.error("2FA Ajax Error:", err);
            errorEl.innerText = '连接服务器失败';
            errorEl.classList.remove('hidden');
        });
    }
</script>
