<?php

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
