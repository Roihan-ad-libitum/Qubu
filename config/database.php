<?php

if (!defined('DB_HOST'))    define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
if (!defined('DB_PORT'))    define('DB_PORT', getenv('DB_PORT') ?: '3306');
if (!defined('DB_NAME'))    define('DB_NAME', getenv('DB_NAME') ?: 'qubu-project');
if (!defined('DB_USER'))    define('DB_USER', getenv('DB_USER') ?: 'root');
if (!defined('DB_PASS'))    define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');

class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                DB_HOST,
                DB_PORT,
                DB_NAME,
                DB_CHARSET
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                error_log('[Database Error] ' . $e->getMessage());
                http_response_code(500);
                exit('Terjadi kesalahan koneksi ke database qubu-project. Silakan coba beberapa saat lagi.');
            }
        }

        return self::$instance;
    }
}

function db(): PDO
{
    return Database::getConnection();
}
