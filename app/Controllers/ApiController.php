<?php

class ApiController extends BaseController
{
    public function services(): void
    {
        $services = Cache::remember('api_services', 600, function () {
            return Database::fetchAll(
                "SELECT s.*, a.id as artist_id, a.name as artist_name
                 FROM services s
                 INNER JOIN artist_services a_s ON s.id = a_s.service_id
                 INNER JOIN artists a ON a_s.artist_id = a.id
                 WHERE s.is_active = 1"
            );
        }, 'homepage');
        $this->json(['services' => $services]);
    }

    public function artists(): void
    {
        $artists = Cache::remember('api_artists', 600, function () {
            return Database::fetchAll("SELECT * FROM artists WHERE is_active = 1");
        }, 'homepage');
        $this->json(['artists' => $artists]);
    }

    public function products(): void
    {
        $products = Cache::remember('api_products', 600, function () {
            return Database::fetchAll("SELECT * FROM products WHERE is_active = 1");
        }, 'products');
        $this->json(['products' => $products]);
    }

    public function userAddresses(): void
    {
        if (!Auth::check()) {
            $this->json(['error' => 'لطفاً وارد شوید'], 401);
            return;
        }
        $addresses = Database::fetchAll(
            "SELECT id, title, address, city, zip_code, phone, is_default FROM addresses WHERE user_id = ? ORDER BY is_default DESC",
            [Auth::id()]
        );
        $this->json(['addresses' => $addresses]);
    }
}
