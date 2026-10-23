<?php
/**
 * Wrapper $_SESSION yang aman + flash message.
 *
 * Contoh:
 *   Session::start();
 *   Session::set('id_user', 1);
 *   Session::setFlash('error', 'Username atau password salah.');
 *   $msg = Session::getFlash('error'); // terbaca sekali, lalu hilang
 */
class Session
{
    private const FLASH_KEY = '_flash';

    /**
     * Mulai session dengan cookie yang aman. Aman dipanggil berulang kali.
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['SERVER_PORT'] ?? null) == 443);

        // Tolak session ID buatan pihak luar (anti session fixation).
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');

        session_set_cookie_params([
            'lifetime' => 0,          // hilang saat browser ditutup
            'path'     => '/',
            'secure'   => $isHttps,   // hanya dikirim lewat HTTPS jika situs HTTPS
            'httponly' => true,       // tidak bisa dibaca JavaScript
            'samesite' => 'Lax',
        ]);

        session_start();
    }

    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    /**
     * Simpan flash message (tampil sekali, pada request berikutnya).
     * $type: 'error', 'success', 'info', dst.
     */
    public static function setFlash(string $type, string $message): void
    {
        self::start();
        $_SESSION[self::FLASH_KEY][$type] = $message;
    }

    /**
     * Ambil flash message lalu hapus, sehingga hanya terbaca sekali.
     */
    public static function getFlash(string $type): ?string
    {
        self::start();
        $message = $_SESSION[self::FLASH_KEY][$type] ?? null;
        unset($_SESSION[self::FLASH_KEY][$type]);

        return $message;
    }

    public static function hasFlash(string $type): bool
    {
        self::start();
        return isset($_SESSION[self::FLASH_KEY][$type]);
    }

    /**
     * Ganti session ID tanpa menghapus data. Panggil tepat setelah login
     * berhasil untuk mencegah session fixation.
     */
    public static function regenerate(): void
    {
        self::start();
        session_regenerate_id(true);
    }

    /**
     * Hapus seluruh data session dan cookie-nya (untuk logout).
     * Setelah ini, start() akan membuat session baru, jadi flash message
     * "berhasil logout" bisa dipasang dengan setFlash().
     */
    public static function destroy(): void
    {
        self::start();

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires'  => time() - 42000,
                'path'     => $params['path'],
                'domain'   => $params['domain'],
                'secure'   => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?: 'Lax',
            ]);
        }

        session_destroy();
    }
}
