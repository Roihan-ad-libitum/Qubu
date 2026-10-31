<?php

if (!defined('APP_NAME')) define('APP_NAME', 'Qubu - Platform Info Lomba & Kolaborasi');

if (!defined('APP_ENV')) define('APP_ENV', getenv('APP_ENV') ?: 'development');

date_default_timezone_set('Asia/Jakarta');

ini_set('display_errors', APP_ENV === 'production' ? '0' : '1');
ini_set('log_errors', '1');
error_reporting(E_ALL);

if (!defined('ROLE_PESERTA')) define('ROLE_PESERTA', 'peserta');
if (!defined('ROLE_DOSEN'))   define('ROLE_DOSEN', 'dosen');
if (!defined('ROLE_ADMIN'))   define('ROLE_ADMIN', 'admin');

if (!defined('BIDANG_QUBU')) {
    define('BIDANG_QUBU', [
        'Frontend',
        'Backend',
        'UI/UX Design',
        'Data Science',
        'Robotic',
    ]);
}

if (!defined('PRODI_FASILKOM')) {
    define('PRODI_FASILKOM', [
        'Informatika',
        'Sistem Informasi',
    ]);
}

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

function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function redirect(string $url): void
{
    $url = str_replace(["\r", "\n"], '', trim($url));

    if ($url === '' || preg_match('#^(?:[a-z][a-z0-9+.-]*:|//)#i', $url)) {
        $url = '?page=login';
    }

    header('Location: ' . url($url));
    exit;
}

function getRoleDashboard(string $role): string
{
    return match ($role) {
        ROLE_ADMIN   => '?page=admin-dashboard',
        ROLE_DOSEN   => '?page=dosen-dashboard',
        ROLE_PESERTA => '?page=mahasiswa-dashboard',
        default      => '?page=login',
    };
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

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

function getCategoryPills(): array
{
    return array_merge(['Semua Bidang'], defined('BIDANG_QUBU') ? BIDANG_QUBU : []);
}

function getProdiOptions(): array
{
    return array_merge(['Semua Prodi'], defined('PRODI_FASILKOM') ? PRODI_FASILKOM : []);
}

function hasPublicFile(?string $relPath): bool
{
    $clean = trim((string) $relPath);
    if ($clean === '') {
        return false;
    }
    if (preg_match('#^https?://#i', $clean)) {
        return filter_var($clean, FILTER_VALIDATE_URL) !== false;
    }
    $fullPath = dirname(__DIR__) . '/' . ltrim($clean, '/');
    return is_file($fullPath);
}

function resolveProfilePhotoUrl(?string $relOrAbsoluteUrl): ?string
{
    $clean = trim((string) $relOrAbsoluteUrl);
    if ($clean === '') {
        return null;
    }
    if (preg_match('#^https?://#i', $clean)) {
        return filter_var($clean, FILTER_VALIDATE_URL) !== false ? $clean : null;
    }
    $cleanRel = ltrim($clean, '/');
    $fullPath = dirname(__DIR__) . '/' . $cleanRel;
    if (is_file($fullPath)) {
        return url($cleanRel);
    }
    return null;
}

function buildInitials(?string $name, string $fallback = 'QB'): string
{
    $clean = trim((string) preg_replace('/,.*$/', '', (string) $name));
    $parts = array_values(array_filter(explode(' ', $clean)));
    if (count($parts) >= 2) {
        return strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
    }
    return $clean !== '' ? strtoupper(substr($clean, 0, 2)) : $fallback;
}

function parseSkillTags(?string $rawCsv, bool $strictOfficial = false, array $fallback = []): array
{
    $rawTags = array_values(array_filter(array_map('trim', explode(',', (string) $rawCsv))));
    $official = defined('BIDANG_QUBU') ? BIDANG_QUBU : [];
    $result = [];

    foreach ($rawTags as $tag) {
        $matchedOfficial = null;
        foreach ($official as $allowed) {
            if (strcasecmp($tag, $allowed) === 0) {
                $matchedOfficial = $allowed;
                break;
            }
        }
        if ($matchedOfficial !== null) {
            $result[] = $matchedOfficial;
        } elseif (!$strictOfficial && $tag !== '') {
            $result[] = $tag;
        }
    }

    $result = array_values(array_unique($result));
    return !empty($result) ? $result : $fallback;
}

function normalizeWaPhone(?string $rawPhone): string
{
    $digits = preg_replace('/\D+/', '', (string) $rawPhone);
    if (strlen($digits) < 8) {
        return '';
    }
    if (str_starts_with($digits, '0')) {
        return '62' . substr($digits, 1);
    }
    if (str_starts_with($digits, '8')) {
        return '62' . $digits;
    }
    return $digits;
}

function buildWhatsAppUrl(?string $rawPhone, string $message = ''): string
{
    $phone = normalizeWaPhone($rawPhone);
    if ($phone === '') {
        return '';
    }
    return 'https://wa.me/' . $phone . ($message !== '' ? '?text=' . rawurlencode($message) : '');
}

function buildMailtoUrl(?string $rawEmail, string $subject = ''): string
{
    $email = filter_var(trim((string) $rawEmail), FILTER_VALIDATE_EMAIL);
    if ($email === false) {
        return '';
    }
    return 'mailto:' . $email . ($subject !== '' ? '?subject=' . rawurlencode($subject) : '');
}

function resolveTeamPostContactLinks(array $post): array
{
    if (($post['status'] ?? 'open') !== 'open') {
        return [];
    }

    $useCustom  = !empty($post['contact_opt_in']);
    $emailValue = $useCustom
        ? ($post['contact_email'] ?: ($post['account_email'] ?? ''))
        : ($post['account_email'] ?: ($post['contact_email'] ?? ''));
    $phoneValue = $useCustom
        ? ($post['contact_whatsapp'] ?: ($post['account_phone'] ?? ''))
        : ($post['account_phone'] ?: ($post['contact_whatsapp'] ?? ''));

    $ownerName    = (string) (($post['nama_mahasiswa'] ?? '') ?: ($post['username'] ?? 'Rekan Qubu'));
    $lombaContext = !empty($post['nama_lomba']) ? ' untuk lomba ' . $post['nama_lomba'] : '';

    $links = [];
    $waHref = buildWhatsAppUrl(
        (string) $phoneValue,
        'Halo ' . $ownerName . ', saya melihat posting pencarian tim Anda' . $lombaContext . ' di Qubu dan tertarik berdiskusi.'
    );
    if ($waHref !== '') {
        $links[] = [
            'label' => 'Hubungi via WhatsApp',
            'href'  => $waHref,
            'type'  => 'whatsapp',
        ];
    }

    $mailHref = buildMailtoUrl(
        (string) $emailValue,
        'Kolaborasi Tim Lomba Qubu: ' . ($post['nama_lomba'] ?? 'Kompetisi')
    );
    if ($mailHref !== '') {
        $links[] = [
            'label' => 'Hubungi via Email',
            'href'  => $mailHref,
            'type'  => 'email',
        ];
    }

    return $links;
}

function formatLombaTanggalIndo(?string $datetime): string
{
    if (empty($datetime)) {
        return '-';
    }
    $timestamp = strtotime($datetime);
    if (!$timestamp) {
        return '-';
    }
    $bulanIndo = [
        1  => 'Januari',  2  => 'Februari', 3  => 'Maret',    4  => 'April',
        5  => 'Mei',      6  => 'Juni',     7  => 'Juli',     8  => 'Agustus',
        9  => 'September', 10 => 'Oktober',  11 => 'November', 12 => 'Desember',
    ];
    $d = date('j', $timestamp);
    $m = (int) date('n', $timestamp);
    $y = date('Y', $timestamp);

    return $d . ' ' . ($bulanIndo[$m] ?? date('M', $timestamp)) . ' ' . $y;
}

function isLombaRegistrationClosed(?string $deadline): bool
{
    if (empty($deadline)) {
        return true;
    }

    $timestamp = strtotime($deadline);
    return $timestamp === false || $timestamp < time();
}

function formatLombaPartisipasi(?string $partisipasi): string
{
    $val = trim((string) $partisipasi);
    if ($val === '' || strcasecmp($val, 'Individu/Tim') === 0) {
        return 'Individu & Tim';
    }
    return $val;
}

function formatLombaBiaya(float|int|string|null $biaya): string
{
    $num = (float) ($biaya ?? 0);
    return $num <= 0 ? 'Gratis' : 'Rp ' . number_format($num, 0, ',', '.');
}

if (!defined('QUBU_FOOTER_WHATSAPP')) define('QUBU_FOOTER_WHATSAPP', '081289917510');
if (!defined('QUBU_FOOTER_EMAIL'))    define('QUBU_FOOTER_EMAIL', 'roihan@gmail.com');
if (!defined('QUBU_FOOTER_GITHUB'))   define('QUBU_FOOTER_GITHUB', 'https://github.com/roihan-ad-libitum');

function normalizeGithubUrl(?string $rawGithub): ?string
{
    $cleaned = trim(str_replace(["\r", "\n", "\0"], '', (string) $rawGithub));
    if ($cleaned === '') {
        return '';
    }
    if (strlen($cleaned) > 255) {
        return null;
    }

    if (preg_match('#^(?:https?://)?(?:www\.)?github\.com/([a-zA-Z0-9](?:[a-zA-Z0-9._-]{0,38})(?:/[a-zA-Z0-9._-]+)?)/?$#i', $cleaned, $m)) {
        return 'https://github.com/' . $m[1];
    }

    $handle = ltrim($cleaned, '@/');
    if (preg_match('#^[a-zA-Z0-9](?:[a-zA-Z0-9._-]{0,38})$#', $handle)) {
        return 'https://github.com/' . $handle;
    }

    return null;
}

function formatGithubDisplay(?string $githubUrl): string
{
    $normalized = normalizeGithubUrl($githubUrl);
    if ($normalized === null || $normalized === '') {
        return '';
    }
    return (string) preg_replace('#^https?://(?:www\.)?#i', '', $normalized);
}
