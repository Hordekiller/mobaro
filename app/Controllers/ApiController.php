<?php

class ApiController extends BaseController
{
    public function services(): void
    {
        $services = Database::fetchAll(
            "SELECT s.*, a.name as artist_name
             FROM services s
             LEFT JOIN artists a ON s.artist_id = a.id
             WHERE s.is_active = 1"
        );
        $this->json(['services' => $services]);
    }

    public function artists(): void
    {
        $artists = Database::fetchAll("SELECT * FROM artists WHERE is_active = 1");
        $this->json(['artists' => $artists]);
    }

    public function products(): void
    {
        $products = Database::fetchAll("SELECT * FROM products WHERE is_active = 1");
        $this->json(['products' => $products]);
    }
}
