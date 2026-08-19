<?php
declare(strict_types=1);

namespace App\Core;

final class Router
{
    private static array $routes = [];
    private static array $middleware = [];

    public static function get(string $path, array|callable $handler, array $middleware = []): void
    {
        self::add('GET', $path, $handler, $middleware);
    }

    public static function post(string $path, array|callable $handler, array $middleware = []): void
    {
        self::add('POST', $path, $handler, $middleware);
    }

    public static function any(string $path, array|callable $handler, array $middleware = []): void
    {
        self::add('GET|POST', $path, $handler, $middleware);
    }

    private static function add(string $methods, string $path, array|callable $handler, array $middleware): void
    {
        self::$routes[] = [
            'methods' => explode('|', $methods),
            'pattern' => preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $path),
            'path' => $path,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public static function middleware(string $name, callable $middleware): void
    {
        self::$middleware[$name] = $middleware;
    }

    public static function dispatch(): void
    {
        $method = Request::method();
        $path = Request::path();

        foreach (self::$routes as $route) {
            if (!in_array($method, $route['methods'], true)) continue;
            if (preg_match('#^' . $route['pattern'] . '$#', $path, $matches) === 1) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                foreach ($route['middleware'] as $mw) {
                    $callable = self::$middleware[$mw] ?? null;
                    if ($callable && $callable() === false) return;
                }
                $handler = $route['handler'];
                if (is_array($handler)) {
                    [$class, $methodName] = $handler;
                    $controller = new $class();
                    $controller->{$methodName}(...array_values($params));
                } elseif (is_callable($handler)) {
                    $handler(...array_values($params));
                }
                return;
            }
        }
        View::renderError('The page you are looking for does not exist.', 404);
    }
}
