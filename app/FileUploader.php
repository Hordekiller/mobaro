<?php

class FileUploader
{
    private const ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private const ALLOWED_EXT = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private const MAX_SIZE = 5 * 1024 * 1024;

    public static function upload(array $file, ?string $prefix = null, ?string $oldFilename = null): ?string
    {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, self::ALLOWED_EXT) || $file['size'] > self::MAX_SIZE) {
            return null;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);

        if (!in_array($mime, self::ALLOWED_MIME)) {
            return null;
        }

        $filename = ($prefix ? $prefix . '_' : '') . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destDir = __DIR__ . '/../public/assets/images/';
        $path = $destDir . $filename;

        $result = move_uploaded_file($file['tmp_name'], $path);
        if (!$result) {
            return null;
        }

        if ($oldFilename && $oldFilename !== $filename) {
            $oldPath = $destDir . basename($oldFilename);
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        return $filename;
    }
}
