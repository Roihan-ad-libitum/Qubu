<?php
/**
 * Model Data Mahasiswa (tabel `mahasiswa` di database qubu-project)
 */

require_once __DIR__ . '/../../config/database.php';

class MahasiswaModel
{
    private ?PDO $pdo = null;
    private static bool $columnsEnsured = false;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
    }

    private function pdo(): PDO
    {
        $db = $this->pdo ??= db();
        if (!self::$columnsEnsured) {
            $this->ensurePartnerColumns($db);
        }
        return $db;
    }

    /**
     * Self-healing: Pastikan kolom `bidang_minat` memiliki panjang VARCHAR(150) untuk mendukung multi-tag
     * dan kolom `foto` tersedia pada tabel `mahasiswa`.
     */
    public function ensurePartnerColumns(?PDO $db = null): void
    {
        self::$columnsEnsured = true;
        $conn = $db ?? ($this->pdo ??= db());

        try {
            $colsRaw = $conn->query("SHOW COLUMNS FROM mahasiswa")->fetchAll(PDO::FETCH_ASSOC);
            $colMap = [];
            foreach ($colsRaw as $col) {
                $colMap[$col['Field']] = strtolower((string) ($col['Type'] ?? ''));
            }

            // 1. Perlebar bidang_minat dari varchar(30) ke varchar(150) agar multi-bidang tidak terpotong
            if (isset($colMap['bidang_minat']) && $colMap['bidang_minat'] === 'varchar(30)') {
                $conn->exec("ALTER TABLE mahasiswa MODIFY COLUMN bidang_minat VARCHAR(150) NOT NULL");
            }

            // 2. Pastikan kolom foto tersedia
            if (!isset($colMap['foto'])) {
                $conn->exec("ALTER TABLE mahasiswa ADD COLUMN foto VARCHAR(255) DEFAULT NULL");
            }
        } catch (Throwable $e) {
            // Abaikan secara aman jika database read-only
        }
    }

    /**
     * Cek apakah NPM sudah pernah didaftarkan
     */
    public function isNpmExists(string $npm): bool
    {
        $stmt = $this->pdo()->prepare("SELECT 1 FROM mahasiswa WHERE npm = :npm LIMIT 1");
        $stmt->execute([':npm' => trim($npm)]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Daftarkan akun Mahasiswa baru (menggunakan transaksi atomik tabel users & mahasiswa)
     *
     * @param array $userData      ['username', 'password', 'no_hp', 'email']
     * @param array $mahasiswaData ['npm', 'nama_mahasiswa', 'program_studi', 'angkatan', 'bidang_minat', 'bio']
     * @return int ID User yang baru dibuat
     * @throws Exception jika gagal query
     */
    public function registerMahasiswa(array $userData, array $mahasiswaData): int
    {
        $db = $this->pdo();
        try {
            $db->beginTransaction();

            // 1. Simpan ke tabel users (role = 'peserta')
            $sqlUser = "INSERT INTO users (username, password, role, no_hp, email) 
                        VALUES (:username, :password, 'peserta', :no_hp, :email)";
            $stmtUser = $db->prepare($sqlUser);
            $stmtUser->execute([
                ':username' => trim($userData['username']),
                ':password' => $userData['password'],
                ':no_hp'    => trim($userData['no_hp']),
                ':email'    => trim($userData['email']),
            ]);

            $idUser = (int) $db->lastInsertId();

            // Cek apakah tabel mahasiswa memiliki kolom 'foto'
            $hasFoto = false;
            try {
                $colStmt = $db->query("SHOW COLUMNS FROM mahasiswa LIKE 'foto'");
                $hasFoto = (bool) $colStmt->fetch();
                if (!$hasFoto && !empty($mahasiswaData['foto'])) {
                    // Coba tambahkan kolom foto jika belum ada di database
                    $db->exec("ALTER TABLE mahasiswa ADD COLUMN foto VARCHAR(255) NULL");
                    $hasFoto = true;
                }
            } catch (Throwable $e) {
                $hasFoto = false;
            }

            // 2. Simpan ke tabel mahasiswa
            if ($hasFoto && !empty($mahasiswaData['foto'])) {
                $sqlMhs = "INSERT INTO mahasiswa (id_user, npm, nama_mahasiswa, program_studi, angkatan, bidang_minat, bio, foto) 
                           VALUES (:id_user, :npm, :nama_mahasiswa, :program_studi, :angkatan, :bidang_minat, :bio, :foto)";
                $stmtMhs = $db->prepare($sqlMhs);
                $stmtMhs->execute([
                    ':id_user'        => $idUser,
                    ':npm'            => trim($mahasiswaData['npm']),
                    ':nama_mahasiswa' => trim($mahasiswaData['nama_mahasiswa']),
                    ':program_studi'  => trim($mahasiswaData['program_studi']),
                    ':angkatan'       => trim($mahasiswaData['angkatan']),
                    ':bidang_minat'   => trim($mahasiswaData['bidang_minat'] ?? 'Umum'),
                    ':bio'            => trim($mahasiswaData['bio'] ?? ''),
                    ':foto'           => trim($mahasiswaData['foto']),
                ]);
            } else {
                $sqlMhs = "INSERT INTO mahasiswa (id_user, npm, nama_mahasiswa, program_studi, angkatan, bidang_minat, bio) 
                           VALUES (:id_user, :npm, :nama_mahasiswa, :program_studi, :angkatan, :bidang_minat, :bio)";
                $stmtMhs = $db->prepare($sqlMhs);
                $stmtMhs->execute([
                    ':id_user'        => $idUser,
                    ':npm'            => trim($mahasiswaData['npm']),
                    ':nama_mahasiswa' => trim($mahasiswaData['nama_mahasiswa']),
                    ':program_studi'  => trim($mahasiswaData['program_studi']),
                    ':angkatan'       => trim($mahasiswaData['angkatan']),
                    ':bidang_minat'   => trim($mahasiswaData['bidang_minat'] ?? 'Umum'),
                    ':bio'            => trim($mahasiswaData['bio'] ?? ''),
                ]);
            }

            $db->commit();
            return $idUser;
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Ambil daftar pilihan bidang peminatan resmi Qubu
     * Fokus secara eksklusif pada kartu yang ada di index.php:
     * (Frontend, Backend, UI/UX Design, Data Science, Robotic)
     *
     * @return array
     */
    public function getBidangPeminatanOptions(): array
    {
        if (defined('BIDANG_QUBU')) {
            return BIDANG_QUBU;
        }

        return [
            'Frontend',
            'Backend',
            'UI/UX Design',
            'Data Science',
            'Robotic'
        ];
    }

    /**
     * Ambil data profil mahasiswa berdasarkan id_user
     */
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

    /**
     * Ambil data profil mahasiswa berdasarkan NPM
     */
    public function findByNpm(string $npm): ?array
    {
        $sql = "SELECT m.*, u.username, u.email, u.no_hp 
                FROM mahasiswa m 
                JOIN users u ON u.id_user = m.id_user 
                WHERE m.npm = :npm 
                LIMIT 1";
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([':npm' => trim($npm)]);
        $data = $stmt->fetch();
        return $data === false ? null : $data;
    }

    /**
     * Perbarui data profil mahasiswa berdasarkan id_user
     */
    public function updateProfileByUserId(int $idUser, array $data): bool
    {
        $fields = [];
        $params = [':id_user' => $idUser];

        if (isset($data['npm'])) {
            $fields[] = 'npm = :npm';
            $params[':npm'] = trim($data['npm']);
        }
        if (isset($data['nama_mahasiswa'])) {
            $fields[] = 'nama_mahasiswa = :nama_mahasiswa';
            $params[':nama_mahasiswa'] = trim($data['nama_mahasiswa']);
        }
        if (isset($data['program_studi'])) {
            $fields[] = 'program_studi = :program_studi';
            $params[':program_studi'] = trim($data['program_studi']);
        }
        if (isset($data['angkatan'])) {
            $fields[] = 'angkatan = :angkatan';
            $params[':angkatan'] = trim($data['angkatan']);
        }
        if (isset($data['bidang_minat'])) {
            $fields[] = 'bidang_minat = :bidang_minat';
            $params[':bidang_minat'] = trim($data['bidang_minat']);
        }
        if (isset($data['bio'])) {
            $fields[] = 'bio = :bio';
            $params[':bio'] = trim($data['bio']);
        }
        if (isset($data['foto'])) {
            try {
                $colStmt = $this->pdo()->query("SHOW COLUMNS FROM mahasiswa LIKE 'foto'");
                if (!$colStmt->fetch()) {
                    $this->pdo()->exec("ALTER TABLE mahasiswa ADD COLUMN foto VARCHAR(255) NULL");
                }
                $fields[] = 'foto = :foto';
                $params[':foto'] = trim($data['foto']);
            } catch (Throwable $e) {
                // Abaikan jika alter gagal
            }
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE mahasiswa SET " . implode(', ', $fields) . " WHERE id_user = :id_user";
        $stmt = $this->pdo()->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Ambil daftar rekan mahasiswa yang memiliki bidang minat sama dengan user
     * Digunakan pada section "Rekomendasi Rekan" Dashboard Qubu
     *
     * @param int $currentUserId ID user yang sedang login (agar tidak merekomendasikan diri sendiri)
     * @param string $bidangMinat Bidang minat dari profil user (bisa single atau dipisahkan koma)
     * @return array
     */
    public function getRecommendedPartnersByBidang(int $currentUserId, string $bidangMinat): array
    {
        try {
            $minatItems = array_filter(array_map('trim', explode(',', $bidangMinat)));
            if (empty($minatItems)) {
                $minatItems = ['UI/UX Design', 'Web Development'];
            }

            $conditions = [];
            $params = [':current_user_id' => $currentUserId];

            foreach ($minatItems as $idx => $m) {
                $key = ':minat_' . $idx;
                $conditions[] = "LOWER(m.bidang_minat) LIKE LOWER($key)";
                $params[$key] = '%' . $m . '%';
            }

            $whereClause = implode(' OR ', $conditions);
            $sql = "SELECT m.*, u.username, u.email, u.no_hp 
                    FROM mahasiswa m 
                    JOIN users u ON u.id_user = m.id_user 
                    WHERE m.id_user != :current_user_id 
                      AND ($whereClause)
                    ORDER BY m.id_mahasiswa DESC 
                    LIMIT 10";

            $stmt = $this->pdo()->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Jika hasil filter bidang spesifik kosong, berikan mahasiswa lain sebagai fallback
            if (empty($results)) {
                $stmtFallback = $this->pdo()->prepare(
                    "SELECT m.*, u.username, u.email, u.no_hp 
                     FROM mahasiswa m 
                     JOIN users u ON u.id_user = m.id_user 
                     WHERE m.id_user != :current_user_id 
                     ORDER BY m.id_mahasiswa DESC 
                     LIMIT 8"
                );
                $stmtFallback->execute([':current_user_id' => $currentUserId]);
                $results = $stmtFallback->fetchAll(PDO::FETCH_ASSOC);
            }

            return $results;
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Daftar Program Studi resmi Fasilkom Qubu
     *
     * @return array<int, string>
     */
    public function getProdiOptions(): array
    {
        if (defined('PRODI_FASILKOM') && is_array(PRODI_FASILKOM)) {
            return PRODI_FASILKOM;
        }

        return [
            'Informatika',
            'Sistem Informasi',
        ];
    }

    /**
     * Validasi dan normalisasi filter bidang terhadap whitelist 5 Bidang Qubu
     */
    public function normalizeBidangFilter(?string $bidang): ?string
    {
        $raw = trim((string) $bidang);
        if ($raw === '' || strcasecmp($raw, 'Semua Bidang') === 0 || strcasecmp($raw, 'semua') === 0) {
            return null;
        }

        foreach ($this->getBidangPeminatanOptions() as $allowed) {
            if (strcasecmp($raw, $allowed) === 0) {
                return $allowed;
            }
        }

        return null;
    }

    /**
     * Validasi dan normalisasi filter program studi terhadap whitelist Prodi Fasilkom
     */
    public function normalizeProdiFilter(?string $prodi): ?string
    {
        $raw = trim((string) $prodi);
        if ($raw === '' || strcasecmp($raw, 'Semua Prodi') === 0 || strcasecmp($raw, 'semua') === 0) {
            return null;
        }

        foreach ($this->getProdiOptions() as $allowed) {
            if (strcasecmp($raw, $allowed) === 0) {
                return $allowed;
            }
        }

        return null;
    }

    /**
     * Cari dan filter kandidat partner mahasiswa (wajib mengecualikan diri sendiri: m.id_user != :exclude_user_id)
     *
     * @param int         $excludeUserId ID user mahasiswa yang sedang login (wajib dikecualikan)
     * @param string|null $bidang        Filter bidang keahlian (Frontend, Backend, UI/UX Design, Data Science, Robotic)
     * @param string|null $prodi         Filter program studi (Informatika, Sistem Informasi)
     * @param string|null $keyword       Kata kunci pencarian (nama, bidang_minat, prodi, bio, username)
     * @param int         $limit         Batas jumlah baris hasil (1..100, default 50)
     * @return array<int, array<string, mixed>>
     */
    public function searchPartners(
        int $excludeUserId,
        ?string $bidang = null,
        ?string $prodi = null,
        ?string $keyword = null,
        int $limit = 50
    ): array {
        $safeLimit = max(1, min(100, $limit));
        $validBidang = $this->normalizeBidangFilter($bidang);
        $validProdi  = $this->normalizeProdiFilter($prodi);
        $cleanKw     = trim((string) preg_replace('/\s+/', ' ', strip_tags((string) $keyword)));
        if (mb_strlen($cleanKw) > 100) {
            $cleanKw = mb_substr($cleanKw, 0, 100);
        }

        $whereClauses = ['m.id_user != :exclude_user_id'];
        $params = [
            ':exclude_user_id' => $excludeUserId,
        ];

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

    /**
     * Ambil detail profil publik mahasiswa berdasarkan id_mahasiswa (tanpa mengekspos password)
     * Digunakan untuk fitur "Lihat Profil" pada halaman Cari Partner.
     */
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

    /**
     * Ubah string bidang_minat yang dipisahkan koma menjadi array tag bersih
     *
     * @return array<int, string>
     */
    public function parseBidangTags(?string $bidangMinat): array
    {
        $rawParts = array_filter(array_map('trim', explode(',', (string) $bidangMinat)));
        $allowed  = $this->getBidangPeminatanOptions();
        $tags     = [];

        foreach ($rawParts as $part) {
            $matched = null;
            foreach ($allowed as $opt) {
                if (strcasecmp($part, $opt) === 0) {
                    $matched = $opt;
                    break;
                }
            }
            $tags[] = $matched ?? $part;
        }

        $tags = array_values(array_unique(array_filter($tags)));

        return !empty($tags) ? $tags : ['Frontend'];
    }
}

