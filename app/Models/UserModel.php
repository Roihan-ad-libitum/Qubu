<?php

class UserModel
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

    public function findByIdentity(string $identity): ?array
    {
        $identity = trim($identity);
        if ($identity === '') {
            return null;
        }

        $sql = "SELECT u.id_user, u.username, u.email, u.password, u.role, u.no_hp,
                       m.id_mahasiswa, d.id_dosen,
                       COALESCE(m.nama_mahasiswa, d.nama_dosen, u.username) AS nama_lengkap
                FROM users u
                LEFT JOIN mahasiswa m ON m.id_user = u.id_user AND u.role = 'peserta'
                LEFT JOIN dosen d     ON d.id_user = u.id_user AND u.role = 'dosen'
                WHERE u.username = :username OR u.email = :email
                ORDER BY u.id_user ASC
                LIMIT 1";

        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([
            ':username' => $identity,
            ':email'    => $identity,
        ]);

        $user = $stmt->fetch();
        return $user === false ? null : $user;
    }

    public function verifyPassword(string $inputPassword, string $storedPassword): bool
    {
        if ($storedPassword === '' || $inputPassword === '') {
            return false;
        }

        if (str_starts_with($storedPassword, '$2y$')
            || str_starts_with($storedPassword, '$2a$')
            || str_starts_with($storedPassword, '$2b$')) {
            return password_verify($inputPassword, $storedPassword);
        }

        return hash_equals($storedPassword, $inputPassword);
    }

    public function isUsernameExists(string $username): bool
    {
        $stmt = $this->pdo()->prepare("SELECT 1 FROM users WHERE username = :username LIMIT 1");
        $stmt->execute([':username' => trim($username)]);
        return (bool) $stmt->fetchColumn();
    }

    public function isEmailExists(string $email): bool
    {
        $stmt = $this->pdo()->prepare("SELECT 1 FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => trim($email)]);
        return (bool) $stmt->fetchColumn();
    }

    public function isEmailExistsForOtherUser(string $email, int $excludeUserId): bool
    {
        $stmt = $this->pdo()->prepare("SELECT 1 FROM users WHERE email = :email AND id_user != :id_user LIMIT 1");
        $stmt->execute([
            ':email'   => trim($email),
            ':id_user' => $excludeUserId,
        ]);
        return (bool) $stmt->fetchColumn();
    }

    public function updateContactByUserId(int $idUser, string $email, string $noHp): bool
    {
        $stmt = $this->pdo()->prepare("
            UPDATE users
            SET email = :email, no_hp = :no_hp
            WHERE id_user = :id_user
        ");
        return $stmt->execute([
            ':email'   => trim($email),
            ':no_hp'   => trim($noHp),
            ':id_user' => $idUser,
        ]);
    }
}
