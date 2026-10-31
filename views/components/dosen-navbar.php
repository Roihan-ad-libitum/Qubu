<?php

$skipStudentNavbarAutoRender = true;
require_once __DIR__ . '/student-navbar.php';
$skipStudentNavbarAutoRender = false;

$activeDosenNavKey = $activeDosenNavKey ?? 'dashboard';
$dosenNavName      = $dosenNavName ?? ($profile['nama_dosen'] ?? ($data['nama_dosen'] ?? Session::get('nama_lengkap', 'Dosen')));
$dosenNavPhoto     = $dosenNavPhoto ?? ($profile['foto'] ?? ($data['foto'] ?? (Session::get('demo_dosen_profile')['foto'] ?? null)));
$dosenNavExpertise = $dosenNavExpertise ?? ($profile['bidang_keahlian'] ?? ($data['bidang_keahlian'] ?? ''));

renderQubuTopNavbar([
    'homeUrl'        => url('?page=dosen-dashboard'),
    'logoAriaLabel'  => 'Dashboard Dosen Qubu',
    'gradientPrefix' => 'lecturerLogo',
    'navAriaLabel'   => 'Menu Utama Dosen',
    'navLinks'       => [
        ['url' => url('?page=dosen-dashboard'), 'label' => 'Dashboard',   'active' => $activeDosenNavKey === 'dashboard'],
        ['url' => url('?page=dosen-profile'),   'label' => 'Profil Saya', 'active' => $activeDosenNavKey === 'profile'],
    ],
    'showAvatar'    => true,
    'userName'      => (string) $dosenNavName,
    'userRoleLabel' => 'Dosen Pembimbing',
    'userMetaLabel' => $dosenNavExpertise !== '' ? ('Keahlian: ' . $dosenNavExpertise) : '',
    'userPhoto'     => $dosenNavPhoto,
    'dropdownLinks' => [
        [
            'url'     => url('?page=dosen-dashboard'),
            'label'   => 'Dashboard',
            'iconSvg' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>',
        ],
        [
            'url'     => url('?page=dosen-profile'),
            'label'   => 'Profil Saya',
            'iconSvg' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>',
        ],
        [
            'url'        => url('?page=logout'),
            'label'      => 'Keluar',
            'extraClass' => 'dash-dropdown-logout',
            'iconSvg'    => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>',
        ],
    ],
]);
