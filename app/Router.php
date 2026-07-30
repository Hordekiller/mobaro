<?php

declare(strict_types=1);

namespace App;

use App\Settings;
use Throwable;

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

                $numericNames = ['id', 'size', 'width', 'height', 'seed'];
                foreach ($params as $k => $v) {
                    if (is_numeric($v) || in_array($k, $numericNames, true)) {
                        $params[$k] = (int) $v;
                    }
                }

                $handler = $route['handler'];

                try {
                    if (is_array($handler)) {
                        [$controller, $action] = $handler;
                        $controllerInstance = new $controller();
                        $controllerInstance->$action(...$params);
                    } else {
                        $handler(...$params);
                    }
                } catch (Throwable $e) {
                    error_log(sprintf(
                        '[Router] %s: %s in %s:%d',
                        get_class($e),
                        $e->getMessage(),
                        $e->getFile(),
                        $e->getLine()
                    ));
                    self::renderError(404);
                    return;
                }
                return;
            }
        }

        self::renderError(404);
    }

    private static function renderError(int $code): void
    {
        http_response_code($code);
        $settings = Settings::all();
        require_once __DIR__ . '/views/layouts/header.php';

        $messages = [
            403 => 'شما دسترسی به این صفحه را ندارید.',
            404 => 'صفحه مورد نظر وجود ندارد.',
            419 => 'درخواست نامعتبر است.',
            500 => 'خطایی غیرمنتظره رخ داده است.',
        ];

        if ($code === 404) {
            require_once __DIR__ . '/views/errors/404.php';
        } else {
            $errorMessage = $messages[$code] ?? 'خطایی رخ داد.';
            require_once __DIR__ . '/views/errors/500.php';
        }

        require_once __DIR__ . '/views/layouts/footer.php';
    }
}
