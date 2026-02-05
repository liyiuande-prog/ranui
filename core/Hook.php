<?php

namespace Core;

class Hook
{
    protected static $actions = [];

    /**
     * Add a hook
     * @param string $hook_name
     * @param callable $callback
     * @param int $priority
     */
    public static function add($hook_name, $callback, $priority = 10)
    {
        self::$actions[$hook_name][] = [
            'callback' => $callback,
            'priority' => $priority
        ];
    }

    /**
     * Execute a hook
     * @param string $hook_name
     * @param mixed $params
     * @return mixed 
     */
    public static function listen($hook_name, $params = null)
    {
        if (isset(self::$actions[$hook_name])) {
            // Sort by priority
            usort(self::$actions[$hook_name], function ($a, $b) {
                return $a['priority'] <=> $b['priority'];
            });

            foreach (self::$actions[$hook_name] as $action) {
                $params = call_user_func($action['callback'], $params);
            }
        }
        return $params;
    }
}
