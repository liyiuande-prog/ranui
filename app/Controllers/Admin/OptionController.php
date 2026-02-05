<?php

namespace App\Controllers\Admin;

use Core\Database;

class OptionController extends BaseController
{
    public function index()
    {
        $db = Database::getInstance(config('db'));
        $options = $db->query("SELECT * FROM options")->fetchAll();
        
        // Convert to key-value array for easier view handling
        $opt = [];
        foreach($options as $o) {
            $opt[$o['option_name']] = $o['option_value'];
        }
        
        $this->view('admin/options/index', ['options' => $opt]);
    }

    public function update()
    {
        $db = Database::getInstance(config('db'));
        
        foreach($_POST as $key => $value) {
            // Upsert logic
            $sql = "INSERT INTO options (option_name, option_value) VALUES (:name, :val) 
                    ON DUPLICATE KEY UPDATE option_value = :val";
            $db->query($sql, [':name' => $key, ':val' => $value]);
        }
        
        $_SESSION['success'] = '系统设置已保存';
        header('Location: ' . url('/admin/options'));
    }
}
