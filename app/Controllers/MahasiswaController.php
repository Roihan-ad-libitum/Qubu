<?php

require_once __DIR__ . '/../Models/MahasiswaModel.php';
require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Models/CompetitionModel.php';
require_once __DIR__ . '/../Models/TeamModel.php';
require_once __DIR__ . '/../../core/Csrf.php';
require_once __DIR__ . '/CompetitionController.php';
require_once __DIR__ . '/../Helpers/UploadHelper.php';
require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/../../core/Validator.php';
require_once __DIR__ . '/../../core/AuthMiddleware.php';

class MahasiswaController
{
    private MahasiswaModel $mahasiswaModel;
    private UserModel $userModel;
    private CompetitionModel $competitionModel;
    private CompetitionController $competitionController;

    public function __construct()
    {
        $this->mahasiswaModel        = new MahasiswaModel();
        $this->userModel             = new UserModel();
        $this->competitionModel      = new CompetitionModel();
        $this->competitionController = new CompetitionController($this->competitionModel);
    }

    public function dashboard(): void
    {
        Session::start();
        AuthMiddleware::hasRole(ROLE_PESERTA);

        $idUser  = (int) Session::get('id_user');
        $profile = $idUser ? $this->mahasiswaModel->findByUserId($idUser) : null;

        $userBidangMinat = $profile['bidang_minat'] ?? (Session::get('demo_profile_data')['bidang_minat'] ?? 'UI/UX Design');
        $namaMahasiswa   = $profile['nama_mahasiswa'] ?? (Session::get('nama_lengkap') ?? 'Fitria');
        $userFoto        = $profile['foto'] ?? (Session::get('demo_profile_data')['foto'] ?? null);

        $nameParts     = explode(' ', trim($namaMahasiswa));
        $userFirstName = $nameParts[0] ?? 'User';

        $filterResult  = $this->competitionController->filterCompetitions($_GET);
        $searchKeyword = $filterResult['keyword'];
        $activeBidang  = $filterResult['bidang'];
        $semuaLomba    = $filterResult['data'];

        $katalogLengkap = array_values(array_filter(
            $this->competitionModel->getAll(),
            static fn(array $lomba): bool => !isLombaRegistrationClosed($lomba['deadline_daftar'] ?? null)
        ));
        if ($searchKeyword === '' && $activeBidang === 'Semua Bidang' && $filterResult['is_valid_bidang']) {
            $semuaLomba = $katalogLengkap;
        }

        $now               = time();
        $deadlineWindowEnd = $now + (30 * 24 * 60 * 60);
        $upcomingLomba = [];
        foreach ($katalogLengkap as $lomba) {
            $deadlineTimestamp = !empty($lomba['deadline_daftar'])
                ? strtotime((string) $lomba['deadline_daftar'])
                : false;
            if ($deadlineTimestamp !== false && $deadlineTimestamp >= $now && $deadlineTimestamp < $deadlineWindowEnd) {
                $lomba['_upcoming_at'] = $deadlineTimestamp;
                $upcomingLomba[] = $lomba;
            }
        }
        usort($upcomingLomba, static fn(array $left, array $right): int => $left['_upcoming_at'] <=> $right['_upcoming_at']);
        $upcomingLomba = array_slice($upcomingLomba, 0, 8);

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

        $categoryPills = getCategoryPills();
        $title         = 'Dashboard Info Lomba - ' . APP_NAME;
        $extraCss      = ['public/css/components.css', 'public/css/dashboard.css'];

        require_once __DIR__ . '/../../views/mahasiswa/dashboard.php';
    }

    public function profile(): void
    {
        Session::start();
        AuthMiddleware::hasRole(ROLE_PESERTA);

        $idUser  = Session::get('id_user');
        $profile = $idUser ? $this->mahasiswaModel->findByUserId((int) $idUser) : null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleUpdateProfile($idUser, $profile);
            return;
        }

        $demoSession = Session::get('demo_profile_data') ?? [];

        $data = [
            'npm'            => $profile['npm'] ?? ($demoSession['npm'] ?? '1234500001'),
            'nama_mahasiswa' => $profile['nama_mahasiswa'] ?? ($demoSession['nama_mahasiswa'] ?? (Session::get('nama_lengkap') ?? 'Roihan Fakhri')),
            'username'       => ltrim((string) ($profile['username'] ?? ($demoSession['username'] ?? (Session::get('username') ?? 'roihanfakh_'))), '@'),
            'program_studi'  => $profile['program_studi'] ?? ($demoSession['program_studi'] ?? 'Informatika'),
            'angkatan'       => $profile['angkatan'] ?? ($demoSession['angkatan'] ?? '2025'),
            'bidang_minat'   => $profile['bidang_minat'] ?? ($demoSession['bidang_minat'] ?? 'Software Development, UI/UX Design'),
            'email'          => $profile['email'] ?? ($demoSession['email'] ?? (Session::get('email') ?? '')),
            'github'         => $profile['github'] ?? ($demoSession['github'] ?? ''),
            'no_hp'          => $profile['no_hp'] ?? ($demoSession['no_hp'] ?? ''),
            'bio'            => $profile['bio'] ?? ($demoSession['bio'] ?? ''),
            'foto'           => $profile['foto'] ?? ($demoSession['foto'] ?? null),
        ];

        $tags             = parseSkillTags((string) $data['bidang_minat'], true, ['Frontend', 'UI/UX Design']);
        $availableOptions = BIDANG_QUBU;
        $csrfToken        = Csrf::token();
        $prodiOptions     = PRODI_FASILKOM;
        $error            = Session::getFlash('error');
        $success          = Session::getFlash('success');
        $title            = 'Informasi Profil Mahasiswa - ' . APP_NAME;
        $extraCss         = ['public/css/components.css', 'public/css/profile.css'];

        require_once __DIR__ . '/../../views/mahasiswa/profile.php';
    }

    public function lihatProfil(mixed $rawId): void
    {
        Session::start();
        AuthMiddleware::hasRole(ROLE_PESERTA);

        $idMahasiswa = Validator::validatePositiveInt($rawId);
        $kandidat    = $idMahasiswa !== null ? $this->mahasiswaModel->findPublicProfileById($idMahasiswa) : null;
        $kandidatOpenPosts = [];
        if ($kandidat !== null) {
            $kandidat['inisial'] = buildInitials((string) $kandidat['nama_mahasiswa']);
            if (!empty($kandidat['id_user'])) {
                $kandidatOpenPosts = (new TeamModel())->getOpenPostsByUserId((int) $kandidat['id_user']);
            }
        }

        $isNotFound    = $kandidat === null;
        $idUser        = (int) Session::get('id_user');
        $myProfile     = $idUser > 0 ? $this->mahasiswaModel->findByUserId($idUser) : null;
        $namaMahasiswa = $myProfile['nama_mahasiswa'] ?? (Session::get('nama_lengkap') ?? 'Mahasiswa');
        $userFoto      = $myProfile['foto'] ?? (Session::get('demo_profile_data')['foto'] ?? null);
        $title         = $isNotFound
            ? 'Profil Tidak Ditemukan - ' . APP_NAME
            : 'Profil ' . $kandidat['nama_mahasiswa'] . ' - ' . APP_NAME;
        $extraCss      = ['public/css/components.css', 'public/css/dashboard.css', 'public/css/cari-partner.css', 'public/css/team-flow.css'];

        require __DIR__ . '/../../views/mahasiswa/lihat-profil.php';
    }

    private function handleUpdateProfile(?int $idUser, ?array $profile): void
    {
        if (!Csrf::isValid($_POST['_csrf'] ?? null)) {
            Session::setFlash('error', 'Formulir profil kedaluwarsa atau tidak valid. Silakan coba kembali.');
            redirect('?page=mahasiswa-profile');
        }

        $npm          = trim($_POST['npm'] ?? '');
        $namaLengkap  = trim($_POST['nama_mahasiswa'] ?? '');
        $programStudi = trim($_POST['program_studi'] ?? '');
        $angkatan     = trim($_POST['angkatan'] ?? '');
        $bidangMinat  = trim($_POST['bidang_minat'] ?? '');
        $bio          = trim($_POST['bio'] ?? '');
        $email        = trim((string) ($_POST['email'] ?? ($profile['email'] ?? '')));
        $rawGithub    = trim((string) ($_POST['github'] ?? ''));
        $githubUrl    = Validator::validateGithub($rawGithub);
        $noHp         = trim((string) ($_POST['no_hp'] ?? ($profile['no_hp'] ?? '')));

        if (empty($npm) || empty($programStudi) || empty($angkatan) || strlen($bio) > 1000) {
            Session::setFlash('error', 'NPM, Program Studi, dan Angkatan wajib diisi. Bio maksimal 1000 karakter.');
            redirect('?page=mahasiswa-profile');
        }

        if (!Validator::isValidAcademicProdi($programStudi)) {
            Session::setFlash('error', 'Pilih Program Studi yang valid (Informatika atau Sistem Informasi).');
            redirect('?page=mahasiswa-profile');
        }

        if ($idUser && $this->mahasiswaModel->isNpmExistsForOtherUser($npm, (int) $idUser)) {
            Session::setFlash('error', 'NPM "' . e($npm) . '" sudah digunakan oleh akun mahasiswa lain.');
            redirect('?page=mahasiswa-profile');
        }

        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            Session::setFlash('error', 'Masukkan alamat email yang valid.');
            redirect('?page=mahasiswa-profile');
        }

        if ($githubUrl === null) {
            Session::setFlash('error', 'Masukkan URL atau username GitHub yang valid (contoh: https://github.com/username atau username).');
            redirect('?page=mahasiswa-profile');
        }

        if ($noHp !== '' && $noHp !== '-' && !Validator::isValidPhone($noHp)) {
            Session::setFlash('error', 'Masukkan nomor telepon / WhatsApp yang valid (8–15 digit).');
            redirect('?page=mahasiswa-profile');
        }

        if ($idUser && $email !== '' && $this->userModel->isEmailExistsForOtherUser($email, (int) $idUser)) {
            Session::setFlash('error', 'Alamat email "' . e($email) . '" sudah digunakan oleh akun lain.');
            redirect('?page=mahasiswa-profile');
        }

        $hapusFoto = !empty($_POST['hapus_foto']) && $_POST['hapus_foto'] === '1';
        $oldFoto   = $profile['foto'] ?? (Session::get('demo_profile_data')['foto'] ?? null);
        $fotoPath  = $oldFoto;

        if ($hapusFoto) {
            if (!empty($oldFoto)) {
                UploadHelper::deleteFile($oldFoto);
            }
            $fotoPath = null;
        } elseif (isset($_FILES['foto']) && ($_FILES['foto']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            try {
                $uploaded = UploadHelper::uploadProfilePhoto($_FILES['foto'], 'mhs', $npm, $oldFoto);
                if ($uploaded !== null) {
                    $fotoPath = $uploaded;
                }
            } catch (RuntimeException $e) {
                Session::setFlash('error', $e->getMessage());
                redirect('?page=mahasiswa-profile');
            }
        }

        $updateData = [
            'npm'           => $npm,
            'program_studi' => $programStudi,
            'angkatan'      => $angkatan,
            'bidang_minat'  => $bidangMinat,
            'bio'           => $bio,
            'foto'          => $fotoPath,
            'github'        => $githubUrl,
        ];
        if (!empty($namaLengkap)) {
            $updateData['nama_mahasiswa'] = $namaLengkap;
        }

        if ($idUser && $profile) {
            try {
                $this->mahasiswaModel->updateProfileByUserId($idUser, $updateData);
                if ($email !== '' || $noHp !== '') {
                    $this->userModel->updateContactByUserId(
                        (int) $idUser,
                        $email !== '' ? $email : (string) ($profile['email'] ?? ''),
                        $noHp !== '' ? $noHp : (string) ($profile['no_hp'] ?? '')
                    );
                    if ($email !== '') {
                        Session::set('email', $email);
                    }
                }
                if (!empty($namaLengkap)) {
                    Session::set('nama_lengkap', $namaLengkap);
                }
                Session::setFlash('success', 'Profil mahasiswa berhasil diperbarui!');
            } catch (Exception $e) {
                Session::setFlash('error', 'Gagal memperbarui profil: ' . $e->getMessage());
            }
        } else {
            Session::set('demo_profile_data', [
                'npm'            => $npm,
                'nama_mahasiswa' => !empty($namaLengkap) ? $namaLengkap : 'Roihan Fakhri',
                'username'       => Session::get('username') ?? 'roihanfakh_',
                'program_studi'  => $programStudi,
                'angkatan'       => $angkatan,
                'bidang_minat'   => $bidangMinat,
                'bio'            => $bio,
                'foto'           => $fotoPath,
                'github'         => $githubUrl,
            ]);
            Session::setFlash('success', 'Profil mahasiswa berhasil diperbarui!');
        }

        redirect('?page=mahasiswa-profile');
    }

    public function buildPartnerInitials(string $nama): string
    {
        return buildInitials($nama, 'QB');
    }

    public function formatPartnerItem(array $row, ?array $targetLomba = null): array
    {
        $idMhs   = (int) ($row['id_mahasiswa'] ?? 0);
        $nama    = (string) ($row['nama_mahasiswa'] ?? 'Mahasiswa Qubu');
        $fotoRel = trim((string) ($row['foto'] ?? ''));

        $row['id_mahasiswa']      = $idMhs;
        $row['id_user']           = (int) ($row['id_user'] ?? 0);
        $row['inisial']           = buildInitials($nama, 'QB');
        $row['bidang_tags']       = $this->mahasiswaModel->parseBidangTags($row['bidang_minat'] ?? '');
        $resolvedPhoto            = resolveProfilePhotoUrl($fotoRel);
        $row['foto_url']          = $resolvedPhoto !== null ? $resolvedPhoto : '';
        $row['github_url']        = normalizeGithubUrl($row['github'] ?? '') ?: '';
        $row['github_display']    = formatGithubDisplay($row['github'] ?? '');
        $row['profil_url']        = url('?page=lihat-profil&id=' . $idMhs);
        $row['target_lomba_id']   = isset($targetLomba['id_lomba']) ? (int) $targetLomba['id_lomba'] : null;
        $row['target_lomba_nama'] = $targetLomba['nama_lomba'] ?? null;

        unset($row['password']);

        return $row;
    }

    public function validatePartnerSearchRequest(array $query = []): array
    {
        $rawKeyword   = $query['q'] ?? ($query['keyword'] ?? '');
        $keyword      = Validator::cleanKeyword($rawKeyword, 100);
        $validLombaId = Validator::validatePositiveInt($query['id_lomba'] ?? null);
        $targetLomba  = $validLombaId !== null ? $this->competitionModel->getById($validLombaId) : null;

        $hasExplicitBidang = array_key_exists('bidang', $query) && trim((string) $query['bidang']) !== '';
        $rawBidang         = $hasExplicitBidang ? $query['bidang'] : ($targetLomba['bidang'] ?? 'Semua Bidang');
        $validatedBidang   = Validator::validateBidang($rawBidang);
        $isValidBidang     = $validatedBidang !== null;
        $bidangLabel       = $validatedBidang ?? 'Semua Bidang';
        $modelBidang       = $bidangLabel === 'Semua Bidang' ? null : $bidangLabel;

        $rawProdi       = $query['prodi'] ?? ($query['program_studi'] ?? 'Semua Prodi');
        $validatedProdi = Validator::validateProdi($rawProdi);
        $isValidProdi   = $validatedProdi !== null;
        $prodiLabel     = $validatedProdi ?? 'Semua Prodi';
        $modelProdi     = $prodiLabel === 'Semua Prodi' ? null : $prodiLabel;

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

        $hasActiveFilter = $searchKeyword !== '' || $activeBidang !== 'Semua Bidang' || $activeProdi !== 'Semua Prodi' || $targetLomba !== null;

        if ($hasActiveFilter) {
            $rawAllPartners       = $this->mahasiswaModel->searchPartners($idUser, null, null, null, 50);
            $semuaKandidatPartner = array_map(
                fn(array $row) => $this->formatPartnerItem($row, $targetLomba),
                $rawAllPartners
            );
        } else {
            $semuaKandidatPartner = $partnerList;
        }

        $rekomendasiPartner = array_slice($semuaKandidatPartner, 0, 3);
        $categoryPills      = getCategoryPills();
        $prodiOptions       = getProdiOptions();
        $title              = 'Cari Partner Lomba - ' . APP_NAME;
        $extraCss           = ['public/css/components.css', 'public/css/dashboard.css', 'public/css/cari-partner.css'];

        $viewPath = __DIR__ . '/../../views/mahasiswa/cari-partner.php';
        if (file_exists($viewPath)) {
            require $viewPath;
        }
    }

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

        $limit  = max(1, min(100, Validator::validatePositiveInt($query['limit'] ?? 50) ?? 50));
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
}
