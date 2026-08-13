<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Cache;
use App\Config;
use App\Database;
use App\Settings;
use App\SEOService;
use App\StructuredData;

class FaqController extends BaseController
{
    public function index(): void
    {
        $faqs = Cache::remember('faq_list', Config::get('cache.ttl.page', 600), function () {
            return Database::fetchAll(
                "SELECT question, answer, category
                 FROM faqs
                 WHERE is_active = 1
                 ORDER BY category ASC, sort_order ASC, id ASC"
            );
        }, 'faq');

        $grouped = [];
        foreach ($faqs as $faq) {
            $category = trim((string) ($faq['category'] ?? ''));
            if ($category === '') {
                $category = 'عمومی';
            }
            $grouped[$category][] = $faq;
        }

        $settings = Settings::all();
        $seo = SEOService::forPage('faq');
        $title = 'سؤالات متداول | ' . ($settings['brand_name'] ?? 'موبارو');
        $jsonLd = StructuredData::render(
            StructuredData::organization(),
            StructuredData::faqPage($faqs)
        );

        $this->view('faq/index', compact('grouped', 'settings', 'seo', 'title', 'jsonLd'));
    }
}
