<?php

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Session.php';
require_once __DIR__ . '/core/Csrf.php';
require_once __DIR__ . '/core/Validator.php';
require_once __DIR__ . '/core/AuthMiddleware.php';
require_once __DIR__ . '/app/Controllers/AuthController.php';
require_once __DIR__ . '/app/Controllers/MahasiswaController.php';
require_once __DIR__ . '/app/Controllers/DosenController.php';
require_once __DIR__ . '/app/Controllers/AdminController.php';
require_once __DIR__ . '/app/Controllers/CompetitionController.php';
require_once __DIR__ . '/app/Controllers/TeamController.php';
require_once __DIR__ . '/app/Controllers/AdvisorController.php';

Session::start();

$page = $_GET['page'] ?? 'home';

switch ($page) {
    case 'home':
        require_once __DIR__ . '/views/home.php';
        break;

    case 'login':
        (new AuthController())->login();
        break;

    case 'logout':
        (new AuthController())->logout();
        break;

    case 'register':
        (new AuthController())->register();
        break;

    case 'profile':
        if (Session::get('role') === ROLE_DOSEN) {
            (new DosenController())->profile();
        } else {
            (new MahasiswaController())->profile();
        }
        break;

    case 'dosen-profile':
        (new DosenController())->profile();
        break;

    case 'mahasiswa-profile':
        (new MahasiswaController())->profile();
        break;

    case 'dashboard':
    case 'mahasiswa-dashboard':
        (new MahasiswaController())->dashboard();
        break;

    case 'api-competitions':
        (new CompetitionController())->apiCompetitions($_GET);
        break;

    case 'lomba-detail':
    case 'detail-lomba':
        (new CompetitionController())->detail($_GET['id'] ?? null);
        break;

    case 'team-posts':
        (new TeamController())->posts();
        break;

    case 'cari-partner':
    case 'find-partners':
        if (($_GET['tab'] ?? '') === 'posts') {
            (new TeamController())->posts();
        } else {
            (new MahasiswaController())->cariPartner();
        }
        break;

    case 'team-post-create':
        (new TeamController())->createPost();
        break;

    case 'team-post-status':
        (new TeamController())->updateStatus();
        break;

    case 'advisor-directory':
    case 'dosen-pembimbing':
        (new AdvisorController())->directory();
        break;

    case 'api-partners':
        (new MahasiswaController())->apiPartners($_GET);
        break;

    case 'lihat-profil':
        (new MahasiswaController())->lihatProfil($_GET['id'] ?? null);
        break;

    case 'dosen-dashboard':
        (new DosenController())->dashboard();
        break;

    case 'admin-dashboard':
        (new AdminController())->dashboard();
        break;

    case 'admin-lomba':
        (new AdminController())->lomba();
        break;

    case 'admin-lomba-form':
        (new AdminController())->lombaForm();
        break;

    case 'admin-lomba-save':
        (new AdminController())->saveLomba();
        break;

    case 'admin-users':
        (new AdminController())->users();
        break;

    case 'admin-dosen':
        (new AdminController())->dosen();
        break;

    default:
        if (Session::has('id_user') && Session::has('role')) {
            redirect(getRoleDashboard(Session::get('role')));
        } else {
            redirect('?page=home');
        }
        break;
}
