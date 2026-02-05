<?php

namespace App\Controllers\Admin;

use Core\Controller;

class IndexController extends BaseController
{
    public function index()
    {
        $db = \Core\Database::getInstance(config('db'));
        
        // 基础统计
        $stats = [
            'posts' => $db->query("SELECT COUNT(*) FROM posts WHERE status='published'")->fetchColumn(),
            'comments' => $db->query("SELECT COUNT(*) FROM comments")->fetchColumn(),
            'views' => $db->query("SELECT SUM(view_count) FROM posts")->fetchColumn() ?: 0,
            'users' => $db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
        ];

        // 待处理任务统计 (动态检测插件是否开启且表是否存在)
        $pending = [];
        
        // 1. 提现申请 (Ran_Wallet)
        if (is_plugin_active('Ran_Wallet') && $this->tableExists('withdraw_orders')) {
            $pending['withdraw'] = $db->query("SELECT COUNT(*) FROM withdraw_orders WHERE status = 0")->fetchColumn();
        }
        // 2. 实名认证 (Ran_Name)
        if (is_plugin_active('Ran_Name') && $this->tableExists('user_auths')) {
            $pending['auth'] = $db->query("SELECT COUNT(*) FROM user_auths WHERE status = 0")->fetchColumn();
        }
        // 3. 圈子审核 (Ran_Circle)
        if (is_plugin_active('Ran_Circle') && $this->tableExists('circles')) {
            $pending['circle'] = $db->query("SELECT COUNT(*) FROM circles WHERE status = 0")->fetchColumn();
        }
        // 4. 积分订单 (Ran_Integral)
        if (is_plugin_active('Ran_Integral') && $this->tableExists('integral_orders')) {
            $pending['integral_order'] = $db->query("SELECT COUNT(*) FROM integral_orders WHERE status = 0")->fetchColumn();
        }
        // 5. 商家入驻 (Ran_Merchant)
        if (is_plugin_active('Ran_Merchant') && $this->tableExists('merchants')) {
            $pending['merchant'] = $db->query("SELECT COUNT(*) FROM merchants WHERE status = 'pending'")->fetchColumn();
        }
        // 6. 申诉处理 (Ran_Punish)
        if (is_plugin_active('Ran_Punish') && $this->tableExists('punishment_appeals')) {
            $pending['appeal'] = $db->query("SELECT COUNT(*) FROM punishment_appeals WHERE status = 'pending'")->fetchColumn();
        }
        // 7. 友情链接 (Ran_link)
        if (is_plugin_active('Ran_link') && $this->tableExists('links')) {
            $pending['link'] = $db->query("SELECT COUNT(*) FROM links WHERE status = 0")->fetchColumn();
        }

        // 收入统计 (今日营收 - 依赖 Ran_Pay)
        $revenueToday = 0;
        if (is_plugin_active('Ran_Pay') && $this->tableExists('pay_orders')) {
            $revenueToday = $db->query("SELECT SUM(amount) FROM pay_orders WHERE status = 'paid' AND DATE(created_at) = CURDATE()")->fetchColumn() ?: 0;
        }

        // 最新动态 (前 5 条)
        $recentPosts = $db->query("SELECT id, title, created_at, view_count, status FROM posts ORDER BY created_at DESC LIMIT 5")->fetchAll();

        // 图表数据 (前 7 天)
        $chartData = $this->getChartData();

        $this->view('admin/index', [
            'stats' => $stats,
            'pending' => $pending,
            'revenue_today' => $revenueToday,
            'recent_posts' => $recentPosts,
            'chart_data' => $chartData,
            'user' => $_SESSION['user']
        ]);
    }

    private function getChartData()
    {
        $db = \Core\Database::getInstance(config('db'));
        $days = [];
        $visits = [];
        $revenue = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $days[] = date('m/d', strtotime($date));
            
            // Traffic (Ran_Seo)
            $vCount = 0;
            if (is_plugin_active('Ran_Seo') && $this->tableExists('seo_visits')) {
                $vCount = $db->query("SELECT COUNT(*) FROM seo_visits WHERE DATE(created_at) = ?", [$date])->fetchColumn();
            }
            $visits[] = (int)$vCount;

            // Revenue (Pay_Orders)
            $rAmount = 0;
            if (is_plugin_active('Ran_Pay') && $this->tableExists('pay_orders')) {
                $rAmount = $db->query("SELECT SUM(amount) FROM pay_orders WHERE status = 'paid' AND DATE(created_at) = ?", [$date])->fetchColumn() ?: 0;
            }
            $revenue[] = (float)$rAmount;
        }

        return [
            'labels' => $days,
            'visits' => $visits,
            'revenue' => $revenue
        ];
    }

    public function notifications()
    {
        if (!is_plugin_active('Ran_Notice') || !$this->tableExists('user_notifications')) {
            $this->view('admin/notifications', ['notifications' => []]);
            return;
        }

        $db = \Core\Database::getInstance(config('db'));
        $adminId = $_SESSION['user']['id'];
        
        $notifications = $db->query("SELECT * FROM user_notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50", [$adminId])->fetchAll();
        
        $this->view('admin/notifications', [
            'notifications' => $notifications
        ]);
    }

    public function readNotification($id)
    {
        if (is_plugin_active('Ran_Notice') && $this->tableExists('user_notifications')) {
            $db = \Core\Database::getInstance(config('db'));
            $adminId = $_SESSION['user']['id'];
            
            $notice = $db->query("SELECT * FROM user_notifications WHERE id = ? AND user_id = ?", [$id, $adminId])->fetch();
            if ($notice) {
                $db->query("UPDATE user_notifications SET is_read = 1 WHERE id = ?", [$id]);
                if (!empty($notice['link'])) {
                    redirect($notice['link']);
                }
            }
        }
        
        redirect('/admin/notifications');
    }

    private function tableExists($table)
    {
        $db = \Core\Database::getInstance(config('db'));
        try {
            $db->query("SELECT 1 FROM `$table` LIMIT 1");
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
