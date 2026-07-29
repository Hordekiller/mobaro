<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Auth;

class AuthMiddleware
{
    public static function handle(): void
    {
        Auth::requireAuth();
    }

    public static function admin(): void
    {
        Auth::requireAdmin();
    }
}
