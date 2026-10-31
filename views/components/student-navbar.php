<?php

if (!function_exists('renderQubuTopNavbar')) {

    function renderQubuTopNavbar(array $config): void
    {
        $homeUrl        = (string) ($config['homeUrl'] ?? url('?page=mahasiswa-dashboard'));
        $logoAriaLabel  = (string) ($config['logoAriaLabel'] ?? 'Dashboard Qubu');
        $gradientPrefix = (string) ($config['gradientPrefix'] ?? 'qubuNavLogo');
        $navAriaLabel   = (string) ($config['navAriaLabel'] ?? 'Menu Utama');
        $navLinks       = (array) ($config['navLinks'] ?? []);
        $showAvatar     = !empty($config['showAvatar']);
        $userName       = (string) ($config['userName'] ?? 'Pengguna');
        $userRoleLabel  = (string) ($config['userRoleLabel'] ?? '');
        $userMetaLabel  = (string) ($config['userMetaLabel'] ?? '');
        $userPhoto      = $config['userPhoto'] ?? null;
        $dropdownLinks  = (array) ($config['dropdownLinks'] ?? []);
        $photoUrl       = resolveProfilePhotoUrl($userPhoto);
        $hasPhoto       = $photoUrl !== null;
        $blueGradId     = $gradientPrefix . 'Blue';
        $purpleGradId   = $gradientPrefix . 'Purple';
        ?>
        <header class="dash-navbar">
            <div class="dash-nav-container">
                <a href="<?= e($homeUrl) ?>" class="dash-logo-link" aria-label="<?= e($logoAriaLabel) ?>">
                    <svg class="dash-logo-svg" viewBox="0 0 160 55" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <defs>
                            <linearGradient id="<?= e($blueGradId) ?>" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#38bdf8" />
                                <stop offset="100%" stop-color="#2563eb" />
                            </linearGradient>
                            <linearGradient id="<?= e($purpleGradId) ?>" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#c084fc" />
                                <stop offset="100%" stop-color="#818cf8" />
                            </linearGradient>
                        </defs>
                        <circle cx="16" cy="11" r="5.5" fill="url(#<?= e($blueGradId) ?>)" />
                        <circle cx="28" cy="11" r="5.5" fill="url(#<?= e($purpleGradId) ?>)" />
                        <path d="M 10 24 C 7 32, 14 42, 22 42 C 30 42, 36 34, 34 25 C 32 17, 23 17, 18 20 C 13 23, 11 31, 14 36" stroke="url(#<?= e($blueGradId) ?>)" stroke-width="4.5" stroke-linecap="round" fill="none" />
                        <path d="M 22 42 C 26 42, 33 40, 36 34 C 38 29, 36 22, 30 20" stroke="url(#<?= e($purpleGradId) ?>)" stroke-width="4.5" stroke-linecap="round" fill="none" />
                        <path d="M 28 35 L 36 43" stroke="url(#<?= e($purpleGradId) ?>)" stroke-width="4.5" stroke-linecap="round" />
                        <text x="48" y="36" font-family="'Plus Jakarta Sans', system-ui, sans-serif" font-size="28" font-weight="900" fill="#ffffff" letter-spacing="-0.03em">QUBU</text>
                    </svg>
                </a>

                <nav class="dash-nav-menu" aria-label="<?= e($navAriaLabel) ?>">
                    <?php foreach ($navLinks as $item):
                        $isActive = !empty($item['active']);
                        $linkId   = (string) ($item['id'] ?? '');
                    ?>
                        <a
                            href="<?= e((string) $item['url']) ?>"
                            class="dash-nav-link <?= $isActive ? 'is-active' : '' ?>"
                            <?= $linkId !== '' ? 'id="' . e($linkId) . '"' : '' ?>
                            <?= $isActive ? 'aria-current="page"' : '' ?>
                        ><?= e((string) $item['label']) ?></a>
                    <?php endforeach; ?>

                    <?php if ($showAvatar): ?>
                        <div class="dash-avatar-wrapper" id="dash-avatar-wrapper">
                            <button type="button" class="dash-avatar-btn" id="dash-avatar-btn" aria-label="Menu Pengguna" aria-expanded="false" aria-controls="dash-dropdown-menu" title="<?= e($userName) ?>">
                                <?php if ($hasPhoto): ?>
                                    <img src="<?= e($photoUrl) ?>" alt="Foto <?= e($userName) ?>" class="dash-avatar-img" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='block';">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="display: none;">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                <?php else: ?>
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                <?php endif; ?>
                            </button>
                            <div class="dash-dropdown-menu" id="dash-dropdown-menu" role="menu" aria-label="Menu Pengguna">
                                <div class="dash-dropdown-header">
                                    <span class="dash-dropdown-name"><?= e($userName) ?></span>
                                    <?php if ($userRoleLabel !== ''): ?>
                                        <span class="dash-dropdown-role"><?= e($userRoleLabel) ?></span>
                                    <?php endif; ?>
                                    <?php if ($userMetaLabel !== ''): ?>
                                        <span class="dash-dropdown-bidang"><?= e($userMetaLabel) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="dash-dropdown-divider"></div>
                                <?php foreach ($dropdownLinks as $dLink): ?>
                                    <a href="<?= e((string) $dLink['url']) ?>" class="dash-dropdown-item <?= !empty($dLink['extraClass']) ? e((string) $dLink['extraClass']) : '' ?>" role="menuitem">
                                        <?= $dLink['iconSvg'] ?? '' ?>
                                        <span><?= e((string) $dLink['label']) ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?= e(url('?page=login')) ?>" class="dash-nav-link">Masuk</a>
                    <?php endif; ?>
                </nav>
            </div>
        </header>
        <script src="<?= e(url('public/js/student-navbar.js')) ?>?v=<?= (int) @filemtime(__DIR__ . '/../../public/js/student-navbar.js') ?>" defer></script>
        <?php
    }
}

if (empty($skipStudentNavbarAutoRender)) {
    $activeStudentNavKey = $activeStudentNavKey ?? '';
    $studentNavName      = $studentNavName ?? ($namaMahasiswa ?? Session::get('nama_lengkap', 'Mahasiswa'));
    $studentNavPhoto     = $studentNavPhoto ?? ($userFoto ?? ($profile['foto'] ?? ($data['foto'] ?? (Session::get('demo_profile_data')['foto'] ?? null))));
    $studentNavProgram   = $studentNavProgram ?? ($profile['program_studi'] ?? ($data['program_studi'] ?? 'Informatika'));
    $studentNavInterest  = $studentNavInterest ?? ($userBidangMinat ?? ($profile['bidang_minat'] ?? ($data['bidang_minat'] ?? '')));

    renderQubuTopNavbar([
        'homeUrl'        => url('?page=mahasiswa-dashboard'),
        'logoAriaLabel'  => 'Dashboard Qubu',
        'gradientPrefix' => 'studentNavLogo',
        'navAriaLabel'   => 'Menu Utama',
        'navLinks'       => [
            ['url' => url('?page=mahasiswa-dashboard'), 'label' => 'Info Lomba',       'active' => $activeStudentNavKey === 'dashboard'],
            ['url' => url('?page=team-posts'),          'label' => 'Cari Partner',     'active' => $activeStudentNavKey === 'teams', 'id' => 'link-cari-partner'],
            ['url' => url('?page=advisor-directory'),   'label' => 'Dosen Pembimbing', 'active' => $activeStudentNavKey === 'advisors'],
        ],
        'showAvatar'    => Session::has('id_user'),
        'userName'      => (string) $studentNavName,
        'userRoleLabel' => 'Mahasiswa • ' . $studentNavProgram,
        'userMetaLabel' => $studentNavInterest !== '' ? ('Minat: ' . $studentNavInterest) : '',
        'userPhoto'     => $studentNavPhoto,
        'dropdownLinks' => [
            [
                'url'     => url('?page=mahasiswa-profile'),
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
}
