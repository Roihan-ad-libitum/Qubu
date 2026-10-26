<?php
/**
 * Model Data Mahasiswa (tabel `mahasiswa` di database qubu-project)
 */

require_once __DIR__ . '/../../config/database.php';

class MahasiswaModel
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? db();
    }

    /**
     * Cek apakah NPM sudah pernah didaftarkan
     */
    public function isNpmExists(string $npm): bool
    {
        $stmt = $this->pdo->prepare("SELECT 1 FROM mahasiswa WHERE npm = :npm LIMIT 1");
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
        try {
            $this->pdo->beginTransaction();

            // 1. Simpan ke tabel users (role = 'peserta')
            $sqlUser = "INSERT INTO users (username, password, role, no_hp, email) 
                        VALUES (:username, :password, 'peserta', :no_hp, :email)";
            $stmtUser = $this->pdo->prepare($sqlUser);
            $stmtUser->execute([
                ':username' => trim($userData['username']),
                ':password' => $userData['password'],
                ':no_hp'    => trim($userData['no_hp']),
                ':email'    => trim($userData['email']),
            ]);

            $idUser = (int) $this->pdo->lastInsertId();

            // 2. Simpan ke tabel mahasiswa
            $sqlMhs = "INSERT INTO mahasiswa (id_user, npm, nama_mahasiswa, program_studi, angkatan, bidang_minat, bio) 
                       VALUES (:id_user, :npm, :nama_mahasiswa, :program_studi, :angkatan, :bidang_minat, :bio)";
            $stmtMhs = $this->pdo->prepare($sqlMhs);
            $stmtMhs->execute([
                ':id_user'        => $idUser,
                ':npm'            => trim($mahasiswaData['npm']),
                ':nama_mahasiswa' => trim($mahasiswaData['nama_mahasiswa']),
                ':program_studi'  => trim($mahasiswaData['program_studi']),
                ':angkatan'       => trim($mahasiswaData['angkatan']),
                ':bidang_minat'   => trim($mahasiswaData['bidang_minat'] ?? 'Umum'),
                ':bio'            => trim($mahasiswaData['bio'] ?? ''),
            ]);

            $this->pdo->commit();
            return $idUser;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
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
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_user' => $idUser]);
        $data = $stmt->fetch();
        return $data === false ? null : $data;
    }
}
