<?php
$activeAdminPage = $activeAdminPage ?? '';
?>
<header class="admin-panel-nav">
    <a class="admin-panel-brand" href="<?= url('?page=admin-dashboard') ?>" aria-label="Dashboard Qubu Admin">
        <svg class="admin-panel-brand-logo" viewBox="0 0 160 55" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <defs>
                <linearGradient id="adminBrandBlue" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#38bdf8" />
                    <stop offset="100%" stop-color="#2563eb" />
                </linearGradient>
                <linearGradient id="adminBrandPurple" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#c084fc" />
                    <stop offset="100%" stop-color="#818cf8" />
                </linearGradient>
            </defs>
            <circle cx="16" cy="11" r="5.5" fill="url(#adminBrandBlue)" />
            <circle cx="28" cy="11" r="5.5" fill="url(#adminBrandPurple)" />
            <path d="M 10 24 C 7 32, 14 42, 22 42 C 30 42, 36 34, 34 25 C 32 17, 23 17, 18 20 C 13 23, 11 31, 14 36"
                  stroke="url(#adminBrandBlue)" stroke-width="4.5" stroke-linecap="round" fill="none" />
            <path d="M 22 42 C 26 42, 33 40, 36 34 C 38 29, 36 22, 30 20"
                  stroke="url(#adminBrandPurple)" stroke-width="4.5" stroke-linecap="round" fill="none" />
            <path d="M 28 35 L 36 43"
                  stroke="url(#adminBrandPurple)" stroke-width="4.5" stroke-linecap="round" />
            <text x="48" y="36" font-family="'Plus Jakarta Sans', system-ui, sans-serif" font-size="28" font-weight="900" fill="#2563eb" letter-spacing="-0.03em">QUBU</text>
        </svg>
        <span class="admin-panel-brand-label">ADMIN</span>
    </a>
    <nav aria-label="Navigasi Admin">
        <a href="<?= url('?page=admin-dashboard') ?>" class="<?= $activeAdminPage === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
        <a href="<?= url('?page=admin-users') ?>" class="<?= $activeAdminPage === 'users' ? 'active' : '' ?>">Pengguna</a>
        <a href="<?= url('?page=admin-dosen') ?>" class="<?= $activeAdminPage === 'dosen' ? 'active' : '' ?>">Dosen Pembimbing</a>
        <a href="<?= url('?page=admin-lomba') ?>" class="<?= $activeAdminPage === 'lomba' ? 'active' : '' ?>">Lomba</a>
    </nav>
    <a class="admin-panel-logout" href="<?= url('?page=logout') ?>">Keluar</a>
</header>
