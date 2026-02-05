<?php

namespace App\Controllers\Admin;

use Core\Database;

class ThemeController extends BaseController
{
    public function index()
    {
        $db = Database::getInstance(config('db'));
        
        // Scan themes directory
        $themesDir = dirname(APP_PATH) . '/themes';
        if (is_dir($themesDir)) {
            $dirs = array_filter(glob($themesDir . '/*'), 'is_dir');
            
            foreach($dirs as $dir) {
                $name = basename($dir);
                // Check if exists in DB
                $exists = $db->query("SELECT id FROM themes WHERE name = :name", [':name' => $name])->fetch();
                if (!$exists) {
                    $db->query("INSERT INTO themes (name, is_active) VALUES (:name, 0)", [':name' => $name]);
                }
            }
        }
        
        $themes = $db->query("SELECT * FROM themes")->fetchAll();
        $this->view('admin/themes/index', ['themes' => $themes]);
    }

    public function activate($id)
    {
        $db = Database::getInstance(config('db'));
        // Deactivate all
        $db->query("UPDATE themes SET is_active = 0");
        // Activate selected
        $db->query("UPDATE themes SET is_active = 1 WHERE id = :id", [':id' => $id]);
        
        $_SESSION['success'] = '主题已激活';
        header('Location: ' . url('/admin/themes'));
    }
    
    public function editor()
    {
        $themeName = $_GET['theme'] ?? '';
        if (!$themeName) {
            die('Theme not specified');
        }
        
        $themePath = dirname(APP_PATH) . '/themes/' . $themeName;
        if (!is_dir($themePath)) {
            die('Theme not found');
        }
        
        // Scan files
        $files = $this->scanThemeFiles($themePath, $themeName);
        
        $this->view('admin/themes/editor', [
            'themeName' => $themeName,
            'files' => $files
        ]);
    }
    
    private function scanThemeFiles($dir, $themeName, $relativePath = '')
    {
        $files = [];
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item == '.' || $item == '..') continue;
            $path = $dir . '/' . $item;
            $rel = $relativePath ? $relativePath . '/' . $item : $item;
            
            if (is_dir($path)) {
                $files[] = [
                    'name' => $item,
                    'path' => $rel,
                    'type' => 'folder',
                    'children' => $this->scanThemeFiles($path, $themeName, $rel)
                ];
            } else {
                $ext = pathinfo($path, PATHINFO_EXTENSION);
                if (in_array($ext, ['php', 'css', 'js', 'html', 'json'])) {
                    $files[] = [
                        'name' => $item,
                        'path' => $rel, // relative to theme root
                        'type' => 'file'
                    ];
                }
            }
        }
        return $files;
    }
    
    public function getFile()
    {
        $theme = $_GET['theme'];
        $file = $_GET['file'];
        
        // Security check
        $path = dirname(APP_PATH) . "/themes/$theme/$file";
        $realPath = realpath($path);
        $themeRoot = realpath(dirname(APP_PATH) . "/themes/$theme");
        
        if ($realPath && strpos($realPath, $themeRoot) === 0 && file_exists($realPath)) {
            echo file_get_contents($realPath);
        } else {
            http_response_code(404);
            echo "File not found or access denied";
        }
    }
    
    public function saveFile()
    {
        $theme = $_POST['theme'];
        $file = $_POST['file'];
        $content = $_POST['content'];
        
        $path = dirname(APP_PATH) . "/themes/$theme/$file";
        $realPath = realpath($path); 
        $themeRoot = realpath(dirname(APP_PATH) . "/themes/$theme");
        
        // Ensure we are inside theme root
        if ($realPath && strpos($realPath, $themeRoot) === 0 && file_exists($realPath)) {
             file_put_contents($realPath, $content);
             echo json_encode(['success' => true]);
        } else {
             echo json_encode(['error' => 'File not found or access denied']);
        }
    }
}
