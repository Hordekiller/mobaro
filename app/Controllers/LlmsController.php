<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Cache;
use App\Config;
use App\Database;
use App\Settings;

class LlmsController extends BaseController
{
    public function index(): void
    {
        header('Content-Type: text/markdown; charset=utf-8');

        $body = Cache::remember('llms.txt', 86400, function () {
            $custom = Settings::get('llms_txt', '');
            if ($custom !== '') {
                return $custom;
            }
            return $this->buildMarkdown();
        });

        echo $body;
        exit;
    }

    private function buildMarkdown(): string
    {
        $settings = Settings::all();
        $brandName = $settings['brand_name'] ?? 'موبارو';
        $tagline = $settings['hero_description'] ?? '';
        $baseUrl = rtrim((string) (Config::get('app.url') ?? ''), '/');
        if ($baseUrl === '') {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $baseUrl = $scheme . '://' . $host;
        }

        $lines = [];
        $lines[] = '# ' . $brandName;
        $lines[] = '';
        $lines[] = '> ' . ($tagline ?: 'سالن زیبایی ' . $brandName . ' — خدمات، فروشگاه، آموزش و وبلاگ.');
        $lines[] = '';
        $lines[] = '## صفحات اصلی';
        $lines[] = '';
        $lines[] = '- [خانه](' . $baseUrl . '/): صفحه اصلی ' . $brandName;
        $lines[] = '- [فروشگاه](' . $baseUrl . '/shop): محصولات و لوازم آرایشی حرفه‌ای';
        $lines[] = '- [آکادمی](' . $baseUrl . '/academy): دوره‌های آموزش آرایشگری';
        $lines[] = '- [وبلاگ](' . $baseUrl . '/blog): مقالات و راهنمای زیبایی';
        $lines[] = '- [رزرو نوبت](' . $baseUrl . '/booking): رزرو آنلاین خدمات';
        $lines[] = '- [درباره ما](' . $baseUrl . '/about): معرفی ' . $brandName;
        $lines[] = '- [تماس با ما](' . $baseUrl . '/contact): اطلاعات تماس';
        $lines[] = '- [حریم خصوصی](' . $baseUrl . '/privacy): سیاست حفظ حریم خصوصی';
        $lines[] = '- [شرایط استفاده](' . $baseUrl . '/terms): قوانین و مقررات';

        $products = $this->topProducts();
        if (!empty($products)) {
            $lines[] = '';
            $lines[] = '## محصولات برتر';
            $lines[] = '';
            foreach ($products as $p) {
                $lines[] = '- [' . ($p['name'] ?? '') . '](' . $baseUrl . '/product/' . (int) $p['id'] . '): '
                    . $this->priceText($p) . ($p['brand'] ? ' — برند ' . $p['brand'] : '');
            }
        }

        $courses = $this->topCourses();
        if (!empty($courses)) {
            $lines[] = '';
            $lines[] = '## دوره‌های آکادمی';
            $lines[] = '';
            foreach ($courses as $c) {
                $price = (int) ($c['price'] ?? 0) > 0 ? priceFormat($c['price']) : 'رایگان';
                $lines[] = '- [' . ($c['title'] ?? '') . '](' . $baseUrl . '/course/' . ($c['slug'] ?? '') . '): '
                    . ($c['teacher'] ? 'مدرس: ' . $c['teacher'] . ' — ' : '') . $price;
            }
        }

        $posts = $this->latestPosts();
        if (!empty($posts)) {
            $lines[] = '';
            $lines[] = '## وبلاگ';
            $lines[] = '';
            foreach ($posts as $post) {
                $lines[] = '- [' . ($post['title'] ?? '') . '](' . $baseUrl . '/blog/' . ($post['slug'] ?? '') . ')'
                    . ($post['excerpt'] ? ': ' . mb_substr(strip_tags($post['excerpt']), 0, 120) : '');
            }
        }

        $phone = $settings['brand_phone'] ?? '';
        $address = $settings['brand_address'] ?? '';
        $hours = $settings['brand_hours'] ?? '';
        $email = $settings['brand_email'] ?? '';
        if ($phone || $address || $hours || $email) {
            $lines[] = '';
            $lines[] = '## تماس';
            $lines[] = '';
            if ($phone) {
                $lines[] = '- تلفن: ' . $phone;
            }
            if ($address) {
                $lines[] = '- آدرس: ' . $address;
            }
            if ($hours) {
                $lines[] = '- ساعت کاری: ' . $hours;
            }
            if ($email) {
                $lines[] = '- ایمیل: ' . $email;
            }
        }

        $social = [];
        foreach (['instagram' => 'اینستاگرام', 'telegram' => 'تلگرام', 'linkedin' => 'لینکدین', 'whatsapp' => 'واتساپ'] as $key => $label) {
            $url = $settings['brand_' . $key] ?? '';
            if ($url !== '' && $url !== '#' && str_starts_with($url, 'http')) {
                $social[] = '- ' . $label . ': ' . $url;
            }
        }
        if (!empty($social)) {
            $lines[] = '';
            $lines[] = '## Optional';
            $lines[] = '';
            foreach ($social as $s) {
                $lines[] = $s;
            }
        }

        $lines[] = '';

        return implode("\n", $lines);
    }

    private function priceText(array $p): string
    {
        $price = (int) ($p['price'] ?? 0);
        return $price > 0 ? 'قیمت: ' . priceFormat($p['price']) : 'قیمت: تماس بگیرید';
    }

    private function topProducts(): array
    {
        try {
            return Database::fetchAll(
                "SELECT id, name, price, brand FROM products WHERE is_active = 1 ORDER BY id DESC LIMIT 5"
            );
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function topCourses(): array
    {
        try {
            return Database::fetchAll(
                "SELECT id, title, slug, teacher, price FROM courses WHERE is_active = 1 ORDER BY id DESC LIMIT 5"
            );
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function latestPosts(): array
    {
        try {
            return Database::fetchAll(
                "SELECT id, title, slug, excerpt FROM blog_posts WHERE is_published = 1 ORDER BY published_at DESC, id DESC LIMIT 5"
            );
        } catch (\Throwable $e) {
            return [];
        }
    }
}
