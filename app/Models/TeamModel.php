<?php
/**
 * Model Data Tim & Pengajuan Bimbingan Dosen (tabel `tim`, `anggota_tim`, `pengajuan_bimbingan`)
 * Mencakup:
 * - DP-1.1: Skema prasyarat tanpa pembedaan peran ketua/anggota & tanpa alasan penolakan
 * - DP-1.3: Query tim mahasiswa, cek keanggotaan tim, pembuatan tim cepat,
 *           pengajuan bimbingan dengan aturan bisnis lengkap, daftar permintaan masuk dosen,
 *           serta aksi Setujui (ACC) dan Tolak oleh dosen pemilik.
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

    // =========================================================================
    // DP-1.3: QUERY TIM MAHASISWA, PENGAJUAN BIMBINGAN & KEPUTUSAN ACC DOSEN
    // =========================================================================

    /**
     * Cek apakah seorang mahasiswa merupakan anggota sah dari suatu tim
     */
    public function isMemberOfTeam(int $idTim, int $idMahasiswa): bool
    {
        if ($idTim <= 0 || $idMahasiswa <= 0) {
            return false;
        }

        $sql = "SELECT 1 FROM anggota_tim WHERE id_tim = :id_tim AND id_mahasiswa = :id_mahasiswa LIMIT 1";
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([
            ':id_tim'       => $idTim,
            ':id_mahasiswa' => $idMahasiswa,
        ]);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * Ambil daftar seluruh anggota pada suatu tim
     */
    public function getTeamMembers(int $idTim): array
    {
        if ($idTim <= 0) {
            return [];
        }

        $sql = "SELECT
                    at.id_anggota,
                    at.id_tim,
                    m.id_mahasiswa,
                    m.id_user,
                    m.npm,
                    m.nama_mahasiswa,
                    m.program_studi,
                    m.angkatan,
                    m.bidang_minat
                FROM anggota_tim at
                JOIN mahasiswa m ON m.id_mahasiswa = at.id_mahasiswa
                WHERE at.id_tim = :id_tim
                ORDER BY at.id_anggota ASC";

        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([':id_tim' => $idTim]);
        return $stmt->fetchAll();
    }

    /**
     * Ambil detail satu tim berdasarkan id_tim beserta informasi lomba, dosen pembimbing, dan daftar anggota
     */
    public function findTeamById(int $idTim): ?array
    {
        if ($idTim <= 0) {
            return null;
        }

        $sql = "SELECT
                    t.id_tim,
                    t.nama_tim,
                    t.id_lomba,
                    t.id_dosen,
                    t.status_bimbingan,
                    t.created_at,
                    l.nama_lomba,
                    l.bidang AS bidang_lomba,
                    l.penyelenggara,
                    l.deadline_daftar,
                    l.link_resmi,
                    d.nama_dosen,
                    d.nidn AS nidn_dosen,
                    d.bidang_keahlian AS bidang_dosen
                FROM tim t
                JOIN lomba l ON l.id_lomba = t.id_lomba
                LEFT JOIN dosen d ON d.id_dosen = t.id_dosen
                WHERE t.id_tim = :id_tim
                LIMIT 1";

        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([':id_tim' => $idTim]);
        $team = $stmt->fetch();

        if ($team === false) {
            return null;
        }

        $team['id_tim']            = (int) $team['id_tim'];
        $team['id_lomba']          = (int) $team['id_lomba'];
        $team['id_dosen']          = $team['id_dosen'] !== null ? (int) $team['id_dosen'] : null;
        $team['anggota_list']      = $this->getTeamMembers($idTim);
        $team['siap_daftar_lomba'] = ($team['status_bimbingan'] === 'disetujui');

        return $team;
    }

    /**
     * Ambil daftar seluruh tim yang diikuti oleh seorang mahasiswa beserta status ACC dosen pembimbing
     */
    public function getTeamsByMahasiswaId(int $idMahasiswa): array
    {
        if ($idMahasiswa <= 0) {
            return [];
        }

        $sql = "SELECT
                    t.id_tim,
                    t.nama_tim,
                    t.id_lomba,
                    t.id_dosen,
                    t.status_bimbingan,
                    t.created_at,
                    l.nama_lomba,
                    l.bidang AS bidang_lomba,
                    l.penyelenggara,
                    l.deadline_daftar,
                    l.link_resmi,
                    d.nama_dosen,
                    d.nidn AS nidn_dosen,
                    d.bidang_keahlian AS bidang_dosen
                FROM anggota_tim at
                JOIN tim t ON t.id_tim = at.id_tim
                JOIN lomba l ON l.id_lomba = t.id_lomba
                LEFT JOIN dosen d ON d.id_dosen = t.id_dosen
                WHERE at.id_mahasiswa = :id_mahasiswa
                ORDER BY t.id_tim DESC";

        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([':id_mahasiswa' => $idMahasiswa]);
        $teams = $stmt->fetchAll();

        foreach ($teams as &$team) {
            $idTim = (int) $team['id_tim'];
            $team['id_tim']            = $idTim;
            $team['id_lomba']          = (int) $team['id_lomba'];
            $team['id_dosen']          = $team['id_dosen'] !== null ? (int) $team['id_dosen'] : null;
            $team['anggota_list']      = $this->getTeamMembers($idTim);
            $team['siap_daftar_lomba'] = ($team['status_bimbingan'] === 'disetujui');

            // Ambil pengajuan bimbingan terakhir tim ini (agar jika ditolak/menunggu, nama dosen terakhir tetap diketahui)
            $lastStmt = $this->pdo()->prepare("
                SELECT pb.id_pengajuan, pb.id_dosen, pb.status, pb.created_at, d.nama_dosen
                FROM pengajuan_bimbingan pb
                JOIN dosen d ON d.id_dosen = pb.id_dosen
                WHERE pb.id_tim = :id_tim
                ORDER BY pb.id_pengajuan DESC
                LIMIT 1
            ");
            $lastStmt->execute([':id_tim' => $idTim]);
            $lastPengajuan = $lastStmt->fetch();
            $team['pengajuan_terakhir'] = $lastPengajuan !== false ? $lastPengajuan : null;
        }
        unset($team);

        return $teams;
    }

    /**
     * Ambil daftar tim milik mahasiswa pada suatu lomba spesifik (untuk pengecekan aturan ACC Wajib di Detail Lomba)
     */
    public function getTeamsByMahasiswaAndLomba(int $idMahasiswa, int $idLomba): array
    {
        $allTeams = $this->getTeamsByMahasiswaId($idMahasiswa);
        return array_values(array_filter($allTeams, fn(array $t) => (int) $t['id_lomba'] === $idLomba));
    }

    /**
     * Buat tim cepat untuk mahasiswa pada suatu lomba (Opsi B: saat mahasiswa belum punya tim di modal pengajuan)
     *
     * @return int ID Tim yang baru dibuat
     */
    public function createQuickTeamForMahasiswa(int $idMahasiswa, int $idLomba, string $namaTim): int
    {
        $cleanNama = trim(strip_tags($namaTim));
        if ($cleanNama === '') {
            throw new InvalidArgumentException('Nama tim tidak boleh kosong.');
        }
        if ($idMahasiswa <= 0 || $idLomba <= 0) {
            throw new InvalidArgumentException('Parameter mahasiswa atau lomba tidak valid.');
        }

        $db = $this->pdo();
        $startedTx = !$db->inTransaction();
        if ($startedTx) {
            $db->beginTransaction();
        }

        try {
            $stmtTim = $db->prepare("
                INSERT INTO tim (nama_tim, id_lomba, id_dosen, status_bimbingan)
                VALUES (:nama_tim, :id_lomba, NULL, 'belum_mengajukan')
            ");
            $stmtTim->execute([
                ':nama_tim' => substr($cleanNama, 0, 100),
                ':id_lomba' => $idLomba,
            ]);
            $newIdTim = (int) $db->lastInsertId();

            $stmtMember = $db->prepare("
                INSERT INTO anggota_tim (id_tim, id_mahasiswa)
                VALUES (:id_tim, :id_mahasiswa)
            ");
            $stmtMember->execute([
                ':id_tim'       => $newIdTim,
                ':id_mahasiswa' => $idMahasiswa,
            ]);

            if ($startedTx) {
                $db->commit();
            }

            return $newIdTim;
        } catch (Throwable $e) {
            if ($startedTx && $db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Ajukan permintaan bimbingan ke dosen untuk tim dan lomba tertentu.
     * Menegakkan seluruh aturan bisnis & edge cases:
     * 1. Pengaju wajib merupakan anggota tim (`isMemberOfTeam`).
     * 2. Dosen wajib ada dan berstatus `bersedia`.
     * 3. Tim yang sudah memiliki dosen pembimbing `disetujui` tidak boleh mengajukan lagi.
     * 4. Cegah pengajuan ganda ke dosen yang sama atau saat masih ada pengajuan berstatus `menunggu`.
     * 5. Jika pengajuan sebelumnya `ditolak`, tim diizinkan mengajukan kembali ke dosen lain.
     */
    public function createPengajuanBimbingan(int $idTim, int $idLomba, int $idDosen, int $idPengaju): array
    {
        if ($idTim <= 0 || $idDosen <= 0 || $idPengaju <= 0) {
            return [
                'success' => false,
                'code'    => 'INVALID_INPUT',
                'message' => 'Parameter tim, dosen, atau mahasiswa pengaju tidak valid.',
            ];
        }

        $db = $this->pdo();

        // 1. Cek keberadaan tim
        $stmtTeam = $db->prepare("SELECT id_tim, nama_tim, id_lomba, id_dosen, status_bimbingan FROM tim WHERE id_tim = :id_tim LIMIT 1");
        $stmtTeam->execute([':id_tim' => $idTim]);
        $team = $stmtTeam->fetch();
        if ($team === false) {
            return [
                'success' => false,
                'code'    => 'TEAM_NOT_FOUND',
                'message' => 'Data tim tidak ditemukan.',
            ];
        }

        $resolvedLombaId = $idLomba > 0 ? $idLomba : (int) $team['id_lomba'];

        // 2. Pastikan mahasiswa yang mengajukan adalah anggota tim tersebut
        if (!$this->isMemberOfTeam($idTim, $idPengaju)) {
            return [
                'success' => false,
                'code'    => 'NOT_TEAM_MEMBER',
                'message' => 'Akses ditolak. Hanya anggota tim yang dapat mengajukan dosen pembimbing untuk tim ini.',
            ];
        }

        // 3. Cek keberadaan dan ketersediaan dosen (`status = 'bersedia'`)
        $stmtDosen = $db->prepare("SELECT id_dosen, nama_dosen, status FROM dosen WHERE id_dosen = :id_dosen LIMIT 1");
        $stmtDosen->execute([':id_dosen' => $idDosen]);
        $dosen = $stmtDosen->fetch();
        if ($dosen === false) {
            return [
                'success' => false,
                'code'    => 'DOSEN_NOT_FOUND',
                'message' => 'Dosen pembimbing yang dipilih tidak ditemukan.',
            ];
        }
        if ($dosen['status'] !== 'bersedia') {
            return [
                'success' => false,
                'code'    => 'DOSEN_UNAVAILABLE',
                'message' => 'Dosen pembimbing tersebut sedang tidak bersedia menerima pengajuan bimbingan.',
            ];
        }

        // 4. Cek apakah tim sudah punya dosen yang menyetujui (ACC)
        $stmtApproved = $db->prepare("
            SELECT 1 FROM pengajuan_bimbingan
            WHERE id_tim = :id_tim AND status = 'disetujui'
            LIMIT 1
        ");
        $stmtApproved->execute([':id_tim' => $idTim]);
        if ($team['status_bimbingan'] === 'disetujui' || $stmtApproved->fetchColumn()) {
            return [
                'success' => false,
                'code'    => 'ALREADY_APPROVED',
                'message' => 'Tim Anda sudah memiliki dosen pembimbing yang menyetujui (ACC).',
            ];
        }

        // 5. Cek apakah sudah ada permintaan berstatus `menunggu` ke dosen yang sama (permintaan ganda)
        $stmtDupSame = $db->prepare("
            SELECT 1 FROM pengajuan_bimbingan
            WHERE id_tim = :id_tim AND id_dosen = :id_dosen AND status = 'menunggu'
            LIMIT 1
        ");
        $stmtDupSame->execute([
            ':id_tim'   => $idTim,
            ':id_dosen' => $idDosen,
        ]);
        if ($stmtDupSame->fetchColumn()) {
            return [
                'success' => false,
                'code'    => 'DUPLICATE_PENDING_SAME_DOSEN',
                'message' => 'Tim Anda sudah mengirim permintaan bimbingan ke dosen ini dan sedang menunggu konfirmasi.',
            ];
        }

        // 6. Cek apakah tim masih memiliki permintaan berstatus `menunggu` ke dosen lain
        $stmtPendingOther = $db->prepare("
            SELECT d.nama_dosen
            FROM pengajuan_bimbingan pb
            JOIN dosen d ON d.id_dosen = pb.id_dosen
            WHERE pb.id_tim = :id_tim AND pb.status = 'menunggu'
            LIMIT 1
        ");
        $stmtPendingOther->execute([':id_tim' => $idTim]);
        $pendingOtherDosen = $stmtPendingOther->fetchColumn();
        if ($pendingOtherDosen) {
            return [
                'success' => false,
                'code'    => 'PENDING_OTHER_DOSEN',
                'message' => 'Tim Anda masih memiliki pengajuan aktif yang menunggu konfirmasi dari ' . $pendingOtherDosen . '.',
            ];
        }

        // 7. Simpan pengajuan bimbingan baru & perbarui status_bimbingan tim menjadi 'menunggu'
        try {
            $db->beginTransaction();

            $stmtInsert = $db->prepare("
                INSERT INTO pengajuan_bimbingan (id_tim, id_lomba, id_dosen, id_pengaju, status)
                VALUES (:id_tim, :id_lomba, :id_dosen, :id_pengaju, 'menunggu')
            ");
            $stmtInsert->execute([
                ':id_tim'     => $idTim,
                ':id_lomba'   => $resolvedLombaId,
                ':id_dosen'   => $idDosen,
                ':id_pengaju' => $idPengaju,
            ]);
            $idPengajuan = (int) $db->lastInsertId();

            $stmtUpdateTim = $db->prepare("
                UPDATE tim
                SET id_dosen = :id_dosen, status_bimbingan = 'menunggu'
                WHERE id_tim = :id_tim
            ");
            $stmtUpdateTim->execute([
                ':id_dosen' => $idDosen,
                ':id_tim'   => $idTim,
            ]);

            $db->commit();

            return [
                'success'      => true,
                'code'         => 'CREATED',
                'id_pengajuan' => $idPengajuan,
                'message'      => 'Permintaan bimbingan untuk tim "' . $team['nama_tim'] . '" berhasil diajukan kepada ' . $dosen['nama_dosen'] . '.',
            ];
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            return [
                'success' => false,
                'code'    => 'DB_ERROR',
                'message' => 'Gagal menyimpan pengajuan bimbingan: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Ambil daftar permintaan bimbingan yang masuk khusus untuk seorang dosen (`id_dosen`).
     * Dosen hanya boleh melihat permintaan miliknya sendiri.
     *
     * @param int         $idDosen      ID Dosen yang sedang login
     * @param string|null $statusFilter Filter opsional ('menunggu', 'disetujui', 'ditolak', atau null untuk semua)
     * @return array
     */
    public function getPengajuanByDosenId(int $idDosen, ?string $statusFilter = null): array
    {
        if ($idDosen <= 0) {
            return [];
        }

        $allowedStatus = ['menunggu', 'disetujui', 'ditolak'];
        $cleanStatus   = strtolower(trim((string) $statusFilter));
        $hasStatus     = in_array($cleanStatus, $allowedStatus, true);

        $whereStatus = $hasStatus ? "AND pb.status = :status" : "";
        $params      = [':id_dosen' => $idDosen];
        if ($hasStatus) {
            $params[':status'] = $cleanStatus;
        }

        $sql = "SELECT
                    pb.id_pengajuan,
                    pb.id_tim,
                    pb.id_lomba,
                    pb.id_dosen,
                    pb.id_pengaju,
                    pb.status,
                    pb.created_at,
                    t.nama_tim,
                    t.status_bimbingan AS status_tim,
                    l.nama_lomba,
                    l.bidang AS bidang_lomba,
                    l.penyelenggara,
                    l.deadline_daftar,
                    m.nama_mahasiswa AS nama_pengaju,
                    m.npm AS npm_pengaju,
                    m.program_studi AS prodi_pengaju
                FROM pengajuan_bimbingan pb
                JOIN tim t ON t.id_tim = pb.id_tim
                JOIN lomba l ON l.id_lomba = pb.id_lomba
                JOIN mahasiswa m ON m.id_mahasiswa = pb.id_pengaju
                WHERE pb.id_dosen = :id_dosen
                {$whereStatus}
                ORDER BY
                    CASE WHEN pb.status = 'menunggu' THEN 0 WHEN pb.status = 'disetujui' THEN 1 ELSE 2 END ASC,
                    pb.id_pengajuan DESC";

        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            $idTim               = (int) $row['id_tim'];
            $row['id_pengajuan'] = (int) $row['id_pengajuan'];
            $row['id_tim']       = $idTim;
            $row['id_lomba']     = (int) $row['id_lomba'];
            $row['id_dosen']     = (int) $row['id_dosen'];
            $row['id_pengaju']   = (int) $row['id_pengaju'];
            $row['anggota_list'] = $this->getTeamMembers($idTim);
        }
        unset($row);

        return $rows;
    }

    /**
     * Proses keputusan dosen (`disetujui` / `ditolak`) atas suatu permintaan bimbingan.
     * Memastikan hanya dosen pemilik (`pb.id_dosen === $idDosen`) yang berhak memprosesnya.
     */
    public function respondPengajuan(int $idPengajuan, int $idDosen, string $keputusan): array
    {
        $cleanKeputusan = strtolower(trim($keputusan));
        if (!in_array($cleanKeputusan, ['disetujui', 'ditolak'], true)) {
            return [
                'success' => false,
                'code'    => 'INVALID_DECISION',
                'message' => 'Keputusan tidak valid. Gunakan "disetujui" atau "ditolak".',
            ];
        }

        if ($idPengajuan <= 0 || $idDosen <= 0) {
            return [
                'success' => false,
                'code'    => 'INVALID_INPUT',
                'message' => 'Parameter ID pengajuan atau ID dosen tidak valid.',
            ];
        }

        $db = $this->pdo();
        $stmt = $db->prepare("
            SELECT pb.*, t.nama_tim
            FROM pengajuan_bimbingan pb
            JOIN tim t ON t.id_tim = pb.id_tim
            WHERE pb.id_pengajuan = :id_pengajuan
            LIMIT 1
        ");
        $stmt->execute([':id_pengajuan' => $idPengajuan]);
        $pengajuan = $stmt->fetch();

        if ($pengajuan === false) {
            return [
                'success' => false,
                'code'    => 'NOT_FOUND',
                'message' => 'Data permintaan bimbingan tidak ditemukan.',
            ];
        }

        // Proteksi kepemilikan: Dosen A tidak boleh memproses pengajuan milik Dosen B
        if ((int) $pengajuan['id_dosen'] !== $idDosen) {
            return [
                'success' => false,
                'code'    => 'FORBIDDEN_DOSEN',
                'message' => 'Akses ditolak. Anda hanya dapat memproses permintaan bimbingan yang ditujukan kepada Anda.',
            ];
        }

        if ($pengajuan['status'] !== 'menunggu') {
            return [
                'success' => false,
                'code'    => 'ALREADY_PROCESSED',
                'message' => 'Permintaan bimbingan ini sudah diproses sebelumnya (Status: ' . ucfirst($pengajuan['status']) . ').',
            ];
        }

        $idTim = (int) $pengajuan['id_tim'];

        try {
            $db->beginTransaction();

            $stmtUpdatePb = $db->prepare("
                UPDATE pengajuan_bimbingan
                SET status = :status
                WHERE id_pengajuan = :id_pengajuan AND id_dosen = :id_dosen
            ");
            $stmtUpdatePb->execute([
                ':status'       => $cleanKeputusan,
                ':id_pengajuan' => $idPengajuan,
                ':id_dosen'     => $idDosen,
            ]);

            if ($cleanKeputusan === 'disetujui') {
                $stmtUpdateTim = $db->prepare("
                    UPDATE tim
                    SET id_dosen = :id_dosen, status_bimbingan = 'disetujui'
                    WHERE id_tim = :id_tim
                ");
                $stmtUpdateTim->execute([
                    ':id_dosen' => $idDosen,
                    ':id_tim'   => $idTim,
                ]);
            } else {
                // Jika ditolak, set status_bimbingan = 'ditolak' dan kosongkan id_dosen agar tim bisa mengajukan ke dosen lain
                $stmtUpdateTim = $db->prepare("
                    UPDATE tim
                    SET id_dosen = NULL, status_bimbingan = 'ditolak'
                    WHERE id_tim = :id_tim
                ");
                $stmtUpdateTim->execute([
                    ':id_tim' => $idTim,
                ]);
            }

            $db->commit();

            $actionLabel = $cleanKeputusan === 'disetujui' ? 'disetujui (ACC)' : 'ditolak';
            return [
                'success'      => true,
                'code'         => strtoupper($cleanKeputusan),
                'id_pengajuan' => $idPengajuan,
                'id_tim'       => $idTim,
                'status'       => $cleanKeputusan,
                'message'      => 'Permintaan bimbingan dari tim "' . $pengajuan['nama_tim'] . '" berhasil ' . $actionLabel . '.',
            ];
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            return [
                'success' => false,
                'code'    => 'DB_ERROR',
                'message' => 'Gagal memproses permintaan bimbingan: ' . $e->getMessage(),
            ];
        }
    }
}
