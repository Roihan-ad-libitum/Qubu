<?php

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/../../core/Csrf.php';
require_once __DIR__ . '/../../core/Validator.php';
require_once __DIR__ . '/../../core/AuthMiddleware.php';
require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Models/DosenModel.php';
require_once __DIR__ . '/../Models/MahasiswaModel.php';
require_once __DIR__ . '/../Helpers/UploadHelper.php';

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

    public function login(): void
    {
        Session::start();

        AuthMiddleware::guestOnly();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processLogin();
            return;
        }

        $error       = Session::getFlash('error');
        $success     = Session::getFlash('success');
        $info        = Session::getFlash('info');
        $oldIdentity = Session::getFlash('old_identity') ?? '';
        $csrfToken   = Csrf::token();
        $title       = 'Masuk ke Akun - ' . APP_NAME;

        require_once __DIR__ . '/../../views/auth/login.php';
    }

    private function processLogin(): void
    {
        if (!Csrf::isValid($_POST['_csrf'] ?? null)) {
            Session::setFlash('error', 'Sesi formulir telah kedaluwarsa. Silakan ulangi proses masuk.');
            redirect('?page=login');
        }

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

        $user = $this->userModel->findByIdentity($identity);

        if (!$user || !$this->userModel->verifyPassword($password, $user['password'])) {
            Session::setFlash('error', 'Username/Email atau password salah.');
            Session::setFlash('old_identity', $identity);
            redirect('?page=login');
        }

        $this->startAuthenticatedSession($user);

        Session::setFlash('success', 'Selamat datang kembali, ' . $user['nama_lengkap'] . '!');
        redirect(getRoleDashboard($user['role']));
    }

    public function logout(): void
    {
        Session::start();
        Session::destroy();

        Session::start();
        Session::setFlash('info', 'Anda telah berhasil keluar dari sistem.');
        redirect('?page=login');
    }

    public function register(): void
    {
        Session::start();
        AuthMiddleware::guestOnly();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Csrf::isValid($_POST['_csrf'] ?? null)) {
                $roleFallback = ($_POST['role'] ?? '') === 'dosen' ? 'dosen' : 'mahasiswa';
                $stepFallback = (int) ($_POST['step'] ?? 1);
                Session::setFlash('error', 'Sesi formulir pendaftaran kedaluwarsa. Silakan coba kembali.');
                redirect('?page=register&role=' . $roleFallback . '&step=' . $stepFallback);
            }
            $role = $_POST['role'] ?? 'dosen';
            if ($role === 'dosen') {
                $this->processRegisterDosen();
            } else {
                $this->processRegisterMahasiswa();
            }
            return;
        }

        $role             = $_GET['role'] ?? null;
        $step             = (int) ($_GET['step'] ?? 1);
        if ($role === 'dosen' && $step === 2 && empty(Session::get('reg_dosen_step1'))) {
            Session::setFlash('error', 'Silakan isi biodata dasar dosen terlebih dahulu.');
            redirect('?page=register&role=dosen&step=1');
        }
        $error            = Session::getFlash('error');
        $success          = Session::getFlash('success');
        $old              = Session::getFlash('old') ?? [];
        $step1Data        = $role === 'dosen'
            ? (Session::get('reg_dosen_step1') ?? [])
            : (Session::get('reg_mhs_step1') ?? []);
        $peminatanOptions = BIDANG_QUBU;
        $prodiOptions     = PRODI_FASILKOM;
        $csrfToken        = Csrf::token();

        $title = $role === 'dosen'
            ? ($step === 2 ? 'Lengkapi Profil Dosen - ' . APP_NAME : 'Daftar Akun Dosen - ' . APP_NAME)
            : ($role === 'mahasiswa'
                ? ($step === 2 ? 'Lengkapi Profil - ' . APP_NAME : 'Daftar Akun Mahasiswa - ' . APP_NAME)
                : 'Ingin mendaftar sebagai apa? - ' . APP_NAME);

        require_once __DIR__ . '/../../views/auth/register.php';
    }

    private function processRegisterDosen(): void
    {
        $step = (int) ($_POST['step'] ?? 1);
        $step1Data = Session::get('reg_dosen_step1') ?? [];

        if ($step === 1) {
            $nidn = trim((string) ($_POST['nidn'] ?? $_POST['nidn_nip'] ?? ''));
            $namaDosen = trim((string) ($_POST['nama_dosen'] ?? $_POST['nama_lengkap'] ?? ''));
            if ($nidn === '' || $namaDosen === '') {
                Session::remove('reg_dosen_step1');
                $this->redirectWithError(
                    'NIDN/NIP dan nama lengkap wajib diisi.',
                    '?page=register&role=dosen&step=1'
                );
            }
            if (strlen($nidn) > 20 || strlen($namaDosen) > 100) {
                Session::remove('reg_dosen_step1');
                $this->redirectWithError(
                    'NIDN/NIP maksimal 20 karakter dan nama maksimal 100 karakter.',
                    '?page=register&role=dosen&step=1'
                );
            }
            if ($this->dosenModel->isNidnExists($nidn)) {
                Session::remove('reg_dosen_step1');
                $this->redirectWithError(
                    'NIDN/NIP tersebut sudah terdaftar. Silakan masuk menggunakan akun Anda.',
                    '?page=register&role=dosen&step=1'
                );
            }
            Session::set('reg_dosen_step1', [
                'nidn' => $nidn,
                'nama_dosen' => $namaDosen,
            ]);
            redirect('?page=register&role=dosen&step=2');
        }

        if ($step !== 2 || empty($step1Data['nidn']) || empty($step1Data['nama_dosen'])) {
            Session::remove('reg_dosen_step1');
            $this->redirectWithError(
                'Silakan lengkapi biodata dosen terlebih dahulu.',
                '?page=register&role=dosen&step=1'
            );
        }

        $nidn = (string) $step1Data['nidn'];
        $namaDosen = (string) $step1Data['nama_dosen'];
        $username = trim((string) ($_POST['username'] ?? ''));
        $bidangKeahlian = trim((string) ($_POST['bidang_keahlian'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $noHp = trim((string) ($_POST['no_hp'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($username === '' || $bidangKeahlian === '' || $email === '' || $noHp === '' || $password === '') {
            $this->redirectWithError(
                'Username, email, nomor telepon, bidang keahlian, dan kata sandi wajib diisi.',
                '?page=register&role=dosen&step=2'
            );
        }
        if (strlen($username) < 3 || strlen($username) > 100) {
            $this->redirectWithError('Username harus terdiri dari 3–100 karakter.', '?page=register&role=dosen&step=2');
        }
        if (!in_array($bidangKeahlian, BIDANG_QUBU, true)) {
            $this->redirectWithError('Pilih bidang keahlian yang tersedia.', '?page=register&role=dosen&step=2');
        }
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $this->redirectWithError('Masukkan alamat email dosen yang valid.', '?page=register&role=dosen&step=2');
        }
        if (!$this->isValidPhoneNumber($noHp)) {
            $this->redirectWithError('Masukkan nomor telepon yang valid (8–15 digit).', '?page=register&role=dosen&step=2');
        }
        if (strlen($password) < 6) {
            $this->redirectWithError('Kata sandi wajib diisi dan minimal 6 karakter.', '?page=register&role=dosen&step=2');
        }
        if ($password !== (string) ($_POST['password_confirm'] ?? '')) {
            $this->redirectWithError('Konfirmasi kata sandi tidak cocok.', '?page=register&role=dosen&step=2');
        }
        if ($this->dosenModel->isNidnExists($nidn)) {
            Session::remove('reg_dosen_step1');
            $this->redirectWithError(
                'NIDN/NIP tersebut sudah terdaftar. Silakan masuk menggunakan akun Anda.',
                '?page=register&role=dosen&step=1'
            );
        }
        if ($this->userModel->isUsernameExists($username)) {
            $this->redirectWithError('Username tersebut sudah digunakan. Silakan pilih username lain.', '?page=register&role=dosen&step=2');
        }
        if ($this->userModel->isEmailExists($email)) {
            $this->redirectWithError('Email tersebut sudah terdaftar. Gunakan alamat email lain.', '?page=register&role=dosen&step=2');
        }

        $fotoPath = null;
        if (isset($_FILES['foto_profil']) && ($_FILES['foto_profil']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            if (($_FILES['foto_profil']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                $this->redirectWithError('Foto profil gagal diunggah. Silakan pilih ulang file.', '?page=register&role=dosen&step=2');
            }
            try {
                $fotoPath = UploadHelper::uploadProfilePhoto($_FILES['foto_profil'], 'dosen', $nidn);
            } catch (RuntimeException $e) {
                $this->redirectWithError($e->getMessage(), '?page=register&role=dosen&step=2');
            }
            if ($fotoPath === null) {
                $this->redirectWithError('Foto profil tidak berhasil disimpan. Silakan coba unggah kembali.', '?page=register&role=dosen&step=2');
            }
        }

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
                'foto'            => $fotoPath,
            ]);
        } catch (Exception $e) {
            error_log('[Qubu Registration - Dosen] ' . $e->getMessage());
            if ($fotoPath !== null) {
                $uploadedFile = __DIR__ . '/../../' . $fotoPath;
                if (is_file($uploadedFile)) {
                    unlink($uploadedFile);
                }
            }
            $this->redirectWithError('Pendaftaran dosen gagal. Pastikan NIDN/NIP, username, dan email belum digunakan lalu coba kembali.', '?page=register&role=dosen&step=2');
        }

        Session::remove('reg_dosen_step1');
        try {
            $user = $this->userModel->findByIdentity($username);
        } catch (Exception $e) {
            error_log('[Qubu Registration Login - Dosen] ' . $e->getMessage());
            Session::setFlash('error', 'Akun dosen berhasil dibuat, tetapi sistem tidak dapat memulai sesi otomatis. Silakan masuk dengan akun yang baru dibuat.');
            redirect('?page=login');
        }
        if ($user === null) {
            Session::setFlash('error', 'Akun dosen berhasil dibuat, tetapi login otomatis gagal. Silakan masuk menggunakan akun yang baru dibuat.');
            redirect('?page=login');
        }
        $this->startAuthenticatedSession($user);
        Session::setFlash('success', 'Pendaftaran berhasil. Selamat datang, ' . $namaDosen . '!');
        redirect(getRoleDashboard($user['role']));
    }

    private function processRegisterMahasiswa(): void
    {
        $step = (int) ($_POST['step'] ?? 1);

        if ($step === 1) {
            $npm           = trim($_POST['npm'] ?? '');
            $namaMahasiswa = trim($_POST['nama_mahasiswa'] ?? $_POST['nama_lengkap'] ?? '');
            $programStudi  = trim($_POST['program_studi'] ?? '');
            $angkatan      = trim($_POST['angkatan'] ?? '');

            if (empty($npm) || empty($namaMahasiswa) || empty($programStudi) || empty($angkatan)) {
                $this->redirectWithError(
                    'Semua kolom bertanda wajib (NPM, Nama Lengkap, Program Studi, Angkatan) harus diisi.',
                    '?page=register&role=mahasiswa&step=1'
                );
            }

            if (!Validator::isValidAcademicProdi($programStudi)) {
                $this->redirectWithError(
                    'Pilih Program Studi resmi Fasilkom (Informatika atau Sistem Informasi).',
                    '?page=register&role=mahasiswa&step=1'
                );
            }

            if ($this->mahasiswaModel->isNpmExists($npm)) {
                $this->redirectWithError(
                    'NPM "' . e($npm) . '" sudah terdaftar. Silakan masuk menggunakan akun Anda.',
                    '?page=register&role=mahasiswa&step=1'
                );
            }

            Session::set('reg_mhs_step1', [
                'npm'            => $npm,
                'nama_mahasiswa' => $namaMahasiswa,
                'program_studi'  => $programStudi,
                'angkatan'       => $angkatan,
            ]);

            redirect('?page=register&role=mahasiswa&step=2');
        }

        $step1Session  = Session::get('reg_mhs_step1') ?? [];
        $npm           = trim($_POST['npm'] ?? $step1Session['npm'] ?? '');
        $namaMahasiswa = trim($_POST['nama_mahasiswa'] ?? $step1Session['nama_mahasiswa'] ?? '');
        $programStudi  = trim($_POST['program_studi'] ?? $step1Session['program_studi'] ?? '');
        $angkatan      = trim($_POST['angkatan'] ?? $step1Session['angkatan'] ?? '');

        if (empty($npm) || empty($namaMahasiswa) || empty($programStudi) || empty($angkatan)) {
            $this->redirectWithError(
                'Data biodata belum lengkap. Silakan lengkapi langkah 1 terlebih dahulu.',
                '?page=register&role=mahasiswa&step=1'
            );
        }

        if ($this->mahasiswaModel->isNpmExists($npm)) {
            $this->redirectWithError(
                'NPM "' . e($npm) . '" sudah terdaftar. Silakan masuk menggunakan akun Anda.',
                '?page=register&role=mahasiswa&step=1'
            );
        }

        $username        = trim($_POST['username'] ?? '');
        $bidangMinat     = trim($_POST['bidang_minat'] ?? $_POST['bidang_peminatan'] ?? 'Umum');
        $password        = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';
        $email           = trim((string) ($_POST['email'] ?? ''));
        $rawGithub       = trim((string) ($_POST['github'] ?? ''));
        $normalizedGithub = Validator::validateGithub($rawGithub);
        $noHp            = trim((string) ($_POST['no_hp'] ?? ''));

        if (empty($username)) {
            $this->redirectWithError('Username wajib diisi.', '?page=register&role=mahasiswa&step=2');
        }

        if (strlen($username) < 3) {
            $this->redirectWithError('Username minimal 3 karakter.', '?page=register&role=mahasiswa&step=2');
        }

        if ($this->userModel->isUsernameExists($username)) {
            $this->redirectWithError(
                'Username "' . e($username) . '" sudah digunakan. Silakan pilih username lain.',
                '?page=register&role=mahasiswa&step=2'
            );
        }

        if (empty($password) || strlen($password) < 6) {
            $this->redirectWithError('Kata Sandi wajib diisi dan minimal 6 karakter.', '?page=register&role=mahasiswa&step=2');
        }

        if ($password !== $passwordConfirm) {
            $this->redirectWithError('Konfirmasi Kata Sandi tidak cocok.', '?page=register&role=mahasiswa&step=2');
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $this->redirectWithError('Masukkan alamat email mahasiswa yang valid.', '?page=register&role=mahasiswa&step=2');
        }

        if ($normalizedGithub === null) {
            $this->redirectWithError(
                'Format akun GitHub tidak valid. Masukkan URL (https://github.com/username) atau username GitHub Anda.',
                '?page=register&role=mahasiswa&step=2'
            );
        }

        if (!$this->isValidPhoneNumber($noHp)) {
            $this->redirectWithError('Masukkan nomor telepon mahasiswa yang valid (8–15 digit).', '?page=register&role=mahasiswa&step=2');
        }

        if ($this->userModel->isEmailExists($email)) {
            $this->redirectWithError(
                'Email tersebut sudah terdaftar. Gunakan alamat email lain atau masuk ke akun Anda.',
                '?page=register&role=mahasiswa&step=2'
            );
        }

        $fotoPath = null;
        if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
            try {
                $fotoPath = UploadHelper::uploadProfilePhoto($_FILES['foto_profil'], 'mhs', $npm);
            } catch (RuntimeException $e) {
                $this->redirectWithError($e->getMessage(), '?page=register&role=mahasiswa&step=2');
            }
        }

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
                'github'         => $normalizedGithub !== '' ? $normalizedGithub : null,
            ]);
        } catch (Exception $e) {
            error_log('[Qubu Registration - Mahasiswa] ' . $e->getMessage());
            if ($fotoPath !== null) {
                UploadHelper::deleteFile($fotoPath);
            }
            $this->redirectWithError(
                'Pendaftaran mahasiswa gagal. Pastikan NPM, username, dan email belum digunakan lalu coba kembali.',
                '?page=register&role=mahasiswa&step=2'
            );
        }

        Session::remove('reg_mhs_step1');
        try {
            $user = $this->userModel->findByIdentity($username);
        } catch (Exception $e) {
            error_log('[Qubu Registration Login - Mahasiswa] ' . $e->getMessage());
            Session::setFlash('error', 'Akun mahasiswa berhasil dibuat, tetapi sistem tidak dapat memulai sesi otomatis. Silakan masuk dengan akun yang baru dibuat.');
            redirect('?page=login');
        }
        if ($user === null) {
            Session::setFlash('error', 'Akun mahasiswa berhasil dibuat, tetapi login otomatis gagal. Silakan masuk menggunakan akun yang baru dibuat.');
            redirect('?page=login');
        }
        $this->startAuthenticatedSession($user);
        Session::setFlash('success', 'Pendaftaran berhasil. Selamat datang, ' . $namaMahasiswa . '!');
        redirect(getRoleDashboard($user['role']));
    }

    private function startAuthenticatedSession(array $user): void
    {
        Session::regenerate();
        Session::set('id_user', (int) $user['id_user']);
        Session::set('username', $user['username']);
        Session::set('email', $user['email']);
        Session::set('role', $user['role']);
        Session::set('nama_lengkap', $user['nama_lengkap']);
        Session::set('id_mahasiswa', $user['id_mahasiswa'] ? (int) $user['id_mahasiswa'] : null);
        Session::set('id_dosen', $user['id_dosen'] ? (int) $user['id_dosen'] : null);
    }

    private function redirectWithError(string $msg, string $page, array $old = []): void
    {
        Session::setFlash('error', $msg);
        if ($old !== []) {
            Session::setFlash('old', $old);
        } else {
            Session::setFlash('old', $_POST);
        }
        redirect($page);
    }

    private function isValidPhoneNumber(string $phone): bool
    {
        if (!preg_match('/^\+?[0-9\s().-]+$/', $phone)) {
            return false;
        }

        $digits     = preg_replace('/\D/', '', $phone);
        $digitCount = strlen($digits);
        return $digitCount >= 8 && $digitCount <= 15;
    }
}
