<?php
/**
 * Model Data Dosen Pembimbing (tabel `dosen` di database qubu-project)
 */

require_once __DIR__ . '/../../config/database.php';

class DosenModel
{
    private ?PDO $pdo = null;
    private static bool $columnsVerified = false;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
        $this->ensureDosenColumns();
    }

    private function pdo(): PDO
    {
        return $this->pdo ??= db();
    }

    /**
     * DP-1.1: Pastikan kolom `bidang_keahlian` pada tabel `dosen` berukuran VARCHAR(150)
     * agar multi-tag keahlian tidak terpotong, serta pastikan seed data dosen pembimbing
     * tersedia lintas 5 bidang Qubu (status `bersedia` dan `tidak_bersedia`).
     */
    public function ensureDosenColumns(): void
    {
        if (self::$columnsVerified) {
            return;
        }

        try {
            $db = $this->pdo();

            // 1. Perlebar dosen.bidang_keahlian menjadi VARCHAR(150) jika masih sempit
            $colStmt = $db->query("SHOW COLUMNS FROM dosen LIKE 'bidang_keahlian'");
            $colInfo = $colStmt ? $colStmt->fetch() : false;
            if ($colInfo && isset($colInfo['Type'])) {
                if (preg_match('/varchar\((\d+)\)/i', (string) $colInfo['Type'], $matches)) {
                    if ((int) $matches[1] < 150) {
                        $db->exec("ALTER TABLE dosen MODIFY COLUMN bidang_keahlian VARCHAR(150) NOT NULL DEFAULT 'Frontend'");
                    }
                }
            }

            // 2. Pastikan kolom foto tersedia
            $fotoStmt = $db->query("SHOW COLUMNS FROM dosen LIKE 'foto'");
            if ($fotoStmt && !$fotoStmt->fetch()) {
                $db->exec("ALTER TABLE dosen ADD COLUMN foto VARCHAR(255) DEFAULT NULL");
            }

            // 3. Seed dosen pembimbing tambahan jika jumlah dosen masih < 5
            $countDosen = (int) $db->query("SELECT COUNT(*) FROM dosen")->fetchColumn();
            if ($countDosen < 5) {
                $seedDosen = [
                    [
                        'username'        => 'budi_dosen',
                        'password'        => 'dosen123',
                        'no_hp'           => '081210000002',
                        'email'           => 'budi.raharjo@dosen.unsika.ac.id',
                        'nama_dosen'      => 'Dr. Budi Raharjo, M.Kom',
                        'bidang_keahlian' => 'UI/UX Design, Frontend',
                        'nidn'            => '0411028801',
                        'status'          => 'bersedia',
                    ],
                    [
                        'username'        => 'siti_dosen',
                        'password'        => 'dosen123',
                        'no_hp'           => '081210000003',
                        'email'           => 'siti.nurhaliza@dosen.unsika.ac.id',
                        'nama_dosen'      => 'Siti Nurhaliza, S.Kom., M.T.',
                        'bidang_keahlian' => 'Data Science, Backend',
                        'nidn'            => '0415058902',
                        'status'          => 'bersedia',
                    ],
                    [
                        'username'        => 'hendra_dosen',
                        'password'        => 'dosen123',
                        'no_hp'           => '081210000004',
                        'email'           => 'hendra.wijaya@dosen.unsika.ac.id',
                        'nama_dosen'      => 'Ir. Hendra Wijaya, M.Eng.',
                        'bidang_keahlian' => 'Robotic, Backend',
                        'nidn'            => '0420088503',
                        'status'          => 'bersedia',
                    ],
                    [
                        'username'        => 'rina_dosen',
                        'password'        => 'dosen123',
                        'no_hp'           => '081210000005',
                        'email'           => 'rina.kartika@dosen.unsika.ac.id',
                        'nama_dosen'      => 'Rina Kartika, M.Kom',
                        'bidang_keahlian' => 'Frontend, UI/UX Design',
                        'nidn'            => '0412119004',
                        'status'          => 'tidak_bersedia',
                    ],
                    [
                        'username'        => 'arif_dosen',
                        'password'        => 'dosen123',
                        'no_hp'           => '081210000006',
                        'email'           => 'arif.pratama@dosen.unsika.ac.id',
                        'nama_dosen'      => 'Dr. Arif Pratama, M.Cs',
                        'bidang_keahlian' => 'Data Science, Robotic',
                        'nidn'            => '0409038705',
                        'status'          => 'tidak_bersedia',
                    ],
                ];

                foreach ($seedDosen as $sd) {
                    if (!$this->isNidnExists($sd['nidn'])) {
                        $this->registerDosen(
                            [
                                'username' => $sd['username'],
                                'password' => $sd['password'],
                                'no_hp'    => $sd['no_hp'],
                                'email'    => $sd['email'],
                            ],
                            [
                                'nama_dosen'      => $sd['nama_dosen'],
                                'bidang_keahlian' => $sd['bidang_keahlian'],
                                'nidn'            => $sd['nidn'],
                                'status'          => $sd['status'],
                            ]
                        );
                    }
                }
            }

            self::$columnsVerified = true;
        } catch (Throwable $e) {
            // Jangan hentikan aplikasi jika koneksi DB sedang di-mock
        }
    }

    /**
     * Cek apakah NIDN sudah pernah didaftarkan
     */
    public function isNidnExists(string $nidn): bool
    {
        $stmt = $this->pdo()->prepare("SELECT 1 FROM dosen WHERE nidn = :nidn LIMIT 1");
        $stmt->execute([':nidn' => trim($nidn)]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Daftarkan akun Dosen baru (menggunakan transaksi atomik tabel users & dosen)
     *
     * @param array $userData  ['username', 'password', 'no_hp', 'email']
     * @param array $dosenData ['nama_dosen', 'bidang_keahlian', 'nidn', 'status']
     * @return int ID User yang baru dibuat
     * @throws Exception jika gagal query
     */
    public function registerDosen(array $userData, array $dosenData): int
    {
        $db = $this->pdo();
        try {
            $db->beginTransaction();

            // 1. Simpan ke tabel users (role = 'dosen')
            $sqlUser = "INSERT INTO users (username, password, role, no_hp, email) 
                        VALUES (:username, :password, 'dosen', :no_hp, :email)";
            $stmtUser = $db->prepare($sqlUser);
            $stmtUser->execute([
                ':username' => trim($userData['username']),
                ':password' => $userData['password'], // Disimpan plain-text sesuai aturan proyek
                ':no_hp'    => trim($userData['no_hp']),
                ':email'    => trim($userData['email']),
            ]);

            $idUser = (int) $db->lastInsertId();

            // 2. Simpan ke tabel dosen
            $sqlDosen = "INSERT INTO dosen (id_user, nama_dosen, bidang_keahlian, nidn, status) 
                         VALUES (:id_user, :nama_dosen, :bidang_keahlian, :nidn, :status)";
            $stmtDosen = $db->prepare($sqlDosen);
            $stmtDosen->execute([
                ':id_user'         => $idUser,
                ':nama_dosen'      => trim($dosenData['nama_dosen']),
                ':bidang_keahlian' => trim($dosenData['bidang_keahlian']),
                ':nidn'            => trim($dosenData['nidn']),
                ':status'          => $dosenData['status'] ?? 'bersedia',
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

    /**
     * Ambil data profil dosen berdasarkan id_user
     */
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

    /**
     * Ambil data profil dosen berdasarkan NIDN
     */
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

    /**
     * Perbarui data profil dosen berdasarkan id_user
     */
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
            // Normalisasi status ke format enum database: bersedia / tidak_bersedia
            if ($statusVal === 'bersedia' || $statusVal === 'tidak_bersedia') {
                $fields[] = 'status = :status';
                $params[':status'] = $statusVal;
            }
        }
        if (isset($data['foto'])) {
            try {
                $colStmt = $this->pdo()->query("SHOW COLUMNS FROM dosen LIKE 'foto'");
                if (!$colStmt->fetch()) {
                    $this->pdo()->exec("ALTER TABLE dosen ADD COLUMN foto VARCHAR(255) NULL");
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

        $sql = "UPDATE dosen SET " . implode(', ', $fields) . " WHERE id_user = :id_user";
        $stmt = $this->pdo()->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Ambil daftar pilihan bidang keahlian dosen
     * Fokus secara eksklusif pada kartu yang ada di index.php:
     * (Frontend, Backend, UI/UX Design, Data Science, Robotic)
     *
     * @return array
     */
    public function getBidangKeahlianOptions(): array
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
}
