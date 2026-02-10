<?php

use Core\Router;

// Frontend Routes
Router::get('/', 'App\Controllers\Home\IndexController@index');
Router::get('/home', 'App\Controllers\Home\IndexController@index');
Router::get('/search', 'App\Controllers\Home\SearchController@index');

// 文章详情页 (e.g. /1.html) - Restrict to numeric IDs to avoid conflict with static pages like /about.html
Router::get('/(\d+).html', 'App\Controllers\Home\PostController@index');

// Captcha
Router::get('/captcha/image', 'App\Controllers\Home\CaptchaController@image');
Router::get('/captcha/info', 'App\Controllers\Home\CaptchaController@info');

// Auth
Router::get('/login', 'App\Controllers\Home\AuthController@login');
Router::post('/login', 'App\Controllers\Home\AuthController@authenticate');
Router::post('/login/code', 'App\Controllers\Home\AuthController@loginWithCode'); // New
Router::post('/auth/send-code', 'App\Controllers\Home\AuthController@sendCode'); // New
Router::get('/auth/qr/session', 'App\Controllers\Home\AuthController@qrSession');
Router::get('/auth/qr/check', 'App\Controllers\Home\AuthController@qrCheck');
Router::get('/forgot-password', 'App\Controllers\Home\AuthController@forgotPassword');
Router::post('/auth/reset-password', 'App\Controllers\Home\AuthController@resetPassword');
Router::post('/user/profile/update', 'App\Controllers\Home\UserController@updateProfile');
Router::post('/user/password/update', 'App\Controllers\Home\UserController@updatePassword');
Router::post('/user/email/update', 'App\Controllers\Home\UserController@updateEmail');
Router::post('/user/cancel/request', 'App\Controllers\Home\UserController@requestAccountCancel');
Router::post('/user/cancel/handle', 'App\Controllers\Home\UserController@handleAccountCancel');
Router::get('/register', 'App\Controllers\Home\AuthController@register');
Router::post('/register', 'App\Controllers\Home\AuthController@store');
Router::get('/logout', 'App\Controllers\Home\AuthController@logout');
Router::get('/my', 'App\Controllers\Home\UserController@my');
Router::get('/user/{id}', 'App\Controllers\Home\UserController@profile');
Router::get('/api/users/search', 'App\Controllers\Home\UserController@search'); // For @ mentions
Router::get('/my/data', 'App\Controllers\Home\UserController@getData'); // AJAX User Data


// Writing
Router::get('/write', 'App\Controllers\Home\PostController@create');
Router::post('/post/like', 'App\Controllers\Home\PostController@toggleLike'); // 点赞接口
Router::post('/post/store', 'App\Controllers\Home\PostController@store');
Router::post('/comment/add', 'App\Controllers\Home\PostController@addComment');

// System Upload & File Serve
Router::post('/upload', 'Core\Upload@upload');
Router::get('/file/view', 'Core\File@serve');

// Auto-Load Plugin Frontend Routes
$pluginRoutes = glob(ROOT_PATH . '/plugins/*/routes/home.php');
if ($pluginRoutes) {
    foreach ($pluginRoutes as $routeFile) {
        require_once $routeFile;
    }
}

// License Server Routes (Added by Auto-Agent)
Router::post('/api/license/verify', 'App\Controllers\Api\LicenseController@verify');
