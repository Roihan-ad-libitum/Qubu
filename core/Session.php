<?php

class Session
{
    private const FLASH_KEY = '_flash';

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['SERVER_PORT'] ?? null) == 443);

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');

        session_set_cookie_params([
            'lifetime' => 0,          
            'path'     => '/',
            'secure'   => $isHttps,   
            'httponly' => true,       
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

    public static function setFlash(string $type, mixed $message): void
    {
        self::start();
        if ($type === 'old' && is_array($message)) {
            unset($message['password'], $message['password_confirm']);
        }
        $_SESSION[self::FLASH_KEY][$type] = $message;
    }

    public static function getFlash(string $type): mixed
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

    public static function regenerate(): void
    {
        self::start();
        session_regenerate_id(true);
    }

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
