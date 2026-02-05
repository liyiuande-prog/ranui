<?php

namespace App\Controllers\Home;

use Core\Controller;

class PageController extends Controller
{
    public function about()
    {
        $this->view('pages/about', [
            'app_name' => get_option('site_title', 'RanUI Blog'),
            'page_title' => '关于我们'
        ]);
    }

    public function help()
    {
        $this->view('pages/help', [
            'app_name' => get_option('site_title', 'RanUI Blog'),
            'page_title' => '帮助中心'
        ]);
    }

    public function join()
    {
        $this->view('pages/join', [
            'app_name' => get_option('site_title', 'RanUI Blog'),
            'page_title' => '加入我们'
        ]);
    }

    public function contact()
    {
        $this->view('pages/contact', [
            'app_name' => get_option('site_title', 'RanUI Blog'),
            'page_title' => '联系我们'
        ]);
    }

    public function privacy()
    {
        $this->view('pages/privacy', [
            'app_name' => get_option('site_title', 'RanUI Blog'),
            'page_title' => '隐私政策'
        ]);
    }

    public function terms()
    {
        $this->view('pages/terms', [
            'app_name' => get_option('site_title', 'RanUI Blog'),
            'page_title' => '服务条款'
        ]);
    }
}
