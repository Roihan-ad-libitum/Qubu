-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 23, 2026 at 02:58 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `qubu-project`
--

-- --------------------------------------------------------

--
-- Table structure for table `dosen`
--

CREATE TABLE `dosen` (
  `id_dosen` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `nama_dosen` varchar(100) NOT NULL,
  `bidang_keahlian` varchar(50) NOT NULL,
  `nidn` varchar(20) NOT NULL,
  `status` enum('bersedia','tidak_bersedia') NOT NULL,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dosen`
--

INSERT INTO `dosen` (`id_dosen`, `id_user`, `nama_dosen`, `bidang_keahlian`, `nidn`, `status`) VALUES
(1, 3, 'Fitria', 'Frontend, Backend', '2511000000000', 'bersedia');

-- --------------------------------------------------------

--
-- Table structure for table `lomba`
--

CREATE TABLE `lomba` (
  `id_lomba` int(11) NOT NULL,
  `nama_lomba` varchar(100) NOT NULL,
  `penyelenggara` varchar(100) NOT NULL,
  `deskripsi` text NOT NULL,
  `deadline_daftar` datetime NOT NULL,
  `tanggal_pelaksanaan` date NOT NULL,
  `tingkat` varchar(50) NOT NULL,
  `biaya` decimal(10,0) NOT NULL,
  `partisipasi` enum('Individu','Tim','Individu/Tim') NOT NULL,
  `bidang` varchar(80) NOT NULL,
  `link_resmi` varchar(255) NOT NULL,
  `poster` varchar(255) DEFAULT NULL,
  `link_guidebook` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lomba`
--

INSERT INTO `lomba` (`id_lomba`, `nama_lomba`, `penyelenggara`, `deskripsi`, `deadline_daftar`, `tanggal_pelaksanaan`, `tingkat`, `biaya`, `partisipasi`, `bidang`, `link_resmi`, `poster`, `link_guidebook`) VALUES
(1, 'Hackathon Web Nasional', 'Institut Teknologi Bandung', 'Kompetisi hackathon tingkat nasional untuk mengembangkan solusi digital inovatif yang menjawab tantangan sosial di Indonesia. Peserta akan berkompetisi selama 48 jam untuk menghasilkan prototipe aplikasi yang fungsional.', '2026-12-12 12:00:00', '2027-02-05', 'Nasional', 120000, 'Tim', 'Frontend', 'https://example.com/uiux', NULL, NULL),
(2, 'Semantik Internformatics Competition', 'Himpunan Mahasiswa Informatika', 'Tuliskan detail syarat dan timeline...', '2025-07-15 00:00:00', '2025-07-30', 'Nasional', 0, 'Individu/Tim', 'UI/UX Design', 'https://bit.ly/daftar-lomba', 'public/images/semantik-poster.jpg', 'https://drive.google.com/semantik'),
(3, 'National UI/UX Design Challenge', 'Universitas Indonesia', 'Kompetisi perancangan antarmuka dan pengalaman pengguna tingkat nasional untuk menciptakan solusi digital inklusif dan human-centered.', '2026-11-30 23:59:00', '2026-12-10', 'Nasional', 50000, 'Individu/Tim', 'UI/UX Design', 'https://uiuxchallenge.id', 'public/images/semantik-poster.jpg', 'https://drive.google.com/uiux-guide'),
(4, 'GEMASTIK Software Development', 'Puspresnas Kemdikbudristek', 'Kompetisi pengembangan perangkat lunak berskala nasional bagi mahasiswa seluruh Indonesia.', '2026-12-05 23:59:00', '2027-01-15', 'Nasional', 0, 'Tim', 'Backend', 'https://gemastik.kemdikbud.go.id', NULL, 'https://gemastik.kemdikbud.go.id/guide'),
(5, 'Compfest Competitive Programming', 'Fasilkom UI', 'Ajang adu kemampuan algoritma dan pemrograman kompetitif tingkat mahasiswa se-Asia Tenggara.', '2026-11-25 23:59:00', '2026-12-15', 'Nasional', 75000, 'Individu', 'Backend', 'https://compfest.id', NULL, 'https://compfest.id/guide'),
(6, 'National Data Science Championship', 'Institut Teknologi Bandung', 'Kompetisi analisis data, permodelan machine learning, dan pemecahan masalah berbasis AI tingkat nasional.', '2026-12-01 23:59:00', '2027-01-20', 'Nasional', 100000, 'Tim', 'Data Science', 'https://datascience.itb.ac.id', NULL, 'https://datascience.itb.ac.id/guide'),
(7, 'Kontes Robotika Indonesia (KRI)', 'BPTI Kemdikbudristek', 'Kompetisi perancangan, otomatisasi, dan pemrograman robotika mahasiswa tingkat nasional.', '2026-12-20 23:59:00', '2027-02-10', 'Nasional', 150000, 'Tim', 'Robotic', 'https://kri.kemdikbud.go.id', NULL, 'https://kri.kemdikbud.go.id/guide');

-- --------------------------------------------------------

--
-- Table structure for table `mahasiswa`
--

CREATE TABLE `mahasiswa` (
  `id_mahasiswa` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `npm` varchar(20) NOT NULL,
  `nama_mahasiswa` varchar(100) NOT NULL,
  `program_studi` varchar(30) NOT NULL,
  `angkatan` varchar(4) NOT NULL,
  `bidang_minat` varchar(30) NOT NULL,
  `bio` text NOT NULL,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mahasiswa`
--

INSERT INTO `mahasiswa` (`id_mahasiswa`, `id_user`, `npm`, `nama_mahasiswa`, `program_studi`, `angkatan`, `bidang_minat`, `bio`, `foto`) VALUES
(1, 2, '251000000000', 'Roihan Fakhri', 'Informatika', '2025', 'Frontend', 'Selamat berjuang, sukses!', NULL),
(2, 4, '251000000001', 'Rangga Candra', 'Informatika', '2025', 'Frontend', 'Frontend jaya jaya jaya', NULL),
(5, 7, '2510631170041', 'Marssello Hotasi', 'Informatika', '2025', 'UI/UX Design', '', NULL),
(6, 8, '251000000010', 'Fitria Ramadhani', 'Informatika', '2025', 'UI/UX Design', 'Passionate in Human-Computer Interaction & Product Design', NULL),
(7, 9, '251000000011', 'Dimas Prasetyo', 'Informatika', '2025', 'Backend', 'Fullstack developer focusing on web architectures.', NULL),
(8, 10, '251000000012', 'Siti Aisyah', 'Sistem Informasi', '2025', 'Data Science', 'Exploring machine learning and big data analytics.', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `undangan`
--

CREATE TABLE `undangan` (
  `id_undangan` int(11) NOT NULL,
  `id_mahasiswa` int(11) NOT NULL,
  `id_lomba` int(11) NOT NULL,
  `bidang_dibutuhkan` varchar(80) NOT NULL,
  `catatan` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `role` enum('peserta','dosen','admin') NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `username`, `password`, `role`, `no_hp`, `email`) VALUES
(1, 'marcelloz', 'marceloz123', 'admin', '081234567890', 'marceloz@admin.unsika.ac.id'),
(2, 'roihanz', 'roihanz123', 'peserta', '081234567890', 'roihanz@student.unsika.ac.id'),
(3, 'fitriaz', 'fitriaz123', 'dosen', '081234567890', 'fitriaz@dosen.unsika.ac.id'),
(4, 'ranggaz', 'ranggaz', 'peserta', '081234567890', 'ranggaz@student.unsika.ac.id'),
(7, 'Celloz', 'celloaja', 'peserta', '-', 'Celloz@student.unsika.ac.id'),
(8, 'fitriaz_mhs', 'password123', 'peserta', '081234567890', 'fitria@student.unsika.ac.id'),
(9, 'dimas_p', 'password123', 'peserta', '081234567890', 'dimas@student.unsika.ac.id'),
(10, 'aisyah_ds', 'password123', 'peserta', '081234567890', 'aisyah@student.unsika.ac.id');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dosen`
--
ALTER TABLE `dosen`
  ADD PRIMARY KEY (`id_dosen`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `lomba`
--
ALTER TABLE `lomba`
  ADD PRIMARY KEY (`id_lomba`);

--
-- Indexes for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD PRIMARY KEY (`id_mahasiswa`),
  ADD UNIQUE KEY `npm` (`npm`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `undangan`
--
ALTER TABLE `undangan`
  ADD PRIMARY KEY (`id_undangan`),
  ADD KEY `id_peserta` (`id_mahasiswa`),
  ADD KEY `id_lomba` (`id_lomba`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dosen`
--
ALTER TABLE `dosen`
  MODIFY `id_dosen` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lomba`
--
ALTER TABLE `lomba`
  MODIFY `id_lomba` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  MODIFY `id_mahasiswa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `undangan`
--
ALTER TABLE `undangan`
  MODIFY `id_undangan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `dosen`
--
ALTER TABLE `dosen`
  ADD CONSTRAINT `dosen_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD CONSTRAINT `mahasiswa_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `undangan`
--
ALTER TABLE `undangan`
  ADD CONSTRAINT `undangan_ibfk_1` FOREIGN KEY (`id_lomba`) REFERENCES `lomba` (`id_lomba`),
  ADD CONSTRAINT `undangan_ibfk_2` FOREIGN KEY (`id_mahasiswa`) REFERENCES `mahasiswa` (`id_mahasiswa`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
