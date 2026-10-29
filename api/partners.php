<?php
/**
 * Endpoint JSON API Pencarian & Filter Partner (api/partners.php)
 * Mendukung query parameters:
 * - ?q={keyword}
 * - ?bidang={Frontend|Backend|UI/UX Design|Data Science|Robotic|Semua Bidang}
 * - ?prodi={Informatika|Sistem Informasi|Semua Prodi}
 * - ?id_lomba={id_lomba}
 * - ?id={id_mahasiswa} (untuk detail profil publik kandidat partner)
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../core/Validator.php';
require_once __DIR__ . '/../app/Controllers/MahasiswaController.php';

(new MahasiswaController())->apiPartners($_GET);

