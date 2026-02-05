<?php
if (!isset($title)) {
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $map = [
        '/admin' => '仪表盘',
        '/admin/dashboard' => '仪表盘',
        '/admin/posts' => '文章管理',
        '/admin/categories' => '分类管理',
        '/admin/comments' => '评论管理',
        '/admin/themes' => '主题管理',
        '/admin/plugins' => '插件管理',
        '/admin/users' => '用户管理',
        '/admin/options' => '系统设置',
        '/admin/finance' => '财务管理',
        '/admin/integral/goods' => '积分商品',
        '/admin/integral/categories' => '商品分类',
        '/admin/integral/orders' => '兑换订单',
        '/admin/integral/config' => '积分配置',
        '/admin/integral/comments' => '商品评论',
    ];
    
    // 1. Try Exact Match
    if (isset($map[$uri])) {
        $title = $map[$uri];
    } else {
        // 2. Try Prefix Match (Longest wins)
        foreach ($map as $path => $name) {
            if (strpos($uri, $path) === 0) {
                if (!isset($title) || strlen($path) > strlen($matchedPath)) {
                    $title = $name;
                    $matchedPath = $path; // Temp var
                }
            }
        }
    }
    
    // 3. Special cases (Sub-actions)
    if (strpos($uri, '/create') !== false) $title = ($title ?? 'Manage') . ' - 新增';
    if (strpos($uri, '/edit') !== false) $title = ($title ?? 'Manage') . ' - 编辑';
    if (strpos($uri, '/editor') !== false) $title = ($title ?? 'Manage') . ' - 编辑器';
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? e($title) . ' - RanUI Admin' : 'RanUI Admin' ?></title>
    <meta name="csrf-token" content="<?= \Core\Csrf::generate() ?>">
    <!-- Tailwind CSS -->
    <script src="/assets/css/tailwindcss.css"></script>
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8f9fa; }
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        .sidebar-scroll::-webkit-scrollbar-thumb { background: #333; }
    </style>
</head>
<body class="text-slate-900 bg-[#f8f9fa]">
