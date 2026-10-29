<?php
/**
 * View Halaman Utama Cari Partner Qubu (?page=cari-partner)
 * Presisi mengikuti mockup desain Cari Partner:
 * - Top Navbar Qubu dengan menu "Cari Partner" aktif (is-active)
 * - Hero Biru "Temukan Rekan Tim Terbaikmu" + 6 Pills Bidang (Semua Bidang, Frontend, Backend, UI/UX Design, Data Science, Robotic)
 * - Banner Biru Gradasi "Bentuk tim lomba impianmu" + Tombol Placeholder "+ Posting Cari Tim"
 * - Section "Rekomendasi Rekan" (3 Kartu Placeholder Sejajar)
 * - Section "Semua Postingan" + Toolbar Filter (Pencarian & Dropdown Program Studi) + Grid 3 Kolom Kartu Partner
 * - Modal Profil Lengkap Read-Only & Toast Placeholder "Ajak ke Tim"
 */

$title         = $title ?? 'Cari Partner Lomba - ' . APP_NAME;
$extraCss      = $extraCss ?? ['public/css/components.css', 'public/css/dashboard.css', 'public/css/cari-partner.css'];
$searchKeyword = $searchKeyword ?? '';
$activeBidang  = $activeBidang ?? 'Semua Bidang';
$activeProdi   = $activeProdi ?? 'Semua Prodi';
$targetLomba   = $targetLomba ?? null;

$categoryPills = $categoryPills ?? array_merge(
    ['Semua Bidang'],
    defined('BIDANG_QUBU') ? BIDANG_QUBU : ['Frontend', 'Backend', 'UI/UX Design', 'Data Science', 'Robotic']
);
$prodiOptions = $prodiOptions ?? array_merge(
    ['Semua Prodi'],
    defined('PRODI_FASILKOM') ? PRODI_FASILKOM : ['Informatika', 'Sistem Informasi']
);

$partnerList          = $partnerList ?? [];
$semuaKandidatPartner = $semuaKandidatPartner ?? $partnerList;
$rekomendasiPartner   = $rekomendasiPartner ?? array_slice($semuaKandidatPartner, 0, 3);

$matchedIdsMap = [];
foreach ($partnerList as $matchedItem) {
    $matchedIdsMap[(int) ($matchedItem['id_mahasiswa'] ?? 0)] = true;
}

$hasActiveFilter = ($activeBidang !== 'Semua Bidang') || ($activeProdi !== 'Semua Prodi') || ($searchKeyword !== '');

require_once __DIR__ . '/../components/partner-card.php';
require_once __DIR__ . '/../layouts/headers.php';
?>

<div class="dash-page">
    <!-- ========================================================================= -->
    <!-- 1. TOP NAVBAR SIKLUS LOGIN (MENU "CARI PARTNER" AKTIF)                    -->
    <!-- ========================================================================= -->
    <header class="dash-navbar">
        <div class="dash-nav-container">
            <a href="<?= url('?page=mahasiswa-dashboard') ?>" class="dash-logo-link" aria-label="Dashboard Qubu">
                <svg class="dash-logo-svg" viewBox="0 0 160 55" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="cpLogoBlue" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#38bdf8" />
                            <stop offset="100%" stop-color="#2563eb" />
                        </linearGradient>
                        <linearGradient id="cpLogoPurple" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#c084fc" />
                            <stop offset="100%" stop-color="#818cf8" />
                        </linearGradient>
                    </defs>
                    <circle cx="16" cy="11" r="5.5" fill="url(#cpLogoBlue)" />
                    <circle cx="28" cy="11" r="5.5" fill="url(#cpLogoPurple)" />
                    <path d="M 10 24 C 7 32, 14 42, 22 42 C 30 42, 36 34, 34 25 C 32 17, 23 17, 18 20 C 13 23, 11 31, 14 36"
                          stroke="url(#cpLogoBlue)" stroke-width="4.5" stroke-linecap="round" fill="none" />
                    <path d="M 22 42 C 26 42, 33 40, 36 34 C 38 29, 36 22, 30 20"
                          stroke="url(#cpLogoPurple)" stroke-width="4.5" stroke-linecap="round" fill="none" />
                    <path d="M 28 35 L 36 43"
                          stroke="url(#cpLogoPurple)" stroke-width="4.5" stroke-linecap="round" />
                    <text x="48" y="36" font-family="'Plus Jakarta Sans', system-ui, sans-serif" font-size="28" font-weight="900" fill="#ffffff" letter-spacing="-0.03em">QUBU</text>
                </svg>
            </a>

            <nav class="dash-nav-menu" aria-label="Menu Utama">
                <a href="<?= url('?page=mahasiswa-dashboard') ?>" class="dash-nav-link">Info Lomba</a>
                <a href="<?= url('?page=cari-partner') ?>" class="dash-nav-link is-active" id="link-cari-partner">Cari Partner</a>
                <a href="<?= url('?page=dosen-profile') ?>" class="dash-nav-link">Dosen Pembimbing</a>

                <div class="dash-avatar-wrapper" id="dash-avatar-wrapper">
                    <button type="button" class="dash-avatar-btn" id="dash-avatar-btn" aria-label="Menu Pengguna" aria-expanded="false" title="<?= e($namaMahasiswa ?? 'Mahasiswa') ?>">
                        <?php if (!empty($userFoto) && file_exists(__DIR__ . '/../../' . $userFoto)): ?>
                            <img src="<?= e(url($userFoto)) ?>" alt="Avatar" class="dash-avatar-img">
                        <?php else: ?>
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        <?php endif; ?>
                    </button>

                    <div class="dash-dropdown-menu" id="dash-dropdown-menu" role="menu">
                        <div class="dash-dropdown-header">
                            <span class="dash-dropdown-name"><?= e($namaMahasiswa ?? 'Mahasiswa') ?></span>
                            <span class="dash-dropdown-role">Mahasiswa &bull; <?= e($profile['program_studi'] ?? 'Informatika') ?></span>
                            <span class="dash-dropdown-bidang">Minat: <?= e($userBidangMinat ?? 'UI/UX Design') ?></span>
                        </div>
                        <div class="dash-dropdown-divider"></div>
                        <a href="<?= url('?page=mahasiswa-profile') ?>" class="dash-dropdown-item" role="menuitem">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            <span>Edit Profil Saya</span>
                        </a>
                        <a href="<?= url('?page=logout') ?>" class="dash-dropdown-item dash-dropdown-logout" role="menuitem">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                <polyline points="16 17 21 12 16 7"></polyline>
                                <line x1="21" y1="12" x2="9" y2="12"></line>
                            </svg>
                            <span>Keluar (Logout)</span>
                        </a>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <!-- ========================================================================= -->
    <!-- 2. HERO CARI PARTNER ("Temukan Rekan Tim Terbaikmu" + 6 PILLS BIDANG)     -->
    <!-- ========================================================================= -->
    <section class="partner-hero-section">
        <div class="dash-hero-noise"></div>
        <div class="partner-hero-inner">
            <h1 class="partner-hero-title">Temukan Rekan Tim<br>Terbaikmu</h1>

            <div class="partner-pills-row" id="partner-pills-container" role="tablist" aria-label="Filter Bidang Partner">
                <?php foreach ($categoryPills as $pill):
                    $isActivePill = strcasecmp($activeBidang, $pill) === 0;
                ?>
                    <button
                        type="button"
                        role="tab"
                        aria-selected="<?= $isActivePill ? 'true' : 'false' ?>"
                        tabindex="<?= $isActivePill ? '0' : '-1' ?>"
                        class="partner-pill-btn <?= $isActivePill ? 'is-active' : '' ?>"
                        data-bidang="<?= e($pill) ?>"
                    >
                        <?= e($pill) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- 3. KONTAINER UTAMA: BANNER CTA, REKOMENDASI REKAN & SEMUA POSTINGAN       -->
    <!-- ========================================================================= -->
    <main class="partner-main-container" data-api-url="<?= e(url('?page=api-partners')) ?>">

        <!-- Banner Biru "+ Posting Cari Tim" Persis Mockup (Placeholder Visual) -->
        <section class="partner-cta-banner" aria-label="Ajakan Posting Cari Tim">
            <div class="partner-cta-text">
                <h2>Bentuk tim lomba impianmu</h2>
                <p>Temukan rekan terbaik yang sebidang atau lintas prodi di Fasilkom.</p>
            </div>
            <button
                type="button"
                class="btn-posting-placeholder"
                id="btn-posting-cari-tim"
                onclick="showPartnerPlaceholderNotice('Fitur + Posting Cari Tim akan diaktifkan pada modul Manajemen Tim.')"
            >
                + Posting Cari Tim
            </button>
        </section>

        <!-- Konteks Lomba jika user masuk dari tombol "Cari Tim" pada Detail Lomba (?id_lomba=...) -->
        <?php if (!empty($targetLomba)): ?>
            <div class="partner-lomba-context" id="partner-lomba-context">
                <div class="partner-lomba-context-info">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1d4ed8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="8" r="7"></circle>
                        <polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline>
                    </svg>
                    <span>Mencari rekan tim untuk lomba: <strong><?= e($targetLomba['nama_lomba']) ?></strong> (Bidang: <?= e($targetLomba['bidang']) ?>)</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.65rem;">
                    <a href="<?= e(url('?page=lomba-detail&id=' . (int) $targetLomba['id_lomba'])) ?>" class="btn-reset-inline" style="text-decoration: none;">
                        Detail Lomba
                    </a>
                    <a href="<?= e(url('?page=cari-partner')) ?>" class="btn-reset-inline" style="text-decoration: none;">
                        Hapus Konteks Lomba
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- ===================================================================== -->
        <!-- SECTION 1: REKOMENDASI REKAN (3 KARTU PLACEHOLDER SESUAI MOCKUP)       -->
        <!-- ===================================================================== -->
        <?php if (!empty($rekomendasiPartner)): ?>
            <section class="partner-section" id="section-rekomendasi-rekan">
                <div class="partner-section-header">
                    <h2 class="partner-section-title">Rekomendasi Rekan</h2>
                </div>

                <div class="partner-grid-3" id="rekomendasi-partner-grid">
                    <?php foreach ($rekomendasiPartner as $rekMhs): ?>
                        <?php renderPartnerCard($rekMhs, [
                            'extraClass'  => 'rekomendasi-partner-card',
                            'targetLomba' => $targetLomba,
                        ]); ?>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- ===================================================================== -->
        <!-- SECTION 2: SEMUA POSTINGAN + TOOLBAR FILTER & PENCARIAN               -->
        <!-- ===================================================================== -->
        <section class="partner-section" id="section-semua-partner">
            <div class="partner-section-header">
                <h2 class="partner-section-title">Semua Postingan</h2>
                <div class="dash-filter-status">
                    <span class="dash-count-badge" id="partner-count-badge">Menampilkan <?= count($partnerList) ?> Rekan</span>
                </div>
            </div>

            <!-- Toolbar Pencarian Kata Kunci & Dropdown Program Studi -->
            <div class="partner-filter-toolbar" role="search" aria-label="Filter Kandidat Partner">
                <div class="partner-search-wrap">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input
                        type="search"
                        id="partner-search-input"
                        name="q"
                        value="<?= e($searchKeyword) ?>"
                        class="partner-search-input"
                        placeholder="Cari nama mahasiswa, keahlian bidang, prodi, atau kata kunci..."
                        aria-label="Cari kandidat partner"
                        autocomplete="off"
                    >
                </div>

                <div class="partner-prodi-wrap">
                    <label for="partner-prodi-select" class="partner-prodi-label">Program Studi:</label>
                    <select id="partner-prodi-select" name="prodi" class="partner-prodi-select" aria-label="Filter Program Studi">
                        <?php foreach ($prodiOptions as $prodiOpt):
                            $isSelectedProdi = strcasecmp($activeProdi, $prodiOpt) === 0;
                        ?>
                            <option value="<?= e($prodiOpt) ?>" <?= $isSelectedProdi ? 'selected' : '' ?>>
                                <?= e($prodiOpt) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Bar Info Filter Aktif & Tombol Reset -->
            <div class="active-filter-bar" id="active-partner-filter-bar" style="<?= $hasActiveFilter ? 'display: flex;' : 'display: none;' ?> margin-bottom: 1.25rem;">
                <span class="active-filter-text" id="active-partner-filter-label">
                    Filter Aktif:
                    <strong><?= e($activeBidang) ?></strong>
                    &bull; Prodi: <strong><?= e($activeProdi) ?></strong>
                    <?= $searchKeyword !== '' ? ' &bull; Kata kunci: "' . e($searchKeyword) . '"' : '' ?>
                </span>
                <button type="button" class="btn-reset-inline" id="btn-clear-partner-filter">Reset Semua Filter</button>
            </div>

            <!-- Indikator Loading State -->
            <div class="partner-loading-state" id="partner-loading-state" role="status" aria-live="polite">
                <div class="partner-spinner" aria-hidden="true"></div>
                <span>Memuat kandidat rekan tim...</span>
            </div>

            <!-- Grid 3 Kolom Kartu Partner -->
            <div class="partner-grid-3" id="partner-grid">
                <?php foreach ($semuaKandidatPartner as $mhs):
                    $idMhs = (int) ($mhs['id_mahasiswa'] ?? 0);
                    $isMatched = isset($matchedIdsMap[$idMhs]);
                ?>
                    <?php renderPartnerCard($mhs, [
                        'extraClass'  => 'partner-card-item',
                        'hidden'      => !$isMatched,
                        'targetLomba' => $targetLomba,
                    ]); ?>
                <?php endforeach; ?>
            </div>

            <!-- Empty State jika tidak ada partner yang cocok -->
            <div class="dash-empty-state" id="partner-empty-state" style="<?= empty($partnerList) ? 'display: flex;' : 'display: none;' ?>">
                <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <line x1="17" y1="11" x2="23" y2="11"></line>
                </svg>
                <h3>Tidak Ada Kandidat Partner yang Cocok</h3>
                <p id="empty-partner-msg">Coba gunakan kata kunci lain, pilih bidang minat berbeda, atau ubah filter Program Studi.</p>
                <button type="button" class="btn-reset-filter" id="btn-reset-empty-partner">Reset Filter Partner</button>
            </div>
        </section>
    </main>
</div>

<!-- ========================================================================= -->
<!-- 4. MODAL PROFIL LENGKAP KANDIDAT PARTNER (READ-ONLY)                       -->
<!-- ========================================================================= -->
<div class="modal-backdrop" id="partner-profile-modal" role="dialog" aria-modal="true" aria-labelledby="pmodal-name" aria-hidden="true">
    <div class="modal-container" style="max-width: 580px;">
        <button type="button" class="modal-close-btn" id="pmodal-close-btn" aria-label="Tutup Modal Profil" onclick="closePartnerProfileModal()">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>

        <div style="display: flex; flex-direction: column; gap: 1.25rem; padding: 0.5rem;">
            <!-- Header Identitas Kandidat -->
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div class="pcard-avatar" id="pmodal-avatar-box" style="width: 64px; height: 64px; font-size: 1.35rem;">
                    <span id="pmodal-avatar-initials">RF</span>
                    <img id="pmodal-avatar-img" src="" alt="" class="pcard-avatar-img" style="display: none;">
                </div>
                <div>
                    <h2 id="pmodal-name" style="font-size: 1.35rem; font-weight: 800; color: #0f172a; margin: 0 0 4px 0;">Nama Mahasiswa</h2>
                    <p id="pmodal-sub" style="font-size: 0.9rem; font-weight: 600; color: #475569; margin: 0;">Informatika &bull; Angkatan 2025</p>
                </div>
            </div>

            <!-- Tabel Kapsul Detail Profil Read-Only -->
            <div class="lomba-meta-table">
                <div class="lomba-meta-row">
                    <span class="meta-label">NPM</span>
                    <span class="badge-pill badge-blue" id="pmodal-npm">-</span>
                </div>
                <div class="lomba-meta-row">
                    <span class="meta-label">Program Studi</span>
                    <span class="badge-pill badge-purple" id="pmodal-prodi">Informatika</span>
                </div>
                <div class="lomba-meta-row">
                    <span class="meta-label">Angkatan</span>
                    <span class="badge-pill badge-green" id="pmodal-angkatan">2025</span>
                </div>
                <div class="lomba-meta-row">
                    <span class="meta-label">Bidang Keahlian</span>
                    <div id="pmodal-skills" class="pcard-skills-list" style="justify-content: flex-end;"></div>
                </div>
            </div>

            <!-- Bio Singkat Mahasiswa -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 1rem;">
                <span style="display: block; font-size: 0.76rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 0.35rem;">Tentang / Bio Kolaborasi</span>
                <p id="pmodal-bio" style="font-size: 0.92rem; line-height: 1.6; color: #334155; margin: 0;">
                    Siap berkolaborasi dalam kompetisi tingkat nasional dan pengembangan proyek tim lintas prodi.
                </p>
            </div>

            <!-- Tombol Aksi Modal (Buka Halaman Profil Mandiri & Placeholder Ajak ke Tim) -->
            <div style="display: flex; flex-wrap: wrap; gap: 0.75rem;">
                <a href="#" id="pmodal-full-link" class="btn-modal-secondary" style="flex: 1; justify-content: center; text-decoration: none;">
                    <span>Halaman Profil Lengkap</span>
                </a>
                <button
                    type="button"
                    id="pmodal-btn-invite"
                    class="btn-pcard-green"
                    style="flex: 1; height: 44px; font-size: 0.9rem;"
                    onclick="showPartnerPlaceholderNotice('Fitur Ajak ke Tim akan diaktifkan pada modul Manajemen Tim.')"
                >
                    Ajak ke Tim
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Pemberitahuan Placeholder -->
<div class="partner-toast" id="partner-toast" role="status" aria-live="polite">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="12" y1="16" x2="12" y2="12"></line>
        <line x1="12" y1="8" x2="12.01" y2="8"></line>
    </svg>
    <span id="partner-toast-text">Fitur Ajak ke Tim akan diaktifkan pada modul Manajemen Tim.</span>
</div>

<script src="<?= url('public/js/cari-partner.js') ?>" defer></script>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>

