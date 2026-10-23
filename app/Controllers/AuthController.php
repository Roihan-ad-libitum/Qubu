<?php
/**
 * Controller Autentikasi (Login & Logout)
 * Menangani alur masuk pengguna multi-role (peserta, dosen, admin).
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/../../core/Validator.php';
require_once __DIR__ . '/../Models/UserModel.php';

class AuthController
{
    private UserModel $userModel;

    public function __construct(?UserModel $userModel = null)
    {
        $this->userModel = $userModel ?? new UserModel();
    }

    /**
     * Menangani halaman & proses login (GET dan POST)
     */
    public function login(): void
    {
        Session::start();

        // 1. Jika sudah login sebelumnya, langsung arahkan ke dashboard masing-masing
        if (Session::has('id_user') && Session::has('role')) {
            redirect(getRoleDashboard(Session::get('role')));
        }

        // 2. Jika request method POST, proses autentikasi
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processLogin();
            return;
        }

        // 3. Jika request method GET, siapkan data & tampilkan view form login
        $error       = Session::getFlash('error');
        $success     = Session::getFlash('success');
        $info        = Session::getFlash('info');
        $oldIdentity = Session::getFlash('old_identity') ?? '';
        $title       = 'Masuk ke Akun - ' . APP_NAME;

        require_once __DIR__ . '/../../views/auth/login.php';
    }

    /**
     * Memproses data submit login
     */
    private function processLogin(): void
    {
        // Validasi input
        $validator = new Validator($_POST, [
            'identity' => 'required|max:100',
            'password' => 'required',
        ], [
            'identity' => 'Username atau Email',
            'password' => 'Password',
        ]);

        if ($validator->fails()) {
            $errorMessage = $validator->getFirstError('identity') ?? $validator->getFirstError('password');
            Session::setFlash('error', $errorMessage);
            Session::setFlash('old_identity', $_POST['identity'] ?? '');
            redirect('?page=login');
        }

        $validated = $validator->validated();
        $identity  = $validated['identity'];
        $password  = $validated['password'];

        // Cari user di database
        $user = $this->userModel->findByIdentity($identity);

        // Verifikasi kredensial
        if (!$user || !$this->userModel->verifyPassword($password, $user['password'])) {
            Session::setFlash('error', 'Username/Email atau password salah.');
            Session::setFlash('old_identity', $identity);
            redirect('?page=login');
        }

        // Login sukses: perbarui ID sesi untuk mencegah Session Fixation
        Session::regenerate();

        // Simpan informasi pengguna ke dalam sesi
        Session::set('id_user', (int) $user['id_user']);
        Session::set('username', $user['username']);
        Session::set('email', $user['email']);
        Session::set('role', $user['role']);
        Session::set('nama_lengkap', $user['nama_lengkap']);
        Session::set('id_mahasiswa', $user['id_mahasiswa'] ? (int) $user['id_mahasiswa'] : null);
        Session::set('id_dosen', $user['id_dosen'] ? (int) $user['id_dosen'] : null);

        // Pesan selamat datang & redirect ke dashboard sesuai role
        Session::setFlash('success', 'Selamat datang kembali, ' . $user['nama_lengkap'] . '!');
        redirect(getRoleDashboard($user['role']));
    }

    /**
     * Menangani proses logout
     */
    public function logout(): void
    {
        Session::start();
        Session::destroy();

        // Buat sesi baru untuk menampung pesan info logout
        Session::start();
        Session::setFlash('info', 'Anda telah berhasil keluar dari sistem.');
        redirect('?page=login');
    }
}
