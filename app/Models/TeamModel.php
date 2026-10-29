<?php
/**
 * Model Data Tim & Pengajuan Bimbingan Dosen (tabel `tim`, `anggota_tim`, `pengajuan_bimbingan`)
 * DP-1.1: Skema prasyarat tanpa pembedaan peran ketua/anggota (semua anggota setara)
 *         dan tanpa kolom alasan penolakan pada `pengajuan_bimbingan`.
 */

require_once __DIR__ . '/../../config/database.php';

class TeamModel
{
    private ?PDO $pdo = null;
    private static bool $tablesVerified = false;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
        $this->ensureTeamAndBimbinganTables();
    }

    private function pdo(): PDO
    {
        return $this->pdo ??= db();
    }

    /**
     * DP-1.1: Buat tabel prasyarat `tim`, `anggota_tim` (tanpa peran ketua/anggota),
     * dan `pengajuan_bimbingan` (tanpa alasan penolakan) secara idempoten,
     * serta isi seed data tim awal jika tabel masih kosong.
     */
    public function ensureTeamAndBimbinganTables(): void
    {
        if (self::$tablesVerified) {
            return;
        }

        try {
            $db = $this->pdo();

            // 1. Tabel `tim`
            $db->exec("
                CREATE TABLE IF NOT EXISTS `tim` (
                    `id_tim` int(11) NOT NULL AUTO_INCREMENT,
                    `nama_tim` varchar(100) NOT NULL,
                    `id_lomba` int(11) NOT NULL,
                    `id_dosen` int(11) DEFAULT NULL,
                    `status_bimbingan` enum('belum_mengajukan','menunggu','disetujui','ditolak') NOT NULL DEFAULT 'belum_mengajukan',
                    `created_at` datetime NOT NULL DEFAULT current_timestamp(),
                    PRIMARY KEY (`id_tim`),
                    KEY `idx_tim_lomba` (`id_lomba`),
                    KEY `idx_tim_dosen` (`id_dosen`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
            ");

            // 2. Tabel `anggota_tim` (tanpa kolom peran ketua/anggota karena semua anggota setara)
            $db->exec("
                CREATE TABLE IF NOT EXISTS `anggota_tim` (
                    `id_anggota` int(11) NOT NULL AUTO_INCREMENT,
                    `id_tim` int(11) NOT NULL,
                    `id_mahasiswa` int(11) NOT NULL,
                    PRIMARY KEY (`id_anggota`),
                    UNIQUE KEY `uniq_tim_mahasiswa` (`id_tim`, `id_mahasiswa`),
                    KEY `idx_anggota_mahasiswa` (`id_mahasiswa`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
            ");

            // 3. Tabel `pengajuan_bimbingan` (tanpa kolom alasan penolakan)
            $db->exec("
                CREATE TABLE IF NOT EXISTS `pengajuan_bimbingan` (
                    `id_pengajuan` int(11) NOT NULL AUTO_INCREMENT,
                    `id_tim` int(11) NOT NULL,
                    `id_lomba` int(11) NOT NULL,
                    `id_dosen` int(11) NOT NULL,
                    `id_pengaju` int(11) NOT NULL,
                    `status` enum('menunggu','disetujui','ditolak') NOT NULL DEFAULT 'menunggu',
                    `created_at` datetime NOT NULL DEFAULT current_timestamp(),
                    PRIMARY KEY (`id_pengajuan`),
                    KEY `idx_pengajuan_tim` (`id_tim`),
                    KEY `idx_pengajuan_lomba` (`id_lomba`),
                    KEY `idx_pengajuan_dosen` (`id_dosen`),
                    KEY `idx_pengajuan_pengaju` (`id_pengaju`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
            ");

            // 4. Seed data tim contoh jika tabel `tim` masih kosong
            $countTim = (int) $db->query("SELECT COUNT(*) FROM tim")->fetchColumn();
            if ($countTim === 0) {
                $db->exec("
                    INSERT INTO `tim` (`id_tim`, `nama_tim`, `id_lomba`, `id_dosen`, `status_bimbingan`) VALUES
                    (1, 'Tim Garuda Frontend', 1, NULL, 'belum_mengajukan'),
                    (2, 'Tim Nusantara UI/UX', 3, NULL, 'belum_mengajukan');
                ");

                $db->exec("
                    INSERT IGNORE INTO `anggota_tim` (`id_tim`, `id_mahasiswa`) VALUES
                    (1, 1),
                    (1, 2),
                    (2, 1),
                    (2, 6);
                ");
            }

            self::$tablesVerified = true;
        } catch (Throwable $e) {
            // Jangan hentikan aplikasi jika koneksi DB belum siap
        }
    }
}

