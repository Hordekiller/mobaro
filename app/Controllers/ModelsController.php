<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Cache;
use App\Config;
use App\Database;
use App\Settings;
use App\SEOService;

class ModelsController extends BaseController
{
    public function index(): void
    {
        $category = sanitize($_GET['category'] ?? 'all');
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $where = "WHERE is_active = 1";
        $params = [];

        if ($category !== 'all') {
            $where .= " AND category = ?";
            $params[] = $category;
        }

        $cacheKey = 'models_list_' . hash('sha256', serialize([$category, $page]));
        $cached = Cache::remember($cacheKey, Config::get('cache.ttl.page', 600), function () use ($where, $params, $perPage, $offset) {
            $countRow = Database::fetch("SELECT COUNT(*) as cnt FROM hair_models {$where}", $params);
            $totalModels = (int) ($countRow['cnt'] ?? 0);
            $totalPages = max(1, (int) ceil($totalModels / $perPage));
            $models = Database::fetchAll(
                "SELECT * FROM hair_models {$where} ORDER BY id DESC LIMIT ? OFFSET ?",
                array_merge($params, [$perPage, $offset])
            );
            return compact('models', 'totalModels', 'totalPages');
        }, 'models');

        $models = $cached['models'];
        $totalModels = $cached['totalModels'];
        $totalPages = $cached['totalPages'];

        $categories = Cache::remember('models_categories', Config::get('cache.ttl.page', 600), function () {
            return Database::fetchAll(
                "SELECT category, COUNT(*) as cnt FROM hair_models WHERE is_active = 1 GROUP BY category ORDER BY cnt DESC"
            );
        }, 'models');

        $settings = Settings::all();
        $seo = SEOService::forPage('models');

        $this->view('models/index', compact(
            'models',
            'category',
            'page',
            'totalPages',
            'totalModels',
            'categories',
            'settings',
            'seo'
        ));
    }
}
