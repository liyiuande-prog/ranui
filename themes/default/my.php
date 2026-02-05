<?php 
$this->render('header'); 
$h = date('H');
$greet = '早安';
if($h >= 11 && $h < 13) $greet = '午安';
elseif($h >= 13 && $h < 18) $greet = '下午好';
elseif($h >= 18) $greet = '晚上好';
else $greet = '早安';

$db = \Core\Database::getInstance(config('db'));
$currency = '积分';
try {
    $r = $db->query("SELECT option_value FROM options WHERE option_name = 'integral_currency_name'")->fetch();
    if($r) $currency = $r['option_value'];
} catch(\Exception $e){}

$oauthBindings = [];
if(function_exists('is_plugin_active') && is_plugin_active('Ran_OAuth')) {
    try {
        $oauthBindings = $db->query("SELECT provider FROM oauth_bindings WHERE user_id = ?", [$user['id']])->fetchAll(PDO::FETCH_COLUMN);
    } catch(\Exception $e){}
}
?>

<main class="max-w-7xl mx-auto px-6 pt-24 md:pt-32 pb-20 min-h-screen">

    <div class="flex flex-col lg:flex-row gap-8">
        
        <!-- Left: Navigation Sidebar -->
        <div class="lg:w-64 flex-shrink-0 animate-fade-in">
            <div class="bg-white dark:bg-[#151515] rounded-2xl p-6 border border-gray-100 dark:border-white/5 sticky top-32">
                
                <!-- User Info -->
                <div class="mb-8 pb-6 border-b border-gray-50 dark:border-white/5 text-center">
                    <div class="relative inline-block mb-3">
                        <img id="nav-avatar" src="<?= e($user['avatar'] ?? '/assets/default-avatar.png') ?>" class="w-20 h-20 rounded-full object-cover border-4 border-gray-50 dark:border-white/5">
                        
                        <!-- Auth Icon (Moved to Avatar Bottom-Right) -->
                         <?php if(is_plugin_active('Ran_Name')): ?>
                             <div class="absolute bottom-0 right-0 bg-white dark:bg-[#151515] rounded-full p-1 shadow-sm z-10 flex items-center justify-center">
                                <?= \Plugins\Ran_Name\Plugin::renderVerifyIcon($user['id'], 'w-5 h-5', 'position: relative; bottom: auto; right: auto;') ?>
                             </div>
                         <?php endif; ?>
                    </div>
                    
                    <div class="<?= (function_exists('is_plugin_active') && is_plugin_active('Ran_Vip')) ? \Plugins\Ran_Vip\Plugin::getVipColor($user['id'], 'font-bold text-ink-900 dark:text-white text-lg') : 'font-bold text-ink-900 dark:text-white text-lg' ?> flex items-center justify-center gap-2">
                        <?= e($user['username'] ?? '') ?>
                    </div>
                    <div class="text-xs text-gray-400 mt-1">UID: <?= e($user['uid'] ?? $user['id']) ?></div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-2 px-2 whitespace-normal break-words leading-relaxed">
                        <?= e($user['bio'] ?? '') ?: '这就去写个签名...' ?>
                    </div>

                    <!-- Follow Stats (Plugin Hook) -->
                    <?php if(is_plugin_active('Ran_Follow')): ?>
                        <?php \Core\Hook::listen('theme_my_follow_stats', $user['id']); ?>
                    <?php endif; ?>

                    <!-- Extra Status Icons (Auth & CoCreation) -->
                    <div class="flex items-center justify-center gap-3 mt-4">
                        <!-- Auth Status -->
                        <?php if(is_plugin_active('Ran_Name')): ?>
                        <?php 
                            $auth = \Plugins\Ran_Name\Plugin::getAuth($user['id']);
                            $authStatus = $auth ? $auth['status'] : -1;
                            $authClass = 'text-gray-300 hover:text-gray-500';
                            $authTitle = '未认证';
                            if($authStatus == 1) { $authClass = 'text-blue-500 hover:text-blue-600'; $authTitle = '已认证'; }
                            elseif($authStatus == 0) { $authClass = 'text-yellow-500 hover:text-yellow-600'; $authTitle = '审核中'; }
                        ?>
                        <a href="<?= url('/my/auth') ?>" class="<?= $authClass ?> transition-colors" title="实名认证: <?= $authTitle ?>">
                            <i class="fas fa-id-card text-lg"></i>
                        </a>
                        <?php endif; ?>

                        <!-- Co-Creation -->
                        <?php if(is_plugin_active('Ran_CoCreation')): ?>
                        <?php
                             $ccPerm = \Core\Database::getInstance(config('db'))->query("SELECT status FROM ran_cocreation_perms WHERE user_id=?", [$user['id']])->fetch();
                             $ccClass = ($ccPerm && $ccPerm['status']=='approved') ? 'text-purple-500 hover:text-purple-600' : 'text-gray-300 hover:text-gray-500';
                        ?>
                        <a href="<?= url('/cocreation/apply') ?>" class="<?= $ccClass ?> transition-colors" title="共创权限">
                            <i class="fas fa-pen-nib text-lg"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <nav class="space-y-1">
                    <button onclick="switchPanel('dashboard')" id="nav-dashboard" class="nav-item w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-white/5 transition-all">
                        <i class="fas fa-th-large w-5 text-center"></i> 账户概览
                    </button>

                    <!-- Title Center Link -->
                     <?php if(is_plugin_active('Ran_Title')): ?>
                    <button onclick="switchPanel('titles')" id="nav-titles" class="nav-item w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-white/5 transition-all">
                        <i class="fas fa-crown w-5 text-center"></i> 头衔中心
                    </button>
                    <?php endif; ?>
                    
                     <!-- Task Center Link -->
                     <?php if(is_plugin_active('Ran_Task') || is_plugin_active('Ran_Grade')): ?>
                    <a href="<?= url('/tasks') ?>" class="nav-item w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-white/5 transition-all">
                        <i class="fas fa-check-circle w-5 text-center"></i> 任务中心
                    </a>
                    <?php endif; ?>

                    <!-- Creation Center -->
                    <button onclick="switchPanel('creative')" id="nav-creative" class="nav-item w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-white/5 transition-all">
                        <i class="fas fa-lightbulb w-5 text-center"></i> 创作中心
                    </button>
                    <button onclick="switchPanel('profile')" id="nav-profile" class="nav-item w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-white/5 transition-all">
                        <i class="fas fa-user-edit w-5 text-center"></i> 修改资料
                    </button>
                    <button onclick="switchPanel('security')" id="nav-security" class="nav-item w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-white/5 transition-all">
                        <i class="fas fa-shield-alt w-5 text-center"></i> 安全中心
                    </button>
                </nav>
                
                <div class="mt-8 pt-6 border-t border-gray-50 dark:border-white/5">
                    <a href="<?= url('/logout') ?>" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold text-red-500 hover:bg-red-50 dark:hover:bg-red-900/10 transition-all">
                        <i class="fas fa-sign-out-alt w-5 text-center"></i> 退出登录
                    </a>
                </div>
            </div>
        </div>

        <!-- Right: Content Area -->
        <div class="flex-1 min-w-0">
            
            <!-- Panel: Dashboard -->
            <div id="panel-dashboard" class="content-panel space-y-8 animate-fade-in">
                <!-- Welcome -->
                <div class="bg-gradient-to-br from-ink-900 to-gray-800 dark:from-white/10 dark:to-black rounded-3xl p-8 text-white relative overflow-hidden">
                    <div class="relative z-10">
                        <h1 class="text-3xl font-extrabold mb-2"><?= $greet ?>, <?= e($user['username']) ?>!</h1>
                        <p class="opacity-80">今天想分享点什么吗？</p>
                    </div>
                    <i class="fas fa-quote-right absolute bottom-0 right-4 text-8xl opacity-5"></i>
                </div>

                <!-- Stats Grid (Balance & Integral Only) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php ?>
                    
                    <!-- Balance -->
                    <?php if(is_plugin_active('Ran_Wallet')): ?>
                    <div class="bg-white dark:bg-[#151515] p-6 rounded-2xl border border-gray-50 dark:border-white/5 hover:shadow-lg transition-shadow">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center">
                                <i class="fas fa-wallet"></i>
                            </div>
                            <div>
                                <h3 class="text-gray-500 text-xs font-bold uppercase">余额</h3>
                                <p class="text-xl font-black text-ink-900 dark:text-white">¥<?= number_format($user['balance'] ?? 0, 2) ?></p>
                            </div>
                        </div>
                         <?php if (is_plugin_active('Ran_Wallet')): ?>
                        <div class="flex gap-2">
                            <button onclick="document.getElementById('wallet_recharge_modal').classList.remove('hidden')" class="flex-1 py-1.5 bg-ink-900 text-white dark:bg-white dark:text-black rounded-lg text-xs font-bold">充值</button>
                            <button onclick="document.getElementById('wallet_withdraw_modal').classList.remove('hidden')" class="flex-1 py-1.5 bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-white rounded-lg text-xs font-bold">提现</button>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Integral -->
                    <?php if(is_plugin_active('Ran_Integral')): ?>
                    <div class="bg-white dark:bg-[#151515] p-6 rounded-2xl border border-gray-50 dark:border-white/5 hover:shadow-lg transition-shadow flex flex-col justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full bg-purple-50 text-purple-500 flex items-center justify-center">
                                <i class="fas fa-coins"></i>
                            </div>
                            <div>
                                <h3 class="text-gray-500 text-xs font-bold uppercase"><?= e($currency) ?></h3>
                                <p class="text-xl font-black text-ink-900 dark:text-white"><?= number_format($user['points'] ?? 0) ?></p>
                            </div>
                        </div>
                        <a href="<?= url('/integral') ?>" class="mt-4 text-xs font-bold text-center text-gray-400 hover:text-purple-500">前往兑换 ></a>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Logs (AJAX) -->
                <?php if(is_plugin_active('Ran_Wallet') || is_plugin_active('Ran_Integral')): ?>
                <div class="bg-white dark:bg-[#151515] rounded-3xl p-8 border border-gray-50 dark:border-white/5">
                    <h3 class="text-lg font-bold mb-6">最近动态</h3>
                     <div id="data-logs-list" class="space-y-4 min-h-[100px] relative">
                         <!-- AJAX Content -->
                     </div>
                     <div id="data-logs-pager" class="flex justify-center mt-6 gap-2"></div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Panel: Title Center -->
             <?php if(is_plugin_active('Ran_Title')): ?>
            <div id="panel-titles" class="content-panel hidden animate-fade-in">
                <div class="bg-white dark:bg-[#151515] rounded-3xl p-8 border border-gray-50 dark:border-white/5 min-h-[500px]">
                    <h2 class="text-2xl font-black text-ink-900 dark:text-white mb-6">头衔中心</h2>
                    
                    <!-- Tabs -->
                    <div class="flex gap-4 border-b border-gray-100 dark:border-white/5 mb-8 overflow-x-auto no-scrollbar">
                        <button onclick="switchTitleTab('owned')" id="title-tab-owned" class="title-tab-btn pb-3 px-1 text-sm font-bold border-b-2 transition-all whitespace-nowrap">我的头衔</button>
                        <button onclick="switchTitleTab('all')" id="title-tab-all" class="title-tab-btn pb-3 px-1 text-sm font-bold border-b-2 transition-all whitespace-nowrap">头衔列表</button>
                    </div>

                    <!-- Tab: Owned -->
                    <div id="title-content-owned" class="title-content hidden animate-fade-in">
                         <div id="data-titles-owned" class="grid grid-cols-1 gap-4"></div>
                    </div>

                    <!-- Tab: All -->
                    <div id="title-content-all" class="title-content hidden animate-fade-in">
                        <div id="data-titles-all" class="grid grid-cols-1 gap-4"></div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Panel: Creative Center -->
            <div id="panel-creative" class="content-panel hidden animate-fade-in">
                <div class="bg-white dark:bg-[#151515] rounded-3xl p-8 border border-gray-50 dark:border-white/5 min-h-[500px]">
                    <h2 class="text-2xl font-black text-ink-900 dark:text-white mb-6">创作中心</h2>
                    
                    <!-- Tabs -->
                    <div class="flex gap-4 border-b border-gray-100 dark:border-white/5 mb-8 overflow-x-auto no-scrollbar">
                        <button onclick="switchCreativeTab('works')" id="crt-tab-works" class="crt-tab-btn pb-3 px-1 text-sm font-bold border-b-2 transition-all whitespace-nowrap">我的作品</button>
                        <button onclick="switchCreativeTab('comments')" id="crt-tab-comments" class="crt-tab-btn pb-3 px-1 text-sm font-bold border-b-2 transition-all whitespace-nowrap">我的评论</button>
                        <button onclick="switchCreativeTab('likes')" id="crt-tab-likes" class="crt-tab-btn pb-3 px-1 text-sm font-bold border-b-2 transition-all whitespace-nowrap">我赞过的</button>
                    </div>

                    <!-- Tab: Works -->
                    <div id="crt-content-works" class="crt-content hidden animate-fade-in">
                        <div id="data-works-list" class="space-y-4 min-h-[100px] relative"></div>
                        <div id="data-works-pager" class="flex justify-center mt-6 gap-2"></div>
                    </div>

                    <!-- Tab: Comments -->
                    <div id="crt-content-comments" class="crt-content hidden animate-fade-in">
                        <div id="data-comments-list" class="space-y-5 min-h-[100px] relative"></div>
                        <div id="data-comments-pager" class="flex justify-center mt-6 gap-2"></div>
                    </div>

                    <!-- Tab: Likes -->
                    <div id="crt-content-likes" class="crt-content hidden animate-fade-in">
                        <div id="data-likes-list" class="space-y-4 min-h-[100px] relative"></div>
                        <div id="data-likes-pager" class="flex justify-center mt-6 gap-2"></div>
                    </div>

                </div>
            </div>

            <!-- Panel: Profile -->
            <div id="panel-profile" class="content-panel hidden animate-fade-in">
                <div class="bg-white dark:bg-[#151515] rounded-3xl p-8 border border-gray-50 dark:border-white/5">
                    <h2 class="text-2xl font-black text-ink-900 dark:text-white mb-8">修改资料</h2>
                    
                    <div class="flex items-center justify-center mb-8">
                         <div class="relative group cursor-pointer" onclick="document.getElementById('avatar-input').click()">
                            <img id="profile-avatar-preview" src="<?= e($user['avatar'] ?? '/assets/default-avatar.png') ?>" class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-xl group-hover:opacity-50 transition-opacity">
                            <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <i class="fas fa-camera text-2xl text-white"></i>
                            </div>
                            <input type="file" id="avatar-input" accept="image/*" class="hidden" onchange="uploadAvatar(this)">
                        </div>
                    </div>

                    <form onsubmit="updateProfile(event)" class="max-w-lg mx-auto space-y-6">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        
                        <div>
                            <label class="block text-sm font-bold text-gray-500 mb-2">昵称</label>
                            <input type="text" name="username" value="<?= e($user['username']) ?>" class="w-full px-4 py-3 rounded-xl bg-gray-50 dark:bg-white/5 border-transparent focus:bg-white focus:border-ink-900 outline-none transition-all font-bold text-ink-900 dark:text-white">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-bold text-gray-500 mb-2">个人简介</label>
                            <textarea name="bio" rows="3" class="w-full px-4 py-3 rounded-xl bg-gray-50 dark:bg-white/5 border-transparent focus:bg-white focus:border-ink-900 outline-none transition-all font-medium text-ink-900 dark:text-white resize-none" placeholder="介绍一下你自己..."><?= e($user['bio'] ?? '') ?></textarea>
                            <p class="text-right text-xs text-gray-400 mt-1">最多 200 字</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-bold text-gray-500 mb-2">UID</label>
                            <input type="text" value="<?= e($user['uid'] ?? $user['id']) ?>" disabled class="w-full px-4 py-3 rounded-xl bg-gray-100 dark:bg-white/5 text-gray-400 cursor-not-allowed border-transparent">
                            <p class="text-xs text-gray-400 mt-1">UID 是您的唯一身份标识，不可修改。</p>
                        </div>
                        
                        <button type="submit" class="w-full py-4 bg-ink-900 dark:bg-white text-white dark:text-black rounded-xl font-bold hover:shadow-lg hover:-translate-y-0.5 transition-all">
                            保存修改
                        </button>
                    </form>
                </div>
            </div>

            <!-- Panel: Security (Tabs) -->
            <div id="panel-security" class="content-panel hidden animate-fade-in">
                <div class="bg-white dark:bg-[#151515] rounded-3xl p-8 border border-gray-50 dark:border-white/5 min-h-[500px]">
                    <h2 class="text-2xl font-black text-ink-900 dark:text-white mb-6">安全中心</h2>
                    
                    <!-- Internal Tabs -->
                    <div class="flex gap-4 border-b border-gray-100 dark:border-white/5 mb-8 overflow-x-auto no-scrollbar">
                        <button onclick="switchSecTab('password')" id="sec-tab-password" class="sec-tab-btn pb-3 px-1 text-sm font-bold border-b-2 transition-all whitespace-nowrap">修改密码</button>
                        <button onclick="switchSecTab('email')" id="sec-tab-email" class="sec-tab-btn pb-3 px-1 text-sm font-bold border-b-2 transition-all whitespace-nowrap">绑定邮箱</button>
                        <?php if(is_plugin_active('Ran_OAuth')): ?>
                        <button onclick="switchSecTab('oauth')" id="sec-tab-oauth" class="sec-tab-btn pb-3 px-1 text-sm font-bold border-b-2 transition-all whitespace-nowrap">第三方绑定</button>
                        <?php endif; ?>
                        <button onclick="switchSecTab('cancel')" id="sec-tab-cancel" class="sec-tab-btn pb-3 px-1 text-sm font-bold border-b-2 transition-all whitespace-nowrap text-red-500/70 hover:text-red-500">注销账号</button>
                    </div>

                    <!-- Tab: Password -->
                    <div id="sec-content-password" class="sec-content hidden animate-fade-in">
                         <?php $passSet = isset($user['password_set']) ? (int)$user['password_set'] : 1; ?>
                         <form onsubmit="updatePassword(event)" class="max-w-lg mx-auto space-y-4">
                             <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                             <?php if ($passSet): ?>
                             <div>
                                 <label class="block text-sm font-bold text-gray-500 mb-2">当前密码</label>
                                 <input type="password" name="old_password" class="w-full px-4 py-3 rounded-xl bg-gray-50 dark:bg-white/5 border-transparent focus:bg-white focus:border-ink-900 outline-none transition-all">
                             </div>
                             <?php else: ?>
                             <div class="p-3 bg-blue-50 text-blue-600 rounded-xl text-sm mb-4">
                                 您尚未设置密码，请立即设置。
                             </div>
                             <?php endif; ?>
                             <div>
                                 <label class="block text-sm font-bold text-gray-500 mb-2">新密码</label>
                                 <input type="password" name="new_password" class="w-full px-4 py-3 rounded-xl bg-gray-50 dark:bg-white/5 border-transparent focus:bg-white focus:border-ink-900 outline-none transition-all">
                             </div>
                             <div>
                                 <label class="block text-sm font-bold text-gray-500 mb-2">确认新密码</label>
                                 <input type="password" name="confirm_password" class="w-full px-4 py-3 rounded-xl bg-gray-50 dark:bg-white/5 border-transparent focus:bg-white focus:border-ink-900 outline-none transition-all">
                             </div>
                             <button type="submit" class="w-full py-4 bg-ink-900 dark:bg-white text-white dark:text-black rounded-xl font-bold">提交修改</button>
                         </form>
                    </div>

                    <!-- Tab: Email -->
                    <div id="sec-content-email" class="sec-content hidden animate-fade-in">
                        <p class="text-gray-500 text-sm mb-6 text-center">绑定邮箱可用于登录、找回密码及接收重要通知。</p>
                        <div class="max-w-lg mx-auto">
                            <form onsubmit="updateEmail(event)" class="space-y-6">
                                <?php $hasEmail = !empty($user['email']); ?>
                                <div>
                                    <label class="block text-sm font-bold text-gray-500 mb-2">邮箱地址</label>
                                    <div class="flex gap-3">
                                        <div class="relative flex-1">
                                            <i class="fas fa-envelope absolute left-4 top-3.5 text-gray-400"></i>
                                            <input type="email" id="email-input" name="email" value="<?= e($user['email']) ?>" <?= $hasEmail ? 'disabled' : '' ?> 
                                                   class="w-full pl-10 pr-4 py-3 rounded-xl bg-gray-50 dark:bg-white/5 border-2 border-transparent focus:border-ink-900 outline-none transition-all font-bold text-ink-900 dark:text-white disabled:opacity-60 disabled:cursor-not-allowed">
                                        </div>
                                        <?php if($hasEmail): ?>
                                        <button type="button" id="btn-change-email" onclick="enableEmailChange()" class="px-4 py-2 bg-gray-100 dark:bg-white/10 text-ink-900 dark:text-white rounded-xl font-bold text-sm whitespace-nowrap hover:bg-gray-200 dark:hover:bg-white/20">
                                            修改邮箱
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div id="email-verify-area" class="<?= $hasEmail ? 'hidden' : '' ?> space-y-6 animate-fade-in">
                                    <div>
                                        <label class="block text-sm font-bold text-gray-500 mb-2">验证码</label>
                                        <div class="flex gap-3">
                                            <input type="text" name="code" placeholder="输入6位验证码" class="flex-1 w-full px-4 py-3 rounded-xl bg-gray-50 dark:bg-white/5 border-transparent focus:bg-white focus:border-ink-900 outline-none transition-all font-medium text-ink-900 dark:text-white">
                                             <button type="button" onclick="sendEmailCode(this)" class="px-5 py-3 bg-ink-900 dark:bg-white text-white dark:text-black rounded-xl font-bold text-sm whitespace-nowrap hover:opacity-90">
                                                获取验证码
                                            </button>
                                        </div>
                                    </div>
                                    <button type="submit" class="w-full py-4 bg-ink-900 dark:bg-white text-white dark:text-black rounded-xl font-bold hover:shadow-lg hover:-translate-y-0.5 transition-all">
                                        确认绑定
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tab: OAuth -->
                    <?php if(is_plugin_active('Ran_OAuth')): ?>
                    <div id="sec-content-oauth" class="sec-content hidden animate-fade-in">
                        <div class="space-y-4 max-w-lg mx-auto">
                            <!-- QQ -->
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-white/5 rounded-xl border border-gray-100 dark:border-white/5">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/20 text-blue-500 flex items-center justify-center text-xl shrink-0">
                                        <i class="fab fa-qq"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-ink-900 dark:text-white">QQ账号</h4>
                                        <p class="text-xs text-gray-400 mt-1">绑定后可使用QQ一键快捷登录</p>
                                    </div>
                                </div>
                                <?php if(in_array('qq', $oauthBindings)): ?>
                                    <span class="text-xs font-bold text-gray-400 bg-gray-100 dark:bg-white/10 px-3 py-1.5 rounded-lg">
                                        <i class="fas fa-check-circle mr-1"></i> 已绑定
                                    </span>
                                <?php else: ?>
                                    <a href="<?= url('/login/oauth/qq') ?>" class="px-4 py-2 bg-black dark:bg-white text-white dark:text-black rounded-lg text-xs font-bold hover:opacity-80 transition-opacity">
                                        立即绑定
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Tab: Cancel -->
                    <div id="sec-content-cancel" class="sec-content hidden animate-fade-in">
                         <div class="bg-red-50 dark:bg-red-900/10 rounded-2xl p-6 border border-red-100 dark:border-red-900/20 max-w-lg mx-auto">
                             <div class="text-center mb-6">
                                 <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                                     <i class="fas fa-exclamation-triangle"></i>
                                 </div>
                                 <h3 class="text-lg font-bold text-red-600 dark:text-red-400 mb-2">风险提示</h3>
                                 <p class="text-sm text-red-500/80">
                                     注销后，您的账号将在 7 天冷静期后被永久删除。期间您可以随时撤销注销申请。
                                 </p>
                             </div>
                             <form action="<?= url('/user/cancel/request') ?>" method="POST" onsubmit="return confirm('确定要申请注销账号吗？此操作不可逆！');">
                                 <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                 <button type="submit" class="w-full py-3 bg-red-600 text-white rounded-xl font-bold hover:bg-red-700 transition-colors shadow-lg shadow-red-500/20">
                                     确认申请注销
                                 </button>
                             </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Follow Panel (Plugin Hook) -->
            <?php if(is_plugin_active('Ran_Follow')): ?>
                <?php \Core\Hook::listen('theme_my_follow_panel'); ?>
            <?php endif; ?>

        </div>
    </div>

</main>

<!-- Apply Title Modal -->
<div id="apply-title-modal" class="fixed inset-0 z-50 hidden bg-black/50 flex items-center justify-center backdrop-blur-sm transition-all">
    <div class="bg-white dark:bg-[#1f1f1f] w-full max-w-md rounded-2xl shadow-2xl p-6 transform scale-100 transition-all">
        <h3 class="text-xl font-bold mb-2 text-ink-900 dark:text-white">申请头衔</h3>
        <p class="text-sm text-gray-500 mb-4">您正在申请头衔：<span id="apply-title-name" class="font-bold text-ink-900 dark:text-gray-300"></span></p>
        
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">申请理由 <span class="text-red-500">*</span></label>
                <textarea id="apply-reason" rows="3" class="w-full px-4 py-3 rounded-xl bg-gray-50 dark:bg-white/5 border-transparent focus:bg-white focus:border-ink-900 outline-none transition-all dark:text-white" placeholder="请填写您申请该头衔的理由..."></textarea>
            </div>
        </div>
        <div class="mt-6 flex justify-end gap-3">
            <button type="button" onclick="document.getElementById('apply-title-modal').classList.add('hidden')" class="px-4 py-2 text-gray-500 hover:bg-gray-50 dark:hover:bg-white/5 rounded-lg">取消</button>
            <button type="button" id="btn-submit-apply" onclick="submitApply()" class="px-4 py-2 bg-black text-white dark:bg-white dark:text-black rounded-lg hover:opacity-90">提交申请</button>
        </div>
    </div>
</div>

<?php if (is_plugin_active('Ran_Wallet')) include dirname(dirname(__DIR__)) . '/plugins/Ran_Wallet/views/home/modals.php'; ?>
<?php if (is_plugin_active('Ran_Vip')) include dirname(dirname(__DIR__)) . '/plugins/Ran_Vip/views/home/modals.php'; ?>

<script>
    const CSRF_TOKEN = "<?= $_SESSION['csrf_token'] ?? '' ?>";
    const CURRENCY_NAME = "<?= $currency ?>";
    
    // -- Data Loading Logic --
    const loadedTabs = {};
    
    function loadData(type, page = 1) {
        const listEl = document.getElementById(`data-${type}-list`);
        const pagerEl = document.getElementById(`data-${type}-pager`);
        if(!listEl) return;
        
        // Loading State
        listEl.innerHTML = '<div class="absolute inset-0 flex items-center justify-center bg-white/50 dark:bg-black/50 z-10"><i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i></div>';
        if(listEl.children.length === 0 || listEl.innerHTML.includes('inset-0')) {
             listEl.innerHTML = '<div class="text-center py-10"><i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i></div>';
        }

        fetch(`/my/data?type=${type}&page=${page}`)
        .then(r => r.json())
        .then(res => {
            if(!res.success) {
                listEl.innerHTML = '<p class="text-center text-red-500">加载失败</p>';
                return;
            }
            
            // Render Items
            if(res.data.length === 0) {
                listEl.innerHTML = renderEmpty(type);
                pagerEl.innerHTML = '';
            } else {
                listEl.innerHTML = renderItems(type, res.data);
                renderPagination(type, res.pagination, pagerEl);
            }
        })
        .catch(e => {
            listEl.innerHTML = '<p class="text-center text-red-500">网络错误</p>';
        });
    }
    
    function renderItems(type, items) {
        if(type === 'works') {
            return items.map(w => `
                <div class="flex items-center justify-between group">
                    <div class="flex-1 min-w-0 pr-4">
                        <h4 class="font-bold text-ink-900 dark:text-white truncate group-hover:text-blue-500 transition-colors">
                            <a href="/post/${w.id}" target="_blank">${escapeHtml(w.title)}</a>
                        </h4>
                        <div class="flex items-center gap-3 text-xs text-gray-400 mt-1">
                            <span>${w.created_at}</span>
                            <span><i class="fas fa-eye ml-1 mr-0.5"></i>${w.view_count}</span>
                            <span class="${w.status==='published'?'text-green-500 bg-green-50 dark:bg-green-900/20':'text-yellow-500 bg-yellow-50 dark:bg-yellow-900/20'} px-1.5 rounded">${w.status==='published'?'已发布':w.status}</span>
                        </div>
                    </div>
                </div>`).join('');
        }
        if(type === 'comments') {
            return items.map(c => `
                <div class="border-b border-gray-50 dark:border-white/5 last:border-0 pb-4 last:pb-0">
                    <div class="text-sm text-gray-600 dark:text-gray-300 mb-2">${escapeHtml(c.content)}</div>
                    <div class="text-xs text-gray-400 flex items-center gap-2">
                        <span>${c.created_at}</span>
                        <span>评论于</span>
                        <a href="/post/${c.post_id}" target="_blank" class="text-blue-500 hover:underline max-w-[200px] truncate">${escapeHtml(c.post_title||'未知')}</a>
                    </div>
                </div>`).join('');
        }
        if(type === 'likes') {
            return items.map(Like => `
                <div class="flex items-center justify-between group">
                    <div class="flex-1 min-w-0 pr-4">
                         <h4 class="font-bold text-ink-900 dark:text-white truncate group-hover:text-red-500 transition-colors">
                            <a href="/post/${Like.id}" target="_blank">${escapeHtml(Like.title)}</a>
                        </h4>
                        <div class="text-xs text-gray-400 mt-1">文章发布于：${Like.created_at}</div>
                    </div>
                    <div class="text-red-500"><i class="fas fa-heart"></i></div>
                </div>`).join('');
        }
        if(type === 'logs') {
            return items.map(log => {
                const isInc = (log.type == 'recharge' || log.type == 'income');
                const color = isInc ? 'text-green-500' : 'text-red-500';
                const sign = isInc ? '+' : '-';
                const unit = log.asset_type === 'balance' ? '元' : CURRENCY_NAME;
                return `
                 <div class="flex justify-between items-center text-sm py-2 border-b border-gray-50 dark:border-white/5 last:border-0">
                     <div>
                         <p class="font-bold text-gray-700 dark:text-gray-300">${escapeHtml(log.description)}</p>
                         <p class="text-xs text-gray-400">${log.created_at}</p>
                     </div>
                     <span class="font-mono font-bold ${color}">${sign}${log.amount} ${unit}</span>
                 </div>`;
            }).join('');
        }
        return '';
    }
    
    function renderEmpty(type) {
        if(type === 'works') return '<div class="text-center py-10 text-gray-400"><i class="fas fa-pencil-alt text-4xl mb-3 opacity-20"></i><p>暂无作品</p></div>';
        if(type === 'comments') return '<div class="text-center py-10 text-gray-400"><i class="fas fa-comment-slash text-4xl mb-3 opacity-20"></i><p>暂无评论</p></div>';
        if(type === 'likes') return '<div class="text-center py-10 text-gray-400"><i class="fas fa-heart-broken text-4xl mb-3 opacity-20"></i><p>暂无点赞</p></div>';
        return '<p class="text-center text-gray-400 text-sm py-4">暂无记录</p>';
    }
    
    function renderPagination(type, pg, container) {
        if(pg.total_pages <= 1) { container.innerHTML = ''; return; }
        
        let html = '';
        if(pg.current_page > 1) {
            html += `<button onclick="loadData('${type}', ${pg.current_page - 1})" class="px-3 py-1 bg-gray-100 dark:bg-white/10 rounded text-sm hover:bg-gray-200"><</button>`;
        }
        html += `<span class="px-2 py-1 text-sm text-gray-500">${pg.current_page} / ${pg.total_pages}</span>`;
        if(pg.current_page < pg.total_pages) {
            html += `<button onclick="loadData('${type}', ${pg.current_page + 1})" class="px-3 py-1 bg-gray-100 dark:bg-white/10 rounded text-sm hover:bg-gray-200">></button>`;
        }
        container.innerHTML = html;
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }


    
    // -- Navigation Logic --
    function switchPanel(panelName) {
        // Update Nav Styles
        document.querySelectorAll('.nav-item').forEach(el => {
            el.classList.remove('bg-ink-900', 'text-white', 'shadow-lg');
            el.classList.add('text-gray-500', 'hover:bg-gray-50');
            if(document.documentElement.classList.contains('dark')) {
                 el.classList.remove('bg-white', 'text-black');
            }
        });
        
        const activeNav = document.getElementById('nav-' + panelName);
        if(activeNav) {
            activeNav.classList.remove('text-gray-500', 'hover:bg-gray-50');
            activeNav.classList.add('bg-ink-900', 'text-white', 'shadow-lg'); 
            if(document.documentElement.classList.contains('dark')) {
                activeNav.classList.remove('bg-ink-900');
                activeNav.classList.add('bg-white', 'text-black');
            }
        }

        // Show Panel
        document.querySelectorAll('.content-panel').forEach(el => el.classList.add('hidden'));
        document.getElementById('panel-' + panelName).classList.remove('hidden');
        
        // Tab defaults
        if(panelName === 'security') switchSecTab('password');
        if(panelName === 'creative') switchCreativeTab('works');
        if(panelName === 'titles') switchTitleTab('owned');

        if(panelName === 'dashboard') {
             if(!loadedTabs['logs']) { loadData('logs'); loadedTabs['logs']=true; }
        }
        if(panelName === 'creative') {
             // Load current tab
             const currentTab = document.querySelector('.crt-tab-btn.border-ink-900')?.id?.replace('crt-tab-', '') || 'works';
             if(!loadedTabs[currentTab]) { loadData(currentTab); loadedTabs[currentTab]=true; }
        }

        history.replaceState(null, null, '#' + panelName);
    }

    // -- Creative Tabs Logic --
    function switchCreativeTab(tabName) {
        document.querySelectorAll('.crt-tab-btn').forEach(el => {
            el.classList.remove('border-ink-900', 'text-ink-900', 'dark:border-white', 'dark:text-white');
            el.classList.add('border-transparent', 'text-gray-400');
        });
        const btn = document.getElementById('crt-tab-' + tabName);
        if(btn) {
            btn.classList.remove('border-transparent', 'text-gray-400');
            btn.classList.add('border-ink-900', 'text-ink-900', 'dark:border-white', 'dark:text-white');
        }
        document.querySelectorAll('.crt-content').forEach(el => el.classList.add('hidden'));
        document.getElementById('crt-content-' + tabName).classList.remove('hidden');
        
        if(!loadedTabs[tabName]) { loadData(tabName); loadedTabs[tabName]=true; }
    }

    // -- Security Tabs Logic --
    function switchSecTab(tabName) {
        document.querySelectorAll('.sec-tab-btn').forEach(el => {
            el.classList.remove('border-ink-900', 'text-ink-900', 'dark:border-white', 'dark:text-white');
            el.classList.add('border-transparent', 'text-gray-400');
            if(el.id === 'sec-tab-cancel') el.classList.add('text-red-500/70');
        });
        const btn = document.getElementById('sec-tab-' + tabName);
        if(btn) {
            btn.classList.remove('border-transparent', 'text-gray-400');
            btn.classList.add('border-ink-900', 'text-ink-900', 'dark:border-white', 'dark:text-white');
        }
        document.querySelectorAll('.sec-content').forEach(el => el.classList.add('hidden'));
        document.getElementById('sec-content-' + tabName).classList.remove('hidden');
    }

    // -- Title Tabs Logic --
    function switchTitleTab(tabName) {
        document.querySelectorAll('.title-tab-btn').forEach(el => {
            el.classList.remove('border-ink-900', 'text-ink-900', 'dark:border-white', 'dark:text-white');
            el.classList.add('border-transparent', 'text-gray-400');
        });
        const btn = document.getElementById('title-tab-' + tabName);
        if(btn) {
            btn.classList.remove('border-transparent', 'text-gray-400');
            btn.classList.add('border-ink-900', 'text-ink-900', 'dark:border-white', 'dark:text-white');
        }
        document.querySelectorAll('.title-content').forEach(el => el.classList.add('hidden'));
        document.getElementById('title-content-' + tabName).classList.remove('hidden');
        
        loadTitleData(tabName);
    }
    
    function loadTitleData(type) {
        const container = document.getElementById(`data-titles-${type}`);
        container.innerHTML = '<div class="col-span-3 text-center py-10"><i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i></div>';
        
        fetch(`/user/titles/data?type=${type}`)
        .then(r => r.json())
        .then(res => {
            if(res.status === 'success') {
                if(res.data.length === 0) {
                    container.innerHTML = '<div class="col-span-3 text-center py-10 text-gray-400">暂无数据</div>';
                } else {
                    container.innerHTML = res.data.map(item => renderTitleItem(type, item)).join('');
                }
            } else {
                container.innerHTML = '<div class="col-span-3 text-center text-red-500">加载失败</div>';
            }
        });
    }

    function renderTitleItem(type, item) {
        // Construct badge style
        let style = '';
        if(item.color) style += `color:${item.color};`;
        if(item.background) style += `background:${item.background};`;
        
        // Render badge HTML
        const badge = `<span class="inline-block px-2 py-0.5 rounded text-xs ${item.css_class||''}" style="${style}">${escapeHtml(item.name)}</span>`;
        
        let action = '';
        let infoText = '';

        if(type === 'owned') {
            if(item.is_equipped) {
                action = `<div class="flex items-center gap-2">
                            <span class="text-xs text-green-500 font-bold px-3 py-1 bg-green-50 rounded-lg whitespace-nowrap">已佩戴</span>
                            <button onclick="unequipTitle()" class="text-xs text-red-500 border border-red-500 hover:bg-red-500 hover:text-white transition px-3 py-1 rounded-lg whitespace-nowrap">取消</button>
                          </div>`;
            } else {
                action = `<button onclick="equipTitle(${item.id})" class="text-xs text-white bg-black hover:bg-gray-800 transition px-3 py-1 rounded-lg whitespace-nowrap">佩戴</button>`;
            }
            
            if(item.expires_at) {
                const expDate = new Date(item.expires_at).toLocaleDateString();
                infoText = `有效期至: ${expDate}`;
            } else {
                 infoText = `永久有效`;
            }
        } else {
            // All list
            if(item.has_owned) {
                action = `<span class="text-xs text-gray-400 whitespace-nowrap"><i class="fas fa-check"></i> 已拥有</span>`;
            } else if (item.is_pending) {
                action = `<span class="text-xs text-yellow-500 bg-yellow-50 px-2 py-1 rounded-lg whitespace-nowrap">审核中</span>`;
            } else {
                 if (item.acquisition_method === 'apply') {
                     action = `<button onclick="openApplyModal(${item.id}, '${escapeHtml(item.name)}')" class="text-xs text-ink-900 border border-ink-900 hover:bg-ink-900 hover:text-white dark:text-gray-300 dark:border-gray-600 dark:hover:bg-white/10 transition px-3 py-1 rounded-lg whitespace-nowrap">申请</button>`;
                 } else if (item.acquisition_method === 'auto') {
                     action = `<span class="text-xs text-blue-500 bg-blue-50 px-2 py-1 rounded-lg whitespace-nowrap">自动获取</span>`;
                 } else {
                     action = `<span class="text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded-lg whitespace-nowrap">站长颁发</span>`;
                 }
            }
            // Use description for info text in 'all' list
            infoText = item.description ? escapeHtml(item.description) : '暂无描述';
        }

        return `
        <div class="bg-gray-50 dark:bg-white/5 px-4 py-3 rounded-xl flex items-center gap-4 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors">
            <div class="shrink-0 min-w-[80px] text-center">
                 ${badge}
            </div>
            <div class="flex-1 min-w-0">
                 <p class="text-xs text-gray-400 truncate" title="${infoText}">${infoText}</p>
            </div>
            <div class="shrink-0">
                ${action}
            </div>
        </div>
        `;
    }
    
    function equipTitle(id) {
        const fd = new FormData();
        fd.append('id', id);
        fd.append('csrf_token', CSRF_TOKEN); // Reuse token
        
        fetch('/user/titles/equip', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if(res.status === 'success') {
                alert('佩戴成功');
                loadTitleData('owned'); // Reload owned list
            } else {
                alert(res.message);
            }
        });
    }

    function unequipTitle() {
        if(!confirm('确定要取消佩戴当前头衔吗？')) return;
        
        const fd = new FormData();
        fd.append('csrf_token', CSRF_TOKEN);
        
        fetch('/user/titles/unequip', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if(res.status === 'success') {
                alert('已取消佩戴');
                loadTitleData('owned'); // Reload owned list
            } else {
                alert(res.message);
            }
        });
    }

    // Apply Modal Logic
    let currentApplyTitleId = 0;
    function openApplyModal(id, name) {
        currentApplyTitleId = id;
        document.getElementById('apply-title-name').innerText = name;
        document.getElementById('apply-title-modal').classList.remove('hidden');
    }

    function submitApply() {
        const reason = document.getElementById('apply-reason').value;
        if(!reason.trim()) { alert('请输入申请理由'); return; }

        const fd = new FormData();
        fd.append('title_id', currentApplyTitleId);
        fd.append('reason', reason);
        fd.append('csrf_token', CSRF_TOKEN);

        const btn = document.getElementById('btn-submit-apply');
        const originalText = btn.innerText;
        btn.disabled = true;
        btn.innerText = '提交中...';

        fetch('/user/titles/apply', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if(res.status === 'success') {
                alert(res.message);
                document.getElementById('apply-title-modal').classList.add('hidden');
                document.getElementById('apply-reason').value = ''; // Reset
                loadTitleData('all'); // Reload list to show pending status
            } else {
                alert(res.message);
            }
            btn.disabled = false;
            btn.innerText = originalText;
        });
    }

    // Init Logic
    document.addEventListener('DOMContentLoaded', () => {
        const hash = window.location.hash.replace('#', '') || 'dashboard';
        if(document.getElementById('panel-' + hash)) {
            switchPanel(hash);
        } else {
            switchPanel('dashboard');
        }
    });

    // -- Profile Logic --
    // -- Profile Logic --
    function uploadAvatar(input) {
         if (!input.files || !input.files[0]) return;
         const file = input.files[0];
         
         // VIP Check for GIF
         const isGif = file.type === 'image/gif' || file.name.toLowerCase().endsWith('.gif');
         
         // Inject PHP VIP check
         const isVip = <?= (function_exists('is_plugin_active') && is_plugin_active('Ran_Vip') && \Plugins\Ran_Vip\Plugin::isVip($user['id'])) ? 'true' : 'false' ?>;

         if (isGif && !isVip) {
             alert('GIF 头像仅限 VIP 会员使用');
             input.value = ''; // Clear selection
             return;
         }

         const formData = new FormData();
         formData.append('file', file);
         
         const preview = document.getElementById('profile-avatar-preview');
         const navAvatar = document.getElementById('nav-avatar');
         
         preview.style.opacity = '0.5';
         
         fetch('<?= url('/upload') ?>', { method: 'POST', body: formData })
         .then(r => r.json())
         .then(data => {
             if (data.location) {
                 const upData = new FormData();
                 upData.append('avatar', data.location);
                 upData.append('csrf_token', CSRF_TOKEN);
                 
                 fetch('<?= url('/user/profile/update') ?>', { method: 'POST', body: upData })
                 .then(r => r.json())
                 .then(res => {
                     if(res.success) {
                         preview.src = data.location;
                         navAvatar.src = data.location;
                         alert('头像更新成功');
                     } else {
                         alert(res.message);
                     }
                     preview.style.opacity = '1';
                 });
             } else {
                 alert('上传失败: ' + (data.error || '未知错误'));
                 preview.style.opacity = '1';
             }
         });
    }

    function updateProfile(e) {
        e.preventDefault();
        const fd = new FormData(e.target);
        fd.append('csrf_token', CSRF_TOKEN);
        fetch('<?= url('/user/profile/update') ?>', { method:'POST', body: fd })
        .then(r=>r.json())
        .then(d => {
            alert(d.message);
            if(d.success) location.reload();
        });
    }

    // -- Email Logic --
    function enableEmailChange() {
        const input = document.getElementById('email-input');
        const btn = document.getElementById('btn-change-email');
        const area = document.getElementById('email-verify-area');
        
        input.disabled = false;
        input.focus();
        input.select();
        
        if(btn) btn.style.display = 'none';
        area.classList.remove('hidden');
    }

    function sendEmailCode(btn) {
        const email = document.getElementById('email-input').value;
        if(!email) { alert('请输入邮箱'); return; }
        
        btn.disabled = true;
        const txt = btn.innerText;
        btn.innerText = '发送中...';
        
        fetch('<?= url('/auth/send-code') ?>', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: 'email=' + encodeURIComponent(email)
        }).then(r=>r.json()).then(d => {
            if(d.success) {
                alert('验证码发送成功');
                let s = 60;
                const t = setInterval(()=>{
                    s--;
                    btn.innerText = s + 's';
                    if(s<=0) { clearInterval(t); btn.disabled=false; btn.innerText='获取验证码'; }
                }, 1000);
            } else {
                alert(d.message);
                btn.disabled = false;
                btn.innerText = txt;
            }
        });
    }

    function updateEmail(e) {
        e.preventDefault();
        const fd = new FormData();
        fd.append('email', e.target.querySelector('[name=email]').value);
        fd.append('code', e.target.querySelector('[name=code]').value);
        fd.append('csrf_token', CSRF_TOKEN);
        
        fetch('<?= url('/user/email/update') ?>', { method:'POST', body: fd })
        .then(r=>r.json())
        .then(d => {
            alert(d.message);
            if(d.success) location.reload();
        });
    }

    // -- Password Logic --
    function updatePassword(e) {
        e.preventDefault();
        const fd = new FormData(e.target);
        fetch('<?= url('/user/password/update') ?>', { method:'POST', body: fd })
        .then(r=>r.json())
        .then(d => {
            alert(d.message);
            if(d.success) e.target.reset();
        });
    }

</script>

<?php $this->render('footer'); ?>
