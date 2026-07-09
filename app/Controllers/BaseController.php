<?php

class BaseController
{
    protected function view(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/' . $view . '.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }

    protected function viewRaw(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../views/' . $view . '.php';
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
                if ($rule === 'required' && empty($data[$field])) {
                    $errors[$field] = 'این فیلد الزامی است.';
                }
                if (str_starts_with($rule, 'min:') && isset($data[$field])) {
                    $min = (int) explode(':', $rule)[1];
                    if (mb_strlen($data[$field]) < $min) {
                        $errors[$field] = "حداقل {$min} کاراکتر وارد کنید.";
                    }
                }
                if (str_starts_with($rule, 'max:') && isset($data[$field])) {
                    $max = (int) explode(':', $rule)[1];
                    if (mb_strlen($data[$field]) > $max) {
                        $errors[$field] = "حداکثر {$max} کاراکتر مجاز است.";
                    }
                }
            }
        }
        return $errors;
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
