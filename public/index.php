<?php

declare(strict_types=1);

use App\Router;
use App\Settings;

require_once __DIR__ . '/../app/bootstrap.php';

const AUTH = 'App\\Controllers\\AuthController';
const DASHBOARD = 'App\\Controllers\\DashboardController';
const BOOKING = 'App\\Controllers\\BookingController';
const SHOP = 'App\\Controllers\\ShopController';
const BLOG = 'App\\Controllers\\BlogController';
const ADMIN = 'App\\Controllers\\AdminController';
const API = 'App\\Controllers\\ApiController';
const ACADEMY = 'App\\Controllers\\AcademyController';
const JSON_HEADER = 'Content-Type: application/json';

$routesFile = __DIR__ . '/../app/routes.php';

if (!is_file($routesFile)) {
    error_log('[mobaro] CRITICAL: app/routes.php missing; deployment incomplete');
    http_response_code(503);
    header('Retry-After: 300');
    exit('Service temporarily unavailable. Please retry shortly.');
}

require_once $routesFile;

Router::dispatch();
