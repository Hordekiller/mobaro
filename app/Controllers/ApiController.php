<?php

class ApiController extends BaseController
{
    public function services(): void
    {
        $services = Database::fetchAll(
            "SELECT s.*, a.id as artist_id, a.name as artist_name
             FROM services s
             INNER JOIN artist_services a_s ON s.id = a_s.service_id
             INNER JOIN artists a ON a_s.artist_id = a.id
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
