<?php
/**
 * Model Pengelolaan Data Kompetisi / Lomba (Tabel `lomba` di database qubu-project)
 */

require_once __DIR__ . '/../../config/database.php';

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
