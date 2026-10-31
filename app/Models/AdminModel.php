<?php

require_once __DIR__ . '/../../config/database.php';

class AdminModel
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

    public function getSummary(): array
    {
        $db = $this->pdo();
        $counts = [
            'users' => (int) $db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
            'students' => (int) $db->query("SELECT COUNT(*) FROM users WHERE role = 'peserta'")->fetchColumn(),
            'lecturers' => (int) $db->query("SELECT COUNT(*) FROM users WHERE role = 'dosen'")->fetchColumn(),
            'competitions' => (int) $db->query("SELECT COUNT(*) FROM lomba")->fetchColumn(),
        ];

        $hasTeamPosts = $db->query("SHOW TABLES LIKE 'team_posts'")->fetchColumn();
        if ($hasTeamPosts) {
            $stmt = $db->prepare("
                SELECT COUNT(*)
                FROM team_posts p
                JOIN lomba l ON l.id_lomba = p.id_lomba
                WHERE p.status = 'open'
                  AND l.deadline_daftar >= :active_now
            ");
            $stmt->execute([':active_now' => date('Y-m-d H:i:s')]);
            $counts['open_team_posts'] = (int) $stmt->fetchColumn();
        } else {
            $counts['open_team_posts'] = 0;
        }

        return $counts;
    }

    public function searchUsers(string $keyword = '', string $role = ''): array
    {
        $where = [];
        $params = [];
        $keyword = trim($keyword);
        if ($keyword !== '') {
            $where[] = "(u.username LIKE :username
                OR u.email LIKE :email
                OR u.no_hp LIKE :phone
                OR m.nama_mahasiswa LIKE :student_name
                OR d.nama_dosen LIKE :lecturer_name
                OR m.npm LIKE :npm
                OR d.nidn LIKE :nidn)";
            $query = '%' . $keyword . '%';
            foreach (['username', 'email', 'phone', 'student_name', 'lecturer_name', 'npm', 'nidn'] as $name) {
                $params[':' . $name] = $query;
            }
        }

        if (in_array($role, ['peserta', 'dosen', 'admin'], true)) {
            $where[] = 'u.role = :role';
            $params[':role'] = $role;
        }

        $sql = "
            SELECT u.id_user, u.username, u.email, u.no_hp, u.role,
                   m.id_mahasiswa, m.nama_mahasiswa, m.npm, m.program_studi, m.angkatan, m.foto AS mahasiswa_foto,
                   d.id_dosen, d.nama_dosen, d.nidn, d.bidang_keahlian, d.foto AS dosen_foto,
                   d.status AS dosen_status
            FROM users u
            LEFT JOIN mahasiswa m ON m.id_user = u.id_user AND u.role = 'peserta'
            LEFT JOIN dosen d ON d.id_user = u.id_user AND u.role = 'dosen'
        ";
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= " ORDER BY u.id_user DESC LIMIT 200";

        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchLecturers(string $keyword = ''): array
    {
        $keyword = trim($keyword);
        $sql = "
            SELECT d.id_dosen, d.nama_dosen, d.nidn, d.bidang_keahlian, d.foto,
                   d.status, d.contact_visible, u.username, u.email, u.no_hp
            FROM dosen d
            JOIN users u ON u.id_user = d.id_user
        ";
        $params = [];
        if ($keyword !== '') {
            $sql .= " WHERE d.nama_dosen LIKE :name
                OR d.nidn LIKE :nidn
                OR d.bidang_keahlian LIKE :skill
                OR u.username LIKE :username";
            $query = '%' . $keyword . '%';
            $params = [
                ':name' => $query,
                ':nidn' => $query,
                ':skill' => $query,
                ':username' => $query,
            ];
        }
        $sql .= " ORDER BY CASE WHEN d.status = 'bersedia' THEN 0 ELSE 1 END,
                  d.nama_dosen ASC LIMIT 200";

        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
