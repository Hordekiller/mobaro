<?php

class HomeController extends BaseController
{
    public function index(): void
    {
        $homeData = Cache::remember('home_data', Config::get('cache.ttl.page', 600), function () {
            return [
                'services' => Database::fetchAll(
                    "SELECT s.*,
                            SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT a.name ORDER BY a.id SEPARATOR '|'), '|', 1) as artist_name,
                            SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT a.avatar ORDER BY a.id SEPARATOR '|'), '|', 1) as artist_avatar
                     FROM services s
                     LEFT JOIN artist_services a_s ON s.id = a_s.service_id
                     LEFT JOIN artists a ON a_s.artist_id = a.id
                     WHERE s.is_active = 1
                     GROUP BY s.id
                     ORDER BY s.id"
                ),
                'artists' => Database::fetchAll("SELECT * FROM artists WHERE is_active = 1"),
                'hairModels' => Database::fetchAll("SELECT * FROM hair_models WHERE is_active = 1"),
                'products' => Database::fetchAll("SELECT * FROM products WHERE is_active = 1 ORDER BY id"),
                'testimonials' => Database::fetchAll("SELECT * FROM testimonials WHERE is_active = 1 ORDER BY id"),
                'educationCourses' => Database::fetchAll("SELECT * FROM courses WHERE is_active = 1 ORDER BY RAND() LIMIT 4"),
            ];
        }, 'homepage');

        $settings = Settings::all();
        $captchaEnabled = Captcha::isEnabled('booking');
        $captchaQuestion = $captchaEnabled ? Captcha::store() : '';

        $this->view('home/index', compact(
            'homeData',
            'settings',
            'captchaQuestion',
            'captchaEnabled'
        ) + [
            'services' => $homeData['services'],
            'artists' => $homeData['artists'],
            'hairModels' => $homeData['hairModels'],
            'educationCourses' => $homeData['educationCourses'],
            'products' => $homeData['products'],
            'testimonials' => $homeData['testimonials'],
        ]);
    }
}
