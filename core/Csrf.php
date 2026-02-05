<?php
namespace Core;

class Csrf {
    /**
     * Generate a new CSRF token if one doesn't exist
     */
    public static function generate() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify the token
     */
    public static function check($token) {
        if (session_status() == PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['csrf_token']) || empty($token)) return false;
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Return hidden input field
     */
    public static function field() {
        $token = self::generate();
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }
}
