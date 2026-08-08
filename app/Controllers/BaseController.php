<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Auth;
use App\Database;
use App\Settings;
use App\StructuredData;

class BaseController
{
    protected const CONTENT_TYPE_JSON = 'Content-Type: application/json';
    private static array $protectedVars = ['view', 'data', 'hideFooter', 'this'];

    protected function requireAdmin(): void
    {
        Auth::requireAdmin();
    }

    /**
     * Active services for the booking flow. Each service appears exactly once
     * (GROUP BY s.id) with its full artist mapping so the frontend can:
     *   - show every active service (LEFT JOIN — services with no artist are
     *     available for all artists),
     *   - avoid duplicate cards for services shared by several artists.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function bookingServicesData(): array
    {
        $rows = Database::fetchAll(
            "SELECT s.*,
                    GROUP_CONCAT(DISTINCT a.id ORDER BY a.id) AS artist_ids,
                    GROUP_CONCAT(DISTINCT a.name ORDER BY a.id SEPARATOR '||') AS artist_names,
                    SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT a.avatar ORDER BY a.id), '|', 1) AS artist_avatar,
                    EXISTS(SELECT 1 FROM service_hair_prices shp WHERE shp.service_id = s.id AND shp.is_active = 1) AS requires_hair_length
             FROM services s
             LEFT JOIN artist_services a_s ON s.id = a_s.service_id
             LEFT JOIN artists a ON a_s.artist_id = a.id
             WHERE s.is_active = 1
             GROUP BY s.id
             ORDER BY s.id"
        );

        return array_map(static function (array $row): array {
            $artistIds = array_values(array_filter(array_map('intval', explode(',', (string) ($row['artist_ids'] ?? '')))));
            $artistNames = array_values(array_filter(array_map('trim', explode('||', (string) ($row['artist_names'] ?? '')))));
            return [
                'id' => (int) $row['id'],
                'title' => (string) $row['title'],
                'description' => (string) ($row['description'] ?? ''),
                'price' => (int) $row['price'],
                'image' => (string) ($row['image'] ?? ''),
                'category' => (string) ($row['category'] ?? ''),
                'duration' => (string) ($row['duration'] ?? ''),
                'rating' => (float) ($row['rating'] ?? 0),
                'is_active' => (int) ($row['is_active'] ?? 1),
                'artist_ids' => $artistIds,
                'artist_names' => $artistNames,
                'artist_id' => $artistIds[0] ?? null,
                'artist_name' => $artistNames[0] ?? '',
                'artist_avatar' => (string) ($row['artist_avatar'] ?? ''),
                'requires_hair_length' => (int) ($row['requires_hair_length'] ?? 0) === 1,
            ];
        }, $rows);
    }

    protected function view(string $view, array $data = []): void
    {
        $hideFooter = $data['hideFooter'] ?? false;
        if (!isset($data['settings'])) {
            $data['settings'] = Settings::all();
        }
        if (!isset($data['jsonLd']) && !$this->skipDefaultSchema($view)) {
            $data['jsonLd'] = $this->defaultSchema($data);
        }
        $safe = array_diff_key($data, array_flip(self::$protectedVars));
        extract($safe);
        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/' . $view . '.php';
        if (empty($hideFooter)) {
            require_once __DIR__ . '/../views/layouts/footer.php';
        }
    }

    /**
     * Views that must never receive the default public schema: the admin and
     * user dashboard, the auth flow (login/register/verify-otp), and public
     * routes that are auth-gated or blocked for crawlers in robots.txt
     * (watch/certificate/cart/wishlist). These must not be described as
     * public WebPage entities.
     */
    private function skipDefaultSchema(string $view): bool
    {
        return str_starts_with($view, 'admin/')
            || str_starts_with($view, 'dashboard/')
            || str_starts_with($view, 'auth/')
            || in_array($view, [
                'academy/watch',
                'academy/certificate',
                'home/cart',
                'shop/wishlist',
            ], true);
    }

    /**
     * Default schema.org markup for public pages that do not pass an explicit
     * $jsonLd (listings, about, contact, booking, legal pages...). Mirrors the
     * <title> fallback chain so the WebPage name always matches visible text.
     *
     * @param array<string, mixed> $data
     * @return array<int, array<string, mixed>>
     */
    private function defaultSchema(array $data): array
    {
        $settings = $data['settings'] ?? Settings::all();
        $brandName = (string) ($settings['brand_name'] ?? 'موبارو');
        $seo = is_array($data['seo'] ?? null) ? $data['seo'] : [];

        $pageName = (string) ($seo['title'] ?? '');
        if ($pageName === '') {
            $pageName = (string) ($data['pageTitle'] ?? '');
        }
        if ($pageName === '') {
            $pageName = (string) ($data['title'] ?? '');
        }
        if ($pageName === '') {
            $pageName = (string) ($settings['meta_title'] ?? '');
        }
        if ($pageName === '') {
            $pageName = $brandName;
        }

        $path = '/';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        if (is_string($uri) && $uri !== '') {
            $path = (string) parse_url($uri, PHP_URL_PATH);
            if ($path === '') {
                $path = '/';
            }
        }

        $blocks = [StructuredData::organization()];

        if ($path !== '/') {
            $blocks[] = StructuredData::breadcrumb([
                ['name' => $brandName, 'url' => url('/')],
                ['name' => $pageName, 'url' => url($path)],
            ]);
            $blocks[] = StructuredData::webPage([
                'name' => $pageName,
                'path' => $path,
                'description' => (string) ($seo['description'] ?? ''),
            ]);
        }

        return StructuredData::render(...$blocks);
    }

    protected function viewRaw(string $view, array $data = []): void
    {
        $safe = array_diff_key($data, array_flip(self::$protectedVars));
        extract($safe);
        require_once __DIR__ . '/../views/' . $view . '.php';
    }

    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header(self::CONTENT_TYPE_JSON . '; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function validate(array $data, array $rules): array
    {
        $errors = [];
        foreach ($rules as $field => $ruleSet) {
            $ruleList = explode('|', $ruleSet);
            foreach ($ruleList as $rule) {
                $error = $this->validateField($field, $rule, $data);
                if ($error) {
                    $errors[$field] = $error;
                }
            }
        }
        return $errors;
    }

    private function validateField(string $field, string $rule, array $data): ?string
    {
        if ($rule === 'required' && (!isset($data[$field]) || $data[$field] === '')) {
            return 'این فیلد الزامی است.';
        }

        if (str_starts_with($rule, 'min:') && isset($data[$field])) {
            $min = (int) explode(':', $rule)[1];
            if (mb_strlen($data[$field]) < $min) {
                return "حداقل {$min} کاراکتر وارد کنید.";
            }
        }

        if (str_starts_with($rule, 'max:') && isset($data[$field])) {
            $max = (int) explode(':', $rule)[1];
            if (mb_strlen($data[$field]) > $max) {
                return "حداکثر {$max} کاراکتر مجاز است.";
            }
        }

        return null;
    }

    protected function redirectWithErrors(string $url, array $errors): void
    {
        flashErrors($errors);
        $_SESSION['_old'] = $_POST;
        redirect($url);
    }

    protected function verifyCsrf(): void
    {
        $token = $_POST['_csrf'] ?? '';
        if (!verifyCsrf($token)) {
            http_response_code(419);
            echo json_encode(['error' => 'درخواست نامعتبر (CSRF).'], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
}
