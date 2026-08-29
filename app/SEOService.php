<?php

declare(strict_types=1);

namespace App;

use App\Settings;
use App\Cache;
use App\Database;

class SEOService
{
    public static function forBlogPost(array $post): array
    {
        $defaultDesc  = Settings::get('meta_description', '');

        return [
            'title'       => $post['meta_title']       ?: $post['title'],
            'description' => $post['meta_description']  ?: ($post['excerpt'] ?: $defaultDesc),
            'canonical'   => $post['canonical_url']     ?: url('/blog/' . $post['slug']),
            'og_title'    => $post['og_title']          ?: ($post['meta_title'] ?: $post['title']),
            'og_desc'     => $post['og_description']    ?: ($post['meta_description'] ?: ($post['excerpt'] ?: $defaultDesc)),
            'og_image'    => $post['og_image']          ?: ($post['image'] ? asset('images/' . $post['image']) : Settings::get('og_image', '/favicon/og-image.png')),
            'og_type'     => 'article',
            'robots'      => $post['robots']            ?? '',
        ];
    }

    public static function forCourse(array $course): array
    {
        $settings = Settings::all();
        $brandName = (string) ($settings['brand_name'] ?? 'موبارو');
        $title = trim((string) ($course['title'] ?? ''));
        $defaultDesc = Settings::get('meta_description', '');

        $description = trim(strip_tags((string) ($course['description'] ?? '')));
        if ($description === '') {
            $description = (string) $defaultDesc;
        }

        $fullTitle = $title !== '' ? $title . ' | ' . $brandName : $brandName;
        $slug = (string) ($course['slug'] ?? $course['id'] ?? '');

        return [
            'title'       => $fullTitle,
            'description' => self::truncateText($description, 160),
            'canonical'   => url('/course/' . $slug),
            'og_title'    => $fullTitle,
            'og_desc'     => self::truncateText($description, 160),
            'og_image'    => !empty($course['image'])
                ? asset('images/' . ltrim((string) $course['image'], '/'))
                : (Settings::get('og_image', '/favicon/og-image.png') ?: '/favicon/og-image.png'),
            'og_type'     => 'website',
            'robots'      => '',
        ];
    }

    public static function forProduct(array $product): array
    {
        $settings = Settings::all();
        $brandName = (string) ($settings['brand_name'] ?? 'موبارو');
        $name = trim((string) ($product['name'] ?? ''));
        $defaultDesc = Settings::get('meta_description', '');
        $id = (string) ($product['id'] ?? 0);
        $slug = trim((string) ($product['slug'] ?? ''));
        $urlPath = $slug !== '' ? '/product/' . $slug : '/product/' . $id;

        $description = trim(strip_tags((string) ($product['description'] ?? '')));
        if ($description === '') {
            $description = (string) $defaultDesc;
        }

        $fullTitle = $name !== '' ? $name . ' | ' . $brandName : $brandName;

        return [
            'title'       => $fullTitle,
            'description' => self::truncateText($description, 160),
            'canonical'   => url($urlPath),
            'og_title'    => $fullTitle,
            'og_desc'     => self::truncateText($description, 160),
            'og_image'    => !empty($product['image'])
                ? asset('images/' . ltrim((string) $product['image'], '/'))
                : (Settings::get('og_image', '/favicon/og-image.png') ?: '/favicon/og-image.png'),
            'og_type'     => 'website',
            'robots'      => '',
        ];
    }

    public static function forPage(string $pageSlug): array
    {
        $cacheKey = 'seo_page_' . $pageSlug . ':' . hostKey();
        return Cache::remember($cacheKey, 86400, function () use ($pageSlug) {
            $row = Database::fetch("SELECT * FROM seo_meta WHERE page_slug = ?", [$pageSlug]) ?? [];
            $settings = Settings::all();

            $canonical = match ($pageSlug) {
                'home'    => url('/'),
                'shop'    => url('/shop'),
                'about'   => url('/about'),
                'contact' => url('/contact'),
                'academy' => url('/academy'),
                'faq'     => url('/faq'),
                'terms'   => url('/terms'),
                'privacy' => url('/privacy'),
                'models'  => url('/models'),
                'blog'    => url('/blog'),
                default   => url('/'),
            };

            return [
                'title'       => ($row['meta_title'] ?? '')       ?: $settings['meta_title'] ?? '',
                'description' => ($row['meta_description'] ?? '')  ?: $settings['meta_description'] ?? '',
                'canonical'   => ($row['canonical_url'] ?? '')     ?: $canonical,
                'og_title'    => ($row['og_title'] ?? '')          ?: (($row['meta_title'] ?? '') ?: $settings['og_title'] ?? ''),
                'og_desc'     => ($row['og_description'] ?? '')    ?: (($row['meta_description'] ?? '') ?: $settings['og_description'] ?? ''),
                'og_image'    => ($row['og_image'] ?? '')          ?: $settings['og_image'] ?? '/favicon/og-image.png',
                'og_type'     => 'website',
                'robots'      => $row['robots']                    ?? $settings['default_robots'] ?? '',
            ];
        }, 'seo');
    }

    private static function truncateText(string $value, int $length): string
    {
        $clean = trim(strip_tags($value));
        if (mb_strlen($clean) <= $length) {
            return $clean;
        }
        return mb_substr($clean, 0, $length) . '…';
    }
}
