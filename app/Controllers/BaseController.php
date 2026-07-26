<?php

class BaseController
{
    private static array $protectedVars = ['view', 'data', 'hideFooter', 'this'];

    protected function view(string $view, array $data = []): void
    {
        $hideFooter = $data['hideFooter'] ?? false;
        if (!isset($data['settings'])) {
            $data['settings'] = Settings::all();
        }
        $safe = array_diff_key($data, array_flip(self::$protectedVars));
        extract($safe);
        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/' . $view . '.php';
        if (empty($hideFooter)) {
            require_once __DIR__ . '/../views/layouts/footer.php';
        }
    }

    protected function viewRaw(string $view, array $data = []): void
    {
        $safe = array_diff_key($data, array_flip(self::$protectedVars));
        extract($safe);
        require_once __DIR__ . '/../views/' . $view . '.php';
    }

    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function validate(array $data, array $rules): array
    {
        $errors = [];
        foreach ($rules as $field => $ruleSet) {
            $ruleList = explode('|', $ruleSet);
            foreach ($ruleList as $rule) {
                $error = $this->validateField($field, $rule, $data);
                if ($error) {
                    $errors[$field] = $error;
                }
            }
        }
        return $errors;
    }

    private function validateField(string $field, string $rule, array $data): ?string
    {
        if ($rule === 'required' && (!isset($data[$field]) || $data[$field] === '')) {
            return 'این فیلد الزامی است.';
        }

        if (str_starts_with($rule, 'min:') && isset($data[$field])) {
            $min = (int) explode(':', $rule)[1];
            if (mb_strlen($data[$field]) < $min) {
                return "حداقل {$min} کاراکتر وارد کنید.";
            }
        }

        if (str_starts_with($rule, 'max:') && isset($data[$field])) {
            $max = (int) explode(':', $rule)[1];
            if (mb_strlen($data[$field]) > $max) {
                return "حداکثر {$max} کاراکتر مجاز است.";
            }
        }

        return null;
    }

    protected function redirectWithErrors(string $url, array $errors): void
    {
        flashErrors($errors);
        $_SESSION['_old'] = $_POST;
        redirect($url);
    }

    protected function verifyCsrf(): void
    {
        $token = $_POST['_csrf'] ?? '';
        if (!verifyCsrf($token)) {
            http_response_code(419);
            echo json_encode(['error' => 'درخواست نامعتبر (CSRF).'], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
}
