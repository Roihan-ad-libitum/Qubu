<?php

require_once __DIR__ . '/../Models/AdminModel.php';
require_once __DIR__ . '/../Models/CompetitionModel.php';
require_once __DIR__ . '/../Models/DosenModel.php';
require_once __DIR__ . '/../Helpers/UploadHelper.php';
require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/../../core/AuthMiddleware.php';
require_once __DIR__ . '/../../core/Csrf.php';

class AdminController
{
    private AdminModel $adminModel;
    private CompetitionModel $competitionModel;

    public function __construct(
        ?AdminModel $adminModel = null,
        ?CompetitionModel $competitionModel = null
    ) {
        $this->adminModel = $adminModel ?? new AdminModel();
        $this->competitionModel = $competitionModel ?? new CompetitionModel();
    }

    public function dashboard(): void
    {
        Session::start();
        AuthMiddleware::hasRole(ROLE_ADMIN);

        $summary = $this->adminModel->getSummary();
        $recentCompetitions = array_slice($this->competitionModel->getAll(), 0, 5);
        $csrfToken = Csrf::token();
        $title = 'Dashboard Admin - ' . APP_NAME;
        $extraCss = ['public/css/admin-panel.css'];
        require __DIR__ . '/../../views/admin/dashboard.php';
    }

    public function lomba(): void
    {
        Session::start();
        AuthMiddleware::hasRole(ROLE_ADMIN);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->deleteCompetition();
            return;
        }

        if (isset($_GET['id'])) {
            redirect('?page=admin-lomba-form&id=' . rawurlencode((string) $_GET['id']));
        }

        $keyword = trim((string) ($_GET['q'] ?? ''));
        $competitions = $this->competitionModel->getAll();
        if ($keyword !== '') {
            $needle = strtolower($keyword);
            $competitions = array_values(array_filter($competitions, static function (array $competition) use ($needle): bool {
                foreach (['nama_lomba', 'penyelenggara', 'bidang', 'tingkat'] as $field) {
                    if (str_contains(strtolower((string) ($competition[$field] ?? '')), $needle)) {
                        return true;
                    }
                }
                return false;
            }));
        }

        $csrfToken = Csrf::token();
        $error = Session::getFlash('error');
        $success = Session::getFlash('success');
        $title = 'Kelola Lomba - Admin ' . APP_NAME;
        $extraCss = ['public/css/admin-panel.css'];
        require __DIR__ . '/../../views/admin/lomba-list.php';
    }

    public function lombaForm(): void
    {
        Session::start();
        AuthMiddleware::hasRole(ROLE_ADMIN);

        $rawId = $_GET['id'] ?? null;
        $id = $rawId === null ? null : filter_var($rawId, FILTER_VALIDATE_INT);
        if ($rawId !== null && ($id === false || $id <= 0)) {
            http_response_code(400);
            Session::setFlash('error', 'ID perlombaan tidak valid.');
            redirect('?page=admin-lomba');
        }

        $lombaData = [
            'id_lomba' => '',
            'nama_lomba' => '',
            'penyelenggara' => '',
            'deadline_daftar' => '',
            'tanggal_pelaksanaan' => '',
            'tingkat' => '',
            'biaya' => 0,
            'partisipasi' => '',
            'bidang' => '',
            'deskripsi' => '',
            'link_guidebook' => '',
            'link_resmi' => '',
            'poster' => null,
            'jenis_biaya' => 'gratis',
        ];

        if ($id !== null) {
            $existing = $this->competitionModel->getById((int) $id);
            if ($existing === null) {
                http_response_code(404);
                Session::setFlash('error', 'Perlombaan tidak ditemukan.');
                redirect('?page=admin-lomba');
            }
            $lombaData = array_merge($lombaData, $existing);
            $lombaData['jenis_biaya'] = (float) ($existing['biaya'] ?? 0) > 0 ? 'berbayar' : 'gratis';
        }
        $isLombaClosed = !empty($lombaData['id_lomba'])
            && isLombaRegistrationClosed($lombaData['deadline_daftar'] ?? null);

        $old = Session::get('admin_lomba_old', []);
        Session::remove('admin_lomba_old');
        foreach ($old as $key => $value) {
            if (array_key_exists($key, $lombaData) && $key !== 'poster') {
                $lombaData[$key] = is_array($value) ? implode(', ', array_filter(array_map('trim', $value))) : $value;
            }
        }
        $lombaData['deadline_daftar'] = $this->dateFieldValue($lombaData['deadline_daftar'] ?? '');
        $lombaData['tanggal_pelaksanaan'] = $this->dateFieldValue($lombaData['tanggal_pelaksanaan'] ?? '');

        $csrfToken = Csrf::token();
        $error = Session::getFlash('error');
        $title = ($id === null ? 'Tambah Lomba' : 'Edit Lomba') . ' - Admin ' . APP_NAME;
        $extraCss = ['public/css/admin-lomba.css', 'public/css/admin-panel.css'];
        require __DIR__ . '/../../views/admin/lomba.php';
    }

    public function saveLomba(): void
    {
        Session::start();
        AuthMiddleware::hasRole(ROLE_ADMIN);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            return;
        }

        $rawId = trim((string) ($_POST['id_lomba'] ?? ''));
        $id = $rawId === '' ? null : filter_var($rawId, FILTER_VALIDATE_INT);
        if ($id === false || ($id !== null && $id <= 0)) {
            $this->failLombaForm('ID perlombaan tidak valid.', null);
        }
        if (!Csrf::isValid($_POST['_csrf'] ?? null)) {
            $this->failLombaForm('Formulir kedaluwarsa atau tidak valid. Silakan coba kembali.', $id);
        }
        if ($id !== null && $this->competitionModel->getById((int) $id) === null) {
            $this->failLombaForm('Perlombaan yang akan diubah tidak ditemukan.', null);
        }

        $name = trim((string) ($_POST['nama_lomba'] ?? ''));
        $organizer = trim((string) ($_POST['penyelenggara'] ?? ''));
        $deadline = $this->parseDate((string) ($_POST['deadline_daftar'] ?? ''));
        $eventDate = $this->parseDate((string) ($_POST['tanggal_pelaksanaan'] ?? ''));
        $level = trim((string) ($_POST['tingkat'] ?? ''));
        $rawBidangInput = $_POST['bidang'] ?? '';
        $rawBidangCsv   = is_array($rawBidangInput)
            ? implode(', ', array_map('trim', $rawBidangInput))
            : trim((string) $rawBidangInput);
        $validBidangList = parseSkillTags($rawBidangCsv, true, []);
        $field           = implode(', ', $validBidangList);

        $participation = trim((string) ($_POST['partisipasi'] ?? ''));
        $description = trim((string) ($_POST['deskripsi'] ?? ''));
        $officialLink = trim((string) ($_POST['link_resmi'] ?? ''));
        $feeType = (string) ($_POST['jenis_biaya'] ?? '');
        $feeRaw = trim((string) ($_POST['biaya'] ?? ''));
        $feeDigits = preg_replace('/[Rp.\s]/i', '', $feeRaw);

        if ($name === '' || strlen($name) > 100 || $organizer === '' || strlen($organizer) > 100) {
            $this->failLombaForm('Nama lomba dan penyelenggara wajib diisi (maksimal 100 karakter).', $id);
        }
        if ($deadline === null || $eventDate === null) {
            $this->failLombaForm('Masukkan tanggal deadline dan tanggal pelaksanaan yang valid.', $id);
        }
        if ($eventDate < $deadline) {
            $this->failLombaForm('Tanggal pelaksanaan tidak boleh mendahului deadline pendaftaran.', $id);
        }
        if ($level === '' || strlen($level) > 50) {
            $this->failLombaForm('Tingkat perlombaan wajib diisi (maksimal 50 karakter).', $id);
        }
        if (empty($validBidangList)) {
            $this->failLombaForm('Pilih minimal satu bidang perlombaan yang tersedia.', $id);
        }
        if (!in_array($participation, ['Individu', 'Tim', 'Individu/Tim'], true)) {
            $this->failLombaForm('Pilih format partisipasi yang valid.', $id);
        }
        if (strlen($description) > 10000) {
            $this->failLombaForm('Deskripsi maksimal 10.000 karakter.', $id);
        }
        if (!in_array($feeType, ['gratis', 'berbayar'], true)
            || ($feeType === 'berbayar' && (!is_string($feeDigits) || !ctype_digit($feeDigits) || (float) $feeDigits < 1 || (float) $feeDigits > 9999999999))) {
            $this->failLombaForm('Masukkan biaya perlombaan yang valid.', $id);
        }
        if (!$this->isValidOptionalUrl($officialLink)) {
            $this->failLombaForm('Tautan pendaftaran harus berupa URL HTTP atau HTTPS yang valid.', $id);
        }

        $posterPath = null;
        if (isset($_FILES['poster']) && ($_FILES['poster']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            if (($_FILES['poster']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                $this->failLombaForm('Poster gagal diunggah. Silakan pilih ulang file poster.', $id);
            }
            try {
                $posterPath = UploadHelper::uploadPoster($_FILES['poster'], $name);
            } catch (RuntimeException $exception) {
                $this->failLombaForm($exception->getMessage(), $id);
            }
            if ($posterPath === null) {
                $this->failLombaForm('Poster tidak berhasil disimpan. Silakan coba lagi.', $id);
            }
        }

        $payload = [
            'nama_lomba' => $name,
            'penyelenggara' => $organizer,
            'deskripsi' => $description,
            'deadline_daftar' => $deadline->format('Y-m-d 23:59:59'),
            'tanggal_pelaksanaan' => $eventDate->format('Y-m-d'),
            'tingkat' => $level,
            'biaya' => $feeType === 'gratis' ? 0 : (float) $feeDigits,
            'partisipasi' => $participation,
            'bidang' => $field,
            'link_resmi' => $officialLink,
            'link_guidebook' => null,
        ];
        if ($posterPath !== null) {
            $payload['poster'] = $posterPath;
        }

        try {
            if ($id !== null) {
                $this->competitionModel->update((int) $id, $payload);
                Session::setFlash('success', 'Perlombaan berhasil diperbarui.');
            } else {
                $this->competitionModel->create($payload);
                Session::setFlash('success', 'Perlombaan berhasil ditambahkan.');
            }
        } catch (Throwable $exception) {
            error_log('[Qubu Admin Competition Save] ' . $exception->getMessage());
            if ($posterPath !== null) {
                $uploadedFile = __DIR__ . '/../../' . $posterPath;
                if (is_file($uploadedFile)) {
                    unlink($uploadedFile);
                }
            }
            $this->failLombaForm('Perubahan lomba gagal disimpan. Periksa data dan coba kembali.', $id);
        }

        redirect('?page=admin-lomba');
    }

    public function users(): void
    {
        Session::start();
        AuthMiddleware::hasRole(ROLE_ADMIN);
        $keyword = trim((string) ($_GET['q'] ?? ''));
        $role = trim((string) ($_GET['role'] ?? ''));
        $users = $this->adminModel->searchUsers($keyword, $role);
        $title = 'Pengguna - Admin ' . APP_NAME;
        $extraCss = ['public/css/admin-panel.css'];
        require __DIR__ . '/../../views/admin/users.php';
    }

    public function dosen(): void
    {
        Session::start();
        AuthMiddleware::hasRole(ROLE_ADMIN);
        $keyword = trim((string) ($_GET['q'] ?? ''));
        $lecturers = $this->adminModel->searchLecturers($keyword);
        $title = 'Dosen Pembimbing - Admin ' . APP_NAME;
        $extraCss = ['public/css/admin-panel.css'];
        require __DIR__ . '/../../views/admin/dosen.php';
    }

    private function deleteCompetition(): void
    {
        if (!Csrf::isValid($_POST['_csrf'] ?? null)) {
            Session::setFlash('error', 'Permintaan hapus tidak valid. Silakan coba kembali.');
            redirect('?page=admin-lomba');
        }

        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        if ($id === false || $id <= 0 || $this->competitionModel->getById((int) $id) === null) {
            Session::setFlash('error', 'Perlombaan tidak ditemukan atau ID tidak valid.');
            redirect('?page=admin-lomba');
        }

        try {
            if (!$this->competitionModel->delete((int) $id)) {
                Session::setFlash('error', 'Perlombaan tidak berhasil dihapus.');
            } else {
                Session::setFlash('success', 'Perlombaan berhasil dihapus.');
            }
        } catch (Throwable $exception) {
            error_log('[Qubu Admin Competition Delete] ' . $exception->getMessage());
            Session::setFlash('error', 'Perlombaan tidak dapat dihapus. Data tersebut mungkin masih digunakan oleh posting atau data lain.');
        }
        redirect('?page=admin-lomba');
    }

    private function failLombaForm(string $message, int|string|null $id): never
    {
        $old = $_POST;
        unset($old['password'], $old['password_confirm'], $old['_csrf']);
        if ($id === null) {
            unset($old['id_lomba']);
        } else {
            $old['id_lomba'] = (string) $id;
        }
        Session::set('admin_lomba_old', $old);
        Session::setFlash('error', $message);
        redirect('?page=admin-lomba-form' . ($id !== null ? '&id=' . (int) $id : ''));
    }

    private function parseDate(string $value): ?DateTimeImmutable
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        $errors = DateTimeImmutable::getLastErrors();
        if (!$date || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
            return null;
        }
        return $date->format('Y-m-d') === $value ? $date : null;
    }

    private function isValidOptionalUrl(string $value): bool
    {
        if ($value === '') {
            return true;
        }
        if (strlen($value) > 255 || filter_var($value, FILTER_VALIDATE_URL) === false) {
            return false;
        }
        return in_array(strtolower((string) parse_url($value, PHP_URL_SCHEME)), ['http', 'https'], true);
    }

    private function dateFieldValue(mixed $value): string
    {
        $datePart = substr((string) $value, 0, 10);
        $date = $this->parseDate($datePart);
        return $date?->format('Y-m-d') ?? '';
    }
}
