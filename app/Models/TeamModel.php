<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../core/Validator.php';

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

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
    }

    private function pdo(): PDO
    {
        return $this->pdo ??= db();
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
        $params[':active_now'] = activeDeadlineThreshold();
        if ($competitionId !== null && $competitionId > 0) {
            $whereClauses[] = 'p.id_lomba = :id_lomba';
            $params[':id_lomba'] = $competitionId;
        }
        if ($bidangFilter !== null && $bidangFilter !== '' && strcasecmp($bidangFilter, 'Semua Bidang') !== 0) {
            $validBidang = Validator::validateOfficialBidang($bidangFilter) ?? trim($bidangFilter);
            $whereClauses[] = "(FIND_IN_SET(LOWER(:bidang_exact), LOWER(REPLACE(p.bidang_dibutuhkan, ', ', ','))) > 0 OR LOWER(p.bidang_dibutuhkan) LIKE LOWER(:bidang_filter))";
            $params[':bidang_exact']  = strtolower($validBidang);
            $params[':bidang_filter'] = '%' . $validBidang . '%';
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
            ':id_user'    => $ownerUserId,
            ':active_now' => activeDeadlineThreshold(),
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
            ':status'          => $status,
            ':id_post'         => $postId,
            ':id_user'         => $ownerId,
            ':status_to_open'  => $status,
            ':open_status'     => self::STATUS_OPEN,
            ':active_now'      => activeDeadlineThreshold(),
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
