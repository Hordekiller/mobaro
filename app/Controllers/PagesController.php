<?php

class PagesController extends BaseController
{
    public function privacy(): void
    {
        $settings = Settings::all();
        $title = 'حریم خصوصی | ' . ($settings['brand_name'] ?? 'موبارو');

        $this->view('pages/privacy', compact('settings', 'title'));
    }

    public function terms(): void
    {
        $settings = Settings::all();
        $title = 'شرایط استفاده | ' . ($settings['brand_name'] ?? 'موبارو');

        $this->view('pages/terms', compact('settings', 'title'));
    }
}
