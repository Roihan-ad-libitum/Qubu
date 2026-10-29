<?php
/**
 * Controller Pengelolaan Data & Profil Mahasiswa Qubu
 */

require_once __DIR__ . '/../Models/MahasiswaModel.php';
require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Models/CompetitionModel.php';
require_once __DIR__ . '/CompetitionController.php';
require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/../../core/Validator.php';
require_once __DIR__ . '/../../core/AuthMiddleWare.php';

class MahasiswaController
{
    private MahasiswaModel $mahasiswaModel;
    private UserModel $userModel;
    private CompetitionModel $competitionModel;
    private CompetitionController $competitionController;

    public function __construct()
    {
        $this->mahasiswaModel = new MahasiswaModel();
        $this->userModel = new UserModel();
        $this->competitionModel = new CompetitionModel();
        $this->competitionController = new CompetitionController($this->competitionModel);
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

        // Validasi & sanitasi parameter pencarian dan filter dari query string ($_GET)
        $filterResult = $this->competitionController->filterCompetitions($_GET);
        $searchKeyword = $filterResult['keyword'];
        $activeBidang  = $filterResult['bidang'];
        $semuaLomba    = $filterResult['data'];

        // Ambil seluruh kompetisi untuk menyusun rekomendasi berdasarkan minat profil user
        $katalogLengkap = ($searchKeyword === '' && $activeBidang === 'Semua Bidang' && $filterResult['is_valid_bidang'])
            ? $semuaLomba
            : $this->competitionModel->getAll();

        // 1. Rekomendasi Lomba berdasarkan bidang yang sama dengan profil
        $minatItems = array_filter(array_map('trim', explode(',', strtolower($userBidangMinat))));
        if (empty($minatItems)) {
            $minatItems = ['ui/ux design'];
        }

        $rekomendasiLomba = [];
        foreach ($katalogLengkap as $lomba) {
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
            $rekomendasiLomba = array_slice($katalogLengkap, 0, 4);
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

    // =========================================================================
    // FITUR CARI PARTNER (CP-2.1 & CP-2.2: VALIDASI, CONTROLLER & JSON API)
    // =========================================================================

    /**
     * Hasilkan 2 huruf inisial nama mahasiswa (mis. "Roihan Fakhri" -> "RF")
     */
    public function buildPartnerInitials(string $nama): string
    {
        $parts = array_values(array_filter(explode(' ', trim($nama))));
        if (count($parts) >= 2) {
            return strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
        }
        $clean = trim($nama);
        return $clean !== '' ? strtoupper(substr($clean, 0, 2)) : 'QB';
    }

    /**
     * Format satu record mahasiswa menjadi struktur kartu partner siap pakai untuk View & JSON API
     */
    public function formatPartnerItem(array $row, ?array $targetLomba = null): array
    {
        $idMhs   = (int) ($row['id_mahasiswa'] ?? 0);
        $nama    = (string) ($row['nama_mahasiswa'] ?? 'Mahasiswa Qubu');
        $fotoRel = trim((string) ($row['foto'] ?? ''));
        $hasFoto = $fotoRel !== '' && file_exists(__DIR__ . '/../../' . $fotoRel);

        $row['id_mahasiswa']      = $idMhs;
        $row['id_user']           = (int) ($row['id_user'] ?? 0);
        $row['inisial']           = $this->buildPartnerInitials($nama);
        $row['bidang_tags']       = $this->mahasiswaModel->parseBidangTags($row['bidang_minat'] ?? '');
        $row['foto_url']          = ($hasFoto && function_exists('url')) ? url($fotoRel) : '';
        $row['profil_url']        = function_exists('url')
            ? url('?page=lihat-profil&id=' . $idMhs)
            : '?page=lihat-profil&id=' . $idMhs;
        $row['target_lomba_id']   = isset($targetLomba['id_lomba']) ? (int) $targetLomba['id_lomba'] : null;
        $row['target_lomba_nama'] = $targetLomba['nama_lomba'] ?? null;

        unset($row['password']);

        return $row;
    }

    /**
     * Validasi dan normalisasi parameter pencarian & filter Cari Partner (q, bidang, prodi, id_lomba)
     */
    public function validatePartnerSearchRequest(array $query = []): array
    {
        $rawKeyword = $query['q'] ?? ($query['keyword'] ?? '');
        $keyword    = Validator::cleanKeyword($rawKeyword, 100);

        // Cek apakah ada konteks id_lomba (mis. user masuk dari tombol "Cari Tim" di Detail Lomba)
        $rawIdLomba  = $query['id_lomba'] ?? null;
        $validLombaId = Validator::validatePositiveInt($rawIdLomba);
        $targetLomba = null;
        if ($validLombaId !== null) {
            $targetLomba = $this->competitionModel->getById($validLombaId);
        }

        // Validasi bidang
        $hasExplicitBidang = array_key_exists('bidang', $query) && trim((string) $query['bidang']) !== '';
        $rawBidang         = $hasExplicitBidang ? $query['bidang'] : ($targetLomba['bidang'] ?? 'Semua Bidang');
        $validatedBidang   = Validator::validateBidang($rawBidang);
        $isValidBidang     = $validatedBidang !== null;
        $bidangLabel       = $validatedBidang ?? 'Semua Bidang';
        $modelBidang       = $bidangLabel === 'Semua Bidang' ? null : $bidangLabel;

        // Validasi prodi (Informatika / Sistem Informasi / Semua Prodi)
        $rawProdi       = $query['prodi'] ?? ($query['program_studi'] ?? 'Semua Prodi');
        $validatedProdi = Validator::validateProdi($rawProdi);
        $isValidProdi   = $validatedProdi !== null;
        $prodiLabel     = $validatedProdi ?? 'Semua Prodi';
        $modelProdi      = $prodiLabel === 'Semua Prodi' ? null : $prodiLabel;

        return [
            'keyword'         => $keyword,
            'bidang_label'    => $bidangLabel,
            'model_bidang'    => $modelBidang,
            'is_valid_bidang' => $isValidBidang,
            'prodi_label'     => $prodiLabel,
            'model_prodi'     => $modelProdi,
            'is_valid_prodi'  => $isValidProdi,
            'id_lomba'        => $targetLomba ? (int) $targetLomba['id_lomba'] : null,
            'target_lomba'    => $targetLomba,
        ];
    }

    /**
     * Eksekusi pencarian & filter kandidat partner (selalu mengecualikan $excludeUserId)
     */
    public function filterPartners(int $excludeUserId, array $query = [], int $limit = 50): array
    {
        $validated   = $this->validatePartnerSearchRequest($query);
        $targetLomba = $validated['target_lomba'];

        $rawRows = $this->mahasiswaModel->searchPartners(
            $excludeUserId,
            $validated['model_bidang'],
            $validated['model_prodi'],
            $validated['keyword'] !== '' ? $validated['keyword'] : null,
            $limit
        );

        $formatted = array_map(
            fn(array $row) => $this->formatPartnerItem($row, $targetLomba),
            $rawRows
        );

        return [
            'data'            => $formatted,
            'total'           => count($formatted),
            'keyword'         => $validated['keyword'],
            'bidang'          => $validated['bidang_label'],
            'prodi'           => $validated['prodi_label'],
            'id_lomba'        => $validated['id_lomba'],
            'target_lomba'    => $targetLomba,
            'is_valid_bidang' => $validated['is_valid_bidang'],
            'is_valid_prodi'  => $validated['is_valid_prodi'],
            'filters'         => [
                'keyword'      => $validated['keyword'],
                'bidang'       => $validated['model_bidang'],
                'bidang_label' => $validated['bidang_label'],
                'prodi'        => $validated['model_prodi'],
                'prodi_label'  => $validated['prodi_label'],
                'id_lomba'     => $validated['id_lomba'],
            ],
        ];
    }

    /**
     * Menampilkan Halaman Utama Cari Partner (?page=cari-partner)
     * Hanya dapat diakses oleh pengguna yang sudah login (AuthMiddleware::check / ROLE_PESERTA).
     */
    public function cariPartner(): void
    {
        Session::start();
        AuthMiddleware::hasRole(ROLE_PESERTA);

        $idUser  = (int) Session::get('id_user');
        $profile = $idUser > 0 ? $this->mahasiswaModel->findByUserId($idUser) : null;

        $userBidangMinat = $profile['bidang_minat'] ?? (Session::get('demo_profile_data')['bidang_minat'] ?? 'UI/UX Design');
        $namaMahasiswa   = $profile['nama_mahasiswa'] ?? (Session::get('nama_lengkap') ?? 'Mahasiswa');
        $userFoto        = $profile['foto'] ?? (Session::get('demo_profile_data')['foto'] ?? null);

        $filterResult  = $this->filterPartners($idUser, $_GET, 50);
        $searchKeyword = $filterResult['keyword'];
        $activeBidang  = $filterResult['bidang'];
        $activeProdi   = $filterResult['prodi'];
        $targetLomba   = $filterResult['target_lomba'];
        $partnerList   = $filterResult['data'];

        // Seluruh kandidat partner (selain diri sendiri) untuk hidrasi DOM client-side & placeholder Rekomendasi Rekan
        $rawAllPartners = $this->mahasiswaModel->searchPartners($idUser, null, null, null, 50);
        $semuaKandidatPartner = array_map(
            fn(array $row) => $this->formatPartnerItem($row, $targetLomba),
            $rawAllPartners
        );

        // 3 kartu placeholder untuk section "Rekomendasi Rekan" sesuai gambar desain
        $rekomendasiPartner = array_slice($semuaKandidatPartner, 0, 3);

        $categoryPills = array_merge(
            ['Semua Bidang'],
            defined('BIDANG_QUBU') ? BIDANG_QUBU : ['Frontend', 'Backend', 'UI/UX Design', 'Data Science', 'Robotic']
        );
        $prodiOptions = array_merge(
            ['Semua Prodi'],
            defined('PRODI_FASILKOM') ? PRODI_FASILKOM : ['Informatika', 'Sistem Informasi']
        );

        $title    = 'Cari Partner Lomba - ' . APP_NAME;
        $extraCss = ['public/css/components.css', 'public/css/dashboard.css', 'public/css/cari-partner.css'];

        $viewPath = __DIR__ . '/../../views/mahasiswa/cari-partner.php';
        if (file_exists($viewPath)) {
            require $viewPath;
        }
    }

    /**
     * Menyusun struktur respons JSON API untuk pencarian partner atau detail profil publik (?page=api-partners)
     */
    public function buildPartnerApiPayload(?int $currentUserId, array $query = []): array
    {
        if ($currentUserId === null || $currentUserId <= 0) {
            return [
                'http_code' => 401,
                'body'      => [
                    'success' => false,
                    'message' => 'Akses ditolak. Silakan login terlebih dahulu untuk mencari partner.',
                    'data'    => null,
                ],
            ];
        }

        // Mode 1: Detail profil publik kandidat partner (?id={id_mahasiswa})
        if (array_key_exists('id', $query) && $query['id'] !== '' && $query['id'] !== null) {
            $validId = Validator::validatePositiveInt($query['id']);
            if ($validId === null) {
                return [
                    'http_code' => 400,
                    'body'      => [
                        'success' => false,
                        'message' => 'Parameter ID mahasiswa tidak valid.',
                        'data'    => null,
                    ],
                ];
            }

            $detail = $this->mahasiswaModel->findPublicProfileById($validId);
            if ($detail === null) {
                return [
                    'http_code' => 404,
                    'body'      => [
                        'success' => false,
                        'message' => 'Profil mahasiswa tidak ditemukan.',
                        'data'    => null,
                    ],
                ];
            }

            return [
                'http_code' => 200,
                'body'      => [
                    'success' => true,
                    'data'    => $this->formatPartnerItem($detail),
                ],
            ];
        }

        // Mode 2: Pencarian & Filter daftar kandidat partner (mengecualikan $currentUserId)
        $rawLimit = $query['limit'] ?? 50;
        $limit    = Validator::validatePositiveInt($rawLimit) ?? 50;
        $limit    = max(1, min(100, $limit));

        $result = $this->filterPartners($currentUserId, $query, $limit);

        return [
            'http_code' => 200,
            'body'      => [
                'success' => true,
                'total'   => $result['total'],
                'filters' => $result['filters'],
                'data'    => $result['data'],
            ],
        ];
    }

    /**
     * Endpoint JSON API pencarian & filter partner (?page=api-partners atau api/partners.php)
     */
    public function apiPartners(array $query = []): void
    {
        Session::start();
        $currentUserId = Session::has('id_user') ? (int) Session::get('id_user') : null;

        $payload = $this->buildPartnerApiPayload($currentUserId, $query);

        if (!headers_sent()) {
            http_response_code($payload['http_code']);
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        }

        echo json_encode($payload['body'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Menampilkan Halaman Profil Publik Kandidat Partner Read-Only (?page=lihat-profil&id={id_mahasiswa})
     * Hanya dapat diakses oleh pengguna yang sudah login.
     */
    public function lihatProfil($rawId = null): void
    {
        Session::start();
        AuthMiddleware::check();

        $idUser        = (int) Session::get('id_user');
        $loggedProfile = $idUser > 0 ? $this->mahasiswaModel->findByUserId($idUser) : null;
        $namaMahasiswa = $loggedProfile['nama_mahasiswa'] ?? (Session::get('nama_lengkap') ?? 'Mahasiswa');
        $userFoto      = $loggedProfile['foto'] ?? (Session::get('demo_profile_data')['foto'] ?? null);

        $inputId = $rawId ?? ($_GET['id'] ?? null);
        $validId = Validator::validatePositiveInt($inputId);

        $kandidat   = null;
        $isNotFound = false;

        if ($validId !== null) {
            $rawKandidat = $this->mahasiswaModel->findPublicProfileById($validId);
            if ($rawKandidat !== null) {
                $kandidat = $this->formatPartnerItem($rawKandidat);
            } else {
                $isNotFound = true;
            }
        } else {
            $isNotFound = true;
        }

        if ($isNotFound && !headers_sent()) {
            http_response_code(404);
        }

        $title = $kandidat
            ? ('Profil ' . $kandidat['nama_mahasiswa'] . ' - ' . APP_NAME)
            : ('404 Profil Tidak Ditemukan - ' . APP_NAME);
        $extraCss = ['public/css/components.css', 'public/css/dashboard.css', 'public/css/cari-partner.css'];

        $viewPath = __DIR__ . '/../../views/mahasiswa/lihat-profil.php';
        if (file_exists($viewPath)) {
            require $viewPath;
        }
    }
}
