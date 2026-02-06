<?php

namespace Plugins\Ran_link\Controllers\Admin;

use Core\Controller as BaseController;
use Core\Database;

class LinkController extends BaseController
{

    public function index()
    {
        $db = Database::getInstance(config('db'));
        $links = $db->query("SELECT * FROM links ORDER BY status ASC, created_at DESC")->fetchAll();
        
        $this->renderPluginView('admin/index', ['links' => $links]);
    }

    public function save()
    {
        $db = Database::getInstance(config('db'));
        $name = $_POST['name'];
        $url = $_POST['url'];
        $id = $_POST['id'] ?? null;
        
        if ($id) {
            $db->query("UPDATE links SET name=:name, url=:url WHERE id=:id", [
                ':name' => $name,
                ':url' => $url,
                ':id' => $id
            ]);
        } else {
            // Admin added links are auto-approved (status=1)
            $db->query("INSERT INTO links (name, url, status) VALUES (:name, :url, 1)", [
                ':name' => $name,
                ':url' => $url
            ]);
        }
        
        $_SESSION['success'] = '链接已保存';
        header('Location: ' . url('/admin/links'));
        exit;
    }

    public function approve($id)
    {
        $db = Database::getInstance(config('db'));
        $db->query("UPDATE links SET status = 1 WHERE id = :id", [':id' => $id]);
        $_SESSION['success'] = '链接已通过';
        header('Location: ' . url('/admin/links'));
        exit;
    }

    public function delete($id)
    {
        $db = Database::getInstance(config('db'));
        $db->query("DELETE FROM links WHERE id = :id", [':id' => $id]);
        $_SESSION['success'] = '链接已删除';
        header('Location: ' . url('/admin/links'));
        exit;
    }


    protected function renderPluginView($view, $data = [])
    {
        extract($data);
        $viewFile = dirname(dirname(__DIR__)) . '/views/' . $view . '.php';
        if (file_exists($viewFile)) {
            require APP_PATH . '/Views/admin/header.php';
            require APP_PATH . '/Views/admin/sidebar.php';
            require $viewFile;
            require APP_PATH . '/Views/admin/footer.php';
        } else {
            echo "View not found: $viewFile";
        }
    }
}
