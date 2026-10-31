<?php

require_once __DIR__ . '/Session.php';
require_once __DIR__ . '/../config/app.php';

class AuthMiddleware
{

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

    public static function hasRole(string ...$allowedRoles): void
    {

        self::check();

        $currentRole = (string) Session::get('role');

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

    public static function guestOnly(): void
    {
        Session::start();

        if (Session::has('id_user') && Session::has('role')) {
            redirect(getRoleDashboard((string) Session::get('role')));
        }
    }

    private static function isApiRequest(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $uri    = $_SERVER['REQUEST_URI'] ?? '';

        return str_contains($accept, 'application/json') 
            || str_contains($uri, '/api/')
            || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    }
}

if (!class_exists('AuthMiddleWare', false)) {
    class_alias('AuthMiddleware', 'AuthMiddleWare');
}
