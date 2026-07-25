<?php

class MediaController
{
    public function stream(int $id): void
    {
        $media = Database::fetch("SELECT * FROM media WHERE id = ? AND is_active = 1", [$id]);
        if (!$media) {
            http_response_code(404);
            exit;
        }

        $filePath = __DIR__ . '/../../public/' . $media['filepath'];

        if (!file_exists($filePath)) {
            http_response_code(404);
            exit;
        }

        if ($media['source_type'] === 'course_video') {
            if (!Auth::check()) {
                redirect('/login');
                return;
            }
            $courseId = (int) $media['source_id'];
            $enrollment = Database::fetch(
                "SELECT id FROM course_enrollments WHERE user_id = ? AND course_id = ?",
                [Auth::id(), $courseId]
            );
            if (!$enrollment) {
                http_response_code(403);
                echo 'شما به این دوره دسترسی ندارید. لطفاً ابتدا در دوره ثبت‌نام کنید.';
                exit;
            }
        }

        $mimeType = $media['mime_type'] ?: mime_content_type($filePath);
        $size = $media['size'] ?: filesize($filePath);

        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . $size);
        header('Accept-Ranges: bytes');
        header('Cache-Control: public, max-age=86400');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT');

        if (isset($_SERVER['HTTP_RANGE'])) {
            $this->streamRange($filePath, $mimeType, $size);
        } else {
            $this->streamFull($filePath);
        }
    }

    private function streamFull(string $filePath): void
    {
        readfile($filePath);
    }

    private function streamRange(string $filePath, string $mimeType, int $fullSize): void
    {
        preg_match('/bytes=(\d+)-(\d*)/', $_SERVER['HTTP_RANGE'], $matches);
        $start = (int) ($matches[1] ?? 0);
        $end = isset($matches[2]) && $matches[2] !== '' ? (int) $matches[2] : $fullSize - 1;

        if ($start >= $fullSize || $end >= $fullSize) {
            http_response_code(416);
            header('Content-Range: bytes */' . $fullSize);
            exit;
        }

        $length = $end - $start + 1;

        http_response_code(206);
        header('Content-Range: bytes ' . $start . '-' . $end . '/' . $fullSize);
        header('Content-Length: ' . $length);

        $fp = fopen($filePath, 'rb');
        if ($fp) {
            fseek($fp, $start);
            $chunkSize = 8192;
            $sent = 0;
            while (!feof($fp) && $sent < $length && connection_aborted() === 0) {
                $remaining = $length - $sent;
                $read = $remaining < $chunkSize ? $remaining : $chunkSize;
                echo fread($fp, $read);
                $sent += $read;
                flush();
            }
            fclose($fp);
        }
    }
}
