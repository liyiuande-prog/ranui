<?php
namespace Core;

class Log {
    public static function error($message, $context = []) {
        self::write('ERROR', $message, $context);
    }

    public static function info($message, $context = []) {
        self::write('INFO', $message, $context);
    }
    
    public static function warning($message, $context = []) {
        self::write('WARNING', $message, $context);
    }

    private static function write($level, $message, $context = []) {
        $logDir = dirname(__DIR__) . '/storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        $date = date('Y-m-d');
        $file = $logDir . "/app-{$date}.log";
        
        $time = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $logLine = "[{$time}] {$level}: {$message}{$contextStr}" . PHP_EOL;
        
        file_put_contents($file, $logLine, FILE_APPEND);
    }
}
