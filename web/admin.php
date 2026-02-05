<?php

use Core\Router;

// Backend Routes
Router::get('/admin', 'App\Controllers\Admin\IndexController@index');
Router::get('/admin/dashboard', 'App\Controllers\Admin\IndexController@index');
Router::get('/admin/notifications', 'App\Controllers\Admin\IndexController@notifications');
Router::get('/admin/notifications/read/{id}', 'App\Controllers\Admin\IndexController@readNotification');

// Auth
Router::get('/admin/login', 'App\Controllers\Admin\AuthController@login');
Router::post('/admin/login', 'App\Controllers\Admin\AuthController@authenticate');
Router::get('/admin/logout', 'App\Controllers\Admin\AuthController@logout');

// Posts
Router::get('/admin/posts', 'App\Controllers\Admin\PostController@index');
Router::get('/admin/posts/create', 'App\Controllers\Admin\PostController@create');
Router::post('/admin/posts/store', 'App\Controllers\Admin\PostController@store');
Router::get('/admin/posts/edit/{id}', 'App\Controllers\Admin\PostController@edit');
Router::post('/admin/posts/update/{id}', 'App\Controllers\Admin\PostController@update');
Router::post('/admin/posts/delete/{id}', 'App\Controllers\Admin\PostController@delete');

// Categories
Router::get('/admin/categories', 'App\Controllers\Admin\CategoryController@index');
Router::get('/admin/categories/create', 'App\Controllers\Admin\CategoryController@create');
Router::post('/admin/categories/store', 'App\Controllers\Admin\CategoryController@store');
Router::get('/admin/categories/edit/{id}', 'App\Controllers\Admin\CategoryController@edit');
Router::post('/admin/categories/update/{id}', 'App\Controllers\Admin\CategoryController@update');
Router::post('/admin/categories/delete/{id}', 'App\Controllers\Admin\CategoryController@delete');

// Comments
Router::get('/admin/comments', 'App\Controllers\Admin\CommentController@index');
Router::get('/admin/comments/edit/{id}', 'App\Controllers\Admin\CommentController@edit');
Router::post('/admin/comments/update/{id}', 'App\Controllers\Admin\CommentController@update');
Router::post('/admin/comments/delete/{id}', 'App\Controllers\Admin\CommentController@delete');
// Optional Create if strictly requested, but usually Reply is better. I'll add standard Create route.
Router::get('/admin/comments/create', 'App\Controllers\Admin\CommentController@create');
Router::post('/admin/comments/store', 'App\Controllers\Admin\CommentController@store');

// Themes
Router::get('/admin/themes', 'App\Controllers\Admin\ThemeController@index');
Router::post('/admin/themes/activate/{id}', 'App\Controllers\Admin\ThemeController@activate');
Router::get('/admin/themes/editor', 'App\Controllers\Admin\ThemeController@editor');
Router::get('/admin/themes/file', 'App\Controllers\Admin\ThemeController@getFile'); // AJAX
Router::post('/admin/themes/save', 'App\Controllers\Admin\ThemeController@saveFile'); // AJAX

// Plugins
Router::get('/admin/plugins', 'App\Controllers\Admin\PluginController@index');
Router::post('/admin/plugins/install/{name}', 'App\Controllers\Admin\PluginController@install');
Router::post('/admin/plugins/uninstall/{id}', 'App\Controllers\Admin\PluginController@uninstall');
Router::post('/admin/plugins/toggle/{id}', 'App\Controllers\Admin\PluginController@toggle');

// Users
Router::get('/admin/users', 'App\Controllers\Admin\UserController@index');
Router::get('/admin/users/create', 'App\Controllers\Admin\UserController@create');
Router::post('/admin/users/store', 'App\Controllers\Admin\UserController@store');
Router::get('/admin/users/edit/{id}', 'App\Controllers\Admin\UserController@edit');
Router::post('/admin/users/update/{id}', 'App\Controllers\Admin\UserController@update');
Router::post('/admin/users/delete/{id}', 'App\Controllers\Admin\UserController@delete');


// Options
Router::get('/admin/options', 'App\Controllers\Admin\OptionController@index');
Router::post('/admin/options/update', 'App\Controllers\Admin\OptionController@update');

// Auto-Load Plugin Admin Routes
$pluginRoutes = glob(ROOT_PATH . '/plugins/*/routes/admin.php');
if ($pluginRoutes) {
    foreach ($pluginRoutes as $routeFile) {
        require_once $routeFile;
    }
}
