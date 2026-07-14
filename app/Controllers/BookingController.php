<?php

class BookingController extends BaseController
{
    private function getFormData(): array
    {
        return Cache::remember('booking_form_data', Config::get('cache.ttl.page', 600), function () {
            $services = Database::fetchAll("SELECT * FROM services WHERE is_active = 1");
            $artists = Database::fetchAll("SELECT * FROM artists WHERE is_active = 1");
            $hairLengths = Database::fetchAll("SELECT * FROM hair_lengths WHERE is_active = 1 ORDER BY sort_order");
            return compact('services', 'artists', 'hairLengths');
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
            ], $artists), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG),
            'hairLengthsJson' => json_encode(array_map(fn($hl) => [
                'id' => $hl['id'],
                'title' => $hl['title'],
                'min_cm' => $hl['min_cm'],
                'max_cm' => $hl['max_cm'],
            ], $formData['hairLengths'] ?? []), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG),
        ]);
    }

    public function getServices(): void
    {
        $this->verifyCsrf();
        $services = Cache::remember('booking_services', Config::get('cache.ttl.page', 600), function () {
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
        $tehranMidnight = clone $tehranNow;
        $tehranMidnight->setTime(0, 0, 0);
        if (!$selectedDate || $selectedDate < $tehranMidnight) {
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

        $hairLengthId = (int) ($_POST['hair_length_id'] ?? 0);
        $hasHairPricing = Database::fetch(
            "SELECT 1 FROM service_hair_prices WHERE service_id = ? AND is_active = 1 LIMIT 1",
            [$serviceId]
        );
        if ($hasHairPricing && !$hairLengthId) {
            $this->json(['error' => 'برای این خدمت انتخاب قد مو الزامی است.'], 400);
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

        $finalPrice = $service['price'];
        $durationModifier = 1.0;

        // Get dynamic price if hair length is selected
        if ($hairLengthId) {
            $priceData = Database::fetch(
                "SELECT price, duration_modifier FROM service_hair_prices 
                 WHERE service_id = ? AND hair_length_id = ? AND is_active = 1",
                [$serviceId, $hairLengthId]
            );
            if ($priceData) {
                $finalPrice = $priceData['price'];
                $durationModifier = (float) $priceData['duration_modifier'];
            }
        }

        $notes = trim($_POST['notes'] ?? '');
        if ($durationModifier !== 1.0) {
            $durationNote = 'ضریب مدت: ' . $durationModifier . 'x';
            $notes = $notes ? $notes . ' | ' . $durationNote : $durationNote;
        }

        $appointmentId = Database::insert('appointments', [
            'user_id' => Auth::id(),
            'service_id' => $serviceId,
            'artist_id' => $artistId ?: null,
            'hair_length_id' => $hairLengthId ?: null,
            'appointment_date' => $date,
            'appointment_time' => $time,
            'price' => $finalPrice,
            'status' => 'pending',
            'notes' => $notes ?: null,
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

    public function getHairLengths(): void
    {
        $hairLengths = Cache::remember('hair_lengths', Config::get('cache.ttl.page', 600), function () {
            return Database::fetchAll("SELECT * FROM hair_lengths WHERE is_active = 1 ORDER BY sort_order");
        }, 'booking');
        $this->json(['hair_lengths' => $hairLengths]);
    }

    public function getServicePrice(): void
    {
        $this->verifyCsrf();
        $serviceId = (int) ($_POST['service_id'] ?? 0);
        $hairLengthId = (int) ($_POST['hair_length_id'] ?? 0);

        if (!$serviceId || !$hairLengthId) {
            $this->json(['error' => 'لطفاً خدمت و قد مو را انتخاب کنید.'], 400);
            return;
        }

        $priceData = Database::fetch(
            "SELECT price, duration_modifier FROM service_hair_prices 
             WHERE service_id = ? AND hair_length_id = ? AND is_active = 1",
            [$serviceId, $hairLengthId]
        );

        if (!$priceData) {
            // Fallback to base service price if no hair length pricing exists
            $service = Database::fetch("SELECT price, duration FROM services WHERE id = ?", [$serviceId]);
            if ($service) {
                $priceData = [
                    'price' => $service['price'],
                    'duration_modifier' => 1.0
                ];
            } else {
                $this->json(['error' => 'خدمت یافت نشد.'], 404);
                return;
            }
        }

        $this->json([
            'price' => $priceData['price'],
            'duration' => $priceData['duration_modifier'],
            'formatted_price' => number_format($priceData['price']) . ' تومان'
        ]);
    }

    public function refreshCaptcha(): void
    {
        $this->verifyCsrf();
        $question = Captcha::store();
        $this->json(['question' => $question]);
    }
}
