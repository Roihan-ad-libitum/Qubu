<?php
/**
 * Front Controller Utama Qubu
 * Mengatur seluruh perutean aplikasi berbasis query parameter (?page=...)
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Session.php';
require_once __DIR__ . '/core/Validator.php';
require_once __DIR__ . '/core/AuthMiddleWare.php';
require_once __DIR__ . '/app/Controllers/AuthController.php';
require_once __DIR__ . '/app/Controllers/MahasiswaController.php';
require_once __DIR__ . '/app/Controllers/DosenController.php';

// Pastikan sesi selalu aktif untuk setiap permintaan
Session::start();

// Ambil rute halaman dari URL, default ke 'home'
$page = $_GET['page'] ?? 'home';

switch ($page) {
    case 'home':
        require_once __DIR__ . '/views/home.php';
        break;

    case 'login':
        (new AuthController())->login();
        break;

    case 'logout':
        (new AuthController())->logout();
        break;

    case 'register':
        (new AuthController())->register();
        break;

    case 'profile':
        if (Session::get('role') === ROLE_DOSEN) {
            (new DosenController())->profile();
        } else {
            (new MahasiswaController())->profile();
        }
        break;

    case 'dosen-profile':
        (new DosenController())->profile();
        break;

    case 'mahasiswa-profile':
        (new MahasiswaController())->profile();
        break;

    case 'mahasiswa-dashboard':
        // Proteksi route: hanya pengguna dengan role 'peserta' yang diizinkan
        AuthMiddleware::hasRole(ROLE_PESERTA);

        $dashboardFile = __DIR__ . '/views/mahasiswa/dashboard.php';
        if (file_exists($dashboardFile) && filesize($dashboardFile) > 10) {
            require_once $dashboardFile;
        } else {
            // Tampilan sementara sebelum Sprint Dashboard dibangun
            renderDashboardPlaceholder('Dashboard Peserta / Mahasiswa', ROLE_PESERTA);
        }
        break;

    case 'dosen-dashboard':
        // Proteksi route: hanya pengguna dengan role 'dosen' yang diizinkan
        AuthMiddleware::hasRole(ROLE_DOSEN);

        renderDashboardPlaceholder('Dashboard Dosen Pembimbing', ROLE_DOSEN);
        break;

    case 'admin-dashboard':
        // Proteksi route: hanya pengguna dengan role 'admin' yang diizinkan
        AuthMiddleware::hasRole(ROLE_ADMIN);

        renderDashboardPlaceholder('Dashboard Administrator', ROLE_ADMIN);
        break;

    default:
        // Rute tidak dikenal: arahkan sesuai status login
        if (Session::has('id_user') && Session::has('role')) {
            redirect(getRoleDashboard(Session::get('role')));
        } else {
            redirect('?page=home');
        }
        break;
}

/**
 * Helper render tampilan sementara untuk dashboard agar login dapat diuji secara nyata
 */
function renderDashboardPlaceholder(string $title, string $role): void
{
    $namaLengkap = e(Session::get('nama_lengkap', 'Pengguna'));
    $username    = e(Session::get('username', '-'));
    $email       = e(Session::get('email', '-'));
    $logoutUrl   = url('?page=logout');
    $appName     = APP_NAME;

    echo <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title} - {$appName}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: system-ui, -apple-system, sans-serif; }
        body { background: #f3f4f6; color: #1f2937; min-height: 100vh; display: flex; flex-direction: column; }
        header { background: #1e3a8a; color: #fff; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        header h1 { font-size: 1.25rem; font-weight: 600; }
        .container { max-width: 900px; margin: 2rem auto; padding: 0 1rem; width: 100%; flex: 1; }
        .card { background: #fff; border-radius: 8px; padding: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #e5e7eb; }
        .badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 600; background: #dbeafe; color: #1e40af; text-transform: uppercase; margin-bottom: 1rem; }
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin: 1.5rem 0; }
        .info-item { background: #f9fafb; padding: 1rem; border-radius: 6px; border: 1px solid #e5e7eb; }
        .info-label { font-size: 0.75rem; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem; }
        .info-val { font-size: 1rem; font-weight: 600; color: #111827; }
        .btn-logout { background: #ef4444; color: #fff; text-decoration: none; padding: 0.6rem 1.25rem; border-radius: 6px; font-size: 0.875rem; font-weight: 500; transition: background 0.2s; }
        .btn-logout:hover { background: #dc2626; }
        .alert-success { background: #ecfdf5; border-left: 4px solid #10b981; color: #065f46; padding: 1rem; border-radius: 4px; margin-bottom: 1.5rem; }
    </style>
</head>
<body>
    <header>
        <h1>{$appName}</h1>
        <a href="{$logoutUrl}" class="btn-logout">Keluar (Logout)</a>
    </header>
    <main class="container">
        <div class="card">
            <span class="badge">Peran: {$role}</span>
            <h2>Selamat Datang, {$namaLengkap}!</h2>
            <p style="color: #4b5563; margin-top: 0.5rem;">Anda berhasil masuk ke sistem dengan autentikasi yang aman.</p>
            
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Nama Lengkap</div>
                    <div class="info-val">{$namaLengkap}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Username</div>
                    <div class="info-val">{$username}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email Terdaftar</div>
                    <div class="info-val">{$email}</div>
                </div>
            </div>

            <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end;">
                <a href="{$logoutUrl}" class="btn-logout">Keluar dari Akun</a>
            </div>
        </div>
    </main>
</body>
</html>
HTML;
}
