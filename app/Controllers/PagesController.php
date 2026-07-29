<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Settings;
use App\SEOService;

class PagesController extends BaseController
{
    public function privacy(): void
    {
        $settings = Settings::all();
        $seo = SEOService::forPage('home');
        $title = 'حریم خصوصی | ' . ($settings['brand_name'] ?? 'موبارو');

        $this->view('pages/privacy', compact('settings', 'title', 'seo'));
    }

    public function terms(): void
    {
        $settings = Settings::all();
        $seo = SEOService::forPage('home');
        $title = 'شرایط استفاده | ' . ($settings['brand_name'] ?? 'موبارو');

        $this->view('pages/terms', compact('settings', 'title', 'seo'));
    }
}
