<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Cache;
use App\Database;

class SitemapController extends BaseController
{
    public function index(): void
    {
        header('Content-Type: application/xml; charset=utf-8');

        $xml = Cache::remember('sitemap.xml', 3600, function () {
            $urls = array_merge(
                $this->staticPages(),
                $this->blogPosts(),
                $this->products(),
                $this->courses(),
            );
            return $this->buildXml($urls);
        });

        echo $xml;
        exit;
    }

    private function buildXml(array $urls): string
    {
        $lines = [];
        $lines[] = '<?xml version="1.0" encoding="UTF-8"?>';
        $lines[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($urls as $url) {
            $lines[] = '  <url>';
            $lines[] = '    <loc>' . e($url['loc']) . '</loc>';
            if (!empty($url['lastmod'])) {
                $lines[] = '    <lastmod>' . $url['lastmod'] . '</lastmod>';
            }
            if (!empty($url['priority'])) {
                $lines[] = '    <priority>' . $url['priority'] . '</priority>';
            }
            if (!empty($url['changefreq'])) {
                $lines[] = '    <changefreq>' . $url['changefreq'] . '</changefreq>';
            }
            $lines[] = '  </url>';
        }

        $lines[] = '</urlset>';
        return implode("\n", $lines) . "\n";
    }

    private function staticPages(): array
    {
        $staticSlugs = ['home', 'shop', 'blog', 'about', 'contact', 'academy'];
        $extraPages = [
            ['loc' => url('/models'), 'priority' => '0.5', 'changefreq' => 'weekly'],
            ['loc' => url('/booking'), 'priority' => '0.7', 'changefreq' => 'weekly'],
            ['loc' => url('/privacy'), 'priority' => '0.3', 'changefreq' => 'monthly'],
            ['loc' => url('/terms'), 'priority' => '0.3', 'changefreq' => 'monthly'],
        ];

        $priorityMap = [
            'home'    => ['priority' => '1.0', 'changefreq' => 'daily'],
            'shop'    => ['priority' => '0.9', 'changefreq' => 'daily'],
            'blog'    => ['priority' => '0.8', 'changefreq' => 'daily'],
            'about'   => ['priority' => '0.6', 'changefreq' => 'monthly'],
            'contact' => ['priority' => '0.5', 'changefreq' => 'monthly'],
            'academy' => ['priority' => '0.7', 'changefreq' => 'weekly'],
        ];

        $rows = Database::fetchAll(
            "SELECT page_slug, robots, updated_at FROM seo_meta"
        );

        $indexed = [];
        foreach ($rows as $row) {
            $indexed[$row['page_slug']] = $row;
        }

        $result = [];
        foreach ($staticSlugs as $slug) {
            $row = $indexed[$slug] ?? null;

            if ($row && $this->isNoindex($row['robots'])) {
                continue;
            }

            $loc = match ($slug) {
                'home'    => url('/'),
                'shop'    => url('/shop'),
                'blog'    => url('/blog'),
                'about'   => url('/about'),
                'contact' => url('/contact'),
                'academy' => url('/academy'),
                default   => url('/' . $slug),
            };

            $entry = ['loc' => $loc];
            $entry['priority'] = $priorityMap[$slug]['priority'];
            $entry['changefreq'] = $priorityMap[$slug]['changefreq'];

            if ($row && !empty($row['updated_at'])) {
                $entry['lastmod'] = date('Y-m-d', strtotime($row['updated_at']));
            }

            $result[] = $entry;
        }

        return array_merge($result, $extraPages);
    }

    private function blogPosts(): array
    {
        $rows = Database::fetchAll(
            "SELECT slug, robots, updated_at, published_at FROM blog_posts WHERE is_published = 1"
        );

        $result = [];
        foreach ($rows as $row) {
            if ($this->isNoindex($row['robots'])) {
                continue;
            }

            $entry = [
                'loc' => url('/blog/' . $row['slug']),
                'priority' => '0.6',
                'changefreq' => 'weekly',
            ];

            $lastmod = $row['updated_at'] ?? $row['published_at'] ?? '';
            if ($lastmod !== '') {
                $entry['lastmod'] = date('Y-m-d', strtotime($lastmod));
            }

            $result[] = $entry;
        }

        return $result;
    }

    private function products(): array
    {
        $rows = Database::fetchAll(
            "SELECT id, created_at FROM products WHERE is_active = 1"
        );

        $result = [];
        foreach ($rows as $row) {
            $entry = [
                'loc' => url('/product/' . $row['id']),
                'priority' => '0.5',
                'changefreq' => 'weekly',
            ];

            if (!empty($row['created_at'])) {
                $entry['lastmod'] = date('Y-m-d', strtotime($row['created_at']));
            }

            $result[] = $entry;
        }

        return $result;
    }

    private function courses(): array
    {
        $rows = Database::fetchAll(
            "SELECT slug, created_at FROM courses WHERE is_active = 1"
        );

        $result = [];
        foreach ($rows as $row) {
            $entry = [
                'loc' => url('/course/' . $row['slug']),
                'priority' => '0.6',
                'changefreq' => 'weekly',
            ];

            if (!empty($row['created_at'])) {
                $entry['lastmod'] = date('Y-m-d', strtotime($row['created_at']));
            }

            $result[] = $entry;
        }

        return $result;
    }

    private function isNoindex(?string $robots): bool
    {
        if ($robots === null || $robots === '') {
            return false;
        }
        return str_contains($robots, 'noindex');
    }
}
