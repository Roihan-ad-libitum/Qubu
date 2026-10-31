<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';

class CompetitionModel
{
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
            $this->ensureAdditionalColumns($db);
        }
        return $db;
    }

    public function getAll(): array
    {
        $stmt = $this->pdo()->query("SELECT * FROM lomba ORDER BY id_lomba DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->pdo()->prepare("SELECT * FROM lomba WHERE id_lomba = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    public function getRelatedCompetitions(int $currentId, string $bidang, int $limit = 4, bool $fallback = true): array
    {
        $db = $this->pdo();
        $safeLimit = max(1, min((int) $limit, 10));
        $bidang = trim($bidang);

        $related = [];
        $bidangTags = parseSkillTags($bidang, false, []);
        if (!empty($bidangTags)) {
            $orClauses = [];
            $paramsRel = [':current_id' => $currentId];
            foreach ($bidangTags as $idx => $bTag) {
                $kExact = ':b_exact_' . $idx;
                $kLike  = ':b_like_' . $idx;
                $orClauses[] = "(FIND_IN_SET(LOWER($kExact), LOWER(REPLACE(bidang, ', ', ','))) > 0 OR LOWER(bidang) LIKE $kLike)";
                $paramsRel[$kExact] = strtolower($bTag);
                $paramsRel[$kLike]  = '%' . strtolower($bTag) . '%';
            }
            $stmt = $db->prepare("
                SELECT * FROM lomba
                WHERE id_lomba != :current_id
                  AND deadline_daftar >= :active_now
                  AND (" . implode(' OR ', $orClauses) . ")
                ORDER BY deadline_daftar ASC, id_lomba DESC
                LIMIT " . $safeLimit
            );
            $paramsRel[':active_now'] = date('Y-m-d H:i:s');
            $stmt->execute($paramsRel);
            $related = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        if ($fallback && count($related) < $safeLimit) {
            $remaining   = $safeLimit - count($related);
            $excludeIds  = array_merge([$currentId], array_column($related, 'id_lomba'));
            $excludeParams = [
                ':active_now' => date('Y-m-d H:i:s'),
            ];
            $placeholders = [];
            foreach ($excludeIds as $index => $excludeId) {
                $placeholder = ':exclude_' . $index;
                $placeholders[] = $placeholder;
                $excludeParams[$placeholder] = (int) $excludeId;
            }

            $stmtFallback = $db->prepare("
                SELECT * FROM lomba
                WHERE id_lomba NOT IN (" . implode(',', $placeholders) . ")
                  AND deadline_daftar >= :active_now
                ORDER BY deadline_daftar ASC, id_lomba DESC
                LIMIT " . (int) $remaining
            );
            $stmtFallback->execute($excludeParams);
            $related = array_merge($related, $stmtFallback->fetchAll(PDO::FETCH_ASSOC));
        }

        return $related;
    }

    public function getDetailWithRelated(int $id, int $relatedLimit = 4, bool $fallback = true): ?array
    {
        $competition = $this->getById($id);
        if (!$competition) {
            return null;
        }

        $competition['related'] = $this->getRelatedCompetitions(
            $id,
            $competition['bidang'] ?? '',
            $relatedLimit,
            $fallback
        );

        return $competition;
    }

    public function searchAndFilter(?string $keyword = null, ?string $bidang = null, int $limit = 50): array
    {
        $db     = $this->pdo();
        $where  = ['deadline_daftar >= :active_now'];
        $params = [':active_now' => date('Y-m-d H:i:s')];

        $kw = trim((string) $keyword);
        if ($kw !== '') {
            $where[] = "(nama_lomba LIKE :kw1
                         OR penyelenggara LIKE :kw2
                         OR bidang LIKE :kw3
                         OR deskripsi LIKE :kw4
                         OR tingkat LIKE :kw5
                         OR partisipasi LIKE :kw6)";
            $kwVal = '%' . $kw . '%';
            $params[':kw1'] = $kwVal;
            $params[':kw2'] = $kwVal;
            $params[':kw3'] = $kwVal;
            $params[':kw4'] = $kwVal;
            $params[':kw5'] = $kwVal;
            $params[':kw6'] = $kwVal;
        }

        $b = trim((string) $bidang);
        if ($b !== '' && !in_array(strtolower($b), ['semua', 'semua bidang', 'all'], true)) {
            $validBidang = null;
            foreach (BIDANG_QUBU as $item) {
                if (strcasecmp($item, $b) === 0) {
                    $validBidang = $item;
                    break;
                }
            }

            if ($validBidang !== null) {
                $where[] = "(FIND_IN_SET(LOWER(:bidang_exact), LOWER(REPLACE(bidang, ', ', ','))) > 0 OR LOWER(bidang) LIKE :bidang_like)";
                $params[':bidang_exact'] = strtolower($validBidang);
                $params[':bidang_like']  = '%' . strtolower($validBidang) . '%';
            } else {

                $where[] = "1 = 0";
            }
        }

        $safeLimit = max(1, min((int) $limit, 100));

        $sql = "SELECT * FROM lomba";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " ORDER BY id_lomba DESC LIMIT " . $safeLimit;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): int
    {
        $db = $this->pdo();

        $sql = "INSERT INTO lomba (
                    nama_lomba, penyelenggara, deskripsi, deadline_daftar,
                    tanggal_pelaksanaan, tingkat, biaya, partisipasi,
                    bidang, link_resmi, poster, link_guidebook
                ) VALUES (
                    :nama_lomba, :penyelenggara, :deskripsi, :deadline_daftar,
                    :tanggal_pelaksanaan, :tingkat, :biaya, :partisipasi,
                    :bidang, :link_resmi, :poster, :link_guidebook
                )";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':nama_lomba'           => trim($data['nama_lomba'] ?? ''),
            ':penyelenggara'        => trim($data['penyelenggara'] ?? ''),
            ':deskripsi'            => trim($data['deskripsi'] ?? ''),
            ':deadline_daftar'      => $data['deadline_daftar'] ?? date('Y-m-d H:i:s'),
            ':tanggal_pelaksanaan'  => $data['tanggal_pelaksanaan'] ?? date('Y-m-d'),
            ':tingkat'              => trim($data['tingkat'] ?? 'Nasional'),
            ':biaya'                => (float) ($data['biaya'] ?? 0),
            ':partisipasi'          => $data['partisipasi'] ?? 'Individu/Tim',
            ':bidang'               => trim($data['bidang'] ?? 'Umum'),
            ':link_resmi'           => trim($data['link_resmi'] ?? ''),
            ':poster'               => $data['poster'] ?? null,
            ':link_guidebook'       => $data['link_guidebook'] ?? null,
        ]);

        return (int) $db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $fields = [
            'nama_lomba = :nama_lomba',
            'penyelenggara = :penyelenggara',
            'deskripsi = :deskripsi',
            'deadline_daftar = :deadline_daftar',
            'tanggal_pelaksanaan = :tanggal_pelaksanaan',
            'tingkat = :tingkat',
            'biaya = :biaya',
            'partisipasi = :partisipasi',
            'bidang = :bidang',
            'link_resmi = :link_resmi',
            'link_guidebook = :link_guidebook',
        ];

        $params = [
            ':id'                   => $id,
            ':nama_lomba'           => trim($data['nama_lomba'] ?? ''),
            ':penyelenggara'        => trim($data['penyelenggara'] ?? ''),
            ':deskripsi'            => trim($data['deskripsi'] ?? ''),
            ':deadline_daftar'      => $data['deadline_daftar'] ?? date('Y-m-d H:i:s'),
            ':tanggal_pelaksanaan'  => $data['tanggal_pelaksanaan'] ?? date('Y-m-d'),
            ':tingkat'              => trim($data['tingkat'] ?? 'Nasional'),
            ':biaya'                => (float) ($data['biaya'] ?? 0),
            ':partisipasi'          => $data['partisipasi'] ?? 'Individu/Tim',
            ':bidang'               => trim($data['bidang'] ?? 'Umum'),
            ':link_resmi'           => trim($data['link_resmi'] ?? ''),
            ':link_guidebook'       => $data['link_guidebook'] ?? null,
        ];

        if (array_key_exists('poster', $data) && $data['poster'] !== null) {
            $fields[] = 'poster = :poster';
            $params[':poster'] = $data['poster'];
        }

        $sql = "UPDATE lomba SET " . implode(', ', $fields) . " WHERE id_lomba = :id";
        $stmt = $this->pdo()->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo()->prepare("DELETE FROM lomba WHERE id_lomba = :id");
        return $stmt->execute([':id' => $id]);
    }

    private function ensureAdditionalColumns(PDO $db): void
    {
        self::$schemaEnsured = true;
        try {
            $cols = $db->query("SHOW COLUMNS FROM lomba")->fetchAll(PDO::FETCH_COLUMN);

            $db->exec("ALTER TABLE lomba MODIFY COLUMN bidang VARCHAR(150) NOT NULL");
            if (!in_array('poster', $cols)) {
                $db->exec("ALTER TABLE lomba ADD COLUMN poster VARCHAR(255) NULL");
            }
            if (!in_array('link_guidebook', $cols)) {
                $db->exec("ALTER TABLE lomba ADD COLUMN link_guidebook VARCHAR(255) NULL");
            }

            $indexes    = $db->query("SHOW INDEX FROM lomba")->fetchAll(PDO::FETCH_ASSOC);
            $indexNames = array_column($indexes, 'Key_name');

            if (!in_array('idx_lomba_bidang', $indexNames)) {
                $db->exec("ALTER TABLE lomba ADD INDEX idx_lomba_bidang (bidang)");
            }
            if (!in_array('idx_lomba_deadline', $indexNames)) {
                $db->exec("ALTER TABLE lomba ADD INDEX idx_lomba_deadline (deadline_daftar)");
            }
        } catch (Throwable $e) {

        }
    }
}
