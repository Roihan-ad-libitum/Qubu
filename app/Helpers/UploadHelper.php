<?php

class UploadHelper
{
    private const ALLOWED_IMAGE_EXT  = ['jpg', 'jpeg', 'png', 'webp'];
    private const ALLOWED_IMAGE_MIME = ['image/jpeg', 'image/png', 'image/webp', 'image/pjpeg', 'image/x-png'];
    private const ALLOWED_POSTER_EXT = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
    private const MAX_PROFILE_SIZE   = 5 * 1024 * 1024;  
    private const MAX_POSTER_SIZE    = 8 * 1024 * 1024;  

    public static function uploadProfilePhoto(array $file, string $prefix, string $identifier, ?string $oldPhotoPath = null): ?string
    {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $fileTmp  = $file['tmp_name'];
        $fileSize = (int) ($file['size'] ?? 0);
        $origName = (string) ($file['name'] ?? '');

        if ($fileSize > self::MAX_PROFILE_SIZE) {
            throw new RuntimeException('Ukuran foto profil maksimal 5MB.');
        }

        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_IMAGE_EXT, true)) {
            throw new RuntimeException('Format foto tidak didukung. Harap unggah file JPG, JPEG, PNG, atau WEBP.');
        }

        if (function_exists('finfo_open')) {
            $finfo    = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $fileTmp);
            finfo_close($finfo);
            if (!in_array($mimeType, self::ALLOWED_IMAGE_MIME, true)) {
                throw new RuntimeException('File yang diunggah bukan gambar yang valid.');
            }
        }

        if (function_exists('getimagesize')) {
            $imgInfo = @getimagesize($fileTmp);
            if ($imgInfo === false || empty($imgInfo[0]) || empty($imgInfo[1])) {
                throw new RuntimeException('File gambar rusak atau tidak dapat dibaca.');
            }
        }

        $safeIdentifier = preg_replace('/[^a-zA-Z0-9_-]/', '', $identifier);
        $newFilename    = $prefix . '_' . ($safeIdentifier !== '' ? $safeIdentifier : 'user') . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

        $newRelPath = self::moveFile(
            $fileTmp,
            __DIR__ . '/../../public/uploads/profile',
            $newFilename,
            'public/uploads/profile'
        );

        if ($newRelPath !== null && !empty($oldPhotoPath)) {
            self::deleteFile($oldPhotoPath);
        }

        return $newRelPath;
    }

    public static function uploadPoster(array $file, string $namaLomba): ?string
    {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $fileTmp  = $file['tmp_name'];
        $fileSize = (int) ($file['size'] ?? 0);
        $origName = (string) ($file['name'] ?? '');

        if ($fileSize > self::MAX_POSTER_SIZE) {
            throw new RuntimeException('Ukuran poster maksimal 8MB.');
        }

        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_POSTER_EXT, true)) {
            throw new RuntimeException('Format poster tidak didukung. Harap unggah file JPG, PNG, WEBP, atau SVG.');
        }

        $safeSlug = preg_replace('/[^a-zA-Z0-9_-]/', '', substr($namaLomba, 0, 20));
        return self::moveFile(
            $fileTmp,
            __DIR__ . '/../../public/uploads/posters',
            'poster_' . ($safeSlug !== '' ? $safeSlug : 'lomba') . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext,
            'public/uploads/posters'
        );
    }

    public static function deleteFile(?string $relPath): bool
    {
        $clean = trim((string) $relPath);
        if ($clean === '') {
            return false;
        }
        $fullPath = realpath(__DIR__ . '/../../' . ltrim($clean, '/'));
        $allowedBase = realpath(__DIR__ . '/../../public/uploads');

        if ($fullPath && $allowedBase && str_starts_with($fullPath, $allowedBase) && is_file($fullPath)) {
            return @unlink($fullPath);
        }

        return false;
    }

    private static function moveFile(string $tmpPath, string $destDir, string $filename, string $relBase): ?string
    {
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $dest = $destDir . '/' . $filename;
        if (move_uploaded_file($tmpPath, $dest)) {
            return $relBase . '/' . $filename;
        }

        return null;
    }
}
