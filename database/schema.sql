-- ============================================================
-- InfoLomba - Schema Database (Task 1: Fitur Login)
-- Jalankan: mysql -u root -p < database/schema.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS db_lomba_fasilkom

   

USE db_lomba_fasilkom;

-- ------------------------------------------------------------
-- Tabel users: akun login untuk semua role
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    email         VARCHAR(191) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('mahasiswa', 'dosen', 'admin') NOT NULL,
    is_active     TINYINT(1) NOT NULL DEFAULT 1,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email),
    KEY idx_users_role (role)
) 

-- ------------------------------------------------------------
-- Tabel mahasiswa_profiles: data profil mahasiswa (1:1 dengan users)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS mahasiswa_profiles (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id    INT UNSIGNED NOT NULL,
    nim        VARCHAR(20)  NOT NULL,
    full_name  VARCHAR(150) NOT NULL,
    angkatan   YEAR NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_mahasiswa_user (user_id),
    UNIQUE KEY uq_mahasiswa_nim (nim),
    CONSTRAINT fk_mahasiswa_user FOREIGN KEY (user_id)
        REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE
)

-- ------------------------------------------------------------
-- Tabel dosen_profiles: data profil dosen (1:1 dengan users)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS dosen_profiles (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id    INT UNSIGNED NOT NULL,
    nidn       VARCHAR(20)  NOT NULL,
    full_name  VARCHAR(150) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_dosen_user (user_id),
    UNIQUE KEY uq_dosen_nidn (nidn),
    CONSTRAINT fk_dosen_user FOREIGN KEY (user_id)
        REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE
) 

-- ------------------------------------------------------------
-- Seeder akun uji. Password semua akun: password123
-- (bcrypt, dibuat dengan password_hash('password123', PASSWORD_BCRYPT))
-- Aman dijalankan ulang: email yang sudah ada tidak diduplikasi.
-- ------------------------------------------------------------
INSERT INTO users (email, password_hash, role, is_active) VALUES
    ('admin@unsika.ac.id',                  '$2y$10$X/H3ZMrHUiwJzWOTCcJh6euKVP7Hiu5ngrpM.pmAVJL36ITJ.n3/6', 'admin',     1),
    ('intan.dosen@unsika.ac.id',            '$2y$10$X/H3ZMrHUiwJzWOTCcJh6euKVP7Hiu5ngrpM.pmAVJL36ITJ.n3/6', 'dosen',     1),
    ('jamal.dosen@unsika.ac.id',             '$2y$10$X/H3ZMrHUiwJzWOTCcJh6euKVP7Hiu5ngrpM.pmAVJL36ITJ.n3/6', 'dosen',     1),
    ('2210631170001@student.unsika.ac.id',  '$2y$10$X/H3ZMrHUiwJzWOTCcJh6euKVP7Hiu5ngrpM.pmAVJL36ITJ.n3/6', 'mahasiswa', 1),
    ('2210631250002@student.unsika.ac.id',  '$2y$10$X/H3ZMrHUiwJzWOTCcJh6euKVP7Hiu5ngrpM.pmAVJL36ITJ.n3/6', 'mahasiswa', 1)
ON DUPLICATE KEY UPDATE email = email;

INSERT INTO dosen_profiles (user_id, nidn, full_name)
SELECT u.id, v.nidn, v.full_name
FROM users u
JOIN (
    SELECT 'intan.dosen@unsika.ac.id' AS email, '0411018801' AS nidn, 'Intan Permata, M.Kom.' AS full_name
    UNION ALL
    SELECT 'jamal.dosen@unsika.ac.id',           '0415077902',         'Jamaludin, M.T.'
) v ON v.email = u.email
ON DUPLICATE KEY UPDATE full_name = VALUES(full_name);

INSERT INTO mahasiswa_profiles (user_id, nim, full_name, angkatan)
SELECT u.id, v.nim, v.full_name, v.angkatan
FROM users u
JOIN (
    SELECT '2210631170001@student.unsika.ac.id' AS email, '2210631170001' AS nim, 'Rere Putri'  AS full_name, 2022 AS angkatan
    UNION ALL
    SELECT '2210631250002@student.unsika.ac.id',           '2210631250002',        'Dimas Prayoga',          2022
) v ON v.email = u.email
ON DUPLICATE KEY UPDATE full_name = VALUES(full_name);
