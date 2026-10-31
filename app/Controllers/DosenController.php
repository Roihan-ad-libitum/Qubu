<?php

require_once __DIR__ . '/../Models/DosenModel.php';
require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/../../core/AuthMiddleware.php';
require_once __DIR__ . '/../../core/Csrf.php';
require_once __DIR__ . '/../../core/Validator.php';
require_once __DIR__ . '/../Helpers/UploadHelper.php';

class DosenController
{
    private DosenModel $dosenModel;

    public function __construct(?DosenModel $dosenModel = null)
    {
        $this->dosenModel = $dosenModel ?? new DosenModel();
    }

    public function dashboard(): void
    {
        Session::start();
        AuthMiddleware::hasRole(ROLE_DOSEN);

        $profile  = $this->dosenModel->findByUserId((int) Session::get('id_user')) ?? [];
        $title    = 'Dashboard Dosen - ' . APP_NAME;
        $success  = Session::getFlash('success');
        $extraCss = ['public/css/components.css', 'public/css/team-flow.css'];
        require __DIR__ . '/../../views/dosen/dashboard.php';
    }

    public function profile(): void
    {
        Session::start();
        AuthMiddleware::hasRole(ROLE_DOSEN);
        $idUser  = Session::get('id_user') ? (int) Session::get('id_user') : null;
        $profile = $idUser ? $this->dosenModel->findByUserId($idUser) : null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Csrf::isValid($_POST['_csrf'] ?? null)) {
                Session::setFlash('error', 'Formulir profil kedaluwarsa atau tidak valid. Silakan coba kembali.');
                redirect('?page=dosen-profile');
            }
            $this->handleUpdateProfile($idUser, $profile);
            return;
        }

        $demoData = Session::get('demo_dosen_profile', [
            'nidn'            => '1234500001',
            'nama_dosen'      => 'Marsello Hotasi Marpaung, M.Kom',
            'username'        => 'marhots_',
            'status'          => 'bersedia',
            'bidang_keahlian' => 'Software Development, UI/UX Design',
            'foto'            => null,
            'contact_visible' => false,
        ]);

        $data = [
            'id_user'         => $idUser,
            'nidn'            => $profile['nidn'] ?? ($demoData['nidn'] ?? '1234500001'),
            'nama_dosen'      => $profile['nama_dosen'] ?? (Session::get('nama_lengkap') ?? $demoData['nama_dosen']),
            'username'        => $profile['username'] ?? (Session::get('username') ?? $demoData['username']),
            'status'          => $profile['status'] ?? ($demoData['status'] ?? 'bersedia'),
            'bidang_keahlian' => $profile['bidang_keahlian'] ?? ($demoData['bidang_keahlian'] ?? 'Software Development, UI/UX Design'),
            'email'           => $profile['email'] ?? (Session::get('email') ?? ''),
            'no_hp'           => $profile['no_hp'] ?? '',
            'foto'            => $profile['foto'] ?? ($demoData['foto'] ?? null),
            'contact_visible' => (bool) ($profile['contact_visible'] ?? ($demoData['contact_visible'] ?? false)),
        ];

        $tags             = parseSkillTags((string) $data['bidang_keahlian'], true, ['Frontend', 'Backend']);
        $availableOptions = BIDANG_QUBU;
        $error            = Session::getFlash('error');
        $success          = Session::getFlash('success');
        $csrfToken        = Csrf::token();

        require_once __DIR__ . '/../../views/dosen/profile.php';
    }

    private function handleUpdateProfile(?int $idUser, ?array $profile): void
    {
        $nidn           = trim($_POST['nidn'] ?? '');
        $namaDosen      = trim($_POST['nama_dosen'] ?? '');
        $statusRaw      = trim($_POST['status'] ?? 'bersedia');
        $bidangKeahlian = trim($_POST['bidang_keahlian'] ?? '');
        $email          = trim((string) ($_POST['email'] ?? ($profile['email'] ?? '')));
        $noHp           = trim((string) ($_POST['no_hp'] ?? ($profile['no_hp'] ?? '')));

        $statusNormalized = (strtolower($statusRaw) === 'tidak bersedia' || strtolower($statusRaw) === 'tidak_bersedia')
            ? 'tidak_bersedia'
            : 'bersedia';

        if (empty($nidn)) {
            Session::setFlash('error', 'NIDN/NIP tidak boleh kosong.');
            redirect('?page=dosen-profile');
        }

        if ($idUser && $this->dosenModel->isNidnExistsForOtherUser($nidn, (int) $idUser)) {
            Session::setFlash('error', 'NIDN/NIP "' . e($nidn) . '" sudah terdaftar pada akun dosen lain.');
            redirect('?page=dosen-profile');
        }

        $hapusFoto = !empty($_POST['hapus_foto']) && $_POST['hapus_foto'] === '1';
        $oldFoto   = $profile['foto'] ?? (Session::get('demo_dosen_profile')['foto'] ?? null);
        $fotoPath  = $oldFoto;

        if ($hapusFoto) {
            if (!empty($oldFoto)) {
                UploadHelper::deleteFile($oldFoto);
            }
            $fotoPath = null;
        } elseif (isset($_FILES['foto']) && ($_FILES['foto']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            try {
                $uploaded = UploadHelper::uploadProfilePhoto($_FILES['foto'], 'dosen', $nidn, $oldFoto);
                if ($uploaded !== null) {
                    $fotoPath = $uploaded;
                }
            } catch (RuntimeException $e) {
                Session::setFlash('error', $e->getMessage());
                redirect('?page=dosen-profile');
            }
        }

        $updateData = [
            'nidn'            => $nidn,
            'status'          => $statusNormalized,
            'bidang_keahlian' => $bidangKeahlian,
            'foto'            => $fotoPath,
            'contact_visible' => isset($_POST['contact_visible']) && $_POST['contact_visible'] === '1',
        ];
        if (!empty($namaDosen)) {
            $updateData['nama_dosen'] = $namaDosen;
        }

        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            Session::setFlash('error', 'Masukkan alamat email dosen yang valid.');
            redirect('?page=dosen-profile');
        }
        if ($noHp !== '' && $noHp !== '-' && !Validator::isValidPhone($noHp)) {
            Session::setFlash('error', 'Masukkan nomor telepon / WhatsApp dosen yang valid (8–15 digit).');
            redirect('?page=dosen-profile');
        }
        $userModel = new UserModel();
        if ($idUser && $email !== '' && $userModel->isEmailExistsForOtherUser($email, (int) $idUser)) {
            Session::setFlash('error', 'Alamat email "' . e($email) . '" sudah digunakan oleh akun lain.');
            redirect('?page=dosen-profile');
        }

        if ($idUser && $profile) {
            try {
                $this->dosenModel->updateProfileByUserId($idUser, $updateData);
                if ($email !== '' || $noHp !== '') {
                    $userModel->updateContactByUserId(
                        (int) $idUser,
                        $email !== '' ? $email : (string) ($profile['email'] ?? ''),
                        $noHp !== '' ? $noHp : (string) ($profile['no_hp'] ?? '')
                    );
                    if ($email !== '') {
                        Session::set('email', $email);
                    }
                }
                if (!empty($namaDosen)) {
                    Session::set('nama_lengkap', $namaDosen);
                }
                Session::setFlash('success', 'Profil dosen berhasil diperbarui!');
            } catch (Exception $e) {
                Session::setFlash('error', 'Gagal memperbarui profil: ' . $e->getMessage());
            }
        } else {
            Session::set('demo_dosen_profile', [
                'nidn'            => $nidn,
                'nama_dosen'      => !empty($namaDosen) ? $namaDosen : 'Marsello Hotasi Marpaung, M.Kom',
                'username'        => Session::get('username') ?? 'marhots_',
                'status'          => $statusNormalized,
                'bidang_keahlian' => $bidangKeahlian,
                'foto'            => $fotoPath,
                'contact_visible' => $updateData['contact_visible'],
            ]);
            Session::setFlash('success', 'Profil dosen berhasil diperbarui!');
        }

        redirect('?page=dosen-profile');
    }
}
