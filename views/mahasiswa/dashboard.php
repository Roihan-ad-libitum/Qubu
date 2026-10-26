<?php
/**
 * Dashboard Mahasiswa Qubu - Terhubung ke Halaman Profil Mahasiswa
 */
require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/../../core/AuthMiddleWare.php';
require_once __DIR__ . '/../../app/Controllers/MahasiswaController.php';

AuthMiddleware::hasRole(ROLE_PESERTA);
(new MahasiswaController())->profile();