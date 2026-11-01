<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../core/Validator.php';

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

    public function isNpmExists(string $npm): bool
    {
        $stmt = $this->pdo()->prepare("SELECT 1 FROM mahasiswa WHERE npm = :npm LIMIT 1");
        $stmt->execute([':npm' => trim($npm)]);
        return (bool) $stmt->fetchColumn();
    }

    public function isNpmExistsForOtherUser(string $npm, int $excludeUserId): bool
    {
        $stmt = $this->pdo()->prepare("SELECT 1 FROM mahasiswa WHERE npm = :npm AND id_user != :id_user LIMIT 1");
        $stmt->execute([
            ':npm'     => trim($npm),
            ':id_user' => $excludeUserId,
        ]);
        return (bool) $stmt->fetchColumn();
    }

    public function registerMahasiswa(array $userData, array $mahasiswaData): int
    {
        $db = $this->pdo();
        try {
            $db->beginTransaction();

            $sqlUser = "INSERT INTO users (username, password, role, no_hp, email)
                        VALUES (:username, :password, 'peserta', :no_hp, :email)";
            $stmtUser = $db->prepare($sqlUser);
            $stmtUser->execute([
                ':username' => trim($userData['username']),
                ':password' => str_starts_with((string) $userData['password'], '$2y$') ? $userData['password'] : password_hash((string) $userData['password'], PASSWORD_BCRYPT),
                ':no_hp'    => trim($userData['no_hp']),
                ':email'    => trim($userData['email']),
            ]);

            $idUser = (int) $db->lastInsertId();

            $githubClean = isset($mahasiswaData['github']) && trim((string) $mahasiswaData['github']) !== ''
                ? (normalizeGithubUrl((string) $mahasiswaData['github']) ?: null)
                : null;
            $sqlMhs = "INSERT INTO mahasiswa (id_user, npm, nama_mahasiswa, program_studi, angkatan, bidang_minat, bio, foto, github)
                       VALUES (:id_user, :npm, :nama_mahasiswa, :program_studi, :angkatan, :bidang_minat, :bio, :foto, :github)";
            $stmtMhs = $db->prepare($sqlMhs);
            $stmtMhs->execute([
                ':id_user'        => $idUser,
                ':npm'            => trim($mahasiswaData['npm']),
                ':nama_mahasiswa' => trim($mahasiswaData['nama_mahasiswa']),
                ':program_studi'  => trim($mahasiswaData['program_studi']),
                ':angkatan'       => trim($mahasiswaData['angkatan']),
                ':bidang_minat'   => trim($mahasiswaData['bidang_minat'] ?? 'Frontend'),
                ':bio'            => trim($mahasiswaData['bio'] ?? ''),
                ':foto'           => isset($mahasiswaData['foto']) ? trim($mahasiswaData['foto']) : null,
                ':github'         => $githubClean,
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

    public function updateProfileByUserId(int $idUser, array $data): bool
    {
        $fields = [];
        $params = [':id_user' => $idUser];

        $fieldMap = [
            'npm'            => ':npm',
            'nama_mahasiswa' => ':nama_mahasiswa',
            'program_studi'  => ':program_studi',
            'angkatan'       => ':angkatan',
            'bidang_minat'   => ':bidang_minat',
            'bio'            => ':bio',
            'github'         => ':github',
        ];

        foreach ($fieldMap as $col => $placeholder) {
            if (isset($data[$col])) {
                $fields[] = "$col = $placeholder";
                $params[$placeholder] = trim($data[$col]);
            }
        }

        if (array_key_exists('foto', $data)) {
            $fields[] = 'foto = :foto';
            $params[':foto'] = $data['foto'] !== null ? trim($data['foto']) : null;
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE mahasiswa SET " . implode(', ', $fields) . " WHERE id_user = :id_user";
        $stmt = $this->pdo()->prepare($sql);
        return $stmt->execute($params);
    }

    public function searchPartners(
        int $excludeUserId,
        ?string $bidang = null,
        ?string $prodi = null,
        ?string $keyword = null,
        int $limit = 50
    ): array {
        $safeLimit       = max(1, min(100, $limit));
        $validatedBidang = Validator::validateBidang($bidang);
        $validBidang     = ($validatedBidang !== null && $validatedBidang !== 'Semua Bidang') ? $validatedBidang : null;
        $validatedProdi  = Validator::validateProdi($prodi);
        $validProdi      = ($validatedProdi !== null && $validatedProdi !== 'Semua Prodi') ? $validatedProdi : null;
        $cleanKw         = Validator::cleanKeyword($keyword ?? '', 100);

        $whereClauses = ['m.id_user != :exclude_user_id'];
        $params = [':exclude_user_id' => $excludeUserId];

        if ($validBidang !== null) {
            $whereClauses[] = 'LOWER(m.bidang_minat) LIKE LOWER(:bidang_like)';
            $params[':bidang_like'] = '%' . $validBidang . '%';
        }

        if ($validProdi !== null) {
            $whereClauses[] = 'LOWER(m.program_studi) = LOWER(:prodi)';
            $params[':prodi'] = $validProdi;
        }

        if ($cleanKw !== '') {
            $kwLike = '%' . $cleanKw . '%';
            $whereClauses[] = '(
                LOWER(m.nama_mahasiswa) LIKE LOWER(:kw1)
                OR LOWER(m.bidang_minat) LIKE LOWER(:kw2)
                OR LOWER(m.program_studi) LIKE LOWER(:kw3)
                OR LOWER(m.bio) LIKE LOWER(:kw4)
                OR LOWER(u.username) LIKE LOWER(:kw5)
            )';
            $params[':kw1'] = $kwLike;
            $params[':kw2'] = $kwLike;
            $params[':kw3'] = $kwLike;
            $params[':kw4'] = $kwLike;
            $params[':kw5'] = $kwLike;
        }

        $whereSql = implode(' AND ', $whereClauses);

        $sql = "SELECT
                    m.id_mahasiswa,
                    m.id_user,
                    m.npm,
                    m.nama_mahasiswa,
                    m.program_studi,
                    m.angkatan,
                    m.bidang_minat,
                    m.bio,
                    m.foto,
                    m.github,
                    u.username,
                    u.email,
                    u.no_hp
                FROM mahasiswa m
                JOIN users u ON u.id_user = m.id_user
                WHERE {$whereSql}
                ORDER BY m.nama_mahasiswa ASC, m.id_mahasiswa DESC
                LIMIT {$safeLimit}";

        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findPublicProfileById(int $idMahasiswa): ?array
    {
        if ($idMahasiswa <= 0) {
            return null;
        }

        $sql = "SELECT
                    m.id_mahasiswa,
                    m.id_user,
                    m.npm,
                    m.nama_mahasiswa,
                    m.program_studi,
                    m.angkatan,
                    m.bidang_minat,
                    m.bio,
                    m.foto,
                    m.github,
                    u.username,
                    u.email,
                    u.no_hp
                FROM mahasiswa m
                JOIN users u ON u.id_user = m.id_user
                WHERE m.id_mahasiswa = :id_mahasiswa
                LIMIT 1";

        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([':id_mahasiswa' => $idMahasiswa]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        $row['bidang_tags'] = $this->parseBidangTags($row['bidang_minat'] ?? '');

        return $row;
    }

    public function parseBidangTags(?string $bidangMinat): array
    {
        return parseSkillTags($bidangMinat, false, ['Frontend']);
    }
}
