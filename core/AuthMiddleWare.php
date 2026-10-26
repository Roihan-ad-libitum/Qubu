<?php
/**
 * Middleware Keamanan & Hak Akses Berbasis Peran (RBAC)
 * Mendukung proteksi route untuk web page dan endpoint JSON/API.
 */

require_once __DIR__ . '/Session.php';
require_once __DIR__ . '/../config/app.php';

class AuthMiddleware
{
    /**
     * Memastikan pengguna telah terautentikasi (sudah login).
     * Jika belum login, alihkan ke halaman login dengan pesan error.
     */
    public static function check(): void
    {
        Session::start();

        if (!Session::has('id_user') || !Session::has('role')) {
            if (self::isApiRequest()) {
                http_response_code(401);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => false,
                    'message' => 'Sesi tidak valid atau telah berakhir. Silakan login kembali.'
                ]);
                exit;
            }

            Session::setFlash('error', 'Silakan masuk untuk mengakses halaman ini.');
            redirect('?page=login');
        }
    }

    /**
     * Memastikan pengguna memiliki salah satu role yang diizinkan.
     * Contoh: AuthMiddleware::hasRole(ROLE_PESERTA)
     *         AuthMiddleware::hasRole(ROLE_PESERTA, ROLE_DOSEN)
     */
    public static function hasRole(string ...$allowedRoles): void
    {
        // 1. Cek apakah sudah login terlebih dahulu
        self::check();

        $currentRole = (string) Session::get('role');

        // 2. Cek apakah role pengguna saat ini termasuk dalam role yang diizinkan
        if (!in_array($currentRole, $allowedRoles, true)) {
            if (self::isApiRequest()) {
                http_response_code(403);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => false,
                    'message' => 'Akses ditolak. Anda tidak memiliki izin untuk tindakan ini.'
                ]);
                exit;
            }

            Session::setFlash('error', 'Akses ditolak. Anda tidak memiliki hak akses ke halaman tersebut.');
            redirect(getRoleDashboard($currentRole));
        }
    }

    /**
     * Memastikan hanya tamu (guest/belum login) yang dapat mengakses halaman (misal form login).
     * Jika sudah login, langsung alihkan ke dashboard role masing-masing.
     */
    public static function guestOnly(): void
    {
        Session::start();

        if (Session::has('id_user') && Session::has('role')) {
            redirect(getRoleDashboard((string) Session::get('role')));
        }
    }

    /**
     * Deteksi apakah permintaan saat ini berasal dari API/AJAX
     */
    private static function isApiRequest(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $uri    = $_SERVER['REQUEST_URI'] ?? '';

        return str_contains($accept, 'application/json') 
            || str_contains($uri, '/api/')
            || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    }
}

// Alias class agar kompatibel jika diketik AuthMiddleWare maupun AuthMiddleware
if (!class_exists('AuthMiddleWare', false)) {
    class_alias('AuthMiddleware', 'AuthMiddleWare');
}
