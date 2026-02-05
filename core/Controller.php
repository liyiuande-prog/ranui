<?php

namespace Core;

class Controller
{
    protected $config;
    protected $view;

    public function __construct($config)
    {
        $this->config = $config;
        $this->view = new View($config);
    }

    protected function view($path, $data = [])
    {
        $this->view->render($path, $data);
    }
    
    // Helper to get config
    protected function getConfig($key) {
        return $this->config[$key] ?? null;
    }
}
