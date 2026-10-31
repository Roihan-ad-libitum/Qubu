SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('peserta','dosen','admin') NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `idx_users_username` (`username`),
  UNIQUE KEY `idx_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id_user`, `username`, `password`, `role`, `no_hp`, `email`) VALUES
(1, 'marcelloz', '$2y$12$3bytHKklgLtQx3lmm4n9Fexa58c98sCT16KG7NNTkZjf1b8LYkMHm', 'admin', '081234567890', 'marceloz@admin.unsika.ac.id'),
(2, 'roihanz', '$2y$12$33Enn5.ZFpbaz6uTkCi98.EFEBmzybzKccP1RiDchaXRfGPhvhZjS', 'peserta', '081234567891', 'roihanz@student.unsika.ac.id'),
(3, 'fitriaz', '$2y$12$AU/uWec55XrlrmuFh0.yRO6fYIc62qvySuvVwMQPlOBhc8pMFatje', 'dosen', '081234567892', 'fitriaz@dosen.unsika.ac.id'),
(4, 'ranggaz', '$2y$12$omL6wKjQlLk1623CCXyKe.jjyQBGJM3NviYFq8/6ouyY0dAm.3fsi', 'peserta', '081234567893', 'ranggaz@student.unsika.ac.id'),
(7, 'Celloz', '$2y$12$Sw/JfJ77eBmiT/Y5iDPB3.WGI4q4uAX3nPOhFVXv.qwYXKBiS/4K6', 'peserta', '081234567897', 'Celloz@student.unsika.ac.id'),
(8, 'fitriaz_mhs', '$2y$12$G/giM4vRm0sCTPw.iziBN.jPQvsBQJ8kis3D1W.xpzB40sYjFKJT.', 'peserta', '081234567898', 'fitria@student.unsika.ac.id'),
(9, 'dimas_p', '$2y$12$G/giM4vRm0sCTPw.iziBN.jPQvsBQJ8kis3D1W.xpzB40sYjFKJT.', 'peserta', '081234567899', 'dimas@student.unsika.ac.id'),
(10, 'aisyah_ds', '$2y$12$G/giM4vRm0sCTPw.iziBN.jPQvsBQJ8kis3D1W.xpzB40sYjFKJT.', 'peserta', '081234567800', 'aisyah@student.unsika.ac.id');

CREATE TABLE `dosen` (
  `id_dosen` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `nama_dosen` varchar(100) NOT NULL,
  `bidang_keahlian` varchar(150) NOT NULL,
  `nidn` varchar(20) NOT NULL,
  `status` enum('bersedia','tidak_bersedia') NOT NULL DEFAULT 'bersedia',
  `foto` varchar(255) DEFAULT NULL,
  `contact_visible` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_dosen`),
  UNIQUE KEY `idx_dosen_nidn` (`nidn`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `dosen_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `dosen` (`id_dosen`, `id_user`, `nama_dosen`, `bidang_keahlian`, `nidn`, `status`, `contact_visible`) VALUES
(1, 3, 'Fitria, M.Kom.', 'Frontend, Backend', '2511000000000', 'bersedia', 1);

CREATE TABLE `mahasiswa` (
  `id_mahasiswa` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `npm` varchar(20) NOT NULL,
  `nama_mahasiswa` varchar(100) NOT NULL,
  `program_studi` varchar(30) NOT NULL,
  `angkatan` varchar(4) NOT NULL,
  `bidang_minat` varchar(150) NOT NULL,
  `bio` text NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `github` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_mahasiswa`),
  UNIQUE KEY `npm` (`npm`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `mahasiswa_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `mahasiswa` (`id_mahasiswa`, `id_user`, `npm`, `nama_mahasiswa`, `program_studi`, `angkatan`, `bidang_minat`, `bio`, `foto`, `github`) VALUES
(1, 2, '251000000000', 'Roihan Fakhri', 'Informatika', '2025', 'Frontend, UI/UX Design', 'Selamat berjuang, sukses berkolaborasi di kompetisi nasional!', NULL, 'https://github.com/roihan-ad-libitum'),
(2, 4, '251000000001', 'Rangga Candra', 'Informatika', '2025', 'Frontend', 'Fokus pada pengembangan arsitektur antarmuka web modern.', NULL, NULL),
(5, 7, '2510631170041', 'Marssello Hotasi', 'Informatika', '2025', 'UI/UX Design', 'Tertarik pada riset pengalaman pengguna dan desain produk digital.', NULL, NULL),
(6, 8, '251000000010', 'Fitria Ramadhani', 'Informatika', '2025', 'UI/UX Design, Frontend', 'Passionate in Human-Computer Interaction & Product Design.', NULL, NULL),
(7, 9, '251000000011', 'Dimas Prasetyo', 'Informatika', '2025', 'Backend', 'Fullstack developer focusing on scalable web architectures.', NULL, NULL),
(8, 10, '251000000012', 'Siti Aisyah', 'Sistem Informasi', '2025', 'Data Science', 'Exploring machine learning and big data analytics.', NULL, NULL);

CREATE TABLE `lomba` (
  `id_lomba` int(11) NOT NULL AUTO_INCREMENT,
  `nama_lomba` varchar(100) NOT NULL,
  `penyelenggara` varchar(100) NOT NULL,
  `deskripsi` text NOT NULL,
  `deadline_daftar` datetime NOT NULL,
  `tanggal_pelaksanaan` date NOT NULL,
  `tingkat` varchar(50) NOT NULL,
  `biaya` decimal(10,0) NOT NULL,
  `partisipasi` enum('Individu','Tim','Individu/Tim') NOT NULL,
  `bidang` varchar(150) NOT NULL,
  `link_resmi` varchar(255) NOT NULL,
  `poster` varchar(255) DEFAULT NULL,
  `link_guidebook` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_lomba`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `lomba` (`id_lomba`, `nama_lomba`, `penyelenggara`, `deskripsi`, `deadline_daftar`, `tanggal_pelaksanaan`, `tingkat`, `biaya`, `partisipasi`, `bidang`, `link_resmi`, `poster`, `link_guidebook`) VALUES
(1, 'Hackathon Web Nasional', 'Institut Teknologi Bandung', 'Kompetisi hackathon tingkat nasional untuk mengembangkan solusi digital inovatif yang menjawab tantangan sosial di Indonesia. Peserta akan berkompetisi selama 48 jam untuk menghasilkan prototipe aplikasi yang fungsional.', '2026-12-12 12:00:00', '2027-02-05', 'Nasional', 120000, 'Tim', 'Frontend', 'https://example.com/uiux', NULL, 'https://example.com/guidebook-hackathon'),
(2, 'Semantik Informatics Competition', 'Himpunan Mahasiswa Informatika', 'Kompetisi desain antarmuka dan inovasi produk teknologi tahunan tingkat nasional.', '2026-11-15 23:59:00', '2026-11-30', 'Nasional', 0, 'Individu/Tim', 'UI/UX Design', 'https://bit.ly/daftar-lomba', 'public/images/semantik-poster.jpg', 'https://drive.google.com/semantik'),
(3, 'National UI/UX Design Challenge', 'Universitas Indonesia', 'Kompetisi perancangan antarmuka dan pengalaman pengguna tingkat nasional untuk menciptakan solusi digital inklusif dan human-centered.', '2026-11-30 23:59:00', '2026-12-10', 'Nasional', 50000, 'Individu/Tim', 'UI/UX Design', 'https://uiuxchallenge.id', 'public/images/semantik-poster.jpg', 'https://drive.google.com/uiux-guide'),
(4, 'GEMASTIK Software Development', 'Puspresnas Kemdikbudristek', 'Kompetisi pengembangan perangkat lunak berskala nasional bagi mahasiswa seluruh Indonesia.', '2026-12-05 23:59:00', '2027-01-15', 'Nasional', 0, 'Tim', 'Backend', 'https://gemastik.kemdikbud.go.id', NULL, 'https://gemastik.kemdikbud.go.id/guide'),
(5, 'Compfest Competitive Programming', 'Fasilkom UI', 'Ajang adu kemampuan algoritma dan pemrograman kompetitif tingkat mahasiswa se-Asia Tenggara.', '2026-11-25 23:59:00', '2026-12-15', 'Nasional', 75000, 'Individu', 'Backend', 'https://compfest.id', NULL, 'https://compfest.id/guide'),
(6, 'National Data Science Championship', 'Institut Teknologi Bandung', 'Kompetisi analisis data, permodelan machine learning, dan pemecahan masalah berbasis AI tingkat nasional.', '2026-12-01 23:59:00', '2027-01-20', 'Nasional', 100000, 'Tim', 'Data Science', 'https://datascience.itb.ac.id', NULL, 'https://datascience.itb.ac.id/guide'),
(7, 'Kontes Robotika Indonesia (KRI)', 'BPTI Kemdikbudristek', 'Kompetisi perancangan, otomatisasi, dan pemrograman robotika mahasiswa tingkat nasional.', '2026-12-20 23:59:00', '2027-02-10', 'Nasional', 150000, 'Tim', 'Robotic', 'https://kri.kemdikbud.go.id', NULL, 'https://kri.kemdikbud.go.id/guide');

CREATE TABLE `team_posts` (
  `id_post` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `id_lomba` int(11) NOT NULL,
  `bidang_dibutuhkan` varchar(150) NOT NULL,
  `jumlah_anggota` tinyint(3) unsigned NOT NULL,
  `deskripsi` text NOT NULL,
  `contact_opt_in` tinyint(1) NOT NULL DEFAULT 0,
  `contact_email` varchar(100) DEFAULT NULL,
  `contact_whatsapp` varchar(30) DEFAULT NULL,
  `status` enum('open','team_complete','archived') NOT NULL DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_post`),
  KEY `idx_team_posts_competition_status` (`id_lomba`,`status`,`created_at`),
  KEY `idx_team_posts_owner` (`id_user`,`status`),
  CONSTRAINT `fk_team_posts_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE,
  CONSTRAINT `fk_team_posts_lomba` FOREIGN KEY (`id_lomba`) REFERENCES `lomba` (`id_lomba`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `team_posts` (`id_post`, `id_user`, `id_lomba`, `bidang_dibutuhkan`, `jumlah_anggota`, `deskripsi`, `contact_opt_in`, `contact_email`, `contact_whatsapp`, `status`) VALUES
(1, 2, 1, 'Backend, UI/UX Design', 2, 'Mencari 1 rekan Backend (Node.js/Laravel) dan 1 UI/UX Designer untuk kolaborasi tim Hackathon Web Nasional. Prototipe awal dan ide solusi sudah disiapkan.', 0, NULL, NULL, 'open'),
(2, 8, 3, 'Frontend, Data Science', 2, 'Kami membutuhkan rekan Frontend Developer dan Data Analyst untuk menyusun riset pengguna serta prototipe interaktif pada National UI/UX Design Challenge.', 0, NULL, NULL, 'open'),
(3, 9, 4, 'Frontend, UI/UX Design', 2, 'Tim GEMASTIK Software Development mencari rekan Frontend dan desainer produk. Fokus pada aplikasi layanan publik kampus.', 0, NULL, NULL, 'open');

COMMIT;
