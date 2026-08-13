<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Cache;
use App\Config;
use App\Database;
use App\Settings;
use App\SEOService;
use App\Captcha;
use App\StructuredData;

class HomeController extends BaseController
{
    public function index(): void
    {
        $educationCount = max(1, min((int) faToEnDigits((string) Settings::get('home_education_count', 4)), 6));
        $blogCount = max(1, min((int) faToEnDigits((string) Settings::get('home_blog_count', 3)), 6));

        $homeData = Cache::remember('home_data', Config::get('cache.ttl.page', 600), function () use ($educationCount, $blogCount) {
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
                'hairModels' => Database::fetchAll("SELECT * FROM hair_models WHERE is_active = 1 LIMIT 10"),
                'products' => array_map('normalizeProduct', Database::fetchAll("SELECT * FROM products WHERE is_active = 1 ORDER BY id LIMIT 10")),
                'testimonials' => Database::fetchAll("SELECT * FROM testimonials WHERE is_active = 1 ORDER BY id"),
                'educationCourses' => array_map('normalizeCourse', Database::fetchAll("SELECT * FROM courses WHERE is_active = 1 ORDER BY id LIMIT ?", [$educationCount])),
                'latestPosts' => Database::fetchAll("SELECT * FROM blog_posts WHERE is_published = 1 ORDER BY published_at DESC, id DESC LIMIT ?", [$blogCount]),
            ];
        }, 'homepage');

        $settings = Settings::all();
        $seo = SEOService::forPage('home');
        $captchaEnabled = Captcha::isEnabled('booking');
        $captchaQuestion = $captchaEnabled ? Captcha::store() : '';

        $this->view('home/index', compact(
            'homeData',
            'settings',
            'seo',
            'captchaQuestion',
            'captchaEnabled'
        ) + [
            'services' => $homeData['services'],
            'artists' => $homeData['artists'],
            'hairModels' => $homeData['hairModels'],
            'educationCourses' => $homeData['educationCourses'],
            'products' => $homeData['products'],
            'testimonials' => $homeData['testimonials'],
            'latestPosts' => $homeData['latestPosts'],
            'homeShowEducation' => (int) Settings::get('home_show_education', 1) === 1,
            'homeShowBlog' => (int) Settings::get('home_show_blog', 1) === 1,
            'jsonLd' => StructuredData::render(StructuredData::organization()),
        ]);
    }
}
