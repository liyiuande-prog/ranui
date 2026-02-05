<?php
namespace Core;

/**
 * Base Controller for Plugins
 * Handles common logic like rendering views with theme headers/footers.
 */
class PluginController extends Controller
{
    /**
     * Path to the plugin root directory.
     * Child classes should define this, e.g., protected $pluginRoot = __DIR__ . '/../../';
     * @var string
     */
    protected $pluginRoot;

    /**
     * Render a plugin view with optional Theme Header/Footer wrapper.
     * 
     * @param string $view Relative path within the plugin's 'views' directory (e.g., 'home/index')
     * @param array $data Data to pass to the view
     * @param bool $withLayout Whether to include the theme header and footer
     */
    protected function renderPluginView($view, $data = [], $withLayout = true)
    {
        extract($data);

        $theme = $this->config['theme'] ?? 'default';

        // 1. Load Theme Header
        if ($withLayout && defined('ROOT_PATH') && file_exists(ROOT_PATH . "/themes/{$theme}/header.php")) {
            require ROOT_PATH . "/themes/{$theme}/header.php";
        }

        // 2. Load Plugin View
        if ($this->pluginRoot) {
            // Remove trailing slashes and ensure separator
            $root = rtrim($this->pluginRoot, '/\\');
            $viewFile = $root . '/views/' . $view . '.php';

            if (file_exists($viewFile)) {
                require $viewFile;
            } else {
                echo "<div class='container mx-auto py-10 text-red-500'>Error: Plugin View not found: " . htmlspecialchars($view) . "</div>";
            }
        } else {
            echo "<div class='container mx-auto py-10 text-red-500'>Error: PluginRoot property not defined in " . get_class($this) . "</div>";
        }

        // 3. Load Theme Footer
        if ($withLayout && defined('ROOT_PATH') && file_exists(ROOT_PATH . "/themes/{$theme}/footer.php")) {
            require ROOT_PATH . "/themes/{$theme}/footer.php";
        }
    }
}
