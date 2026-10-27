<?php
/**
 * Controller Pengelolaan Data & Profil Mahasiswa Qubu
 */

require_once __DIR__ . '/../Models/MahasiswaModel.php';
require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Models/CompetitionModel.php';
require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/../../core/AuthMiddleWare.php';

class MahasiswaController
{
    private MahasiswaModel $mahasiswaModel;
    private UserModel $userModel;
    private CompetitionModel $competitionModel;

    public function __construct()
    {
        $this->mahasiswaModel = new MahasiswaModel();
        $this->userModel = new UserModel();
        $this->competitionModel = new CompetitionModel();
    }

    /**
     * Menampilkan Dashboard Utama Mahasiswa Qubu setelah berhasil login
     * Sesuai mockup desain UI:
     * - Top Navbar di kanan atas (Info Lomba, Cari Partner, Dosen Pembimbing, Avatar)
     * - Hero greeting ("Hi Fitria! Ayo Cari Lomba Impianmu", Search box & category pills)
     * - Section "Rekomendasi Rekan" (lomba & rekan dengan bidang sama dengan profil user)
     * - Section "Semua Lomba" (katalog lengkap kompetisi)
     */
    public function dashboard(): void
    {
        Session::start();
        AuthMiddleware::hasRole(ROLE_PESERTA);

        $idUser = (int) Session::get('id_user');
        $profile = null;

        if ($idUser) {
            $profile = $this->mahasiswaModel->findByUserId($idUser);
        }

        // Ambil data profil mahasiswa dari database atau sesi
        $userBidangMinat = $profile['bidang_minat'] ?? (Session::get('demo_profile_data')['bidang_minat'] ?? 'UI/UX Design');
        $namaMahasiswa   = $profile['nama_mahasiswa'] ?? (Session::get('nama_lengkap') ?? 'Fitria');
        $userFoto        = $profile['foto'] ?? (Session::get('demo_profile_data')['foto'] ?? null);

        // Ambil nama panggilan untuk greeting ("Hi Fitria!")
        $nameParts = explode(' ', trim($namaMahasiswa));
        $userFirstName = $nameParts[0] ?? 'User';

        // Ambil data kompetisi
        $semuaLomba = $this->competitionModel->getAll();

        // 1. Rekomendasi Lomba berdasarkan bidang yang sama dengan profil
        $minatItems = array_filter(array_map('trim', explode(',', strtolower($userBidangMinat))));
        if (empty($minatItems)) {
            $minatItems = ['ui/ux design'];
        }

        $rekomendasiLomba = [];
        foreach ($semuaLomba as $lomba) {
            $lombaBidang = strtolower(trim($lomba['bidang'] ?? ''));
            foreach ($minatItems as $minat) {
                if ($minat !== '' && (strpos($lombaBidang, $minat) !== false || strpos($minat, $lombaBidang) !== false)) {
                    $rekomendasiLomba[] = $lomba;
                    break;
                }
            }
        }

        // Fallback jika tidak ada lomba dengan bidang yang sama persis
        if (empty($rekomendasiLomba)) {
            $rekomendasiLomba = array_slice($semuaLomba, 0, 4);
        }

        // 2. Rekomendasi Rekan Mahasiswa (teman mahasiswa dengan bidang yang sama)
        $rekomendasiRekan = $this->mahasiswaModel->getRecommendedPartnersByBidang($idUser, $userBidangMinat);

        // 3. Kategori Bidang untuk Filter Pills (Fokus eksklusif pada kartu index.php)
        $focusBidang = defined('BIDANG_QUBU') ? BIDANG_QUBU : [
            'Frontend',
            'Backend',
            'UI/UX Design',
            'Data Science',
            'Robotic'
        ];
        $categoryPills = array_merge(['Semua Bidang'], $focusBidang);

        $title = 'Dashboard Info Lomba - ' . APP_NAME;
        $extraCss = ['public/css/components.css', 'public/css/dashboard.css'];

        require_once __DIR__ . '/../../views/mahasiswa/dashboard.php';
    }


    /**
     * Menampilkan & mengelola halaman profil Mahasiswa
     * Presisi sesuai mockup desain UI Qubu
     */
    public function profile(): void
    {
        Session::start();

        $idUser = Session::get('id_user');
        $profile = null;

        // Ambil profil dari database jika user sedang login
        if ($idUser) {
            $profile = $this->mahasiswaModel->findByUserId((int) $idUser);
        }

        // Tangani update profil jika ada pengiriman form POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleUpdateProfile($idUser, $profile);
            return;
        }

        // Data profil untuk tampilan (menggunakan data database, session, atau default persis mockup)
        $demoSession = Session::get('demo_profile_data') ?? [];

        $data = [
            'npm'            => $profile['npm'] ?? ($demoSession['npm'] ?? '1234500001'),
            'nama_mahasiswa' => $profile['nama_mahasiswa'] ?? ($demoSession['nama_mahasiswa'] ?? (Session::get('nama_lengkap') ?? 'Roihan Fakhri')),
            'username'       => $profile['username'] ?? ($demoSession['username'] ?? (Session::get('username') ?? 'roihanfakh_')),
            'program_studi'  => $profile['program_studi'] ?? ($demoSession['program_studi'] ?? 'Informatika'),
            'angkatan'       => $profile['angkatan'] ?? ($demoSession['angkatan'] ?? '2025'),
            'bidang_minat'   => $profile['bidang_minat'] ?? ($demoSession['bidang_minat'] ?? 'Software Development, UI/UX Design'),
            'foto'           => $profile['foto'] ?? ($demoSession['foto'] ?? null),
        ];

        // Format username (hilangkan prefix @ jika ada agar bersih, nanti ditambahkan di view)
        $data['username'] = ltrim($data['username'], '@');

        // Parse bidang peminatan menjadi array tags yang hanya fokus pada kartu index.php
        $rawMinat = $data['bidang_minat'];
        $rawTags = array_filter(array_map('trim', explode(',', (string) $rawMinat)));
        $allowedOptions = $this->mahasiswaModel->getBidangPeminatanOptions();
        $tags = [];
        foreach ($rawTags as $rt) {
            foreach ($allowedOptions as $ao) {
                if (strcasecmp($rt, $ao) === 0) {
                    $tags[] = $ao;
                    break;
                }
            }
        }
        if (empty($tags)) {
            $tags = ['Frontend', 'UI/UX Design'];
        }
        $tags = array_values(array_unique($tags));

        // Ambil pilihan opsi bidang peminatan resmi Qubu untuk menu "Tambah +"
        $availableOptions = $allowedOptions;

        $error    = Session::getFlash('error');
        $success  = Session::getFlash('success');
        $title    = 'Informasi Profil Mahasiswa - ' . APP_NAME;
        $extraCss = ['public/css/components.css', 'public/css/profile.css'];

        require_once __DIR__ . '/../../views/mahasiswa/profile.php';
    }

    /**
     * Memproses pembaruan data profil dan foto mahasiswa
     */
    private function handleUpdateProfile(?int $idUser, ?array $profile): void
    {
        $npm          = trim($_POST['npm'] ?? '');
        $namaLengkap  = trim($_POST['nama_mahasiswa'] ?? '');
        $programStudi = trim($_POST['program_studi'] ?? '');
        $angkatan     = trim($_POST['angkatan'] ?? '');
        $bidangMinat  = trim($_POST['bidang_minat'] ?? '');

        if (empty($npm) || empty($programStudi) || empty($angkatan)) {
            Session::setFlash('error', 'NPM, Program Studi, dan Angkatan tidak boleh kosong.');
            redirect('?page=mahasiswa-profile');
        }

        // Handle upload foto baru jika diunggah
        $fotoPath = $profile['foto'] ?? (Session::get('demo_profile_data')['foto'] ?? null);

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $fileTmp  = $_FILES['foto']['tmp_name'];
            $fileSize = $_FILES['foto']['size'];
            $origName = $_FILES['foto']['name'];

            if ($fileSize <= 5 * 1024 * 1024) {
                $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                if (in_array($ext, $allowed)) {
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
            }
        }

        $updateData = [
            'npm'           => $npm,
            'program_studi' => $programStudi,
            'angkatan'      => $angkatan,
            'bidang_minat'  => $bidangMinat,
            'foto'          => $fotoPath,
        ];
        if (!empty($namaLengkap)) {
            $updateData['nama_mahasiswa'] = $namaLengkap;
        }

        if ($idUser && $profile) {
            // Update ke database
            try {
                $this->mahasiswaModel->updateProfileByUserId($idUser, $updateData);
                if (!empty($namaLengkap)) {
                    Session::set('nama_lengkap', $namaLengkap);
                }
                Session::setFlash('success', 'Profil mahasiswa berhasil diperbarui!');
            } catch (Exception $e) {
                Session::setFlash('error', 'Gagal memperbarui profil: ' . $e->getMessage());
            }
        } else {
            // Simpan ke sesi untuk lingkungan demo jika belum login ke database
            Session::set('demo_profile_data', [
                'npm'            => $npm,
                'nama_mahasiswa' => !empty($namaLengkap) ? $namaLengkap : 'Roihan Fakhri',
                'username'       => Session::get('username') ?? 'roihanfakh_',
                'program_studi'  => $programStudi,
                'angkatan'       => $angkatan,
                'bidang_minat'   => $bidangMinat,
                'foto'           => $fotoPath,
            ]);
            Session::setFlash('success', 'Profil mahasiswa berhasil diperbarui!');
        }

        redirect('?page=mahasiswa-profile');
    }
}
