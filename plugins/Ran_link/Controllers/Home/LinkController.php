<?php

namespace Plugins\Ran_link\Controllers\Home;

use Core\Controller as BaseController;
use Core\Database;

class LinkController extends \Core\PluginController
{
    protected $pluginRoot = __DIR__ . '/../..';

    public function index()
    {
        $db = Database::getInstance(config('db'));
        $links = $db->query("SELECT * FROM links WHERE status = 1 ORDER BY created_at DESC")->fetchAll();
        
        $page_title = '友情链接';
        $page_keywords = '友情链接, 申请友链, 合作伙伴, RanUI Links';
        $page_description = 'RanUI 友情链接页面，欢迎交换友链，共同发展。';
        
        $this->renderPluginView('home/index', [
            'links' => $links, 
            'page_title' => $page_title,
            'page_keywords' => $page_keywords,
            'page_description' => $page_description
        ]);
    }

    public function apply()
    {
        $name = trim($_POST['name']);
        $url = trim($_POST['url']);
        
        if ($name && $url) {
            $db = Database::getInstance(config('db'));
            // Check dup
            $exists = $db->query("SELECT id FROM links WHERE url = :url", [':url' => $url])->fetch();
            if (!$exists) {
                // Insert Pending
                $db->query("INSERT INTO links (name, url, status) VALUES (:name, :url, 0)", [
                    ':name' => $name,
                    ':url' => $url
                ]);

                // Notify Admin
                if (class_exists('Plugins\Ran_Notice\Service')) {
                     require_once ROOT_PATH . '/plugins/Ran_Notice/Service.php';
                     \Plugins\Ran_Notice\Service::sendToAdmins('admin_link_apply', [
                         'link_name' => $name,
                         'link' => url('/admin/links?status=0')
                     ]);
                }
            }
        }
        
        header('Location: ' . url('/links?success=1'));
        exit;
    }

}
