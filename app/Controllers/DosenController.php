<?php
/**
 * Controller untuk mengelola halaman dan aksi Dosen Pembimbing
 */

require_once __DIR__ . '/../Models/DosenModel.php';
require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/../../core/AuthMiddleWare.php';

class DosenController
{
    private DosenModel $dosenModel;

    public function __construct(?DosenModel $dosenModel = null)
    {
        $this->dosenModel = $dosenModel ?? new DosenModel();
    }

    /**
     * Tampilkan dan kelola Halaman Profil Dosen
     */
    public function profile(): void
    {
        $idUser = Session::get('id_user') ? (int) Session::get('id_user') : null;
        $profile = null;

        if ($idUser) {
            $profile = $this->dosenModel->findByUserId($idUser);
        }

        // Tangani form submission pembaruan profil (POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleUpdateProfile($idUser, $profile);
            return;
        }

        // Data default yang presisi sesuai mockup Dosen:
        // Nama: Marsello Hotasi Marpaung, M.Kom
        // Username: @marhots_
        // NIDN: 1234500001
        // Ketersediaan: Bersedia
        // Bidang Keahlian: Software Development, UI/UX Design
        $demoData = Session::get('demo_dosen_profile', [
            'nidn'            => '1234500001',
            'nama_dosen'      => 'Marsello Hotasi Marpaung, M.Kom',
            'username'        => 'marhots_',
            'status'          => 'bersedia',
            'bidang_keahlian' => 'Software Development, UI/UX Design',
            'foto'            => null,
        ]);

        $data = [
            'id_user'         => $idUser,
            'nidn'            => $profile['nidn'] ?? ($demoData['nidn'] ?? '1234500001'),
            'nama_dosen'      => $profile['nama_dosen'] ?? (Session::get('nama_lengkap') ?? $demoData['nama_dosen']),
            'username'        => $profile['username'] ?? (Session::get('username') ?? $demoData['username']),
            'status'          => $profile['status'] ?? ($demoData['status'] ?? 'bersedia'),
            'bidang_keahlian' => $profile['bidang_keahlian'] ?? ($demoData['bidang_keahlian'] ?? 'Software Development, UI/UX Design'),
            'foto'            => $profile['foto'] ?? ($demoData['foto'] ?? null),
        ];

        // Format tags keahlian menjadi array
        $rawTags = array_map('trim', explode(',', (string) $data['bidang_keahlian']));
        $tags = array_values(array_filter($rawTags, fn($t) => $t !== ''));

        if (empty($tags)) {
            $tags = ['Software Development', 'UI/UX Design'];
        }

        // Pilihan bidang keahlian dari database & fallback
        $availableOptions = $this->dosenModel->getBidangKeahlianOptions();

        $error = Session::getFlash('error');
        $success = Session::getFlash('success');

        require_once __DIR__ . '/../../views/dosen/profile.php';
    }

    /**
     * Proses pembaruan profil Dosen (POST)
     */
    private function handleUpdateProfile(?int $idUser, ?array $profile): void
    {
        $nidn           = trim($_POST['nidn'] ?? '');
        $namaDosen      = trim($_POST['nama_dosen'] ?? '');
        $statusRaw      = trim($_POST['status'] ?? 'bersedia');
        $bidangKeahlian = trim($_POST['bidang_keahlian'] ?? '');

        // Normalisasi status ke format database: bersedia / tidak_bersedia
        $statusNormalized = (strtolower($statusRaw) === 'tidak bersedia' || strtolower($statusRaw) === 'tidak_bersedia') 
            ? 'tidak_bersedia' 
            : 'bersedia';

        if (empty($nidn)) {
            Session::setFlash('error', 'NIDN tidak boleh kosong.');
            redirect('?page=dosen-profile');
        }

        // Handle upload foto profil jika ada
        $fotoPath = $profile['foto'] ?? (Session::get('demo_dosen_profile')['foto'] ?? null);

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
                    $safeNidn = preg_replace('/[^a-zA-Z0-9_-]/', '', $nidn);
                    $filename = 'dosen_' . $safeNidn . '_' . time() . '.' . $ext;
                    $destPath = $uploadDir . '/' . $filename;
                    if (move_uploaded_file($fileTmp, $destPath)) {
                        $fotoPath = 'public/uploads/profile/' . $filename;
                    }
                }
            }
        }

        $updateData = [
            'nidn'            => $nidn,
            'status'          => $statusNormalized,
            'bidang_keahlian' => $bidangKeahlian,
            'foto'            => $fotoPath,
        ];
        if (!empty($namaDosen)) {
            $updateData['nama_dosen'] = $namaDosen;
        }

        if ($idUser && $profile) {
            try {
                $this->dosenModel->updateProfileByUserId($idUser, $updateData);
                if (!empty($namaDosen)) {
                    Session::set('nama_lengkap', $namaDosen);
                }
                Session::setFlash('success', 'Profil dosen berhasil diperbarui!');
            } catch (Exception $e) {
                Session::setFlash('error', 'Gagal memperbarui profil: ' . $e->getMessage());
            }
        } else {
            // Mode demo jika belum login
            Session::set('demo_dosen_profile', [
                'nidn'            => $nidn,
                'nama_dosen'      => !empty($namaDosen) ? $namaDosen : 'Marsello Hotasi Marpaung, M.Kom',
                'username'        => Session::get('username') ?? 'marhots_',
                'status'          => $statusNormalized,
                'bidang_keahlian' => $bidangKeahlian,
                'foto'            => $fotoPath,
            ]);
            Session::setFlash('success', 'Profil dosen berhasil diperbarui!');
        }

        redirect('?page=dosen-profile');
    }
}
