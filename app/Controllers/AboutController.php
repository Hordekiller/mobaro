<?php

class AboutController extends BaseController
{
    public function index(): void
    {
        $settings = Settings::all();

        $this->view('about/index', compact('settings'));
    }
}
