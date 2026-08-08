<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use App\Services\SitemapNotifier;
use App\Settings;

/**
 * Cache-on-Demand sitemap generator (Rank Math / Yoast style).
 *
 * /sitemap.xml is a <sitemapindex> pointing at per-type child sitemaps:
 *
 *   - /sitemap-pages.xml    static pages (home, shop, blog, about, ...)
 *   - /sitemap-blog.xml     published blog posts (+ image:image)
 *   - /sitemap-products.xml products (+ image:image for cover & gallery)
 *   - /sitemap-courses.xml  courses (+ image:image and video blocks)
 *   - /sitemap-news.xml     Google News sitemap (recent posts, opt-in)
 *
 * Every file is written to public/ and, as long as it exists, the web server
 * serves it directly (see public/.htaccess `RewriteCond %{REQUEST_FILENAME}
 * !-f`), so no PHP runs on cache hits. Every content save in the admin panel
 * deletes the files (see AdminController::clearCache()) and the next request
 * rebuilds them all in one pass. When IndexNow is enabled, rebuilding also
 * fires a best-effort, throttled IndexNow ping (see SitemapNotifier).
 *
 * Google/Bing ignore <priority> and <changefreq>, so they are intentionally
 * not emitted. Only <loc>, <lastmod> (W3C datetime with timezone), Google
 * image-sitemap blocks (xmlns:image) and video-sitemap blocks (xmlns:video)
 * are generated. An XSL stylesheet (public/sitemap.xsl) renders the files
 * nicely in a browser, like Yoast/Rank Math do.
 */
class SitemapController extends BaseController
{
    /**
     * Known invalid/test/demo slugs that must not be indexed. Extend this
     * array with any future junk — rows stay untouched in the database, they
     * are only excluded from the generated XML.
     */
    private const BLACKLIST = [
        'dsffds',
        'sdad',
        'sda',
        'fit2',
        'fit4',
        'item-b12f483f',
        'bugfixtest',
        'final-test-1785127443',
    ];

    /** Child sitemap name => generator method. */
    private const SECTION_METHODS = [
        'pages'    => 'staticPages',
        'blog'     => 'blogPosts',
        'products' => 'products',
        'courses'  => 'courses',
    ];

    private const MAX_URLS_PER_SITEMAP = 50000;

    private static array $lastmodColumns = [];

    public function index(): void
    {
        header('Content-Type: application/xml; charset=utf-8');

        $sections = array_keys(self::SECTION_METHODS);
        if ($this->newsEnabled()) {
            $sections[] = 'news';
        }

        $entries = [];
        foreach ($sections as $name) {
            $built = $this->buildSection($name);
            @file_put_contents($this->sitemapFilePath('sitemap-' . $name . '.xml'), $built['xml']);
            $entries[] = ['loc' => url('/sitemap-' . $name . '.xml'), 'lastmod' => $built['lastmod']];
        }

        $xml = $this->buildIndexXml($entries);
        @file_put_contents($this->sitemapFilePath('sitemap.xml'), $xml);

        if ($entries !== []) {
            SitemapNotifier::notify($this->collectLocs($sections));
        }

        echo $xml;
        exit;
    }

    public function section(string $name): void
    {
        header('Content-Type: application/xml; charset=utf-8');

        $built = $this->buildSection($name);
        @file_put_contents($this->sitemapFilePath('sitemap-' . $name . '.xml'), $built['xml']);

        echo $built['xml'];
        exit;
    }

    /**
     * Flat merged urlset. Not served anymore (the index + children are) but
     * kept for the test suite and as a quick all-in-one dump.
     */
    public function generate(): string
    {
        $urls = array_merge(
            $this->staticPages(),
            $this->blogPosts(),
            $this->products(),
            $this->courses(),
        );

        return $this->buildXml($urls);
    }

    private function sitemapFilePath(string $file): string
    {
        return __DIR__ . '/../../public/' . $file;
    }

    /**
     * Builds one child sitemap and returns its XML plus the most recent
     * lastmod found inside (used as the <sitemap><lastmod> in the index).
     *
     * @return array{xml: string, lastmod: string}
     */
    private function buildSection(string $name): array
    {
        if ($name === 'news') {
            return ['xml' => $this->newsXml(), 'lastmod' => $this->lastmod(date('Y-m-d H:i:s'))];
        }

        if (!isset(self::SECTION_METHODS[$name])) {
            return ['xml' => $this->buildXml([]), 'lastmod' => $this->lastmod(date('Y-m-d H:i:s'))];
        }

        $urls = $this->{self::SECTION_METHODS[$name]}();

        $max = 0;
        foreach ($urls as $url) {
            if (!empty($url['lastmod'])) {
                $ts = strtotime($url['lastmod']);
                if ($ts !== false && $ts > $max) {
                    $max = $ts;
                }
            }
        }
        $lastmod = $this->lastmod($max > 0 ? date('Y-m-d H:i:s', $max) : date('Y-m-d H:i:s'));

        return ['xml' => $this->buildXml($urls), 'lastmod' => $lastmod];
    }

    /**
     * All <loc> values across the given child sections, deduplicated. Used as
     * the IndexNow payload so changed URLs are announced to Bing/Yandex.
     *
     * @param list<string> $sections
     * @return list<string>
     */
    private function collectLocs(array $sections): array
    {
        $locs = [];
        foreach ($sections as $name) {
            $built = $this->buildSection($name);
            if (preg_match_all('/<loc>([^<]+)<\/loc>/', $built['xml'], $matches)) {
                foreach ($matches[1] as $loc) {
                    $locs[] = $loc;
                }
            }
        }

        return array_values(array_unique($locs));
    }

    private function buildIndexXml(array $entries): string
    {
        $lines = [];
        $lines[] = '<?xml version="1.0" encoding="UTF-8"?>';
        $lines[] = '<?xml-stylesheet type="text/xsl" href="' . e(url('/sitemap.xsl')) . '"?>';
        $lines[] = '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($entries as $entry) {
            $lines[] = '  <sitemap>';
            $lines[] = '    <loc>' . e($entry['loc']) . '</loc>';
            $lines[] = '    <lastmod>' . $entry['lastmod'] . '</lastmod>';
            $lines[] = '  </sitemap>';
        }

        $lines[] = '</sitemapindex>';
        return implode("\n", $lines) . "\n";
    }

    private function buildXml(array $urls): string
    {
        $lines = [];
        $lines[] = '<?xml version="1.0" encoding="UTF-8"?>';
        $lines[] = '<?xml-stylesheet type="text/xsl" href="' . e(url('/sitemap.xsl')) . '"?>';
        $lines[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
        $lines[] = '        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"';
        $lines[] = '        xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">';

        $count = 0;
        foreach ($urls as $url) {
            if ($count >= self::MAX_URLS_PER_SITEMAP) {
                break;
            }
            $count++;

            $lines[] = '  <url>';
            $lines[] = '    <loc>' . e($url['loc']) . '</loc>';
            if (!empty($url['lastmod'])) {
                $lines[] = '    <lastmod>' . $url['lastmod'] . '</lastmod>';
            }
            foreach ($url['images'] ?? [] as $image) {
                $lines[] = '    <image:image>';
                $lines[] = '      <image:loc>' . e($image) . '</image:loc>';
                $lines[] = '    </image:image>';
            }
            if (!empty($url['video'])) {
                $video = $url['video'];
                $lines[] = '    <video:video>';
                $lines[] = '      <video:thumbnail_loc>' . e($video['thumbnail_loc']) . '</video:thumbnail_loc>';
                $lines[] = '      <video:title>' . e($video['title']) . '</video:title>';
                $lines[] = '      <video:description>' . e($video['description']) . '</video:description>';
                if (isset($video['content_loc'])) {
                    $lines[] = '      <video:content_loc>' . e($video['content_loc']) . '</video:content_loc>';
                }
                if (isset($video['player_loc'])) {
                    $lines[] = '      <video:player_loc allow_embed="yes">' . e($video['player_loc']) . '</video:player_loc>';
                }
                $lines[] = '    </video:video>';
            }
            $lines[] = '  </url>';
        }

        $lines[] = '</urlset>';
        return implode("\n", $lines) . "\n";
    }

    private function newsXml(): string
    {
        if (!$this->newsEnabled()) {
            return $this->buildXml([]);
        }

        $exclude = $this->excludeClause('slug');
        $rows = Database::fetchAll(
            "SELECT slug, title, robots, published_at
             FROM blog_posts
             WHERE is_published = 1
               AND published_at IS NOT NULL
               AND published_at >= DATE_SUB(NOW(), INTERVAL 48 HOUR)" . $exclude['sql'] . "
             ORDER BY published_at DESC, id DESC
             LIMIT 1000",
            $exclude['params']
        );

        $brand = Settings::get('brand_name', '') ?: 'Mobaro';

        $lines = [];
        $lines[] = '<?xml version="1.0" encoding="UTF-8"?>';
        $lines[] = '<?xml-stylesheet type="text/xsl" href="' . e(url('/sitemap.xsl')) . '"?>';
        $lines[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
        $lines[] = '        xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">';

        foreach ($rows as $row) {
            if ($this->isNoindex($row['robots'])) {
                continue;
            }

            $date = substr((string) $row['published_at'], 0, 10);

            $lines[] = '  <url>';
            $lines[] = '    <loc>' . e(url('/blog/' . $row['slug'])) . '</loc>';
            $lines[] = '    <news:news>';
            $lines[] = '      <news:publication>';
            $lines[] = '        <news:name>' . e(mb_substr($brand, 0, 50)) . '</news:name>';
            $lines[] = '        <news:language>fa</news:language>';
            $lines[] = '      </news:publication>';
            $lines[] = '      <news:publication_date>' . e($date) . '</news:publication_date>';
            $lines[] = '      <news:title>' . e(mb_substr($row['title'], 0, 100)) . '</news:title>';
            $lines[] = '    </news:news>';
            $lines[] = '  </url>';
        }

        $lines[] = '</urlset>';
        return implode("\n", $lines) . "\n";
    }

    private function newsEnabled(): bool
    {
        return (bool) Settings::get('sitemap_news_enabled', false);
    }

    private function staticPages(): array
    {
        $staticSlugs = ['home', 'shop', 'blog', 'about', 'contact', 'academy'];
        $extraPages = [
            ['loc' => url('/models')],
            ['loc' => url('/booking')],
            ['loc' => url('/privacy')],
            ['loc' => url('/terms')],
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
            if ($row && !empty($row['updated_at'])) {
                $entry['lastmod'] = $this->lastmod($row['updated_at']);
            }

            $result[] = $entry;
        }

        return array_merge($result, $extraPages);
    }

    private function blogPosts(): array
    {
        $exclude = $this->excludeClause('slug');

        $rows = Database::fetchAll(
            "SELECT slug, image, robots, updated_at, published_at
             FROM blog_posts
             WHERE is_published = 1" . $exclude['sql'],
            $exclude['params']
        );

        $result = [];
        foreach ($rows as $row) {
            if ($this->isNoindex($row['robots'])) {
                continue;
            }

            $entry = ['loc' => url('/blog/' . $row['slug'])];

            $lastmod = $row['updated_at'] ?? $row['published_at'] ?? '';
            if ($lastmod !== '') {
                $entry['lastmod'] = $this->lastmod($lastmod);
            }

            $image = $this->absoluteImage((string) ($row['image'] ?? ''));
            if ($image !== '') {
                $entry['images'] = [$image];
            }

            $result[] = $entry;
        }

        return $result;
    }

    private function products(): array
    {
        $lastmod = $this->lastmodExpr('products');

        $rows = Database::fetchAll(
            "SELECT id, image, {$lastmod} AS lastmod_col
             FROM products
             WHERE is_active = 1
             ORDER BY id DESC"
        );

        $gallery = [];
        foreach (Database::fetchAll("SELECT product_id, image FROM product_images ORDER BY sort_order, id") as $g) {
            $gallery[$g['product_id']][] = $g['image'];
        }

        $result = [];
        foreach ($rows as $row) {
            $entry = ['loc' => url('/product/' . $row['id'])];

            if (!empty($row['lastmod_col'])) {
                $entry['lastmod'] = $this->lastmod($row['lastmod_col']);
            }

            $images = $this->productImages((string) $row['image'], $gallery[$row['id']] ?? []);
            if ($images !== []) {
                $entry['images'] = $images;
            }

            $result[] = $entry;
        }

        return $result;
    }

    private function courses(): array
    {
        $exclude = $this->excludeClause('slug');
        $lastmod = $this->lastmodExpr('courses');

        $rows = Database::fetchAll(
            "SELECT slug, title, description, image, video_url, video_type, {$lastmod} AS lastmod_col
             FROM courses
             WHERE is_active = 1" . $exclude['sql'] . "
             ORDER BY id DESC",
            $exclude['params']
        );

        $result = [];
        foreach ($rows as $row) {
            $entry = ['loc' => url('/course/' . $row['slug'])];

            if (!empty($row['lastmod_col'])) {
                $entry['lastmod'] = $this->lastmod($row['lastmod_col']);
            }

            $image = $this->absoluteImage((string) ($row['image'] ?? ''));
            if ($image !== '') {
                $entry['images'] = [$image];
            }

            $video = $this->videoBlock($row);
            if ($video !== null) {
                $entry['video'] = $video;
            }

            $result[] = $entry;
        }

        return $result;
    }

    /**
     * Builds a Google video-sitemap block for a course that has a video.
     * youtube/aparat are emitted as <video:player_loc> (their embed/watch
     * pages); local uploads as <video:content_loc>. Required tags follow
     * Google's video sitemap spec (thumbnail_loc, title ≤100 chars,
     * description ≤2048 chars). Returns null when the course has no video or
     * its video URL cannot be resolved.
     *
     * @param array<string, mixed> $course
     * @return array<string, string>|null
     */
    private function videoBlock(array $course): ?array
    {
        $url = trim((string) ($course['video_url'] ?? ''));
        if ($url === '') {
            return null;
        }

        $type = $course['video_type'] ?? 'upload';

        $thumbnail = $this->absoluteImage((string) ($course['image'] ?? ''));
        if ($thumbnail === '') {
            $thumbnail = asset('images/logo.png');
        }

        $title = trim((string) ($course['title'] ?? ''));
        $description = trim((string) ($course['description'] ?? ''));
        if ($title === '') {
            $title = (string) ($course['slug'] ?? 'video');
        }
        if ($description === '') {
            $description = $title;
        }

        $block = [
            'thumbnail_loc' => $thumbnail,
            'title' => mb_substr($title, 0, 100),
            'description' => mb_substr($description, 0, 2048),
        ];

        if ($type === 'youtube') {
            $id = getYoutubeId($url);
            if ($id === '') {
                return null;
            }
            $block['player_loc'] = 'https://www.youtube.com/watch?v=' . $id;
        } elseif ($type === 'aparat') {
            $hash = getAparatHash($url);
            if ($hash === '') {
                return null;
            }
            $block['player_loc'] = 'https://www.aparat.com/v/' . $hash;
        } elseif (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            $block['player_loc'] = $url;
        } else {
            $path = ltrim($url, '/');
            if (!is_file(__DIR__ . '/../../public/' . $path)) {
                return null;
            }
            $block['content_loc'] = $this->absoluteMediaUrl($url);
        }

        return $block;
    }

    /**
     * Builds the exclusion WHERE fragment for a slug column. This project has
     * no ORM/query builder (App\Database is a raw-SQL wrapper), so this is the
     * hand-written equivalent of whereNotIn() plus the always-on safety
     * filters. Rows are never deleted — they are only filtered out of the XML.
     *
     * @return array{sql: string, params: list<string>}
     */
    private function excludeClause(string $column): array
    {
        $conditions = [
            "{$column} IS NOT NULL",
            "{$column} <> ''",
            "{$column} NOT REGEXP '^-?[0-9]+$'",
            "{$column} NOT LIKE 'test%'",
            "{$column} NOT LIKE '%-test'",
        ];

        $params = [];
        if (self::BLACKLIST !== []) {
            $placeholders = implode(',', array_fill(0, count(self::BLACKLIST), '?'));
            $conditions[] = "{$column} NOT IN ({$placeholders})";
            foreach (self::BLACKLIST as $slug) {
                $params[] = $slug;
            }
        }

        return [
            'sql' => ' AND ' . implode(' AND ', $conditions),
            'params' => $params,
        ];
    }

    /**
     * lastmod SQL expression for a table. Only uses COALESCE(updated_at,
     * created_at) when the column actually exists — COALESCE would otherwise
     * error with "Unknown column" on tables that only have created_at.
     */
    private function lastmodExpr(string $table): string
    {
        if (!isset(self::$lastmodColumns[$table])) {
            $hasUpdatedAt = (bool) Database::fetch("SHOW COLUMNS FROM `{$table}` LIKE 'updated_at'");
            self::$lastmodColumns[$table] = $hasUpdatedAt ? 'COALESCE(updated_at, created_at)' : 'created_at';
        }

        return self::$lastmodColumns[$table];
    }

    /**
     * Absolute image URLs for the image-sitemap: the product cover
     * (products.image) plus its gallery (product_images.image), deduplicated.
     * Google caps image-sitemap entries at 1000 images per URL.
     *
     * @param list<string> $gallery
     * @return list<string>
     */
    private function productImages(string $cover, array $gallery): array
    {
        $images = [];
        $seen = [];

        foreach (array_merge([$cover], $gallery) as $image) {
            $absolute = $this->absoluteImage($image);
            if ($absolute === '' || isset($seen[$absolute])) {
                continue;
            }
            $seen[$absolute] = true;
            $images[] = $absolute;
        }

        return array_slice($images, 0, 1000);
    }

    /**
     * Absolute URL for a locally stored image, or '' when the file does not
     * exist on this server. Only real, reachable images are listed in the
     * sitemap — a missing file (deleted upload, bad filename in the DB) would
     * otherwise show up as a 404 in Google Search Console. External http(s)
     * URLs are returned untouched.
     */
    private function absoluteImage(string $image): string
    {
        $image = trim($image);
        if ($image === '') {
            return '';
        }
        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            return $image;
        }
        if (str_starts_with($image, '/')) {
            $path = ltrim($image, '/');
            return is_file(__DIR__ . '/../../public/' . $path) ? url($image) : '';
        }
        $name = ltrim($image, '/');
        return is_file(__DIR__ . '/../../public/assets/images/' . $name) ? asset('images/' . $name) : '';
    }

    /**
     * lastmod in W3C datetime format with timezone (e.g.
     * 2026-07-14T12:30:00+03:30). Accepted by the sitemap protocol's
     * tLastmod union and gives Google the most precise recrawl hint.
     */
    private function lastmod(string $value): string
    {
        return date('Y-m-d\TH:i:sP', strtotime($value));
    }

    /**
     * Absolute URL for a locally stored media file (e.g. uploaded course
     * videos under /assets/uploads/videos/).
     */
    private function absoluteMediaUrl(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            return '';
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        return url('/' . ltrim($path, '/'));
    }

    private function isNoindex(?string $robots): bool
    {
        if ($robots === null || $robots === '') {
            return false;
        }
        return str_contains($robots, 'noindex');
    }
}
