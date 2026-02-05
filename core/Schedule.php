<?php

namespace Core;

/**
 * RanUI Unified Task Scheduler
 * Handles registration and execution of background tasks.
 */
class Schedule
{
    protected static $tasks = [];
    protected static $db;

    /**
     * Register a task
     * @param string $name Unique name for the task
     * @param string $frequency frequency: 'minute', 'hourly', 'daily', 'weekly'
     * @param callable $callback The code to run
     */
    public static function register($name, $frequency, $callback)
    {
        self::$tasks[$name] = [
            'frequency' => $frequency,
            'callback' => $callback
        ];
    }

    /**
     * Run all due tasks
     */
    public static function run()
    {
        self::$db = Database::getInstance(config('db'));
        self::ensureTaskTable();

        foreach (self::$tasks as $name => $task) {
            if (self::isDue($name, $task['frequency'])) {
                try {
                    // Execute task
                    call_user_func($task['callback']);
                    
                    // Update last run time
                    self::markExecuted($name);
                } catch (\Exception $e) {
                    // Log error (simple file log)
                    file_put_contents(ROOT_PATH . '/storage/logs/cron_error.log', 
                        date('[Y-m-d H:i:s] ') . "Task '$name' failed: " . $e->getMessage() . "\n", FILE_APPEND);
                }
            }
        }
    }

    /**
     * Check if a task is due based on its frequency and last run record
     */
    protected static function isDue($name, $frequency)
    {
        $sql = "SELECT last_run FROM system_tasks WHERE task_name = ?";
        $lastRun = self::$db->query($sql, [$name])->fetchColumn();

        if (!$lastRun) return true; // Never run

        $lastTimestamp = strtotime($lastRun);
        $now = time();

        switch ($frequency) {
            case 'minute':
                return ($now - $lastTimestamp) >= 60;
            case 'hourly':
                return ($now - $lastTimestamp) >= 3600;
            case 'daily':
                // Check if the current day is different from last run day
                return date('Ymd', $now) !== date('Ymd', $lastTimestamp);
            case 'weekly':
                return ($now - $lastTimestamp) >= 86400 * 7;
            default:
                return false;
        }
    }

    protected static function markExecuted($name)
    {
        $sql = "INSERT INTO system_tasks (task_name, last_run) VALUES (?, NOW()) 
                ON DUPLICATE KEY UPDATE last_run = NOW()";
        self::$db->query($sql, [$name]);
    }

    protected static function ensureTaskTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `system_tasks` (
            `task_name` varchar(100) NOT NULL,
            `last_run` datetime NOT NULL,
            PRIMARY KEY (`task_name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        self::$db->query($sql);
    }
}
