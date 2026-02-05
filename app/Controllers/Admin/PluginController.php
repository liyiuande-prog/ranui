<?php

namespace App\Controllers\Admin;

use Core\Database;

class PluginController extends BaseController
{
    public function index()
    {
        $db = Database::getInstance(config('db'));
        $pluginsDir = ROOT_PATH . '/plugins';
        
        // 1. 扫描文件系统中的插件
        $allPlugins = []; 
        
        if (is_dir($pluginsDir)) {
            $dirs = glob($pluginsDir . '/*', GLOB_ONLYDIR);
            if ($dirs) {
                foreach ($dirs as $dir) {
                    $name = basename($dir);
                    $file = $dir . '/Plugin.php';
                    $displayName = $name;
                    $description = '';
                    
                    if (file_exists($file)) {
                        $content = file_get_contents($file);
                        if (preg_match('/Version:\s*([0-9\.]+)/i', $content, $m)) $version = trim($m[1]);
                        if (preg_match('/Plugin Name:\s*(.+)/u', $content, $m)) $displayName = trim($m[1]);
                        if (preg_match('/Description:\s*(.+)/u', $content, $m)) $description = trim($m[1]);
                    }
                    
                    // 默认状态
                    $allPlugins[$name] = [
                        'name' => $name,
                        'displayName' => $displayName,
                        'description' => $description,
                        'version' => $version,
                        'is_installed' => false,
                        'id' => null,
                        'is_active' => 0,
                        'installed_at' => null
                    ];
                }
            }
        }
        
        // 2. 获取数据库即已安装插件的信息
        try {
            $dbPlugins = $db->query("SELECT * FROM plugins")->fetchAll();
            foreach ($dbPlugins as $p) {
                $name = $p['name'];
                if (isset($allPlugins[$name])) {
                    $allPlugins[$name]['is_installed'] = true;
                    $allPlugins[$name]['id'] = $p['id'];
                    $allPlugins[$name]['is_active'] = $p['is_active'];
                    $allPlugins[$name]['installed_at'] = $p['installed_at'];
                }
            }
        } catch (\Exception $e) {
            // 如果表不存在，忽略错误 (视为无已安装插件)
        }
        
        $this->view('admin/plugins/index', ['plugins' => $allPlugins]);
    }

    /**
     * 安装插件 (写入数据库)
     */
    public function install($name)
    {
        // Safe Name Check
        if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $name)) {
             $_SESSION['error'] = '插件名称不合法';
             header('Location: ' . url('/admin/plugins'));
             exit;
        }

        // 安全检查：检查文件夹是否存在
        $dir = ROOT_PATH . '/plugins/' . $name;
        if (!is_dir($dir)) {
             $_SESSION['error'] = '插件目录不存在';
             header('Location: ' . url('/admin/plugins'));
             exit;
        }

        $db = Database::getInstance(config('db'));
        
        // 解析版本
        $version = '1.0.0';
        $file = $dir . '/Plugin.php';
        if (file_exists($file)) {
            $content = file_get_contents($file);
            if (preg_match('/Version:\s*([0-9\.]+)/i', $content, $m)) $version = trim($m[1]);
        }
        
        // 写入数据库
        try {
            $exists = $db->query("SELECT id FROM plugins WHERE name = :name", [':name' => $name])->fetch();
            if (!$exists) {
                $db->query("INSERT INTO plugins (name, version, is_active) VALUES (:name, :ver, 0)", [':name' => $name, ':ver' => $version]);
            }
        } catch (\Exception $e) {
            // handle error
        }

        $_SESSION['success'] = "插件 {$name} 安装成功！";
        header('Location: ' . url('/admin/plugins'));
        exit;
    }

    /**
     * 卸载插件 (删除数据库记录)
     */
    public function uninstall($id)
    {
        $db = Database::getInstance(config('db'));
        $db->query("DELETE FROM plugins WHERE id = :id", [':id' => $id]);
        
        $_SESSION['success'] = '插件已卸载';
        header('Location: ' . url('/admin/plugins'));
        exit;
    }
    
    /**
     * 启用/停用插件
     */
    public function toggle($id)
    {
        $db = Database::getInstance(config('db'));
        $plugin = $db->query("SELECT * FROM plugins WHERE id=:id", [':id'=>$id])->fetch();
        
        if ($plugin) {
            $newStatus = $plugin['is_active'] ? 0 : 1;
            
            // If Deactivating (Going from 1 to 0)
            if ($plugin['is_active'] == 1) {
                $className = "Plugins\\" . $plugin['name'] . "\\Plugin";
                if (class_exists($className) && method_exists($className, 'uninstall')) {
                    call_user_func([$className, 'uninstall']);
                }
            }

            $db->query("UPDATE plugins SET is_active = :status WHERE id = :id", [
                ':status' => $newStatus,
                ':id' => $id
            ]);
        }
        
        $_SESSION['success'] = ($newStatus ? '插件已启用' : '插件已停用');
        header('Location: ' . url('/admin/plugins'));
        exit;
    }
}
