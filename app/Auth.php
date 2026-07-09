<?php

class Auth
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => false,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }

    public static function login(int $userId, array $userData = []): void
    {
        self::start();
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        $_SESSION['user'] = $userData;
        $_SESSION['logged_in'] = true;
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }

    public static function logout(): void
    {
        self::start();
        session_destroy();
        $_SESSION = [];
    }

    public static function check(): bool
    {
        self::start();
        return !empty($_SESSION['logged_in']);
    }

    public static function id(): ?int
    {
        self::start();
        return $_SESSION['user_id'] ?? null;
    }

    public static function user(): ?array
    {
        self::start();
        if (!self::check()) {
            return null;
        }
        if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
            $user = Database::fetch("SELECT * FROM users WHERE id = ?", [self::id()]);
            $_SESSION['user'] = $user ?: [];
        }
        return $_SESSION['user'] ?: null;
    }

    public static function isAdmin(): bool
    {
        $user = self::user();
        return $user && ($user['role'] ?? 'user') === 'admin';
    }

    public static function requireAuth(): void
    {
        if (!self::check()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: /login');
            exit;
        }
    }

    public static function requireAdmin(): void
    {
        self::requireAuth();
        if (!self::isAdmin()) {
            http_response_code(403);
            echo "دسترسی غیرمجاز";
            exit;
        }
    }

    public static function hash(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public static function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
