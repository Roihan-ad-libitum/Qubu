<?php

require_once __DIR__ . '/../Models/TeamModel.php';
require_once __DIR__ . '/../Models/CompetitionModel.php';
require_once __DIR__ . '/../Models/MahasiswaModel.php';
require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/../../core/AuthMiddleware.php';
require_once __DIR__ . '/../../core/Csrf.php';
require_once __DIR__ . '/../../core/Validator.php';

class TeamController
{
    private TeamModel $teamModel;
    private CompetitionModel $competitionModel;
    private MahasiswaModel $mahasiswaModel;

    public function __construct()
    {
        $this->teamModel = new TeamModel();
        $this->competitionModel = new CompetitionModel();
        $this->mahasiswaModel = new MahasiswaModel();
    }

    public function posts(): void
    {
        Session::start();
        AuthMiddleware::hasRole(ROLE_PESERTA);

        $idUser  = (int) Session::get('id_user');
        $profile = $this->mahasiswaModel->findByUserId($idUser);

        $selectedId          = Validator::validatePositiveInt($_GET['id_lomba'] ?? null);
        $selectedCompetition = $selectedId ? $this->competitionModel->getById($selectedId) : null;
        $selectedCompetitionClosed = $selectedCompetition !== null
            && isLombaRegistrationClosed($selectedCompetition['deadline_daftar'] ?? null);
        if ($selectedId && !$selectedCompetition) {
            http_response_code(404);
        }

        $activeBidang = Validator::validateBidang($_GET['bidang'] ?? 'Semua Bidang') ?? 'Semua Bidang';

        $canSearchTeams = !$selectedCompetition
            || in_array($selectedCompetition['partisipasi'] ?? '', ['Tim', 'Individu/Tim'], true);

        $seekerSkills = (string) ($profile['bidang_minat'] ?? '');
        $competitions = array_values(array_filter(
            $this->competitionModel->getAll(),
            static fn(array $competition): bool => !isLombaRegistrationClosed($competition['deadline_daftar'] ?? null)
        ));
        $allPosts     = $this->teamModel->getOpenPosts(null, $seekerSkills, 'Semua Bidang');
        $posts        = $canSearchTeams
            ? $this->teamModel->getOpenPosts(
                $selectedCompetition ? (int) $selectedCompetition['id_lomba'] : null,
                $seekerSkills,
                $activeBidang
            )
            : [];

        $ownerPosts    = $this->teamModel->getOwnerPosts($idUser);
        $categoryPills = getCategoryPills();
        $error         = Session::getFlash('error');
        $success       = Session::getFlash('success');
        $title         = 'Cari Partner & Tim Lomba - ' . APP_NAME;

        require __DIR__ . '/../../views/mahasiswa/team-posts.php';
    }

    public function createPost(): void
    {
        Session::start();
        AuthMiddleware::hasRole(ROLE_PESERTA);
        $idUser  = (int) Session::get('id_user');
        $profile = $this->mahasiswaModel->findByUserId($idUser);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $competitions = array_values(array_filter(
                $this->competitionModel->getAll(),
                static fn(array $competition): bool => !isLombaRegistrationClosed($competition['deadline_daftar'] ?? null)
            ));
            $selectedId   = Validator::validatePositiveInt($_GET['id_lomba'] ?? null);
            $error        = Session::getFlash('error');
            $old          = Session::get('team_post_old', []);
            Session::remove('team_post_old');
            $csrfToken    = Csrf::token();
            $title        = 'Buat Posting Cari Tim - ' . APP_NAME;
            require __DIR__ . '/../../views/mahasiswa/team-post-form.php';
            return;
        }

        if (!Csrf::isValid($_POST['_csrf'] ?? null)) {
            Session::setFlash('error', 'Formulir kedaluwarsa atau tidak valid. Silakan coba kembali.');
            redirect('?page=team-post-create');
        }

        $competitionId = Validator::validatePositiveInt($_POST['id_lomba'] ?? null);
        $competition   = $competitionId !== null ? $this->competitionModel->getById($competitionId) : null;
        if ($competition !== null && isLombaRegistrationClosed($competition['deadline_daftar'] ?? null)) {
            Session::setFlash('error', 'Pendaftaran Ditutup');
            redirect('?page=team-post-create');
        }

        $skillsRaw       = $_POST['bidang_dibutuhkan'] ?? [];
        $skillsRaw       = is_array($skillsRaw) ? implode(',', $skillsRaw) : (string) $skillsRaw;
        $skills          = parseSkillTags($skillsRaw, true, []);
        $members         = filter_var($_POST['jumlah_anggota'] ?? null, FILTER_VALIDATE_INT);
        $description     = trim((string) ($_POST['deskripsi'] ?? ''));
        $contactOptIn    = isset($_POST['contact_opt_in']) && $_POST['contact_opt_in'] === '1';
        $contactEmail    = trim((string) ($_POST['contact_email'] ?? ''));
        $contactWhatsapp = trim((string) ($_POST['contact_whatsapp'] ?? ''));

        if (!$competition
            || !in_array($competition['partisipasi'] ?? '', ['Tim', 'Individu/Tim'], true)
            || empty($skills)
            || $members === false
            || $members < 1
            || $members > 20
            || strlen($description) > 2000
            || $description === '') {
            Session::set('team_post_old', $_POST);
            Session::setFlash('error', 'Lengkapi perlombaan kategori Tim, bidang yang dibutuhkan, jumlah anggota (1–20), dan deskripsi kebutuhan tim.');
            redirect('?page=team-post-create' . ($competitionId ? '&id_lomba=' . $competitionId : ''));
        }

        $validCustomEmail = $contactEmail === '' || filter_var($contactEmail, FILTER_VALIDATE_EMAIL) !== false;
        $validCustomWa    = $contactWhatsapp === '' || preg_match('/^\+?[0-9\s()-]{8,20}$/', $contactWhatsapp) === 1;

        if ($contactOptIn) {
            if (!$validCustomEmail || !$validCustomWa || ($contactEmail === '' && $contactWhatsapp === '')) {
                Session::set('team_post_old', $_POST);
                Session::setFlash('error', 'Selama posting berstatus Open, wajib menyertakan minimal satu kontak aktif (WhatsApp atau Email) yang valid.');
                redirect('?page=team-post-create&id_lomba=' . (int) $competition['id_lomba']);
            }
        } else {
            $accEmail    = trim((string) ($profile['email'] ?? Session::get('email', '')));
            $hasAccEmail = filter_var($accEmail, FILTER_VALIDATE_EMAIL) !== false;
            $hasAccPhone = normalizeWaPhone((string) ($profile['no_hp'] ?? '')) !== '';

            if (!$hasAccEmail && !$hasAccPhone) {
                Session::set('team_post_old', $_POST);
                Session::setFlash('error', 'Akun Anda belum memiliki nomor WhatsApp atau Email yang valid. Silakan centang opsi kontak khusus dan isi minimal satu kontak (WhatsApp atau Email).');
                redirect('?page=team-post-create&id_lomba=' . (int) $competition['id_lomba']);
            }
        }

        $this->teamModel->create($idUser, [
            'id_lomba'          => (int) $competition['id_lomba'],
            'bidang_dibutuhkan' => implode(', ', $skills),
            'jumlah_anggota'    => $members,
            'deskripsi'         => $description,
            'contact_opt_in'    => $contactOptIn,
            'contact_email'     => $contactOptIn && $validCustomEmail && $contactEmail !== '' ? $contactEmail : null,
            'contact_whatsapp'  => $contactOptIn && $validCustomWa && $contactWhatsapp !== '' ? $contactWhatsapp : null,
        ]);

        Session::setFlash('success', 'Posting pencarian anggota tim berhasil diterbitkan dengan status Open!');
        redirect('?page=team-posts&id_lomba=' . (int) $competition['id_lomba']);
    }

    public function updateStatus(): void
    {
        Session::start();
        AuthMiddleware::hasRole(ROLE_PESERTA);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }
        if (!Csrf::isValid($_POST['_csrf'] ?? null)) {
            Session::setFlash('error', 'Permintaan perubahan status tidak valid. Silakan coba kembali.');
            redirect('?page=team-posts');
        }

        $postId = Validator::validatePositiveInt($_POST['id_post'] ?? null);
        $status = trim((string) ($_POST['status'] ?? ''));
        if ($postId === null || !in_array($status, TeamModel::ALLOWED_STATUSES, true)) {
            Session::setFlash('error', 'Permintaan perubahan status posting tidak valid.');
            redirect('?page=team-posts');
        }

        if (!$this->teamModel->updateStatus($postId, (int) Session::get('id_user'), $status)) {
            Session::setFlash('error', 'Posting tidak ditemukan atau tidak ada perubahan status.');
            redirect('?page=team-posts');
        }

        $statusMessages = [
            TeamModel::STATUS_TEAM_COMPLETE => 'Status posting berhasil diubah menjadi Team Complete (kebutuhan anggota tim telah terpenuhi).',
            TeamModel::STATUS_ARCHIVED      => 'Posting berhasil diarsipkan (Archived) dan disembunyikan dari daftar pencarian aktif.',
            TeamModel::STATUS_OPEN          => 'Status posting dibuka kembali menjadi Open (masih mencari anggota).',
        ];

        Session::setFlash('success', $statusMessages[$status] ?? 'Status posting berhasil diperbarui.');
        redirect('?page=team-posts');
    }
}
