<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';

class DosenModel
{
    private ?PDO $pdo = null;
    private static bool $contactVisibilityEnsured = false;
    private static bool $fotoColumnEnsured = false;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
    }

    private function pdo(): PDO
    {
        $db = $this->pdo ??= db();

        if (!self::$contactVisibilityEnsured) {
            self::$contactVisibilityEnsured = true;
            try {
                $columns = $db->query("SHOW COLUMNS FROM dosen LIKE 'contact_visible'")->fetch();
                if ($columns === false) {
                    $db->exec("ALTER TABLE dosen ADD COLUMN contact_visible TINYINT(1) NOT NULL DEFAULT 0");
                }
            } catch (Throwable $e) {

            }
        }

        if (!self::$fotoColumnEnsured) {
            self::$fotoColumnEnsured = true;
            try {
                $col = $db->query("SHOW COLUMNS FROM dosen LIKE 'foto'")->fetch();
                if ($col === false) {
                    $db->exec("ALTER TABLE dosen ADD COLUMN foto VARCHAR(255) DEFAULT NULL");
                }
            } catch (Throwable $e) {

            }
        }

        return $db;
    }

    public function isNidnExists(string $nidn): bool
    {
        $stmt = $this->pdo()->prepare("SELECT 1 FROM dosen WHERE nidn = :nidn LIMIT 1");
        $stmt->execute([':nidn' => trim($nidn)]);
        return (bool) $stmt->fetchColumn();
    }

    public function isNidnExistsForOtherUser(string $nidn, int $excludeUserId): bool
    {
        $stmt = $this->pdo()->prepare("SELECT 1 FROM dosen WHERE nidn = :nidn AND id_user != :id_user LIMIT 1");
        $stmt->execute([
            ':nidn'    => trim($nidn),
            ':id_user' => $excludeUserId,
        ]);
        return (bool) $stmt->fetchColumn();
    }

    public function registerDosen(array $userData, array $dosenData): int
    {
        $db = $this->pdo();
        try {
            $db->beginTransaction();

            $sqlUser = "INSERT INTO users (username, password, role, no_hp, email)
                        VALUES (:username, :password, 'dosen', :no_hp, :email)";
            $stmtUser = $db->prepare($sqlUser);
            $stmtUser->execute([
                ':username' => trim($userData['username']),
                ':password' => str_starts_with((string) $userData['password'], '$2y$') ? $userData['password'] : password_hash((string) $userData['password'], PASSWORD_BCRYPT),
                ':no_hp'    => trim($userData['no_hp']),
                ':email'    => trim($userData['email']),
            ]);

            $idUser = (int) $db->lastInsertId();

            $sqlDosen = "INSERT INTO dosen (id_user, nama_dosen, bidang_keahlian, nidn, status, foto)
                         VALUES (:id_user, :nama_dosen, :bidang_keahlian, :nidn, :status, :foto)";
            $stmtDosen = $db->prepare($sqlDosen);
            $stmtDosen->execute([
                ':id_user'         => $idUser,
                ':nama_dosen'      => trim($dosenData['nama_dosen']),
                ':bidang_keahlian' => trim($dosenData['bidang_keahlian']),
                ':nidn'            => trim($dosenData['nidn']),
                ':status'          => $dosenData['status'] ?? 'bersedia',
                ':foto'            => $dosenData['foto'] ?? null,
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

    public function getDirectory(): array
    {
        $sql = "SELECT d.id_dosen, d.nama_dosen, d.bidang_keahlian, d.status, d.foto,
                       d.contact_visible, u.email, u.no_hp, u.username
                FROM dosen d
                JOIN users u ON u.id_user = d.id_user
                ORDER BY CASE WHEN d.status = 'bersedia' THEN 0 ELSE 1 END,
                         d.nama_dosen ASC";
        return $this->pdo()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

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

            if ($statusVal === 'bersedia' || $statusVal === 'tidak_bersedia') {
                $fields[] = 'status = :status';
                $params[':status'] = $statusVal;
            }
        }
        if (array_key_exists('contact_visible', $data)) {
            $fields[] = 'contact_visible = :contact_visible';
            $params[':contact_visible'] = !empty($data['contact_visible']) ? 1 : 0;
        }
        if (array_key_exists('foto', $data)) {
            $fields[] = 'foto = :foto';
            $params[':foto'] = $data['foto'] !== null ? trim($data['foto']) : null;
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE dosen SET " . implode(', ', $fields) . " WHERE id_user = :id_user";
        $stmt = $this->pdo()->prepare($sql);
        return $stmt->execute($params);
    }
}
