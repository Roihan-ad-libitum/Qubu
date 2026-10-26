<?php
/**
 * Model Data Mahasiswa (tabel `mahasiswa` di database qubu-project)
 */

require_once __DIR__ . '/../../config/database.php';

class MahasiswaModel
{
    private ?PDO $pdo = null;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
    }

    private function pdo(): PDO
    {
        return $this->pdo ??= db();
    }

    /**
     * Cek apakah NPM sudah pernah didaftarkan
     */
    public function isNpmExists(string $npm): bool
    {
        $stmt = $this->pdo()->prepare("SELECT 1 FROM mahasiswa WHERE npm = :npm LIMIT 1");
        $stmt->execute([':npm' => trim($npm)]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Daftarkan akun Mahasiswa baru (menggunakan transaksi atomik tabel users & mahasiswa)
     *
     * @param array $userData      ['username', 'password', 'no_hp', 'email']
     * @param array $mahasiswaData ['npm', 'nama_mahasiswa', 'program_studi', 'angkatan', 'bidang_minat', 'bio']
     * @return int ID User yang baru dibuat
     * @throws Exception jika gagal query
     */
    public function registerMahasiswa(array $userData, array $mahasiswaData): int
    {
        $db = $this->pdo();
        try {
            $db->beginTransaction();

            // 1. Simpan ke tabel users (role = 'peserta')
            $sqlUser = "INSERT INTO users (username, password, role, no_hp, email) 
                        VALUES (:username, :password, 'peserta', :no_hp, :email)";
            $stmtUser = $db->prepare($sqlUser);
            $stmtUser->execute([
                ':username' => trim($userData['username']),
                ':password' => $userData['password'],
                ':no_hp'    => trim($userData['no_hp']),
                ':email'    => trim($userData['email']),
            ]);

            $idUser = (int) $db->lastInsertId();

            // Cek apakah tabel mahasiswa memiliki kolom 'foto'
            $hasFoto = false;
            try {
                $colStmt = $db->query("SHOW COLUMNS FROM mahasiswa LIKE 'foto'");
                $hasFoto = (bool) $colStmt->fetch();
                if (!$hasFoto && !empty($mahasiswaData['foto'])) {
                    // Coba tambahkan kolom foto jika belum ada di database
                    $db->exec("ALTER TABLE mahasiswa ADD COLUMN foto VARCHAR(255) NULL");
                    $hasFoto = true;
                }
            } catch (Throwable $e) {
                $hasFoto = false;
            }

            // 2. Simpan ke tabel mahasiswa
            if ($hasFoto && !empty($mahasiswaData['foto'])) {
                $sqlMhs = "INSERT INTO mahasiswa (id_user, npm, nama_mahasiswa, program_studi, angkatan, bidang_minat, bio, foto) 
                           VALUES (:id_user, :npm, :nama_mahasiswa, :program_studi, :angkatan, :bidang_minat, :bio, :foto)";
                $stmtMhs = $db->prepare($sqlMhs);
                $stmtMhs->execute([
                    ':id_user'        => $idUser,
                    ':npm'            => trim($mahasiswaData['npm']),
                    ':nama_mahasiswa' => trim($mahasiswaData['nama_mahasiswa']),
                    ':program_studi'  => trim($mahasiswaData['program_studi']),
                    ':angkatan'       => trim($mahasiswaData['angkatan']),
                    ':bidang_minat'   => trim($mahasiswaData['bidang_minat'] ?? 'Umum'),
                    ':bio'            => trim($mahasiswaData['bio'] ?? ''),
                    ':foto'           => trim($mahasiswaData['foto']),
                ]);
            } else {
                $sqlMhs = "INSERT INTO mahasiswa (id_user, npm, nama_mahasiswa, program_studi, angkatan, bidang_minat, bio) 
                           VALUES (:id_user, :npm, :nama_mahasiswa, :program_studi, :angkatan, :bidang_minat, :bio)";
                $stmtMhs = $db->prepare($sqlMhs);
                $stmtMhs->execute([
                    ':id_user'        => $idUser,
                    ':npm'            => trim($mahasiswaData['npm']),
                    ':nama_mahasiswa' => trim($mahasiswaData['nama_mahasiswa']),
                    ':program_studi'  => trim($mahasiswaData['program_studi']),
                    ':angkatan'       => trim($mahasiswaData['angkatan']),
                    ':bidang_minat'   => trim($mahasiswaData['bidang_minat'] ?? 'Umum'),
                    ':bio'            => trim($mahasiswaData['bio'] ?? ''),
                ]);
            }

            $db->commit();
            return $idUser;
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Ambil daftar pilihan bidang peminatan dari database (tabel lomba dan mahasiswa)
     * dengan fallback otomatis ke kategori lomba resmi Qubu jika database kosong/offline.
     *
     * @return array
     */
    public function getBidangPeminatanOptions(): array
    {
        $defaultOptions = [
            'Frontend',
            'Backend',
            'UI/UX Design',
            'Data Science',
            'Robotic',
            'Web Development',
            'Mobile Development',
            'Cyber Security',
            'Artificial Intelligence',
            'Game Development',
            'Cloud Computing',
            'Internet of Things (IoT)'
        ];

        try {
            $db = $this->pdo();
            $dbOptions = [];

            // Ambil dari tabel lomba (kolom bidang)
            try {
                $stmtLomba = $db->query("SELECT DISTINCT bidang FROM lomba WHERE bidang IS NOT NULL AND TRIM(bidang) != ''");
                if ($stmtLomba) {
                    while ($val = $stmtLomba->fetchColumn()) {
                        $trimmed = trim((string)$val);
                        if ($trimmed !== '') {
                            $dbOptions[] = $trimmed;
                        }
                    }
                }
            } catch (Throwable $e) {
                // Abaikan jika tabel lomba belum siap
            }

            // Ambil dari tabel mahasiswa (kolom bidang_minat)
            try {
                $stmtMhs = $db->query("SELECT DISTINCT bidang_minat FROM mahasiswa WHERE bidang_minat IS NOT NULL AND TRIM(bidang_minat) != ''");
                if ($stmtMhs) {
                    while ($val = $stmtMhs->fetchColumn()) {
                        $trimmed = trim((string)$val);
                        if ($trimmed !== '') {
                            $dbOptions[] = $trimmed;
                        }
                    }
                }
            } catch (Throwable $e) {
                // Abaikan jika tabel mahasiswa belum siap
            }

            if (!empty($dbOptions)) {
                $merged = array_unique(array_merge($defaultOptions, $dbOptions));
                sort($merged, SORT_STRING | SORT_FLAG_CASE);
                return array_values($merged);
            }
        } catch (Throwable $e) {
            // Fallback ke default
        }

        sort($defaultOptions, SORT_STRING | SORT_FLAG_CASE);
        return $defaultOptions;
    }

    /**
     * Ambil data profil mahasiswa berdasarkan id_user
     */
    public function findByUserId(int $idUser): ?array
    {
        $sql = "SELECT m.*, u.username, u.email, u.no_hp 
                FROM mahasiswa m 
                JOIN users u ON u.id_user = m.id_user 
                WHERE m.id_user = :id_user 
                LIMIT 1";
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([':id_user' => $idUser]);
        $data = $stmt->fetch();
        return $data === false ? null : $data;
    }
}
