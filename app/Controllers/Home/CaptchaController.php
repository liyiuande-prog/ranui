<?php

namespace App\Controllers\Home;

use Core\Controller;
use Core\Captcha;

class CaptchaController extends Controller
{
    public function image()
    {
        header('Content-Type: image/jpeg');
        // Render existing session data (or fallback)
        echo Captcha::renderImage();
        exit;
    }
    
    public function info()
    {
        header('Content-Type: application/json');
        // Generate NEW data
        echo json_encode([
            'text' => Captcha::generateData()
        ]);
        exit;
    }
}
