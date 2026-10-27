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
     * Ambil daftar pilihan bidang peminatan resmi Qubu
     * Fokus secara eksklusif pada kartu yang ada di index.php:
     * (Frontend, Backend, UI/UX Design, Data Science, Robotic)
     *
     * @return array
     */
    public function getBidangPeminatanOptions(): array
    {
        if (defined('BIDANG_QUBU')) {
            return BIDANG_QUBU;
        }

        return [
            'Frontend',
            'Backend',
            'UI/UX Design',
            'Data Science',
            'Robotic'
        ];
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

    /**
     * Ambil data profil mahasiswa berdasarkan NPM
     */
    public function findByNpm(string $npm): ?array
    {
        $sql = "SELECT m.*, u.username, u.email, u.no_hp 
                FROM mahasiswa m 
                JOIN users u ON u.id_user = m.id_user 
                WHERE m.npm = :npm 
                LIMIT 1";
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([':npm' => trim($npm)]);
        $data = $stmt->fetch();
        return $data === false ? null : $data;
    }

    /**
     * Perbarui data profil mahasiswa berdasarkan id_user
     */
    public function updateProfileByUserId(int $idUser, array $data): bool
    {
        $fields = [];
        $params = [':id_user' => $idUser];

        if (isset($data['npm'])) {
            $fields[] = 'npm = :npm';
            $params[':npm'] = trim($data['npm']);
        }
        if (isset($data['nama_mahasiswa'])) {
            $fields[] = 'nama_mahasiswa = :nama_mahasiswa';
            $params[':nama_mahasiswa'] = trim($data['nama_mahasiswa']);
        }
        if (isset($data['program_studi'])) {
            $fields[] = 'program_studi = :program_studi';
            $params[':program_studi'] = trim($data['program_studi']);
        }
        if (isset($data['angkatan'])) {
            $fields[] = 'angkatan = :angkatan';
            $params[':angkatan'] = trim($data['angkatan']);
        }
        if (isset($data['bidang_minat'])) {
            $fields[] = 'bidang_minat = :bidang_minat';
            $params[':bidang_minat'] = trim($data['bidang_minat']);
        }
        if (isset($data['bio'])) {
            $fields[] = 'bio = :bio';
            $params[':bio'] = trim($data['bio']);
        }
        if (isset($data['foto'])) {
            try {
                $colStmt = $this->pdo()->query("SHOW COLUMNS FROM mahasiswa LIKE 'foto'");
                if (!$colStmt->fetch()) {
                    $this->pdo()->exec("ALTER TABLE mahasiswa ADD COLUMN foto VARCHAR(255) NULL");
                }
                $fields[] = 'foto = :foto';
                $params[':foto'] = trim($data['foto']);
            } catch (Throwable $e) {
                // Abaikan jika alter gagal
            }
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE mahasiswa SET " . implode(', ', $fields) . " WHERE id_user = :id_user";
        $stmt = $this->pdo()->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Ambil daftar rekan mahasiswa yang memiliki bidang minat sama dengan user
     * Digunakan pada section "Rekomendasi Rekan" Dashboard Qubu
     *
     * @param int $currentUserId ID user yang sedang login (agar tidak merekomendasikan diri sendiri)
     * @param string $bidangMinat Bidang minat dari profil user (bisa single atau dipisahkan koma)
     * @return array
     */
    public function getRecommendedPartnersByBidang(int $currentUserId, string $bidangMinat): array
    {
        try {
            $minatItems = array_filter(array_map('trim', explode(',', $bidangMinat)));
            if (empty($minatItems)) {
                $minatItems = ['UI/UX Design', 'Web Development'];
            }

            $conditions = [];
            $params = [':current_user_id' => $currentUserId];

            foreach ($minatItems as $idx => $m) {
                $key = ':minat_' . $idx;
                $conditions[] = "LOWER(m.bidang_minat) LIKE LOWER($key)";
                $params[$key] = '%' . $m . '%';
            }

            $whereClause = implode(' OR ', $conditions);
            $sql = "SELECT m.*, u.username, u.email, u.no_hp 
                    FROM mahasiswa m 
                    JOIN users u ON u.id_user = m.id_user 
                    WHERE m.id_user != :current_user_id 
                      AND ($whereClause)
                    ORDER BY m.id_mahasiswa DESC 
                    LIMIT 10";

            $stmt = $this->pdo()->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Jika hasil filter bidang spesifik kosong, berikan mahasiswa lain sebagai fallback
            if (empty($results)) {
                $stmtFallback = $this->pdo()->prepare(
                    "SELECT m.*, u.username, u.email, u.no_hp 
                     FROM mahasiswa m 
                     JOIN users u ON u.id_user = m.id_user 
                     WHERE m.id_user != :current_user_id 
                     ORDER BY m.id_mahasiswa DESC 
                     LIMIT 8"
                );
                $stmtFallback->execute([':current_user_id' => $currentUserId]);
                $results = $stmtFallback->fetchAll(PDO::FETCH_ASSOC);
            }

            return $results;
        } catch (Throwable $e) {
            return [];
        }
    }
}

