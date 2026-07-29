<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Settings;
use App\SEOService;

class AboutController extends BaseController
{
    public function index(): void
    {
        $settings = Settings::all();
        $seo = SEOService::forPage('about');

        $this->view('about/index', compact('settings', 'seo'));
    }
}
