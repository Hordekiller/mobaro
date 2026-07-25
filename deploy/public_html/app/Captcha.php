<?php

class Captcha
{
    private static int $min = 1;
    private static int $max = 30;
    private static ?array $settingsCache = null;
    private const DIFFICULTY_RANGES = [
        'easy' => [1, 10],
        'medium' => [1, 30],
        'hard' => [10, 99],
    ];
    private const MULTIPLIER_RANGES = [
        'easy' => [2, 5],
        'medium' => [2, 12],
        'hard' => [5, 20],
    ];

    public static function isEnabled(string $section): bool
    {
        $key = 'captcha_enabled_' . $section;
        return (self::getSetting($key) ?? '1') === '1';
    }

    public static function generate(): array
    {
        $difficulty = self::getSetting('captcha_difficulty') ?? 'medium';
        $range = self::DIFFICULTY_RANGES[$difficulty] ?? self::DIFFICULTY_RANGES['medium'];
        $mRange = self::MULTIPLIER_RANGES[$difficulty] ?? self::MULTIPLIER_RANGES['medium'];

        $type = random_int(0, 3);
        return match ($type) {
            0 => self::addition($range),
            1 => self::subtraction($range),
            2 => self::multiplication($mRange),
            3 => self::mixed($range),
        };
    }

    private static function addition(array $range): array
    {
        $a = random_int($range[0], $range[1]);
        $b = random_int($range[0], $range[1]);
        $answer = $a + $b;
        $question = self::toPersian("{$a} + {$b}");
        return ['question' => $question, 'answer' => $answer];
    }

    private static function subtraction(array $range): array
    {
        $a = random_int($range[0], $range[1]);
        $b = random_int($range[0], $a);
        $answer = $a - $b;
        $question = self::toPersian("{$a} - {$b}");
        return ['question' => $question, 'answer' => $answer];
    }

    private static function multiplication(array $range): array
    {
        $a = random_int($range[0], $range[1]);
        $b = random_int($range[0], $range[1]);
        $answer = $a * $b;
        $question = self::toPersian("{$a} × {$b}");
        return ['question' => $question, 'answer' => $answer];
    }

    private static function mixed(array $range): array
    {
        $a = random_int($range[0], $range[1]);
        $b = random_int(2, max(2, (int)($range[1] / 2)));
        $c = random_int(1, min(5, $b - 1));
        $answer = $a + $b - $c;
        $question = self::toPersian("{$a} + {$b} - {$c}");
        return ['question' => $question, 'answer' => $answer];
    }

    public static function store(): string
    {
        $captcha = self::generate();
        $_SESSION['captcha_answer'] = $captcha['answer'];
        $_SESSION['captcha_time'] = time();
        $question = $captcha['question'];
        $_SESSION['captcha_question'] = $question;
        return $question;
    }

    public static function verify(mixed $input): bool
    {
        $input = self::normalizeInput($input);
        $expected = (int) ($_SESSION['captcha_answer'] ?? -1);
        $time = (int) ($_SESSION['captcha_time'] ?? 0);

        unset($_SESSION['captcha_answer'], $_SESSION['captcha_time']);

        if ($expected === -1) {
            return false;
        }

        if (time() - $time > 300) {
            return false;
        }

        return $input === $expected;
    }

    public static function verifyAndRegenerate(mixed $input): ?string
    {
        $valid = self::verify($input);
        if (!$valid) {
            return null;
        }
        return self::store();
    }

    public static function getStoredQuestions(): array
    {
        $questions = [];
        for ($i = 1; $i <= 10; $i++) {
            $val = self::getSetting('captcha_question_' . $i);
            if ($val) {
                $questions[] = $val;
            }
        }
        return $questions;
    }

    public static function getSetting(string $key): ?string
    {
        if (self::$settingsCache === null) {
            self::$settingsCache = [];
            try {
                $all = Settings::all();
                foreach ($all as $k => $v) {
                    if (str_starts_with($k, 'captcha_')) {
                        self::$settingsCache[$k] = $v;
                    }
                }
            } catch (Throwable) {
                return null;
            }
        }
        return self::$settingsCache[$key] ?? null;
    }

    public static function clearCache(): void
    {
        self::$settingsCache = null;
    }

    private static function normalizeInput(mixed $input): int
    {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $input = str_replace($persian, range(0, 9), (string) $input);
        $input = trim($input);
        if ($input === '' || !ctype_digit($input)) {
            return -1;
        }
        return (int) $input;
    }

    private static function toPersian(string $text): string
    {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        return str_replace($english, $persian, $text);
    }
}
