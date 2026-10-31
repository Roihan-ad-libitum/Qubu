<?php

require_once __DIR__ . '/../Models/DosenModel.php';
require_once __DIR__ . '/../Models/MahasiswaModel.php';
require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/../../core/AuthMiddleware.php';
require_once __DIR__ . '/../../core/Validator.php';

class AdvisorController
{
    private DosenModel $dosenModel;

    public function __construct()
    {
        $this->dosenModel = new DosenModel();
    }

    public function directory(): void
    {
        Session::start();
        AuthMiddleware::hasRole(ROLE_PESERTA);

        $profile     = (new MahasiswaModel())->findByUserId((int) Session::get('id_user'));
        $allAdvisors = $this->dosenModel->getDirectory();

        $searchKeyword = Validator::cleanKeyword($_GET['q'] ?? '', 100);
        $activeBidang  = Validator::validateBidang($_GET['bidang'] ?? 'Semua Bidang') ?? 'Semua Bidang';

        $advisors = array_values(array_filter($allAdvisors, static function (array $adv) use ($searchKeyword, $activeBidang): bool {
            $nama     = strtolower((string) ($adv['nama_dosen'] ?? ''));
            $username = strtolower((string) ($adv['username'] ?? ''));
            $nidn     = strtolower((string) ($adv['nidn'] ?? ''));
            $bidang   = strtolower((string) ($adv['bidang_keahlian'] ?? ''));

            if ($activeBidang !== 'Semua Bidang' && strpos($bidang, strtolower($activeBidang)) === false) {
                return false;
            }
            if ($searchKeyword !== '') {
                $kw = strtolower($searchKeyword);
                if (
                    strpos($nama, $kw) === false
                    && strpos($bidang, $kw) === false
                    && strpos($username, $kw) === false
                    && strpos($nidn, $kw) === false
                ) {
                    return false;
                }
            }
            return true;
        }));

        $categoryPills = array_merge(['Semua Bidang'], defined('BIDANG_QUBU') ? BIDANG_QUBU : []);
        $title         = 'Direktori Dosen Pembimbing - ' . APP_NAME;
        require __DIR__ . '/../../views/mahasiswa/advisor-directory.php';
    }
}
