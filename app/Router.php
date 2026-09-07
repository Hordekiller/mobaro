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
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $uri = is_string($uri) ? rtrim($uri, '/') : '';
        $uri = $uri !== '' ? $uri : '/';

        foreach (self::$routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter($matches, fn($key) => is_string($key), ARRAY_FILTER_USE_KEY);

                // Path segments arrive percent-encoded (e.g. /blog/bleach%20-touch-up
                // as "bleach%20-touch-up"). Decode them so slug/course lookups match
                // the stored, human-readable value. rawurldecode() only expands %XX
                // and leaves "+" untouched — correct for URL paths (unlike urldecode,
                // which would turn "+" into a space). Numeric casts below then see
                // the decoded value (e.g. "%31" -> "1"), also the intended number.
                foreach ($params as $k => $v) {
                    $params[$k] = rawurldecode($v);
                }

                // Numeric path segments (id, size, width, height, seed) are
                // always cast to int. A non-numeric value such as /product/abc
                // becomes 0 so the controller's own "not found" branch decides
                // (404/400) instead of a TypeError rendering a 500 page.
                $numericNames = ['id', 'size', 'width', 'height', 'seed'];
                foreach ($params as $k => $v) {
                    if (in_array($k, $numericNames, true)) {
                        $params[$k] = (int) $v;
                    }
                }

                // Dispatch controller actions positionally: named arguments
                // (id: 5) would throw "Unknown named parameter" whenever a
                // route param name differs from the method signature (e.g.
                // ShopController::postReview(int $productId)). Parameter order
                // always matches the route pattern, so positional is safe.
                $params = array_values($params);

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
                    self::renderError(500);
                    return;
                }
                return;
            }
        }

        self::renderError(404);
    }

    private static function renderError(int $code): void
    {
        if (!headers_sent()) {
            http_response_code($code);
        }
        $settings = Settings::all();
        $seo = [];
        $title = 'موبارو';
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
