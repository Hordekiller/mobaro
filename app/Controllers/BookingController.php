<?php

class BookingController extends BaseController
{
    public function index(): void
    {
        $services = Database::fetchAll("SELECT * FROM services WHERE is_active = 1");
        $artists = Database::fetchAll("SELECT * FROM artists WHERE is_active = 1");
        $settings = [];
        $rows = Database::fetchAll("SELECT setting_key, setting_value FROM settings");
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        $this->view('booking/index', compact('services', 'artists', 'settings') + ['artistsJson' => json_encode(array_map(fn($a) => ['id' => $a['id'], 'name' => $a['name'], 'specialty' => $a['specialty'], 'avatar' => $a['avatar'] ?? ''], $artists), JSON_UNESCAPED_UNICODE)]);
    }

    public function getServices(): void
    {
        $this->verifyCsrf();
        $services = Database::fetchAll(
            "SELECT s.*, a.name as artist_name
             FROM services s
             LEFT JOIN artists a ON s.artist_id = a.id
             WHERE s.is_active = 1"
        );
        $this->json(['services' => $services]);
    }

    public function getSlots(): void
    {
        $this->verifyCsrf();
        $date = sanitize($_POST['date'] ?? date('Y-m-d'));
        $serviceId = (int) ($_POST['service_id'] ?? 0);

        $existingAppointments = Database::fetchAll(
            "SELECT appointment_time, service_id FROM appointments WHERE appointment_date = ? AND status != 'cancelled'",
            [$date]
        );
        $bookedTimes = array_column($existingAppointments, 'appointment_time');

        $allSlots = ['۱۰:۰۰', '۱۱:۰۰', '۱۲:۳۰', '۱۴:۰۰', '۱۴:۴۵', '۱۶:۰۰', '۱۷:۳۰', '۱۸:۰۰', '۱۹:۰۰', '۲۰:۰۰'];
        $availableSlots = array_values(array_diff($allSlots, $bookedTimes));

        $this->json([
            'date' => $date,
            'available_slots' => $availableSlots,
            'booked_slots' => array_values($bookedTimes),
        ]);
    }

    public function confirm(): void
    {
        if (!Auth::check()) {
            $this->json(['require_login' => true, 'error' => 'لطفاً ابتدا وارد شوید.'], 401);
            return;
        }

        $this->verifyCsrf();

        $serviceId = (int) ($_POST['service_id'] ?? 0);
        $artistId = (int) ($_POST['artist_id'] ?? 0);
        $date = sanitize($_POST['date'] ?? '');
        $time = sanitize($_POST['time'] ?? '');

        if (!$serviceId || !$date || !$time) {
            $this->json(['error' => 'لطفاً تمام موارد را پر کنید.'], 400);
            return;
        }

        $service = Database::fetch("SELECT * FROM services WHERE id = ?", [$serviceId]);
        if (!$service) {
            $this->json(['error' => 'خدمت مورد نظر یافت نشد.'], 404);
            return;
        }

        $appointmentId = Database::insert('appointments', [
            'user_id' => Auth::id(),
            'service_id' => $serviceId,
            'artist_id' => $artistId ?: null,
            'appointment_date' => $date,
            'appointment_time' => $time,
            'status' => 'confirmed',
        ]);

        $this->json([
            'success' => true,
            'appointment_id' => $appointmentId,
            'message' => 'نوبت شما با موفقیت ثبت شد.',
            'service' => $service['title'],
            'date' => $date,
            'time' => $time,
        ]);
    }
}
