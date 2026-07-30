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
            'og_image'    => $post['og_image']          ?: ($post['image'] ? asset('assets/images/' . $post['image']) : Settings::get('og_image', '/favicon/og-image.png')),
            'robots'      => $post['robots']            ?? '',
        ];
    }

    public static function forPage(string $pageSlug): array
    {
        $cacheKey = 'seo_page_' . $pageSlug;
        return Cache::remember($cacheKey, 86400, function () use ($pageSlug) {
            $row = Database::fetch("SELECT * FROM seo_meta WHERE page_slug = ?", [$pageSlug]);
            $settings = Settings::all();

            $canonical = match ($pageSlug) {
                'home'    => url('/'),
                'shop'    => url('/shop'),
                'about'   => url('/about'),
                'contact' => url('/contact'),
                'academy' => url('/academy'),
                default   => url('/'),
            };

            return [
                'title'       => $row['meta_title']       ?: $settings['meta_title'] ?? '',
                'description' => $row['meta_description']  ?: $settings['meta_description'] ?? '',
                'canonical'   => $row['canonical_url']     ?: $canonical,
                'og_title'    => $row['og_title']          ?: ($row['meta_title'] ?: $settings['og_title'] ?? ''),
                'og_desc'     => $row['og_description']    ?: ($row['meta_description'] ?: $settings['og_description'] ?? ''),
                'og_image'    => $row['og_image']          ?: $settings['og_image'] ?? '/favicon/og-image.png',
                'robots'      => $row['robots']            ?? $settings['default_robots'] ?? '',
            ];
        });
    }
}
