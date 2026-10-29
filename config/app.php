<?php
/**
 * Konfigurasi Dasar Aplikasi Qubu Project (InfoLomba Fasilkom)
 * Berdasarkan Database: qubu-project
 */

if (!defined('APP_NAME')) define('APP_NAME', 'Qubu - Platform Info Lomba & Kolaborasi');

// 'development' menampilkan error; 'production' hanya mencatat ke log.
if (!defined('APP_ENV')) define('APP_ENV', getenv('APP_ENV') ?: 'development');

date_default_timezone_set('Asia/Jakarta');

ini_set('display_errors', APP_ENV === 'production' ? '0' : '1');
ini_set('log_errors', '1');
error_reporting(E_ALL);

/**
 * Definisi Role Pengguna sesuai ENUM tabel `users` di qubu-project.sql
 */
if (!defined('ROLE_PESERTA')) define('ROLE_PESERTA', 'peserta');
if (!defined('ROLE_DOSEN'))   define('ROLE_DOSEN', 'dosen');
if (!defined('ROLE_ADMIN'))   define('ROLE_ADMIN', 'admin');

/**
 * Daftar Bidang Utama Qubu yang berfokus secara eksklusif pada 5 kartu di index.php
 * (Frontend, Robotic, UI/UX Design, Backend, Data Science)
 */
if (!defined('BIDANG_QUBU')) {
    define('BIDANG_QUBU', [
        'Frontend',
        'Backend',
        'UI/UX Design',
        'Data Science',
        'Robotic'
    ]);
}

/**
 * BASE_URL otomatis tanpa trailing slash, mis. http://localhost/InfoLomba
 * Dapat di-override melalui environment variable APP_URL jika diperlukan.
 */
if (!defined('BASE_URL')) {
    $envUrl = getenv('APP_URL');

    if ($envUrl) {
        define('BASE_URL', rtrim($envUrl, '/'));
    } elseif (PHP_SAPI === 'cli') {
        define('BASE_URL', 'http://localhost');
    } else {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['SERVER_PORT'] ?? null) == 443);
        $scheme = $isHttps ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $path   = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        $path   = (string) preg_replace('#/api$#i', '', $path);

        define('BASE_URL', $scheme . '://' . $host . $path);
    }
}

/**
 * Helper menghasilkan URL lengkap aplikasi.
 */
function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

/**
 * Redirect internal yang aman ke rute tujuan lalu menghentikan eksekusi.
 *
 * Contoh: redirect('?page=login'), redirect('?page=dashboard')
 */
function redirect(string $url): void
{
    $url = str_replace(["\r", "\n"], '', trim($url));

    // Cegah open redirect ke domain luar
    if ($url === '' || preg_match('#^(?:[a-z][a-z0-9+.-]*:|//)#i', $url)) {
        $url = '?page=login';
    }

    header('Location: ' . url($url));
    exit;
}

/**
 * Mendapatkan rute dashboard default berdasarkan role pengguna di qubu-project.
 */
function getRoleDashboard(string $role): string
{
    return match ($role) {
        ROLE_ADMIN   => '?page=admin-dashboard',
        ROLE_DOSEN   => '?page=dosen-dashboard',
        ROLE_PESERTA => '?page=mahasiswa-dashboard',
        default      => '?page=login',
    };
}

/**
 * Escape output HTML untuk mencegah XSS.
 * Digunakan di view: <?= e($var) ?>
 */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Validasi dan sanitasi URL eksternal (mis. link_resmi, link_guidebook)
 * Hanya mengizinkan skema http:// dan https:// untuk mencegah XSS via javascript: atau data: URI.
 */
function safeExternalUrl(?string $rawUrl): string
{
    $cleaned = trim(str_replace(["\r", "\n", "\0"], '', (string) $rawUrl));
    if ($cleaned === '') {
        return '';
    }
    if (!preg_match('#^https?://#i', $cleaned)) {
        return '';
    }
    return filter_var($cleaned, FILTER_VALIDATE_URL) !== false ? $cleaned : '';
}
