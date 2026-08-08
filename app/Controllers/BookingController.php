<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Cache;
use App\Config;
use App\Database;
use App\Settings;
use App\Captcha;
use App\Auth;
use App\Services\SmsService;
use DateTime;
use DateTimeZone;
use Throwable;

class BookingController extends BaseController
{
    private const TIMEZONE = 'Asia/Tehran';

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
            return $this->bookingServicesData();
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

        if ($date === (new DateTime('now', new DateTimeZone(self::TIMEZONE)))->format('Y-m-d')) {
            $tehranNow = new DateTime('now', new DateTimeZone(self::TIMEZONE));
            $currentMinutes = (int) $tehranNow->format('G') * 60 + (int) $tehranNow->format('i');
            $allSlots = array_values(array_filter($allSlots, function ($slot) use ($currentMinutes) {
                $e = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
                $en = str_replace($e, ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'], $slot);
                $parts = explode(':', $en);
                $slotMinutes = (int) $parts[0] * 60 + (int) ($parts[1] ?? 0);
                return $slotMinutes > $currentMinutes;
            }));
        }

        $availableSlots = array_values(array_diff($allSlots, $bookedTimes));

        $this->json([
            'date' => $date,
            'available_slots' => $availableSlots,
            'booked_slots' => $bookedTimes,
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

        $timeError = $this->validateTimeSlot($date, $time);
        if ($timeError) {
            $this->json(['error' => $timeError], 400);
            return;
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
            $hasAnyAssignment = Database::fetch(
                "SELECT id FROM artist_services WHERE service_id = ? LIMIT 1",
                [$serviceId]
            );
            if ($hasAnyAssignment) {
                $validAssignment = Database::fetch(
                    "SELECT id FROM artist_services WHERE artist_id = ? AND service_id = ?",
                    [$artistId, $serviceId]
                );
                if (!$validAssignment) {
                    $this->json(['error' => 'آرایشگر انتخاب شده برای این خدمت فعال نیست.'], 400);
                    return;
                }
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

        $priceResult = $this->calculateFinalPrice($serviceId, $hairLengthId, $service['price']);
        $finalPrice = $priceResult['price'];
        $durationModifier = $priceResult['modifier'];

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

        $this->notifyNewBooking($appointmentId, $service['title'], $date, $time);

        $bookingPhone = Settings::get('booking_phone', '۰۳۱-۳۶۶۶۲۱۲۲');

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

    private function notifyNewBooking(int $appointmentId, string $serviceTitle, string $date, string $time): void
    {
        try {
            $ownerPhone = smsOwnerPhone();
            if ($ownerPhone === '') {
                return;
            }

            $user = Auth::user();
            $name = $user['name'] ?? 'کاربر';
            $phone = $user['phone'] ?? '';

            SmsService::notify('booking_new', [$ownerPhone], [
                'Service' => $serviceTitle,
                'Date' => $date,
                'Time' => $time,
                'Name' => $name,
                'Phone' => $phone,
            ]);
        } catch (Throwable $e) {
            error_log("SMS new booking notify failed: " . $e->getMessage());
        }
    }

    private function validateTimeSlot(string $date, string $time): ?string
    {
        $tehranNow = new DateTime('now', new DateTimeZone(self::TIMEZONE));
        $selectedDate = DateTime::createFromFormat('Y-m-d', $date, new DateTimeZone(self::TIMEZONE));
        $tehranMidnight = clone $tehranNow;
        $tehranMidnight->setTime(0, 0, 0);

        if (!$selectedDate || $selectedDate < $tehranMidnight) {
            return 'تاریخ انتخاب‌شده نامعتبر است.';
        }

        if ($date === $tehranNow->format('Y-m-d')) {
            $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            $timeNum = str_replace($persian, ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'], $time);
            $parts = explode(':', $timeNum);
            $slotMinutes = (int) $parts[0] * 60 + (int) ($parts[1] ?? 0);
            $currentMinutes = (int) $tehranNow->format('G') * 60 + (int) $tehranNow->format('i');
            if ($slotMinutes <= $currentMinutes) {
                return 'زمان انتخاب‌شده گذشته است.';
            }
        }

        return null;
    }

    private function calculateFinalPrice(int $serviceId, int $hairLengthId, int $basePrice): array
    {
        if (!$hairLengthId) {
            return ['price' => $basePrice, 'modifier' => 1.0];
        }

        $priceData = Database::fetch(
            "SELECT price, duration_modifier FROM service_hair_prices 
             WHERE service_id = ? AND hair_length_id = ? AND is_active = 1",
            [$serviceId, $hairLengthId]
        );

        if ($priceData) {
            return ['price' => (int) $priceData['price'], 'modifier' => (float) $priceData['duration_modifier']];
        }

        return ['price' => $basePrice, 'modifier' => 1.0];
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
            'price' => (int) $priceData['price'],
            'duration' => (float) $priceData['duration_modifier'],
            'formatted_price' => priceFormat($priceData['price']),
        ]);
    }

    public function refreshCaptcha(): void
    {
        $this->verifyCsrf();
        $question = Captcha::store();
        $this->json(['question' => $question]);
    }
}
