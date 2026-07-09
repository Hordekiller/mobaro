<?php

class BookingController extends BaseController
{
    public function index(): void
    {
        $services = Database::fetchAll("SELECT * FROM services WHERE is_active = 1");
        $artists = Database::fetchAll("SELECT * FROM artists WHERE is_active = 1");
        $this->view('home/booking', compact('services', 'artists'));
    }

    public function getServices(): void
    {
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
        $date = sanitize($_POST['date'] ?? date('Y-m-d'));
        $serviceId = (int) ($_POST['service_id'] ?? 0);

        $existingAppointments = Database::fetchAll(
            "SELECT appointment_time FROM appointments WHERE appointment_date = ? AND status != 'cancelled'",
            [$date]
        );
        $bookedTimes = array_column($existingAppointments, 'appointment_time');

        $allSlots = ['۱۱:۰۰', '۱۲:۳۰', '۱۴:۰۰', '۱۴:۴۵', '۱۶:۰۰', '۱۷:۳۰', '۱۸:۰۰', '۱۹:۰۰'];
        $availableSlots = array_values(array_diff($allSlots, $bookedTimes));

        $this->json([
            'date' => $date,
            'available_slots' => $availableSlots,
            'booked_slots' => array_values($bookedTimes),
        ]);
    }

    public function confirm(): void
    {
        Auth::requireAuth();
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
