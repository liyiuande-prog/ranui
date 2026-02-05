<?php
namespace Core;

class PluginManager
{
    /**
     * 加载所有已启用的插件
     */
    public static function load()
    {
        try {
            // 获取数据库实例
            $db = Database::getInstance(config('db'));
            
            // 查询已启用的插件
            $plugins = $db->query("SELECT * FROM plugins WHERE is_active = 1")->fetchAll();
            
            if ($plugins) {
                foreach ($plugins as $plugin) {
                    $pluginName = $plugin['name'];
                    // 约定插件主文件为 Plugin.php
                    $file = ROOT_PATH . '/plugins/' . $pluginName . '/Plugin.php';
                    
                    if (file_exists($file)) {
                        require_once $file;
                        
                        // 约定命名空间 Plugins\{DirectoryName}\Plugin
                        $class = "Plugins\\{$pluginName}\\Plugin";
                        
                        if (class_exists($class) && method_exists($class, 'init')) {
                            // 初始化插件
                            $class::init();
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // 忽略错误，确保核心功能正常运行
        }
    }
}
