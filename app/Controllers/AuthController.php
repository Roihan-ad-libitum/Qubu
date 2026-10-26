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
     * Mendukung form biodata sesuai mockup (NIDN/NIP, Nama Lengkap & Gelar, Bidang Keahlian & Fokus Riset)
     */
    private function processRegisterDosen(): void
    {
        // 1. Ambil input biodata dosen (mendukung nama field fleksibel)
        $nidn           = trim($_POST['nidn'] ?? $_POST['nidn_nip'] ?? '');
        $namaDosen      = trim($_POST['nama_dosen'] ?? $_POST['nama_lengkap'] ?? '');
        $bidangKeahlian = trim($_POST['bidang_keahlian'] ?? $_POST['bidang_riset'] ?? '');

        // 2. Validasi kolom wajib
        if (empty($nidn) || empty($namaDosen) || empty($bidangKeahlian)) {
            Session::setFlash('error', 'Semua kolom bertanda wajib (NIDN/NIP, Nama Lengkap & Gelar, Bidang Keahlian & Fokus Riset) harus diisi.');
            Session::setFlash('old', $_POST);
            redirect('?page=register&role=dosen');
        }

        // 3. Cek keunikan NIDN
        if ($this->dosenModel->isNidnExists($nidn)) {
            Session::setFlash('error', 'NIDN/NIP "' . e($nidn) . '" sudah terdaftar. Silakan masuk menggunakan akun Anda.');
            Session::setFlash('old', $_POST);
            redirect('?page=register&role=dosen');
        }

        // 4. Kredensial akun: gunakan input jika ada, atau buat otomatis berdasarkan NIDN
        $username = !empty($_POST['username']) ? trim($_POST['username']) : $nidn;
        $email    = !empty($_POST['email']) ? trim($_POST['email']) : ($nidn . '@dosen.unsika.ac.id');
        $noHp     = !empty($_POST['no_hp']) ? trim($_POST['no_hp']) : '-';

        // Password: jika diberikan pada form, validasi; jika tidak, default menggunakan NIDN
        if (!empty($_POST['password'])) {
            if (strlen($_POST['password']) < 6) {
                Session::setFlash('error', 'Password minimal 6 karakter.');
                Session::setFlash('old', $_POST);
                redirect('?page=register&role=dosen');
            }
            if (isset($_POST['password_confirm']) && $_POST['password'] !== $_POST['password_confirm']) {
                Session::setFlash('error', 'Konfirmasi password tidak cocok.');
                Session::setFlash('old', $_POST);
                redirect('?page=register&role=dosen');
            }
            $password = $_POST['password'];
        } else {
            $password = $nidn;
        }

        // 5. Cek keunikan username
        if ($this->userModel->isUsernameExists($username)) {
            if (empty($_POST['username'])) {
                $username = 'dsn_' . $nidn;
            } else {
                Session::setFlash('error', 'Username "' . e($username) . '" sudah digunakan. Silakan pilih username lain.');
                Session::setFlash('old', $_POST);
                redirect('?page=register&role=dosen');
            }
        }

        // 6. Cek keunikan email
        if ($this->userModel->isEmailExists($email)) {
            if (!empty($_POST['email'])) {
                Session::setFlash('error', 'Email "' . e($email) . '" sudah terdaftar.');
                Session::setFlash('old', $_POST);
                redirect('?page=register&role=dosen');
            } else {
                $email = $nidn . '_' . time() . '@dosen.unsika.ac.id';
            }
        }

        // 7. Simpan data akun & profil dosen dalam transaksi atomik
        try {
            $this->dosenModel->registerDosen([
                'username' => $username,
                'password' => $password,
                'no_hp'    => $noHp,
                'email'    => $email,
            ], [
                'nama_dosen'      => $namaDosen,
                'bidang_keahlian' => $bidangKeahlian,
                'nidn'            => $nidn,
                'status'          => 'bersedia',
            ]);

            Session::setFlash('success', 'Pendaftaran Dosen berhasil! Silakan masuk dengan NIDN/NIP (' . e($nidn) . ') sebagai Username dan Password awal.');
            redirect('?page=login');
        } catch (Exception $e) {
            Session::setFlash('error', 'Terjadi kesalahan sistem saat mendaftar: ' . $e->getMessage());
            Session::setFlash('old', $_POST);
            redirect('?page=register&role=dosen');
        }
    }

    /**
     * Memproses data registrasi Mahasiswa / Peserta
     * Mendukung form biodata sesuai mockup (NPM, Nama Lengkap, Program Studi, Angkatan, Bidang Peminatan)
     */
    private function processRegisterMahasiswa(): void
    {
        // 1. Ambil input biodata (mendukung nama field fleksibel)
        $namaMahasiswa = trim($_POST['nama_mahasiswa'] ?? $_POST['nama_lengkap'] ?? '');
        $npm           = trim($_POST['npm'] ?? '');
        $programStudi  = trim($_POST['program_studi'] ?? '');
        $angkatan      = trim($_POST['angkatan'] ?? '');
        $bidangMinat   = trim($_POST['bidang_minat'] ?? $_POST['bidang_peminatan'] ?? 'Umum');

        // 2. Validasi kolom wajib biodata
        if (empty($npm) || empty($namaMahasiswa) || empty($programStudi) || empty($angkatan)) {
            Session::setFlash('error', 'Semua kolom bertanda wajib (NPM, Nama Lengkap, Program Studi, Angkatan) harus diisi.');
            Session::setFlash('old', $_POST);
            redirect('?page=register&role=mahasiswa');
        }

        // 3. Cek keunikan NPM
        if ($this->mahasiswaModel->isNpmExists($npm)) {
            Session::setFlash('error', 'NPM "' . e($npm) . '" sudah terdaftar. Silakan masuk menggunakan akun Anda.');
            Session::setFlash('old', $_POST);
            redirect('?page=register&role=mahasiswa');
        }

        // 4. Kredensial akun: gunakan input jika ada, atau buat otomatis berdasarkan NPM
        $username = !empty($_POST['username']) ? trim($_POST['username']) : $npm;
        $email    = !empty($_POST['email']) ? trim($_POST['email']) : ($npm . '@student.unsika.ac.id');
        $noHp     = !empty($_POST['no_hp']) ? trim($_POST['no_hp']) : '-';

        // Password: jika diberikan pada form, lakukan pengecekan; jika tidak, default menggunakan NPM
        if (!empty($_POST['password'])) {
            if (strlen($_POST['password']) < 6) {
                Session::setFlash('error', 'Password minimal 6 karakter.');
                Session::setFlash('old', $_POST);
                redirect('?page=register&role=mahasiswa');
            }
            if (isset($_POST['password_confirm']) && $_POST['password'] !== $_POST['password_confirm']) {
                Session::setFlash('error', 'Konfirmasi password tidak cocok.');
                Session::setFlash('old', $_POST);
                redirect('?page=register&role=mahasiswa');
            }
            $password = $_POST['password'];
        } else {
            $password = $npm;
        }

        // 5. Cek keunikan username
        if ($this->userModel->isUsernameExists($username)) {
            if (empty($_POST['username'])) {
                $username = 'mhs_' . $npm;
            } else {
                Session::setFlash('error', 'Username "' . e($username) . '" sudah digunakan. Silakan pilih username lain.');
                Session::setFlash('old', $_POST);
                redirect('?page=register&role=mahasiswa');
            }
        }

        // 6. Cek keunikan email
        if ($this->userModel->isEmailExists($email)) {
            if (!empty($_POST['email'])) {
                Session::setFlash('error', 'Email "' . e($email) . '" sudah terdaftar.');
                Session::setFlash('old', $_POST);
                redirect('?page=register&role=mahasiswa');
            } else {
                $email = $npm . '_' . time() . '@student.unsika.ac.id';
            }
        }

        // 7. Simpan akun & profil mahasiswa dalam transaksi database atomik
        try {
            $this->mahasiswaModel->registerMahasiswa([
                'username' => $username,
                'password' => $password,
                'no_hp'    => $noHp,
                'email'    => $email,
            ], [
                'npm'            => $npm,
                'nama_mahasiswa' => $namaMahasiswa,
                'program_studi'  => $programStudi,
                'angkatan'       => $angkatan,
                'bidang_minat'   => !empty($bidangMinat) ? $bidangMinat : 'Umum',
                'bio'            => '',
            ]);

            Session::setFlash('success', 'Pendaftaran Mahasiswa berhasil! Silakan masuk dengan NPM (' . e($npm) . ') sebagai Username dan Password awal.');
            redirect('?page=login');
        } catch (Exception $e) {
            Session::setFlash('error', 'Terjadi kesalahan sistem saat mendaftar: ' . $e->getMessage());
            Session::setFlash('old', $_POST);
            redirect('?page=register&role=mahasiswa');
        }
    }
}
