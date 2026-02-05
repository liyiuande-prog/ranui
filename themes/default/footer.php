
    <!-- ================= 页脚区域 ================= -->
    <?php
    $getLink = function($tpl) {
        if(function_exists('is_plugin_active') && is_plugin_active('Ran_Portfolio') && class_exists('\Plugins\Ran_Portfolio\Plugin')) {
            return \Plugins\Ran_Portfolio\Plugin::getPageLink($tpl);
        }
        return null;
    };
    ?>
    <footer class="bg-white dark:bg-black text-ink-900 dark:text-white pt-20 pb-10 border-t border-gray-100 dark:border-white/10">
        <div class="max-w-7xl mx-auto px-6">
            <div class="hidden md:grid grid-cols-1 md:grid-cols-4 gap-12 mb-16">
                
                <!-- 1. 品牌 & 简介 -->
            <div class="space-y-6 ">
                    <a href="<?= url('/') ?>" class="text-2xl font-extrabold tracking-tighter dark:text-white transition-colors">
                        <?php if($logo = get_option('site_logo')): ?>
                        <img src="<?= e($logo) ?>" alt="<?= e(get_option('site_title', 'RanUI')) ?>" class="h-16 w-auto object-contain dark:invert transition-all duration-300">
                        <?php else: ?>
                        <?= e(get_option('site_title', 'RanUI')) ?>
                        <?php endif; ?>
                    </a>    
                    <p class="text-ink-500 dark:text-gray-400 text-sm leading-relaxed max-w-xs">
                    <?= e(get_option('site_description', '一个专注于设计与技术的极简博客框架。')) ?>
                </p>
                <!-- 社交链接 -->
                <div class="flex gap-4">
                    <?php if($qq = get_option('social_qq')): ?>
                    <a href="tencent://message/?uin=<?= e($qq) ?>" class="w-8 h-8 rounded-full bg-gray-100 dark:bg-white/10 flex items-center justify-center hover:bg-black dark:hover:bg-white hover:text-white dark:hover:text-black transition-all text-ink-500 dark:text-gray-400" title="QQ: <?= e($qq) ?>">
                        <i class="fab fa-qq text-xs"></i>
                    </a>
                    <?php endif; ?>
                    
                    <?php if($wechat = get_option('social_wechat')): ?>
                    <a href="<?= e($wechat) ?>" class="w-8 h-8 rounded-full bg-gray-100 dark:bg-white/10 flex items-center justify-center hover:bg-black dark:hover:bg-white hover:text-white dark:hover:text-black transition-all text-ink-500 dark:text-gray-400" title="WeChat: <?= e($wechat) ?>">
                        <i class="fab fa-weixin text-xs"></i>
                    </a>
                    <?php endif; ?>
                    
                    <?php if($github = get_option('social_github')): ?>
                    <a href="<?= e($github) ?>" target="_blank" class="w-8 h-8 rounded-full bg-gray-100 dark:bg-white/10 flex items-center justify-center hover:bg-black dark:hover:bg-white hover:text-white dark:hover:text-black transition-all text-ink-500 dark:text-gray-400">
                        <i class="fab fa-github text-xs"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>

                <!-- 导航列 -->
                <div>
                    <h4 class="text-sm font-bold uppercase tracking-widest text-ink-400 dark:text-gray-500 mb-6">导航</h4>
                    <ul class="flex flex-wrap gap-4 md:flex-col md:gap-y-4 text-sm text-ink-600 dark:text-gray-400">
                        <?php \Core\Hook::listen('theme_header_nav'); ?>
                        <?php if($l = $getLink('about')): ?><li><a href="<?= $l ?>" class="hover:text-ink-900 dark:hover:text-white transition-colors">关于我们</a></li><?php endif; ?>
                        <?php if($l = $getLink('help')): ?><li><a href="<?= $l ?>" class="hover:text-ink-900 dark:hover:text-white transition-colors">帮助中心</a></li><?php endif; ?>
                        <?php if($l = $getLink('join')): ?><li><a href="<?= $l ?>" class="hover:text-ink-900 dark:hover:text-white transition-colors">加入我们</a></li><?php endif; ?>
                        <?php if($l = $getLink('contact')): ?><li><a href="<?= $l ?>" class="hover:text-ink-900 dark:hover:text-white transition-colors">联系方式</a></li><?php endif; ?>
                        <?php \Core\Hook::listen('theme_footer_nav'); ?>
                    </ul>
                </div>

                <!-- 友情链接列 -->
                <div>
                    <h4 class="text-sm font-bold uppercase tracking-widest text-ink-400 dark:text-gray-500 mb-6">友情链接</h4>
                    <ul class="flex flex-wrap gap-4 md:flex-col md:gap-y-4 text-sm text-ink-600 dark:text-gray-400">
                        <li><a href="/" class="hover:text-ink-900 dark:hover:text-white transition-colors">RanUI</a></li>
                    </ul>
                </div>

                <!-- 订阅列 (Mobile Hidden) -->
                <div class="hidden md:block">
                    <h4 class="text-sm font-bold uppercase tracking-widest text-ink-400 dark:text-gray-500 mb-6">订阅周刊</h4>
                    <p class="text-ink-500 dark:text-gray-400 text-sm mb-4">获取最新的精选文章和深度思考。</p>
                    <form class="space-y-3">
                        <input type="email" placeholder="Email Address" class="w-full bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 px-4 py-3 rounded text-sm text-ink-900 dark:text-white focus:outline-none focus:border-black dark:focus:border-white focus:ring-0 transition-all placeholder-gray-400 dark:placeholder-gray-500">
                        <button class="w-full bg-ink-900 dark:bg-white text-white dark:text-black px-4 py-3 rounded text-sm font-medium hover:bg-black dark:hover:bg-gray-200 transition-colors">
                            立即订阅
                        </button>
                    </form>
                </div>
            </div>

            <div class="pt-8 border-t border-gray-100 dark:border-white/10 flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-ink-400 dark:text-gray-500">
                <p>&copy; 2026 Ranui. All rights reserved.
                    <?php if($icp = get_option('site_icp')): ?>
                        <a href="https://beian.miit.gov.cn/" target="_blank" class="hover:text-ink-900 dark:hover:text-white transition-colors ml-2"><?= e($icp) ?></a>
                    <?php endif; ?>
                </p>
                <div class="flex gap-6">
                    <?php if(function_exists('is_plugin_active') && is_plugin_active('Ran_App')): ?>
                    <a href="<?= url('/app/download') ?>" class="hover:text-ink-900 dark:hover:text-white transition-colors">客户端下载</a>
                    <?php endif; ?>
                    <?php if($l = $getLink('privacy')): ?><a href="<?= $l ?>" class="hover:text-ink-900 dark:hover:text-white transition-colors">隐私政策</a><?php endif; ?>
                    <?php if($l = $getLink('terms')): ?><a href="<?= $l ?>" class="hover:text-ink-900 dark:hover:text-white transition-colors">服务条款</a><?php endif; ?>
                    <?php if(function_exists('is_plugin_active') && is_plugin_active('Ran_Ads')): ?>
                    <a href="<?= url('/ads/cooperation') ?>" class="hover:text-ink-900 dark:hover:text-white transition-colors">广告合作</a>
                    <?php endif; ?>
                    <?php \Core\Hook::listen('theme_footer_nav_link'); ?>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
