<?php
/**
 * Model Pengelolaan Data Kompetisi / Lomba (Tabel `lomba` di database qubu-project)
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';

class CompetitionModel
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
     * Ambil semua data lomba
     */
    public function getAll(): array
    {
        $stmt = $this->pdo()->query("SELECT * FROM lomba ORDER BY id_lomba DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ambil data lomba berdasarkan ID
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->pdo()->prepare("SELECT * FROM lomba WHERE id_lomba = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    /**
     * Ambil rekomendasi kompetisi terkait (Related Competitions) pada bidang yang sama
     *
     * @param int    $currentId ID kompetisi yang sedang aktif (dikecualikan dari hasil)
     * @param string $bidang    Bidang kompetisi
     * @param int    $limit     Maksimal jumlah rekomendasi (default 4, maks 10)
     * @param bool   $fallback  Jika true, tambahkan kompetisi lain jika lomba sebidang kurang dari target limit
     * @return array Daftar kompetisi terkait
     */
    public function getRelatedCompetitions(int $currentId, string $bidang, int $limit = 4, bool $fallback = true): array
    {
        $db = $this->pdo();
        $safeLimit = max(1, min((int) $limit, 10));
        $bidang = trim($bidang);

        $related = [];
        if ($bidang !== '') {
            $stmt = $db->prepare("
                SELECT * FROM lomba 
                WHERE id_lomba != :current_id 
                  AND (bidang = :bidang OR LOWER(bidang) = LOWER(:bidang_lower))
                ORDER BY deadline_daftar ASC, id_lomba DESC 
                LIMIT " . $safeLimit
            );
            $stmt->execute([
                ':current_id'   => $currentId,
                ':bidang'       => $bidang,
                ':bidang_lower' => strtolower($bidang),
            ]);
            $related = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Fallback jika lomba dengan bidang sama kurang dari target limit
        if ($fallback && count($related) < $safeLimit) {
            $remaining = $safeLimit - count($related);
            $excludeIds = array_merge([$currentId], array_column($related, 'id_lomba'));
            $placeholders = implode(',', array_fill(0, count($excludeIds), '?'));

            $stmtFallback = $db->prepare("
                SELECT * FROM lomba 
                WHERE id_lomba NOT IN ($placeholders)
                ORDER BY deadline_daftar ASC, id_lomba DESC 
                LIMIT " . (int) $remaining
            );
            $stmtFallback->execute($excludeIds);
            $fallbackLomba = $stmtFallback->fetchAll(PDO::FETCH_ASSOC);

            $related = array_merge($related, $fallbackLomba);
        }

        return $related;
    }

    /**
     * Ambil data detail lomba lengkap beserta kompetisi terkait (Related Competitions)
     * Mengambil 1 kompetisi spesifik berdasarkan id_lomba, dan 3-4 kompetisi lain pada bidang yang sama.
     *
     * @param int  $id           ID Lomba yang ingin dilihat detailnya
     * @param int  $relatedLimit Jumlah maksimal rekomendasi lomba terkait (default 4)
     * @param bool $fallback     Apakah menyertakan kompetisi lain jika lomba sebidang kurang dari limit
     * @return array|null Mengembalikan data lomba lengkap dengan key 'related', atau null jika id tidak ditemukan
     */
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

    /**
     * Pencarian dan Filter Data Kompetisi / Lomba secara Terpadu
     * Mendukung pencarian keyword pada berbagai kolom (nama_lomba, penyelenggara, deskripsi, tingkat, partisipasi)
     * dan filter bidang secara ketat berdasarkan whitelist BIDANG_QUBU.
     *
     * @param string|null $keyword Kata kunci pencarian (opsional)
     * @param string|null $bidang  Filter bidang spesifik sesuai BIDANG_QUBU (opsional)
     * @param int         $limit   Batas maksimal jumlah record (default 50, maks 100)
     * @return array Daftar kompetisi hasil query
     */
    public function searchAndFilter(?string $keyword = null, ?string $bidang = null, int $limit = 50): array
    {
        $db = $this->pdo();
        $where = [];
        $params = [];

        // 1. Filter Keyword dinamis menggunakan parameterized query
        $kw = trim((string) $keyword);
        if ($kw !== '') {
            $where[] = "(nama_lomba LIKE :kw1 
                         OR penyelenggara LIKE :kw2 
                         OR deskripsi LIKE :kw3 
                         OR tingkat LIKE :kw4 
                         OR partisipasi LIKE :kw5)";
            $kwVal = '%' . $kw . '%';
            $params[':kw1'] = $kwVal;
            $params[':kw2'] = $kwVal;
            $params[':kw3'] = $kwVal;
            $params[':kw4'] = $kwVal;
            $params[':kw5'] = $kwVal;
        }

        // 2. Filter Bidang secara ketat dengan whitelist BIDANG_QUBU
        $b = trim((string) $bidang);
        if ($b !== '' && !in_array(strtolower($b), ['semua', 'semua bidang', 'all'], true)) {
            $allowedBidang = defined('BIDANG_QUBU') 
                ? BIDANG_QUBU 
                : ['Frontend', 'Backend', 'UI/UX Design', 'Data Science', 'Robotic'];

            $validBidang = null;
            foreach ($allowedBidang as $item) {
                if (strcasecmp($item, $b) === 0) {
                    $validBidang = $item;
                    break;
                }
            }

            if ($validBidang !== null) {
                $where[] = "bidang = :bidang";
                $params[':bidang'] = $validBidang;
            } else {
                // Bidang di luar whitelist tidak valid, cegah kebocoran data
                $where[] = "1 = 0";
            }
        }

        // 3. Batasi limit data dengan rentang aman (1 - 100)
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

    /**
     * Buat lomba baru
     */
    public function create(array $data): int
    {
        $db = $this->pdo();

        // Pastikan kolom poster dan link_guidebook tersedia
        $this->ensureAdditionalColumns();

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

    /**
     * Perbarui data lomba berdasarkan ID
     */
    public function update(int $id, array $data): bool
    {
        $this->ensureAdditionalColumns();

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

    /**
     * Hapus lomba berdasarkan ID
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo()->prepare("DELETE FROM lomba WHERE id_lomba = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Memastikan kolom poster dan link_guidebook sudah dibuat di tabel lomba
     */
    private function ensureAdditionalColumns(): void
    {
        try {
            $db = $this->pdo();
            $cols = $db->query("SHOW COLUMNS FROM lomba")->fetchAll(PDO::FETCH_COLUMN);

            if (!in_array('poster', $cols)) {
                $db->exec("ALTER TABLE lomba ADD COLUMN poster VARCHAR(255) NULL");
            }
            if (!in_array('link_guidebook', $cols)) {
                $db->exec("ALTER TABLE lomba ADD COLUMN link_guidebook VARCHAR(255) NULL");
            }
        } catch (Throwable $e) {
            // Abaikan jika alter gagal atau sudah ada
        }
    }
}
