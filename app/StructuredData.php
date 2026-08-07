<?php

declare(strict_types=1);

namespace App;

use App\Settings;

class StructuredData
{
    private const CONTEXT = 'https://schema.org';

    /**
     * HairSalon + Organization + WebSite identity block.
     * Rendered on the homepage and inherited by all pages.
     *
     * @return array<string, mixed>
     */
    public static function organization(): array
    {
        $s = Settings::all();
        $name = $s['brand_name'] ?? 'موبارو';
        $url = rtrim((string) url('/'), '/');
        $phone = self::normalizePhone($s['brand_phone'] ?? '');
        $address = $s['brand_address'] ?? '';
        $city = $s['brand_city'] ?? '';
        $hours = $s['brand_hours'] ?? '';

        $sameAs = [];
        foreach (['instagram' => 'brand_instagram', 'telegram' => 'brand_telegram', 'linkedin' => 'brand_linkedin', 'whatsapp' => 'brand_whatsapp'] as $key => $settingKey) {
            $value = trim((string) ($s[$settingKey] ?? ''));
            if ($value !== '' && $value !== '#' && (str_starts_with($value, 'http://') || str_starts_with($value, 'https://'))) {
                $sameAs[] = $value;
            }
        }

        $business = [
            '@context' => self::CONTEXT,
            '@type' => 'HairSalon',
            'name' => $name,
            'url' => $url,
            'telephone' => $phone,
            'priceRange' => '$$',
        ];

        if ($s['brand_email'] ?? '') {
            $business['email'] = $s['brand_email'];
        }
        if ($address !== '') {
            $postal = ['@type' => 'PostalAddress', 'streetAddress' => $address];
            if ($city !== '') {
                $postal['addressLocality'] = $city;
            }
            $postal['addressCountry'] = 'IR';
            $business['address'] = $postal;
        }
        if ($hours !== '') {
            $business['openingHours'] = $hours;
        }
        if (!empty($sameAs)) {
            $business['sameAs'] = array_values(array_unique($sameAs));
        }

        $website = [
            '@context' => self::CONTEXT,
            '@type' => 'WebSite',
            'name' => $name,
            'url' => $url,
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => $url . '/shop?q={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ];

        $org = [
            '@context' => self::CONTEXT,
            '@type' => 'Organization',
            'name' => $name,
            'url' => $url,
            'logo' => asset('images/logo.png'),
        ];
        if (!empty($sameAs)) {
            $org['sameAs'] = array_values(array_unique($sameAs));
        }

        return ['@graph' => [$business, $org, $website]];
    }

    /**
     * BreadcrumbList for interior pages.
     *
     * @param array<int, array{name: string, url: string}> $items position 1 = home
     * @return array<string, mixed>
     */
    public static function breadcrumb(array $items): array
    {
        $list = [];
        foreach ($items as $i => $item) {
            $list[] = [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $item['name'],
                'item' => $item['url'],
            ];
        }

        return [
            '@context' => self::CONTEXT,
            '@type' => 'BreadcrumbList',
            'itemListElement' => $list,
        ];
    }

    /**
     * Product + Offer + AggregateRating (only real reviews).
     *
     * @param array<string, mixed> $p
     * @param array<int, array<string, mixed>> $reviews
     * @return array<string, mixed>
     */
    public static function product(array $p, array $reviews = [], float $avgRating = 0.0, int $reviewCount = 0): array
    {
        $name = (string) ($p['name'] ?? '');
        $image = self::productImage($p);
        $currency = 'IRR';
        $price = (int) ($p['price'] ?? 0);

        $offer = [
            '@type' => 'Offer',
            'priceCurrency' => $currency,
            'price' => (string) $price,
            'availability' => ((int) ($p['stock'] ?? 0)) > 0
                ? 'https://schema.org/InStock'
                : 'https://schema.org/OutOfStock',
            'url' => url('/product/' . (int) $p['id']),
        ];

        if ((int) ($p['old_price'] ?? 0) > 0) {
            $offer['priceValidUntil'] = date('Y-12-31');
            $offer['priceSpecification'] = [
                '@type' => 'PriceSpecification',
                'price' => (string) $price,
                'priceCurrency' => $currency,
                'referenceQuantity' => ['@type' => 'QuantitativeValue', 'value' => 1],
            ];
        }

        $data = [
            '@context' => self::CONTEXT,
            '@type' => 'Product',
            'name' => $name,
            'description' => self::truncate((string) ($p['description'] ?? ''), 200),
            'image' => $image ?: asset('images/logo.png'),
            'offers' => $offer,
        ];

        if (!empty($p['brand'])) {
            $data['brand'] = ['@type' => 'Brand', 'name' => (string) $p['brand']];
        }
        if (!empty($p['category'])) {
            $data['category'] = (string) $p['category'];
        }
        if (!empty($p['sku'])) {
            $data['sku'] = (string) $p['sku'];
        }

        if ($reviewCount > 0 && $avgRating > 0) {
            $data['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => number_format($avgRating, 1, '.', ''),
                'reviewCount' => $reviewCount,
                'bestRating' => '5',
                'worstRating' => '1',
            ];
        }

        $reviewNodes = [];
        foreach (array_slice($reviews, 0, 5) as $r) {
            $rating = (int) ($r['rating'] ?? 0);
            if ($rating <= 0) {
                continue;
            }
            $reviewNodes[] = [
                '@type' => 'Review',
                'reviewRating' => ['@type' => 'Rating', 'ratingValue' => $rating, 'bestRating' => '5'],
                'author' => ['@type' => 'Person', 'name' => (string) ($r['user_name'] ?? $r['name'] ?? 'کاربر')],
                'reviewBody' => self::truncate((string) ($r['comment'] ?? $r['review'] ?? ''), 200),
            ];
        }
        if (!empty($reviewNodes)) {
            $data['review'] = $reviewNodes;
        }

        return $data;
    }

    /**
     * BlogPosting for blog detail pages.
     *
     * @param array<string, mixed> $post
     * @return array<string, mixed>
     */
    public static function blogPosting(array $post): array
    {
        $s = Settings::all();
        $author = (string) ($post['author'] ?? ($s['blog_default_author'] ?? ''));
        $datePublished = !empty($post['published_at'])
            ? date('c', strtotime((string) $post['published_at']))
            : date('c', strtotime((string) ($post['created_at'] ?? 'now')));
        $dateModified = !empty($post['updated_at'])
            ? date('c', strtotime((string) $post['updated_at']))
            : $datePublished;

        $data = [
            '@context' => self::CONTEXT,
            '@type' => 'BlogPosting',
            'headline' => (string) ($post['title'] ?? ''),
            'description' => self::truncate((string) ($post['excerpt'] ?? ''), 200),
            'image' => self::postImage($post),
            'datePublished' => $datePublished,
            'dateModified' => $dateModified,
            'mainEntityOfPage' => url('/blog/' . ($post['slug'] ?? '')),
            'author' => ['@type' => 'Person', 'name' => $author !== '' ? $author : ($s['brand_name'] ?? '')],
            'publisher' => [
                '@type' => 'Organization',
                'name' => $s['brand_name'] ?? 'موبارو',
                'logo' => ['@type' => 'ImageObject', 'url' => asset('images/logo.png')],
            ],
        ];

        if (!empty($post['image_alt'])) {
            $data['image'] = [
                '@type' => 'ImageObject',
                'url' => $data['image'],
                'caption' => (string) $post['image_alt'],
            ];
        }

        return $data;
    }

    /**
     * Course + Offer for academy detail pages.
     *
     * @param array<string, mixed> $course
     * @return array<string, mixed>
     */
    public static function course(array $course): array
    {
        $s = Settings::all();
        $price = (int) ($course['price'] ?? 0);

        $data = [
            '@context' => self::CONTEXT,
            '@type' => 'Course',
            'name' => (string) ($course['title'] ?? ''),
            'description' => self::truncate((string) ($course['description'] ?? ''), 200),
            'url' => url('/course/' . ($course['slug'] ?? '')),
            'provider' => [
                '@type' => 'Organization',
                'name' => $s['brand_name'] ?? 'موبارو',
                'sameAs' => rtrim((string) url('/'), '/'),
            ],
        ];

        if (!empty($course['image'])) {
            $data['image'] = asset('images/' . ltrim((string) $course['image'], '/'));
        }
        if (!empty($course['teacher'])) {
            $data['instructor'] = ['@type' => 'Person', 'name' => (string) $course['teacher']];
        }
        if (!empty($course['duration'])) {
            $data['timeRequired'] = 'PT' . (int) preg_replace('/\D+/', '', (string) $course['duration']) . 'M';
        }
        if (!empty($course['level'])) {
            $data['coursePrerequisites'] = (string) $course['level'];
        }
        if (!empty($course['rating'])) {
            $data['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => number_format((float) $course['rating'], 1, '.', ''),
                'bestRating' => '5',
                'ratingCount' => max(1, (int) ($course['students'] ?? 1)),
            ];
        }

        $data['offers'] = [
            '@type' => 'Offer',
            'priceCurrency' => 'IRR',
            'price' => (string) $price,
            'availability' => 'https://schema.org/InStock',
            'url' => url('/course/' . ($course['slug'] ?? '')),
        ];

        return $data;
    }

    /**
     * WebPage / Service fallback for static pages.
     *
     * @param array<string, mixed> $page
     * @return array<string, mixed>
     */
    public static function webPage(array $page): array
    {
        $name = (string) ($page['name'] ?? '');
        $data = [
            '@context' => self::CONTEXT,
            '@type' => 'WebPage',
            'name' => $name,
            'url' => url((string) ($page['path'] ?? '/')),
        ];
        if (!empty($page['description'])) {
            $data['description'] = (string) $page['description'];
        }
        return $data;
    }

    /**
     * @return string[]
     */
    public static function render(array ...$blocks): array
    {
        return array_values(array_filter($blocks));
    }

    private static function productImage(array $p): string
    {
        $image = (string) ($p['image'] ?? '');
        if ($image === '') {
            return '';
        }
        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            return $image;
        }
        if (str_starts_with($image, '/')) {
            return url($image);
        }
        return asset('images/' . ltrim($image, '/'));
    }

    private static function postImage(array $post): string
    {
        $image = (string) ($post['image'] ?? ($post['og_image'] ?? ''));
        if ($image === '') {
            return asset('images/logo.png');
        }
        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            return $image;
        }
        if (str_starts_with($image, '/')) {
            return url($image);
        }
        return asset('images/' . ltrim($image, '/'));
    }

    private static function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if ($digits === '' || $digits === null) {
            return '';
        }
        return $digits;
    }

    private static function truncate(string $value, int $length): string
    {
        if ($value === '') {
            return '';
        }
        $clean = trim(strip_tags($value));
        if (mb_strlen($clean) <= $length) {
            return $clean;
        }
        return mb_substr($clean, 0, $length) . '…';
    }
}
