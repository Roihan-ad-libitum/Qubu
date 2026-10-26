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
        $role             = $_GET['role'] ?? null;
        $step             = (int) ($_GET['step'] ?? 1);
        $error            = Session::getFlash('error');
        $success          = Session::getFlash('success');
        $old              = Session::getFlash('old') ?? [];
        $step1Data        = Session::get('reg_mhs_step1') ?? [];
        $peminatanOptions = $this->mahasiswaModel->getBidangPeminatanOptions();

        $title = $role === 'dosen' 
            ? 'Daftar Akun Dosen - ' . APP_NAME 
            : ($role === 'mahasiswa' 
                ? ($step === 2 ? 'Lengkapi Profil - ' . APP_NAME : 'Daftar Akun Mahasiswa - ' . APP_NAME)
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
     * Mendukung multi-step registration (Langkah 1: Biodata, Langkah 2: Lengkapi Profil & Foto)
     */
    private function processRegisterMahasiswa(): void
    {
        $step = (int) ($_POST['step'] ?? 1);

        // =========================================================================
        // A. JIKA SUBMIT LANGKAH 1 (BIODATA MAHASISWA)
        // =========================================================================
        if ($step === 1) {
            $npm           = trim($_POST['npm'] ?? '');
            $namaMahasiswa = trim($_POST['nama_mahasiswa'] ?? $_POST['nama_lengkap'] ?? '');
            $programStudi  = trim($_POST['program_studi'] ?? '');
            $angkatan      = trim($_POST['angkatan'] ?? '');

            // 1. Validasi kolom wajib biodata
            if (empty($npm) || empty($namaMahasiswa) || empty($programStudi) || empty($angkatan)) {
                Session::setFlash('error', 'Semua kolom bertanda wajib (NPM, Nama Lengkap, Program Studi, Angkatan) harus diisi.');
                Session::setFlash('old', $_POST);
                redirect('?page=register&role=mahasiswa&step=1');
            }

            // 2. Cek keunikan NPM
            if ($this->mahasiswaModel->isNpmExists($npm)) {
                Session::setFlash('error', 'NPM "' . e($npm) . '" sudah terdaftar. Silakan masuk menggunakan akun Anda.');
                Session::setFlash('old', $_POST);
                redirect('?page=register&role=mahasiswa&step=1');
            }

            // 3. Simpan data langkah 1 ke sesi
            Session::set('reg_mhs_step1', [
                'npm'            => $npm,
                'nama_mahasiswa' => $namaMahasiswa,
                'program_studi'  => $programStudi,
                'angkatan'       => $angkatan,
            ]);

            // Lanjut ke Step 2 (Lengkapi Profil)
            redirect('?page=register&role=mahasiswa&step=2');
        }

        // =========================================================================
        // B. JIKA SUBMIT LANGKAH 2 (LENGKAPI PROFIL: FOTO, USERNAME, MINAT, PASSWORD)
        // =========================================================================
        // 1. Ambil data langkah 1 dari POST (hidden) atau sesi
        $step1Session  = Session::get('reg_mhs_step1') ?? [];
        $npm           = trim($_POST['npm'] ?? $step1Session['npm'] ?? '');
        $namaMahasiswa = trim($_POST['nama_mahasiswa'] ?? $step1Session['nama_mahasiswa'] ?? '');
        $programStudi  = trim($_POST['program_studi'] ?? $step1Session['program_studi'] ?? '');
        $angkatan      = trim($_POST['angkatan'] ?? $step1Session['angkatan'] ?? '');

        // Validasi kelengkapan data langkah 1
        if (empty($npm) || empty($namaMahasiswa) || empty($programStudi) || empty($angkatan)) {
            Session::setFlash('error', 'Data biodata belum lengkap. Silakan lengkapi langkah 1 terlebih dahulu.');
            Session::setFlash('old', $_POST);
            redirect('?page=register&role=mahasiswa&step=1');
        }

        // Cek kembali keunikan NPM
        if ($this->mahasiswaModel->isNpmExists($npm)) {
            Session::setFlash('error', 'NPM "' . e($npm) . '" sudah terdaftar. Silakan masuk menggunakan akun Anda.');
            Session::setFlash('old', $_POST);
            redirect('?page=register&role=mahasiswa&step=1');
        }

        // 2. Ambil data langkah 2
        $username        = trim($_POST['username'] ?? '');
        $bidangMinat     = trim($_POST['bidang_minat'] ?? $_POST['bidang_peminatan'] ?? 'Umum');
        $password        = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        // Validasi kolom langkah 2
        if (empty($username)) {
            Session::setFlash('error', 'Username wajib diisi.');
            Session::setFlash('old', $_POST);
            redirect('?page=register&role=mahasiswa&step=2');
        }

        if (strlen($username) < 3) {
            Session::setFlash('error', 'Username minimal 3 karakter.');
            Session::setFlash('old', $_POST);
            redirect('?page=register&role=mahasiswa&step=2');
        }

        if ($this->userModel->isUsernameExists($username)) {
            Session::setFlash('error', 'Username "' . e($username) . '" sudah digunakan. Silakan pilih username lain.');
            Session::setFlash('old', $_POST);
            redirect('?page=register&role=mahasiswa&step=2');
        }

        if (empty($password) || strlen($password) < 6) {
            Session::setFlash('error', 'Kata Sandi wajib diisi dan minimal 6 karakter.');
            Session::setFlash('old', $_POST);
            redirect('?page=register&role=mahasiswa&step=2');
        }

        if ($password !== $passwordConfirm) {
            Session::setFlash('error', 'Konfirmasi Kata Sandi tidak cocok.');
            Session::setFlash('old', $_POST);
            redirect('?page=register&role=mahasiswa&step=2');
        }

        // 3. Handle Upload Foto Profil jika diunggah
        $fotoPath = null;
        if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
            $fileTmp  = $_FILES['foto_profil']['tmp_name'];
            $fileSize = $_FILES['foto_profil']['size'];
            $origName = $_FILES['foto_profil']['name'];

            // Cek ukuran max 5MB
            if ($fileSize > 5 * 1024 * 1024) {
                Session::setFlash('error', 'Ukuran foto profil maksimal 5MB.');
                Session::setFlash('old', $_POST);
                redirect('?page=register&role=mahasiswa&step=2');
            }

            // Validasi ekstensi
            $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($ext, $allowedExt)) {
                Session::setFlash('error', 'Format foto tidak didukung. Harap unggah file JPG, JPEG, PNG, atau WEBP.');
                Session::setFlash('old', $_POST);
                redirect('?page=register&role=mahasiswa&step=2');
            }

            // Validasi MIME type jika finfo tersedia
            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $fileTmp);
                finfo_close($finfo);
                $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
                if (!in_array($mimeType, $allowedMimes)) {
                    Session::setFlash('error', 'File yang diunggah bukan gambar yang valid.');
                    Session::setFlash('old', $_POST);
                    redirect('?page=register&role=mahasiswa&step=2');
                }
            }

            // Siapkan direktori penyimpanan
            $uploadDir = __DIR__ . '/../../public/uploads/profile';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $safeNpm  = preg_replace('/[^a-zA-Z0-9_-]/', '', $npm);
            $filename = 'mhs_' . $safeNpm . '_' . time() . '.' . $ext;
            $destPath = $uploadDir . '/' . $filename;

            if (move_uploaded_file($fileTmp, $destPath)) {
                $fotoPath = 'public/uploads/profile/' . $filename;
            }
        }

        $email = $username . '@student.unsika.ac.id';
        if ($this->userModel->isEmailExists($email)) {
            $email = $username . '_' . time() . '@student.unsika.ac.id';
        }
        $noHp = '-';

        // 4. Simpan akun & profil mahasiswa dalam transaksi database atomik
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
                'foto'           => $fotoPath,
            ]);

            // Bersihkan data session pendaftaran
            Session::remove('reg_mhs_step1');

            Session::setFlash('success', 'Pendaftaran Mahasiswa berhasil! Akun Anda telah siap. Silakan masuk menggunakan Username dan Kata Sandi Anda.');
            redirect('?page=login');
        } catch (Exception $e) {
            Session::setFlash('error', 'Terjadi kesalahan sistem saat mendaftar: ' . $e->getMessage());
            Session::setFlash('old', $_POST);
            redirect('?page=register&role=mahasiswa&step=2');
        }
    }
}
