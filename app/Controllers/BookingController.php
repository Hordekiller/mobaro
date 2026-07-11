<?php

class BookingController extends BaseController
{
    private function getFormData(): array
    {
        return Cache::remember('booking_form_data', 600, function () {
            $services = Database::fetchAll("SELECT * FROM services WHERE is_active = 1");
            $artists = Database::fetchAll("SELECT * FROM artists WHERE is_active = 1");
            return compact('services', 'artists');
        }, 'booking');
    }

    public function index(): void
    {
        $formData = $this->getFormData();
        $services = $formData['services'];
        $artists = $formData['artists'];
        $settings = Settings::all();

        $captchaEnabled = Captcha::isEnabled('booking');
        $captchaQuestion = $captchaEnabled ? Captcha::store() : '';

        $this->view('booking/index', compact('services', 'artists', 'settings', 'captchaQuestion', 'captchaEnabled') + [
            'artistsJson' => json_encode(array_map(fn($a) => [
                'id' => $a['id'],
                'name' => $a['name'],
                'specialty' => $a['specialty'],
                'avatar' => $a['avatar'] ?? '',
                'working_hours' => $a['working_hours'] ?? '۹ صبح - ۸ شب',
                'bio' => $a['bio'] ?? '',
            ], $artists), JSON_UNESCAPED_UNICODE),
        ]);
    }

    public function getServices(): void
    {
        $this->verifyCsrf();
        $services = Cache::remember('booking_services', 600, function () {
            return Database::fetchAll(
                "SELECT s.*, a.id as artist_id, a.name as artist_name
                 FROM services s
                 INNER JOIN artist_services a_s ON s.id = a_s.service_id
                 INNER JOIN artists a ON a_s.artist_id = a.id
                 WHERE s.is_active = 1"
            );
        }, 'booking');
        $this->json(['services' => $services]);
    }

    public function getSlots(): void
    {
        $this->verifyCsrf();
        $date = sanitize($_POST['date'] ?? date('Y-m-d'));
        $serviceId = (int) ($_POST['service_id'] ?? 0);
        $artistId = (int) ($_POST['artist_id'] ?? 0);

        $params = [$date];
        $artistFilter = '';
        if ($artistId) {
            $artistFilter = ' AND artist_id = ?';
            $params[] = $artistId;
        }
        $existingAppointments = Database::fetchAll(
            "SELECT appointment_time FROM appointments WHERE appointment_date = ? AND status != 'cancelled'{$artistFilter}",
            $params
        );
        $bookedTimes = array_map(function ($t) {
            return str_replace(['0','1','2','3','4','5','6','7','8','9'], ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'], $t);
        }, array_column($existingAppointments, 'appointment_time'));

        $allSlots = ['۱۰:۰۰', '۱۱:۰۰', '۱۲:۳۰', '۱۴:۰۰', '۱۴:۴۵', '۱۶:۰۰', '۱۷:۳۰', '۱۸:۰۰', '۱۹:۰۰', '۲۰:۰۰'];

        if ($date === (new DateTime('now', new DateTimeZone('Asia/Tehran')))->format('Y-m-d')) {
            $tehranNow = new DateTime('now', new DateTimeZone('Asia/Tehran'));
            $currentMinutes = (int) $tehranNow->format('G') * 60 + (int) $tehranNow->format('i');
            $allSlots = array_values(array_filter($allSlots, function ($slot) use ($currentMinutes) {
                $e = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
                $en = str_replace($e, range(0, 9), $slot);
                $parts = explode(':', $en);
                $slotMinutes = (int) ($parts[0] ?? 0) * 60 + (int) ($parts[1] ?? 0);
                return $slotMinutes > $currentMinutes;
            }));
        }

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

        if (Captcha::isEnabled('booking') && !Captcha::verify($_POST['captcha'] ?? '')) {
            $this->json(['error' => 'پاسخ کپچا اشتباه است.', 'captcha_error' => true], 400);
            return;
        }

        $serviceId = (int) ($_POST['service_id'] ?? 0);
        $artistId = (int) ($_POST['artist_id'] ?? 0);
        $date = sanitize($_POST['date'] ?? '');
        $time = sanitize($_POST['time'] ?? '');

        if (!$serviceId || !$date || !$time) {
            $this->json(['error' => 'لطفاً تمام موارد را پر کنید.'], 400);
            return;
        }

        $tehranNow = new DateTime('now', new DateTimeZone('Asia/Tehran'));
        $selectedDate = DateTime::createFromFormat('Y-m-d', $date, new DateTimeZone('Asia/Tehran'));
        if (!$selectedDate || $selectedDate < $tehranNow->setTime(0, 0, 0)) {
            $this->json(['error' => 'تاریخ انتخاب‌شده نامعتبر است.'], 400);
            return;
        }
        if ($date === $tehranNow->format('Y-m-d')) {
            $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            $timeNum = str_replace($persian, range(0, 9), $time);
            $parts = explode(':', $timeNum);
            $slotMinutes = (int) ($parts[0] ?? 0) * 60 + (int) ($parts[1] ?? 0);
            $currentMinutes = (int) $tehranNow->format('G') * 60 + (int) $tehranNow->format('i');
            if ($slotMinutes <= $currentMinutes) {
                $this->json(['error' => 'زمان انتخاب‌شده گذشته است.'], 400);
                return;
            }
        }

        $service = Database::fetch("SELECT * FROM services WHERE id = ?", [$serviceId]);
        if (!$service) {
            $this->json(['error' => 'خدمت مورد نظر یافت نشد.'], 404);
            return;
        }

        if ($artistId) {
            $validAssignment = Database::fetch(
                "SELECT id FROM artist_services WHERE artist_id = ? AND service_id = ?",
                [$artistId, $serviceId]
            );
            if (!$validAssignment) {
                $this->json(['error' => 'آرایشگر انتخاب شده برای این خدمت فعال نیست.'], 400);
                return;
            }
        }

        $existing = Database::fetch(
            "SELECT id FROM appointments WHERE appointment_date = ? AND appointment_time = ? AND artist_id = ? AND status != 'cancelled'",
            [$date, $time, $artistId ?: null]
        );
        if ($existing) {
            $this->json(['error' => 'متأسفانه این زمان توسط شخص دیگری رزرو شده است.'], 409);
            return;
        }

        $appointmentId = Database::insert('appointments', [
            'user_id' => Auth::id(),
            'service_id' => $serviceId,
            'artist_id' => $artistId ?: null,
            'appointment_date' => $date,
            'appointment_time' => $time,
            'status' => 'pending',
        ]);

        $bookingPhone = Settings::get('booking_phone', '۰۲۱-۲۲۸۸۴۲۶۷');

        $this->json([
            'success' => true,
            'appointment_id' => $appointmentId,
            'message' => 'نوبت شما ثبت شد! برای تأیید نهایی با شماره ' . $bookingPhone . ' تماس بگیرید.',
            'booking_phone' => $bookingPhone,
            'service' => $service['title'],
            'date' => $date,
            'time' => $time,
        ]);
    }

    public function refreshCaptcha(): void
    {
        $this->verifyCsrf();
        $question = Captcha::store();
        $this->json(['question' => $question]);
    }
}
