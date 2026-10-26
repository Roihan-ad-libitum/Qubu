<?php
/**
 * Controller Pengelolaan Halaman dan Aksi Administrator Qubu (CRUD Lomba)
 */

require_once __DIR__ . '/../Models/CompetitionModel.php';
require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/../../core/AuthMiddleWare.php';

class AdminController
{
    private CompetitionModel $competitionModel;

    public function __construct(?CompetitionModel $competitionModel = null)
    {
        $this->competitionModel = $competitionModel ?? new CompetitionModel();
    }

    /**
     * Tampilkan dan kelola Halaman CRUD Lomba Admin
     */
    public function lomba(): void
    {
        // Tangani penghapusan jika action=delete
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
            $id = (int) $_GET['id'];
            $this->competitionModel->delete($id);
            Session::setFlash('success', 'Lomba berhasil dihapus.');
            redirect('?page=admin-lomba');
            return;
        }

        // Tangani form submission pembuatan / pengubahan lomba (POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleSaveLomba();
            return;
        }

        // Tangani mode Edit jika ada parameter id
        $idLomba = isset($_GET['id']) ? (int) $_GET['id'] : null;
        $lombaData = null;

        if ($idLomba) {
            $lombaData = $this->competitionModel->getById($idLomba);
        }

        // Jika tidak ada data dari DB (atau penambahan baru), gunakan nilai default presisi sesuai mockup
        if (!$lombaData) {
            $lombaData = [
                'id_lomba'            => '',
                'nama_lomba'          => 'Semantik Internformatics Competition',
                'penyelenggara'       => 'Himpunan Mahasiswa Informatika',
                'deadline_daftar'     => '2026-07-15',
                'tanggal_pelaksanaan' => '2026-08-01',
                'tingkat'             => 'Nasional',
                'biaya'               => 50000,
                'partisipasi'         => 'Individu/Tim',
                'bidang'              => 'UI/UX Design',
                'deskripsi'           => '',
                'link_guidebook'      => 'https://bit.ly/guidebook-lomba',
                'link_resmi'          => 'https://bit.ly/daftar-lomba',
                'poster'              => null,
            ];
        }

        $error = Session::getFlash('error');
        $success = Session::getFlash('success');

        require_once __DIR__ . '/../../views/admin/lomba.php';
    }

    /**
     * Proses penyimpanan lomba (Tambah / Update)
     */
    private function handleSaveLomba(): void
    {
        $idLomba       = !empty($_POST['id_lomba']) ? (int) $_POST['id_lomba'] : null;
        $namaLomba     = trim($_POST['nama_lomba'] ?? '');
        $penyelenggara  = trim($_POST['penyelenggara'] ?? '');
        $deadlineRaw   = trim($_POST['deadline_daftar'] ?? '');
        $tingkat       = trim($_POST['tingkat'] ?? 'Nasional');
        $bidang        = trim($_POST['bidang'] ?? 'UI/UX Design');
        $partisipasi   = trim($_POST['partisipasi'] ?? 'Individu/Tim');
        $jenisBiaya    = trim($_POST['jenis_biaya'] ?? 'berbayar'); // gratis / berbayar
        $biayaNominal  = trim($_POST['biaya'] ?? '0');
        $deskripsi     = trim($_POST['deskripsi'] ?? '');
        $linkGuidebook = trim($_POST['link_guidebook'] ?? '');
        $linkResmi     = trim($_POST['link_resmi'] ?? '');

        // Validasi dasar
        if (empty($namaLomba) || empty($penyelenggara)) {
            Session::setFlash('error', 'Nama Perlombaan dan Penyelenggara wajib diisi.');
            redirect('?page=admin-lomba' . ($idLomba ? '&id=' . $idLomba : ''));
            return;
        }

        // Format tanggal deadline
        $deadlineFormatted = date('Y-m-d H:i:s', strtotime($deadlineRaw ?: '2026-07-15'));
        $tglPelaksanaan    = date('Y-m-d', strtotime('+15 days', strtotime($deadlineRaw ?: '2026-07-15')));

        // Format biaya
        $biayaClean = 0;
        if (strtolower($jenisBiaya) === 'berbayar') {
            $biayaClean = (float) preg_replace('/[^0-9]/', '', $biayaNominal);
        }

        // Normalisasi partisipasi enum ('Individu', 'Tim', 'Individu/Tim')
        $validPartisipasi = ['Individu', 'Tim', 'Individu/Tim'];
        if (!in_array($partisipasi, $validPartisipasi, true)) {
            $partisipasi = 'Individu/Tim';
        }

        // Handle upload poster jika ada
        $posterPath = null;
        if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
            $fileTmp  = $_FILES['poster']['tmp_name'];
            $fileSize = $_FILES['poster']['size'];
            $origName = $_FILES['poster']['name'];

            if ($fileSize <= 8 * 1024 * 1024) {
                $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
                if (in_array($ext, $allowed)) {
                    $uploadDir = __DIR__ . '/../../public/uploads/posters';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    $safeSlug = preg_replace('/[^a-zA-Z0-9_-]/', '', substr($namaLomba, 0, 20));
                    $filename = 'poster_' . $safeSlug . '_' . time() . '.' . $ext;
                    $destPath = $uploadDir . '/' . $filename;
                    if (move_uploaded_file($fileTmp, $destPath)) {
                        $posterPath = 'public/uploads/posters/' . $filename;
                    }
                }
            }
        }

        $payload = [
            'nama_lomba'          => $namaLomba,
            'penyelenggara'       => $penyelenggara,
            'deskripsi'           => $deskripsi,
            'deadline_daftar'     => $deadlineFormatted,
            'tanggal_pelaksanaan' => $tglPelaksanaan,
            'tingkat'             => $tingkat,
            'biaya'               => $biayaClean,
            'partisipasi'         => $partisipasi,
            'bidang'              => $bidang,
            'link_resmi'          => $linkResmi,
            'link_guidebook'      => $linkGuidebook,
        ];

        if ($posterPath !== null) {
            $payload['poster'] = $posterPath;
        }

        try {
            if ($idLomba) {
                $this->competitionModel->update($idLomba, $payload);
                Session::setFlash('success', 'Perlombaan berhasil diperbarui!');
            } else {
                $newId = $this->competitionModel->create($payload);
                Session::setFlash('success', 'Perlombaan berhasil diposting!');
            }
        } catch (Exception $e) {
            Session::setFlash('error', 'Gagal memposting lomba: ' . $e->getMessage());
        }

        redirect('?page=admin-lomba');
    }
}
