<?php
/**
 * Controller Autentikasi (Login & Logout)
 * Menangani alur masuk pengguna multi-role (peserta, dosen, admin).
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/../../core/Validator.php';
require_once __DIR__ . '/../../core/AuthMiddleWare.php';
require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Models/DosenModel.php';
require_once __DIR__ . '/../Models/MahasiswaModel.php';

class AuthController
{
    private UserModel $userModel;
    private DosenModel $dosenModel;
    private MahasiswaModel $mahasiswaModel;

    public function __construct(
        ?UserModel $userModel = null,
        ?DosenModel $dosenModel = null,
        ?MahasiswaModel $mahasiswaModel = null
    ) {
        $this->userModel = $userModel ?? new UserModel();
        $this->dosenModel = $dosenModel ?? new DosenModel();
        $this->mahasiswaModel = $mahasiswaModel ?? new MahasiswaModel();
    }

    /**
     * Menangani halaman & proses login (GET dan POST)
     */
    public function login(): void
    {
        Session::start();

        // 1. Jika sudah login sebelumnya, langsung arahkan ke dashboard masing-masing
        AuthMiddleware::guestOnly();

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

    /**
     * Menangani halaman pemilihan peran & form pendaftaran
     */
    public function register(): void
    {
        Session::start();
        AuthMiddleware::guestOnly();

        // Jika request POST, proses pendaftaran sesuai role
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $role = $_POST['role'] ?? 'dosen';
            if ($role === 'dosen') {
                $this->processRegisterDosen();
            } else {
                $this->processRegisterMahasiswa();
            }
            return;
        }

        // Jika request GET, render tampilan pemilihan peran atau form
        $role    = $_GET['role'] ?? null;
        $error   = Session::getFlash('error');
        $success = Session::getFlash('success');
        $old     = Session::getFlash('old') ?? [];
        $title   = $role === 'dosen' ? 'Daftar Akun Dosen - ' . APP_NAME 
                 : ($role === 'mahasiswa' ? 'Daftar Akun Mahasiswa - ' . APP_NAME 
                 : 'Ingin mendaftar sebagai apa? - ' . APP_NAME);

        require_once __DIR__ . '/../../views/auth/register.php';
    }

    /**
     * Memproses data registrasi Dosen Pembimbing
     */
    private function processRegisterDosen(): void
    {
        $validator = new Validator($_POST, [
            'nama_dosen'       => 'required|max:100',
            'nidn'             => 'required|max:20',
            'bidang_keahlian'  => 'required|max:50',
            'email'            => 'required|email|max:100',
            'no_hp'            => 'required|max:20',
            'username'         => 'required|min:3|max:50',
            'password'         => 'required|min:6',
            'password_confirm' => 'required',
        ], [
            'nama_dosen'       => 'Nama Lengkap Dosen',
            'nidn'             => 'NIDN',
            'bidang_keahlian'  => 'Bidang Keahlian',
            'email'            => 'Email',
            'no_hp'            => 'Nomor Handphone',
            'username'         => 'Username',
            'password'         => 'Password',
            'password_confirm' => 'Konfirmasi Password',
        ]);

        if ($validator->fails()) {
            Session::setFlash('error', $validator->getFirstError() ?? 'Data pendaftaran belum lengkap.');
            Session::setFlash('old', $_POST);
            redirect('?page=register&role=dosen');
        }

        $data = $validator->validated();

        // 1. Cek kecocokan konfirmasi password
        if ($data['password'] !== $_POST['password_confirm']) {
            Session::setFlash('error', 'Konfirmasi password tidak cocok dengan password.');
            Session::setFlash('old', $_POST);
            redirect('?page=register&role=dosen');
        }

        // 2. Cek keunikan username
        if ($this->userModel->isUsernameExists($data['username'])) {
            Session::setFlash('error', 'Username "' . e($data['username']) . '" sudah digunakan. Silakan pilih username lain.');
            Session::setFlash('old', $_POST);
            redirect('?page=register&role=dosen');
        }

        // 3. Cek keunikan email
        if ($this->userModel->isEmailExists($data['email'])) {
            Session::setFlash('error', 'Email "' . e($data['email']) . '" sudah terdaftar.');
            Session::setFlash('old', $_POST);
            redirect('?page=register&role=dosen');
        }

        // 4. Cek keunikan NIDN
        if ($this->dosenModel->isNidnExists($data['nidn'])) {
            Session::setFlash('error', 'NIDN "' . e($data['nidn']) . '" sudah terdaftar.');
            Session::setFlash('old', $_POST);
            redirect('?page=register&role=dosen');
        }

        // 5. Simpan data akun & profil dosen
        try {
            $this->dosenModel->registerDosen([
                'username' => $data['username'],
                'password' => $data['password'],
                'no_hp'    => $data['no_hp'],
                'email'    => $data['email'],
            ], [
                'nama_dosen'      => $data['nama_dosen'],
                'bidang_keahlian' => $data['bidang_keahlian'],
                'nidn'            => $data['nidn'],
                'status'          => 'bersedia',
            ]);

            Session::setFlash('success', 'Pendaftaran akun Dosen berhasil! Silakan masuk dengan username dan password Anda.');
            redirect('?page=login');
        } catch (Exception $e) {
            Session::setFlash('error', 'Terjadi kesalahan sistem saat mendaftar: ' . $e->getMessage());
            Session::setFlash('old', $_POST);
            redirect('?page=register&role=dosen');
        }
    }

    /**
     * Memproses data registrasi Mahasiswa / Peserta
     */
    private function processRegisterMahasiswa(): void
    {
        $validator = new Validator($_POST, [
            'nama_mahasiswa'   => 'required|max:100',
            'npm'              => 'required|max:20',
            'program_studi'    => 'required|max:30',
            'angkatan'         => 'required|max:4',
            'bidang_minat'     => 'max:30',
            'email'            => 'required|email|max:100',
            'no_hp'            => 'required|max:20',
            'username'         => 'required|min:3|max:50',
            'password'         => 'required|min:6',
            'password_confirm' => 'required',
        ], [
            'nama_mahasiswa'   => 'Nama Mahasiswa',
            'npm'              => 'NPM',
            'program_studi'    => 'Program Studi',
            'angkatan'         => 'Angkatan',
            'bidang_minat'     => 'Bidang Minat',
            'email'            => 'Email',
            'no_hp'            => 'Nomor Handphone',
            'username'         => 'Username',
            'password'         => 'Password',
            'password_confirm' => 'Konfirmasi Password',
        ]);

        if ($validator->fails()) {
            Session::setFlash('error', $validator->getFirstError() ?? 'Data pendaftaran belum lengkap.');
            Session::setFlash('old', $_POST);
            redirect('?page=register&role=mahasiswa');
        }

        $data = $validator->validated();

        if ($data['password'] !== $_POST['password_confirm']) {
            Session::setFlash('error', 'Konfirmasi password tidak cocok dengan password.');
            Session::setFlash('old', $_POST);
            redirect('?page=register&role=mahasiswa');
        }

        if ($this->userModel->isUsernameExists($data['username'])) {
            Session::setFlash('error', 'Username "' . e($data['username']) . '" sudah digunakan. Silakan pilih username lain.');
            Session::setFlash('old', $_POST);
            redirect('?page=register&role=mahasiswa');
        }

        if ($this->userModel->isEmailExists($data['email'])) {
            Session::setFlash('error', 'Email "' . e($data['email']) . '" sudah terdaftar.');
            Session::setFlash('old', $_POST);
            redirect('?page=register&role=mahasiswa');
        }

        if ($this->mahasiswaModel->isNpmExists($data['npm'])) {
            Session::setFlash('error', 'NPM "' . e($data['npm']) . '" sudah terdaftar.');
            Session::setFlash('old', $_POST);
            redirect('?page=register&role=mahasiswa');
        }

        try {
            $this->mahasiswaModel->registerMahasiswa([
                'username' => $data['username'],
                'password' => $data['password'],
                'no_hp'    => $data['no_hp'],
                'email'    => $data['email'],
            ], [
                'npm'            => $data['npm'],
                'nama_mahasiswa' => $data['nama_mahasiswa'],
                'program_studi'  => $data['program_studi'],
                'angkatan'       => $data['angkatan'],
                'bidang_minat'   => $data['bidang_minat'] ?? 'Umum',
                'bio'            => '',
            ]);

            Session::setFlash('success', 'Pendaftaran akun Mahasiswa berhasil! Silakan masuk dengan akun baru Anda.');
            redirect('?page=login');
        } catch (Exception $e) {
            Session::setFlash('error', 'Terjadi kesalahan sistem saat mendaftar: ' . $e->getMessage());
            Session::setFlash('old', $_POST);
            redirect('?page=register&role=mahasiswa');
        }
    }
}
