<?php

require_once __DIR__ . '/../../config/database.php';

class TeamModel
{
    public const STATUS_OPEN          = 'open';
    public const STATUS_TEAM_COMPLETE = 'team_complete';
    public const STATUS_ARCHIVED      = 'archived';
    public const ALLOWED_STATUSES     = [
        self::STATUS_OPEN,
        self::STATUS_TEAM_COMPLETE,
        self::STATUS_ARCHIVED,
    ];

    private ?PDO $pdo = null;
    private static bool $schemaEnsured = false;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
    }

    private function pdo(): PDO
    {
        $db = $this->pdo ??= db();
        if (!self::$schemaEnsured) {
            self::$schemaEnsured = true;
            try {
                $db->exec("
                    CREATE TABLE IF NOT EXISTS team_posts (
                        id_post INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                        id_user INT NOT NULL,
                        id_lomba INT NOT NULL,
                        bidang_dibutuhkan VARCHAR(150) NOT NULL,
                        jumlah_anggota TINYINT UNSIGNED NOT NULL,
                        deskripsi TEXT NOT NULL,
                        contact_opt_in TINYINT(1) NOT NULL DEFAULT 0,
                        contact_email VARCHAR(100) DEFAULT NULL,
                        contact_whatsapp VARCHAR(30) DEFAULT NULL,
                        status ENUM('open', 'team_complete', 'archived') NOT NULL DEFAULT 'open',
                        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        INDEX idx_team_posts_competition_status (id_lomba, status, created_at),
                        INDEX idx_team_posts_owner (id_user, status)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                $columns = $db->query("SHOW COLUMNS FROM team_posts")->fetchAll(PDO::FETCH_ASSOC);
                $colMap = [];
                foreach ($columns as $col) {
                    $colMap[$col['Field']] = strtolower((string) ($col['Type'] ?? ''));
                }
                if (!isset($colMap['contact_email'])) {
                    $db->exec("ALTER TABLE team_posts ADD COLUMN contact_email VARCHAR(100) DEFAULT NULL");
                }
                if (!isset($colMap['contact_whatsapp'])) {
                    $db->exec("ALTER TABLE team_posts ADD COLUMN contact_whatsapp VARCHAR(30) DEFAULT NULL");
                }
                if (isset($colMap['status']) && strpos($colMap['status'], 'archived') === false) {
                    $db->exec("UPDATE team_posts SET status = 'team_complete' WHERE status = 'closed'");
                    $db->exec("ALTER TABLE team_posts MODIFY COLUMN status ENUM('open', 'team_complete', 'archived') NOT NULL DEFAULT 'open'");
                }
            } catch (Throwable $e) {

            }
        }

        return $db;
    }

    public function create(int $ownerId, array $data): int
    {
        $stmt = $this->pdo()->prepare("
            INSERT INTO team_posts (
                id_user, id_lomba, bidang_dibutuhkan, jumlah_anggota, deskripsi,
                contact_opt_in, contact_email, contact_whatsapp, status
            ) VALUES (
                :id_user, :id_lomba, :bidang_dibutuhkan, :jumlah_anggota, :deskripsi,
                :contact_opt_in, :contact_email, :contact_whatsapp, 'open'
            )
        ");
        $stmt->execute([
            ':id_user'           => $ownerId,
            ':id_lomba'          => (int) $data['id_lomba'],
            ':bidang_dibutuhkan' => trim((string) $data['bidang_dibutuhkan']),
            ':jumlah_anggota'    => (int) $data['jumlah_anggota'],
            ':deskripsi'         => trim((string) $data['deskripsi']),
            ':contact_opt_in'    => !empty($data['contact_opt_in']) ? 1 : 0,
            ':contact_email'     => !empty($data['contact_opt_in']) ? ($data['contact_email'] ?? null) : null,
            ':contact_whatsapp'  => !empty($data['contact_opt_in']) ? ($data['contact_whatsapp'] ?? null) : null,
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function getOpenPosts(?int $competitionId, string $seekerSkills, ?string $bidangFilter = null): array
    {
        $skills = $this->normalizeSkills($seekerSkills);
        $scoreTerms = [];
        $params = [];
        foreach ($skills as $index => $skill) {
            $placeholder = ':match_skill_' . $index;
            $scoreTerms[] = "(FIND_IN_SET($placeholder, LOWER(REPLACE(p.bidang_dibutuhkan, ', ', ','))) > 0)";
            $params[$placeholder] = $skill;
        }
        $scoreExpression = $scoreTerms ? '(' . implode(' + ', $scoreTerms) . ')' : '0';

        $whereClauses = ["p.status = 'open'", 'l.deadline_daftar >= :active_now'];
        $params[':active_now'] = date('Y-m-d H:i:s');
        if ($competitionId !== null && $competitionId > 0) {
            $whereClauses[] = 'p.id_lomba = :id_lomba';
            $params[':id_lomba'] = $competitionId;
        }
        if ($bidangFilter !== null && $bidangFilter !== '' && strcasecmp($bidangFilter, 'Semua Bidang') !== 0) {
            $whereClauses[] = 'LOWER(p.bidang_dibutuhkan) LIKE LOWER(:bidang_filter)';
            $params[':bidang_filter'] = '%' . trim($bidangFilter) . '%';
        }

        $whereSql = implode(' AND ', $whereClauses);
        $stmt = $this->pdo()->prepare("
            SELECT p.*,
                   l.nama_lomba, l.bidang AS bidang_lomba, l.tingkat AS tingkat_lomba, l.deadline_daftar,
                   $scoreExpression AS match_score,
                   m.id_mahasiswa, m.npm, m.nama_mahasiswa, m.program_studi, m.angkatan,
                   m.bidang_minat, m.bio, m.foto, m.github,
                   u.username, u.email AS account_email, u.no_hp AS account_phone
            FROM team_posts p
            JOIN lomba l ON l.id_lomba = p.id_lomba
            JOIN users u ON u.id_user = p.id_user
            LEFT JOIN mahasiswa m ON m.id_user = p.id_user
            WHERE {$whereSql}
            ORDER BY match_score DESC, p.created_at DESC
            LIMIT 100
        ");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOpenByCompetition(int $competitionId, string $seekerSkills): array
    {
        return $this->getOpenPosts($competitionId, $seekerSkills);
    }

    public function getOpenPostsByUserId(int $ownerUserId): array
    {
        $stmt = $this->pdo()->prepare("
            SELECT p.*, l.nama_lomba, l.bidang AS bidang_lomba, l.tingkat AS tingkat_lomba
            FROM team_posts p
            JOIN lomba l ON l.id_lomba = p.id_lomba
            WHERE p.id_user = :id_user
              AND p.status = 'open'
              AND l.deadline_daftar >= :active_now
            ORDER BY p.created_at DESC
        ");
        $stmt->execute([
            ':id_user' => $ownerUserId,
            ':active_now' => date('Y-m-d H:i:s'),
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOwnerPosts(int $ownerId): array
    {
        $stmt = $this->pdo()->prepare("
            SELECT p.*, l.nama_lomba, l.bidang AS bidang_lomba, l.deadline_daftar
            FROM team_posts p
            JOIN lomba l ON l.id_lomba = p.id_lomba
            WHERE p.id_user = :id_user
            ORDER BY
                CASE p.status
                    WHEN 'open' THEN 0
                    WHEN 'team_complete' THEN 1
                    ELSE 2
                END,
                p.created_at DESC
        ");
        $stmt->execute([':id_user' => $ownerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus(int $postId, int $ownerId, string $status): bool
    {
        if (!in_array($status, self::ALLOWED_STATUSES, true)) {
            return false;
        }

        $stmt = $this->pdo()->prepare("
            UPDATE team_posts p
            JOIN lomba l ON l.id_lomba = p.id_lomba
            SET p.status = :status
            WHERE p.id_post = :id_post
              AND p.id_user = :id_user
              AND (:status_to_open != :open_status OR l.deadline_daftar >= :active_now)
        ");
        $stmt->execute([
            ':status'  => $status,
            ':id_post' => $postId,
            ':id_user' => $ownerId,
            ':status_to_open' => $status,
            ':open_status' => self::STATUS_OPEN,
            ':active_now' => date('Y-m-d H:i:s'),
        ]);
        return $stmt->rowCount() > 0;
    }

    private function normalizeSkills(string $value): array
    {
        return array_values(array_unique(array_filter(array_map(
            static fn(string $skill): string => strtolower(trim($skill)),
            explode(',', $value)
        ))));
    }
}
