<?php
/**
 * Model akun pengguna (tabel `users` di database qubu-project).
 * Menggunakan pencocokan password plain-text sesuai data tim.
 */
class UserModel
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? db();
    }

    /**
     * Cari akun berdasarkan username ATAU email.
     * Mengembalikan null jika tidak ditemukan.
     */
    public function findByIdentity(string $identity): ?array
    {
        $identity = trim($identity);
        if ($identity === '') {
            return null;
        }

        // Gunakan 2 placeholder unik (:username dan :email) agar tidak bentrok di PDO
        $sql = "SELECT u.id_user, u.username, u.email, u.password, u.role, u.no_hp,
                       m.id_mahasiswa, d.id_dosen,
                       COALESCE(m.nama_mahasiswa, d.nama_dosen, u.username) AS nama_lengkap
                FROM users u
                LEFT JOIN mahasiswa m ON m.id_user = u.id_user AND u.role = 'peserta'
                LEFT JOIN dosen d     ON d.id_user = u.id_user AND u.role = 'dosen'
                WHERE u.username = :username OR u.email = :email
                ORDER BY u.id_user ASC
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':username' => $identity,
            ':email'    => $identity
        ]);

        $user = $stmt->fetch();

        return $user === false ? null : $user;
    }

    /**
     * Verifikasi password secara plain-text (aman dari timing attack).
     * Jika ada password yang di-hash bcrypt, tetap didukung agar fleksibel.
     */
    public function verifyPassword(string $inputPassword, string $storedPassword): bool
    {
        if ($storedPassword === '' || $inputPassword === '') {
            return false;
        }

        // Jika data password diawali format hash bcrypt ($2y$), gunakan password_verify
        if (str_starts_with($storedPassword, '$2y$') || str_starts_with($storedPassword, '$2a$')) {
            return password_verify($inputPassword, $storedPassword);
        }

        // Pencocokan plain-text menggunakan hash_equals untuk keamanan
        return hash_equals($storedPassword, $inputPassword);
    }
}
