<?php

return [
    'app_name' => 'RanUI Blog',
    'base_url' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? ''),
    'debug' => true,
    
    // Database Configuration
    'db' => [
        'host' => '127.0.0.1',
        'name' => 'ranui_test',
        'user' => 'root',
        'pass' => 'root',
        'charset' => 'utf8mb4'
    ],
    
    // Theme Configuration
    'theme' => 'default',
];
