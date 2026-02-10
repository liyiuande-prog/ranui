<?php

namespace Core;

class Captcha
{
    /**
     * Generate Click Captcha Image
     * Returns the image binary data directly.
     */
    /**
     * Step 1: Generate Data & Store in Session
     * Returns the Target Text (SHUFFLED)
     */
    public static function generateData()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        $width = 300;
        $height = 150;
        
        $keys = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        // Ensure unique characters by shuffling once and picking sequence
        $shuffled = str_shuffle($keys);
        $items = [];
        
        for ($i = 0; $i < 4; $i++) {
            $char = $shuffled[$i];
            
            $qW = $width / 4;
            $x = rand($i * $qW + 10, ($i + 1) * $qW - 20);
            $y = rand(20, $height - 20);
            
            $items[] = ['char' => $char, 'x' => $x + 7, 'y' => $y + 7];
        }
        
        $targetItems = $items;
        shuffle($targetItems);
        
        $_SESSION['captcha_target'] = $targetItems;
        $_SESSION['captcha_items'] = $items; 
        $_SESSION['captcha_time'] = microtime(true); // Timestamp for time-based validation
        
        // Return Target Text
        $chars = array_map(function($item) { return $item['char']; }, $targetItems);
        return implode('  ', $chars);
    }

    /**
     * Step 2: Render Image based on Session Data
     */
    public static function renderImage()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        $items = $_SESSION['captcha_items'] ?? [];
        // If no items, regenerate (fallback)
        if (empty($items)) {
             self::generateData();
             $items = $_SESSION['captcha_items'];
        }
        
        $width = 300;
        $height = 150;
        $image = imagecreatetruecolor($width, $height);
        
        // Colors
        $bg = imagecolorallocate($image, 255, 255, 255);
        $text_color = imagecolorallocate($image, 50, 50, 50);
        $line_color = imagecolorallocate($image, 200, 200, 200);
        
        imagefill($image, 0, 0, $bg);
        
        // Noise
        for ($i = 0; $i < 5; $i++) {
            imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $line_color);
        }
        
        // Draw Items
        foreach ($items as $item) {
             // -7 offsets the center logic we used earlier to get back to top-left for imagestring
             imagestring($image, 5, $item['x'] - 7, $item['y'] - 7, $item['char'], $text_color);
        }
        
        ob_start();
        imagejpeg($image);
        $data = ob_get_clean();
        imagedestroy($image);
        
        return $data;
    }
    
    public static function check($points)
    {
        // Global Bypass
        if (function_exists('get_option') && get_option('login_captcha_enable', '1') !== '1') {
            return true;
        }

        if (session_status() === PHP_SESSION_NONE) session_start();
        $targets = $_SESSION['captcha_target'] ?? null;
        if (!$targets) return false;
        
        // Points string: "x1,y1,x2,y2,x3,y3,..."
        $clicks = explode(',', $points);
        if (count($clicks) != 8) return false; // 4 pairs

        // Time Check: At least 1 second must pass (Human reaction time)
        $genTime = $_SESSION['captcha_time'] ?? 0;
        if (microtime(true) - $genTime < 1.0) {
            return false; // Too fast (Bot)
        }
        
        $tolerance = 30; // 30px radius
        
        // Validate each click against the SHUFFLED target order
        for($i=0; $i<4; $i++) {
             $clickX = intval($clicks[$i*2]);
             $clickY = intval($clicks[$i*2+1]);
             
             // The expected target for this Nth click
             $target = $targets[$i];
             
             $dist = sqrt(pow($clickX - $target['x'], 2) + pow($clickY - $target['y'], 2));
             if ($dist > $tolerance) {
                 return false;
             }
        }
        
        // Clear after check
        unset($_SESSION['captcha_target']);
        return true;
    }
    
    public static function getTargetText() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $targets = $_SESSION['captcha_target'] ?? [];
        // Extract chars
        $chars = array_map(function($item) { return $item['char']; }, $targets);
        return implode('  ', $chars);
    }
}
