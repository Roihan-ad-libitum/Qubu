<?php
/**
 * Controller Pengelolaan Data & Profil Mahasiswa Qubu
 */

require_once __DIR__ . '/../Models/MahasiswaModel.php';
require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/../../core/AuthMiddleWare.php';

class MahasiswaController
{
    private MahasiswaModel $mahasiswaModel;
    private UserModel $userModel;

    public function __construct()
    {
        $this->mahasiswaModel = new MahasiswaModel();
        $this->userModel = new UserModel();
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

        // Parse bidang peminatan menjadi array tags
        $rawMinat = $data['bidang_minat'];
        $tags = array_filter(array_map('trim', explode(',', (string) $rawMinat)));
        if (empty($tags)) {
            $tags = ['Software Development', 'UI/UX Design'];
        }

        // Ambil pilihan opsi bidang peminatan dari database untuk menu "Tambah +"
        $availableOptions = $this->mahasiswaModel->getBidangPeminatanOptions();

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
