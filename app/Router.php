<?php

class Router
{
    private static array $routes = [];

    public static function get(string $path, callable|array $handler): void
    {
        self::add('GET', $path, $handler);
    }

    public static function post(string $path, callable|array $handler): void
    {
        self::add('POST', $path, $handler);
    }

    private static function add(string $method, string $path, callable|array $handler): void
    {
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';
        self::$routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'handler' => $handler,
        ];
    }

    public static function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';

        foreach (self::$routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter($matches, fn($key) => is_string($key), ARRAY_FILTER_USE_KEY);
                $handler = $route['handler'];

                if (is_array($handler)) {
                    [$controller, $action] = $handler;
                    $controllerInstance = new $controller();
                    $controllerInstance->$action(...$params);
                } else {
                    $handler(...$params);
                }
                return;
            }
        }

        http_response_code(404);
        self::renderError(404);
    }

    private static function renderError(int $code): void
    {
        http_response_code($code);
        require __DIR__ . '/views/layouts/header.php';

        if ($code === 404) {
            require __DIR__ . '/views/errors/404.php';
        } else {
            $errorMessage = $code === 500 ? 'خطایی غیرمنتظره رخ داده است.' : 'خطایی رخ داد.';
            require __DIR__ . '/views/errors/500.php';
        }

        require __DIR__ . '/views/layouts/footer.php';
    }
}
