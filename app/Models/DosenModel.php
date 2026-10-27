<?php
/**
 * Model Data Dosen Pembimbing (tabel `dosen` di database qubu-project)
 */

require_once __DIR__ . '/../../config/database.php';

class DosenModel
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
     * Cek apakah NIDN sudah pernah didaftarkan
     */
    public function isNidnExists(string $nidn): bool
    {
        $stmt = $this->pdo()->prepare("SELECT 1 FROM dosen WHERE nidn = :nidn LIMIT 1");
        $stmt->execute([':nidn' => trim($nidn)]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Daftarkan akun Dosen baru (menggunakan transaksi atomik tabel users & dosen)
     *
     * @param array $userData  ['username', 'password', 'no_hp', 'email']
     * @param array $dosenData ['nama_dosen', 'bidang_keahlian', 'nidn', 'status']
     * @return int ID User yang baru dibuat
     * @throws Exception jika gagal query
     */
    public function registerDosen(array $userData, array $dosenData): int
    {
        $db = $this->pdo();
        try {
            $db->beginTransaction();

            // 1. Simpan ke tabel users (role = 'dosen')
            $sqlUser = "INSERT INTO users (username, password, role, no_hp, email) 
                        VALUES (:username, :password, 'dosen', :no_hp, :email)";
            $stmtUser = $db->prepare($sqlUser);
            $stmtUser->execute([
                ':username' => trim($userData['username']),
                ':password' => $userData['password'], // Disimpan plain-text sesuai aturan proyek
                ':no_hp'    => trim($userData['no_hp']),
                ':email'    => trim($userData['email']),
            ]);

            $idUser = (int) $db->lastInsertId();

            // 2. Simpan ke tabel dosen
            $sqlDosen = "INSERT INTO dosen (id_user, nama_dosen, bidang_keahlian, nidn, status) 
                         VALUES (:id_user, :nama_dosen, :bidang_keahlian, :nidn, :status)";
            $stmtDosen = $db->prepare($sqlDosen);
            $stmtDosen->execute([
                ':id_user'         => $idUser,
                ':nama_dosen'      => trim($dosenData['nama_dosen']),
                ':bidang_keahlian' => trim($dosenData['bidang_keahlian']),
                ':nidn'            => trim($dosenData['nidn']),
                ':status'          => $dosenData['status'] ?? 'bersedia',
            ]);

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
     * Ambil data profil dosen berdasarkan id_user
     */
    public function findByUserId(int $idUser): ?array
    {
        $sql = "SELECT d.*, u.username, u.email, u.no_hp 
                FROM dosen d 
                JOIN users u ON u.id_user = d.id_user 
                WHERE d.id_user = :id_user 
                LIMIT 1";
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([':id_user' => $idUser]);
        $data = $stmt->fetch();
        return $data === false ? null : $data;
    }

    /**
     * Ambil data profil dosen berdasarkan NIDN
     */
    public function findByNidn(string $nidn): ?array
    {
        $sql = "SELECT d.*, u.username, u.email, u.no_hp 
                FROM dosen d 
                JOIN users u ON u.id_user = d.id_user 
                WHERE d.nidn = :nidn 
                LIMIT 1";
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([':nidn' => trim($nidn)]);
        $data = $stmt->fetch();
        return $data === false ? null : $data;
    }

    /**
     * Perbarui data profil dosen berdasarkan id_user
     */
    public function updateProfileByUserId(int $idUser, array $data): bool
    {
        $fields = [];
        $params = [':id_user' => $idUser];

        if (isset($data['nidn'])) {
            $fields[] = 'nidn = :nidn';
            $params[':nidn'] = trim($data['nidn']);
        }
        if (isset($data['nama_dosen'])) {
            $fields[] = 'nama_dosen = :nama_dosen';
            $params[':nama_dosen'] = trim($data['nama_dosen']);
        }
        if (isset($data['bidang_keahlian'])) {
            $fields[] = 'bidang_keahlian = :bidang_keahlian';
            $params[':bidang_keahlian'] = trim($data['bidang_keahlian']);
        }
        if (isset($data['status'])) {
            $statusVal = strtolower(trim($data['status']));
            // Normalisasi status ke format enum database: bersedia / tidak_bersedia
            if ($statusVal === 'bersedia' || $statusVal === 'tidak_bersedia') {
                $fields[] = 'status = :status';
                $params[':status'] = $statusVal;
            }
        }
        if (isset($data['foto'])) {
            try {
                $colStmt = $this->pdo()->query("SHOW COLUMNS FROM dosen LIKE 'foto'");
                if (!$colStmt->fetch()) {
                    $this->pdo()->exec("ALTER TABLE dosen ADD COLUMN foto VARCHAR(255) NULL");
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

        $sql = "UPDATE dosen SET " . implode(', ', $fields) . " WHERE id_user = :id_user";
        $stmt = $this->pdo()->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Ambil daftar pilihan bidang keahlian dosen
     * Fokus secara eksklusif pada kartu yang ada di index.php:
     * (Frontend, Backend, UI/UX Design, Data Science, Robotic)
     *
     * @return array
     */
    public function getBidangKeahlianOptions(): array
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
}
