<?php

namespace Plugins\Ran_link;

use Core\Hook;
use Core\Database;

/**
 * Plugin Name: 友情链接 (Ran_link)
 * Description: 友情链接管理，支持前台申请、后台审核及首页底部展示。
 * Version: 1.0.0
 * Author: RanUI
 */
class Plugin
{
    public static function init()
    {
        // Hooks
        Hook::add('admin_sidebar_extension', [self::class, 'renderAdminMenu']);
        Hook::add('theme_footer_links', [self::class, 'renderFooterLinks']);
        
        // Register Scheduled Task
        Hook::add('system_schedule_register', [self::class, 'registerSchedule']);

        // Auto Install
        self::checkInstall();
    }

    public static function registerSchedule()
    {
        \Core\Schedule::register('link_health_check', 'weekly', [self::class, 'checkLinks']);
    }

    public static function checkLinks()
    {
        $db = Database::getInstance(config('db'));
        $links = $db->query("SELECT * FROM links WHERE status = 1")->fetchAll();
        
        foreach ($links as $link) {
            $ch = curl_init($link['url']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            $health = ($code >= 200 && $code < 400) ? 1 : 0;
            $db->query("UPDATE links SET is_healthy = ?, last_check = NOW() WHERE id = ?", [$health, $link['id']]);
        }
    }

    public static function renderAdminMenu()
    {
        $url = url('/admin/links');
        echo <<<HTML
        <a href="{$url}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-500 hover:text-white hover:bg-white/5 transition-all">
            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
            <span>友情链接</span>
        </a>
HTML;
    }
    
    public static function renderFooterLinks()
    {
        $db = Database::getInstance(config('db'));
        // Fetch 2 approved links
        $links = $db->query("SELECT * FROM links WHERE status = 1 ORDER BY RAND() LIMIT 2")->fetchAll();
        
        foreach ($links as $link) {
            echo '<li><a href="'. e($link['url']) .'" target="_blank" class="hover:text-ink-900 dark:hover:text-white transition-colors">'. e($link['name']) .'</a></li>';
        }
        
        // More Link
        echo '<li><a href="'. url('/links') .'" class="text-xs text-gray-400 hover:text-ink-900 dark:hover:text-white transition-colors">更多链接 & 申请 &rarr;</a></li>';
    }

    public static function checkInstall()
    {
        $db = Database::getInstance(config('db'));
        
        $sql = <<<SQL
        CREATE TABLE IF NOT EXISTS `links` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `name` varchar(100) NOT NULL,
          `url` varchar(255) NOT NULL,
          `status` tinyint(1) DEFAULT '0' COMMENT '0:Pending, 1:Approved',
          `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL;
        $db->query($sql);
        // Ensure utf8mb4
        $db->query("ALTER TABLE links CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        // Add health columns
        $check = $db->query("SHOW COLUMNS FROM `links` LIKE 'is_healthy'")->fetch();
        if (!$check) {
            $db->query("ALTER TABLE links ADD COLUMN is_healthy TINYINT(1) DEFAULT 1, ADD COLUMN last_check DATETIME DEFAULT NULL");
        }
    }

    public static function uninstall() 
    {
        $db = Database::getInstance(config('db'));
        $db->query("DROP TABLE IF EXISTS links");
    }
}
