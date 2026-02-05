<?php

namespace Core;

class Router
{
    protected static $routes = [];

    public static function get($uri, $action)
    {
        self::add('GET', $uri, $action);
    }

    public static function post($uri, $action)
    {
        self::add('POST', $uri, $action);
    }

    protected static function add($method, $uri, $action)
    {
        // Normalize URI: ensure leading slash
        $uri = '/' . ltrim($uri, '/');
        
        // Convert {param} to regex capture group ([^/]+)
        // Escape forward slashes for regex if strictly needed, but roughly:
        $routePattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([^/]+)', $uri);
        // Add regex delimiters
        $routePattern = "#^" . $routePattern . "$#";
        
        self::$routes[$method][$routePattern] = $action;
    }

    public static function dispatch($uri, $method)
    {
        $method = strtoupper($method);
        // Normalize URI to have leading slash
        $uri = '/' . ltrim($uri, '/');
        // Remove query string if present (already handled by App::parseUrl usually, but safe to check)
        
        if (!isset(self::$routes[$method])) {
             throw new \Exception("Request Method $method not supported for this route or no routes defined.");
        }

        foreach (self::$routes[$method] as $pattern => $action) {
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // remove full match (the whole string)

                if (is_callable($action)) {
                    return call_user_func_array($action, $matches);
                }

                if (is_string($action)) {
                    if (strpos($action, '@') === false) {
                        throw new \Exception("Invalid route definition: $action");
                    }
                    
                    [$controllerClass, $controllerMethod] = explode('@', $action);
                    
                    if (class_exists($controllerClass)) {
                        // Assumption: Config is needed. We can use global config() helper or pass it.
                        // For compatibility with existing Controller constructor:
                        global $config; 
                        $controller = new $controllerClass($config);
                        
                        if (method_exists($controller, $controllerMethod)) {
                            return call_user_func_array([$controller, $controllerMethod], $matches);
                        } else {
                            throw new \Exception("Method $controllerMethod not found in $controllerClass");
                        }
                    } else {
                         throw new \Exception("Controller $controllerClass not found");
                    }
                }
            }
        }
        
        throw new \Exception("404 Not Found: $uri");
    }
}
