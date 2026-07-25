<?php

class PagesController extends BaseController
{
    public function privacy(): void
    {
        $title = 'حریم خصوصی | موبارو';
        $settings = Settings::all();

        $this->view('pages/privacy', compact('settings', 'title'));
    }

    public function terms(): void
    {
        $title = 'شرایط استفاده | موبارو';
        $settings = Settings::all();

        $this->view('pages/terms', compact('settings', 'title'));
    }
}
