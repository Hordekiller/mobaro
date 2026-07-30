<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Cache;
use App\Settings;

class RobotsController extends BaseController
{
    public function index(): void
    {
        header('Content-Type: text/plain; charset=utf-8');

        $body = Cache::remember('robots.txt', 86400, function () {
            $custom = Settings::get('robots_txt', '');
            if ($custom !== '') {
                return $custom;
            }
            return $this->defaultRules();
        });

        echo $body;
        exit;
    }

    private function defaultRules(): string
    {
        return implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin/',
            'Disallow: /login',
            'Disallow: /register',
            'Disallow: /logout',
            'Disallow: /verify-otp',
            'Disallow: /auth/',
            'Disallow: /dashboard/',
            'Disallow: /cart/summary',
            'Disallow: /api/',
            'Disallow: /media/',
            'Disallow: /avatar/',
            '',
            'Sitemap: ' . url('/sitemap.xml'),
            '',
        ]);
    }
}
