<?php

class HomeController extends BaseController
{
    public function index(): void
    {
        $services = Database::fetchAll(
            "SELECT s.*, a.name as artist_name, a.avatar as artist_avatar
             FROM services s
             LEFT JOIN artists a ON s.artist_id = a.id
             WHERE s.is_active = 1
             ORDER BY s.id"
        );

        $artists = Database::fetchAll("SELECT * FROM artists WHERE is_active = 1");

        $hairModels = Database::fetchAll("SELECT * FROM hair_models WHERE is_active = 1");

        $tutorials = Database::fetchAll("SELECT * FROM tutorials WHERE is_active = 1");

        $products = Database::fetchAll("SELECT * FROM products WHERE is_active = 1 ORDER BY id");

        $testimonials = Database::fetchAll("SELECT * FROM testimonials WHERE is_active = 1 ORDER BY id");

        $settings = [];
        $rows = Database::fetchAll("SELECT setting_key, setting_value FROM settings");
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        $this->view('home/index', compact(
            'services', 'artists', 'hairModels', 'tutorials',
            'products', 'testimonials', 'settings'
        ));
    }
}
