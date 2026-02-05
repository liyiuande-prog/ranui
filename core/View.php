<?php

namespace Core;

class View
{
    protected $config;
    protected $data = []; // Shared data container

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function render($viewPath, $data = [])
    {
        // Merge new data with existing global data
        $this->data = array_merge($this->data, $data);
        
        // Extract all data so it's available in the view
        extract($this->data);

        $theme = $this->config['theme'] ?? 'default';
        
        // 1. Try to find in Theme directory
        $themeFile = ROOT_PATH . "/themes/{$theme}/{$viewPath}.php";
        
        // 2. Try to find in App/Views (Default/Admin view)
        $defaultFile = APP_PATH . "/Views/{$viewPath}.php";

        if (file_exists($themeFile)) {
            require $themeFile;
        } elseif (file_exists($defaultFile)) {
            require $defaultFile;
        } else {
            throw new \Exception("View file not found: $viewPath (Theme: $theme)");
        }
    }
}
