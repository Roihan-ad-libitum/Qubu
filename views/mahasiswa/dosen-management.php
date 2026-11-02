<?php
/**
 * View Halaman Utama Cari Dosen Pembimbing Qubu (?page=cari-dosen / ?page=dosen-pembimbing)
 * Presisi mengikuti mockup desain Cari Dosen Pembimbing:
 * - Top Navbar Qubu dengan menu "Dosen Pembimbing" aktif (is-active)
 * - Hero Biru "Dapatkan Dosen Pembimbing Terbaikmu" + 6 Pills Bidang (Semua Bidang, Frontend, Backend, UI/UX Design, Data Science, Robotic)
 * - Section "Rekomendasi Pembimbing" (3 Kartu Dosen yang `bersedia` & memiliki bidang keahlian yang sama)
 * - Section "Semua Dosen Pembimbing" + Toolbar Pencarian Nama Dosen + Grid 3 Kolom Kartu Dosen
 * - Indikator Loading State & Empty State Kontekstual
 */

$title         = $title ?? 'Cari Dosen Pembimbing - ' . APP_NAME;
$extraCss      = $extraCss ?? ['public/css/components.css', 'public/css/dashboard.css', 'public/css/cari-dosen.css'];
$searchKeyword = $searchKeyword ?? '';
$activeBidang  = $activeBidang ?? 'Semua Bidang';
$targetLomba   = $targetLomba ?? null;

$categoryPills = $categoryPills ?? array_merge(
    ['Semua Bidang'],
    defined('BIDANG_QUBU') ? BIDANG_QUBU : ['Frontend', 'Backend', 'UI/UX Design', 'Data Science', 'Robotic']
);

$dosenList        = $dosenList ?? [];
$semuaDosen       = $semuaDosen ?? $dosenList;
$rekomendasiDosen = $rekomendasiDosen ?? [];
$timMahasiswa     = $timMahasiswa ?? [];
$lombaList        = $lombaList ?? [];

$matchedIdsMap = [];
foreach ($dosenList as $matchedItem) {
    $matchedIdsMap[(int) ($matchedItem['id_dosen'] ?? 0)] = true;
}

$hasActiveFilter = ($activeBidang !== 'Semua Bidang') || ($searchKeyword !== '');

$emptyFilterParts = [];
if ($activeBidang !== 'Semua Bidang') {
    $emptyFilterParts[] = 'bidang keahlian "' . $activeBidang . '"';
}
if ($searchKeyword !== '') {
    $emptyFilterParts[] = 'kata kunci "' . $searchKeyword . '"';
}
$initialEmptyMessage = !empty($emptyFilterParts)
    ? ('Tidak ditemukan dosen pembimbing untuk ' . implode(' dan ', $emptyFilterParts) . '. Coba pilih bidang lain atau reset filter.')
    : 'Belum ada dosen pembimbing yang cocok dengan pencarian Anda.';

require_once __DIR__ . '/../components/dosen-card.php';
require_once __DIR__ . '/../layouts/headers.php';
?>

<div class="dash-page">
    <!-- ========================================================================= -->
    <!-- 1. TOP NAVBAR SIKLUS LOGIN (MENU "DOSEN PEMBIMBING" AKTIF)               -->
    <!-- ========================================================================= -->
    <header class="dash-navbar">
        <div class="dash-nav-container">
            <a href="<?= url('?page=mahasiswa-dashboard') ?>" class="dash-logo-link" aria-label="Dashboard Qubu">
                <svg class="dash-logo-svg" viewBox="0 0 160 55" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="dpLogoBlue" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#38bdf8" />
                            <stop offset="100%" stop-color="#2563eb" />
                        </linearGradient>
                        <linearGradient id="dpLogoPurple" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#c084fc" />
                            <stop offset="100%" stop-color="#818cf8" />
                        </linearGradient>
                    </defs>
                    <circle cx="16" cy="11" r="5.5" fill="url(#dpLogoBlue)" />
                    <circle cx="28" cy="11" r="5.5" fill="url(#dpLogoPurple)" />
                    <path d="M 10 24 C 7 32, 14 42, 22 42 C 30 42, 36 34, 34 25 C 32 17, 23 17, 18 20 C 13 23, 11 31, 14 36"
                          stroke="url(#dpLogoBlue)" stroke-width="4.5" stroke-linecap="round" fill="none" />
                    <path d="M 22 42 C 26 42, 33 40, 36 34 C 38 29, 36 22, 30 20"
                          stroke="url(#dpLogoPurple)" stroke-width="4.5" stroke-linecap="round" fill="none" />
                    <path d="M 28 35 L 36 43"
                          stroke="url(#dpLogoPurple)" stroke-width="4.5" stroke-linecap="round" />
                    <text x="48" y="36" font-family="'Plus Jakarta Sans', system-ui, sans-serif" font-size="28" font-weight="900" fill="#ffffff" letter-spacing="-0.03em">QUBU</text>
                </svg>
            </a>

            <nav class="dash-nav-menu" aria-label="Menu Utama">
                <a href="<?= url('?page=mahasiswa-dashboard') ?>" class="dash-nav-link">Info Lomba</a>
                <a href="<?= url('?page=cari-partner') ?>" class="dash-nav-link" id="link-cari-partner">Cari Partner</a>
                <a href="<?= url('?page=cari-dosen') ?>" class="dash-nav-link is-active" id="link-dosen-pembimbing">Dosen Pembimbing</a>

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
                            <span class="dash-dropdown-bidang">Minat: <?= e($userBidangMinat ?? 'Frontend') ?></span>
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
    <!-- 2. HERO CARI DOSEN ("Dapatkan Dosen Pembimbing Terbaikmu" + 6 PILLS)      -->
    <!-- ========================================================================= -->
    <section class="dosen-hero-section">
        <div class="dash-hero-noise"></div>
        <div class="dosen-hero-inner">
            <h1 class="dosen-hero-title">Dapatkan Dosen<br>Pembimbing Terbaikmu</h1>

            <div class="dosen-pills-row" id="dosen-pills-container" role="tablist" aria-label="Filter Bidang Keahlian Dosen">
                <?php foreach ($categoryPills as $pill):
                    $isActivePill = strcasecmp($activeBidang, $pill) === 0;
                ?>
                    <button
                        type="button"
                        role="tab"
                        aria-selected="<?= $isActivePill ? 'true' : 'false' ?>"
                        tabindex="<?= $isActivePill ? '0' : '-1' ?>"
                        class="dosen-pill-btn <?= $isActivePill ? 'is-active' : '' ?>"
                        data-bidang="<?= e($pill) ?>"
                    >
                        <?= e($pill) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- 3. KONTAINER UTAMA: REKOMENDASI PEMBIMBING & SEMUA DOSEN PEMBIMBING       -->
    <!-- ========================================================================= -->
    <main
        class="dosen-main-container"
        id="dosen-main-container"
        data-api-url="<?= e(url('?page=api-dosen')) ?>"
        data-id-lomba="<?= !empty($targetLomba['id_lomba']) ? (int) $targetLomba['id_lomba'] : '' ?>"
    >
        <!-- Flash Alert (Sukses / Error) -->
        <?php if (!empty($success)): ?>
            <div class="dosen-flash-alert dosen-flash-success" role="alert" id="dosen-flash-success">
                <span><?= e($success) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="dosen-flash-alert dosen-flash-error" role="alert" id="dosen-flash-error">
                <span><?= e($error) ?></span>
            </div>
        <?php endif; ?>

        <!-- ===================================================================== -->
        <!-- SECTION 1: REKOMENDASI PEMBIMBING (3 KARTU BERSEDIA & SEBIDANG)       -->
        <!-- ===================================================================== -->
        <?php if (!empty($rekomendasiDosen)): ?>
            <section class="dosen-section" id="section-rekomendasi-dosen">
                <div class="dosen-section-header">
                    <h2 class="dosen-section-title">Rekomendasi Pembimbing</h2>
                </div>

                <div class="dosen-grid-3" id="rekomendasi-dosen-grid">
                    <?php foreach ($rekomendasiDosen as $rekDosen): ?>
                        <?php renderDosenCard($rekDosen, [
                            'extraClass' => 'rekomendasi-dosen-card',
                        ]); ?>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- ===================================================================== -->
        <!-- SECTION 2: SEMUA DOSEN PEMBIMBING + TOOLBAR PENCARIAN NAMA            -->
        <!-- ===================================================================== -->
        <section class="dosen-section" id="section-semua-dosen">
            <div class="dosen-section-header">
                <h2 class="dosen-section-title">Semua Dosen Pembimbing</h2>
                <div class="dash-filter-status">
                    <span class="dash-count-badge" id="dosen-count-badge">Menampilkan <?= count($dosenList) ?> Dosen</span>
                </div>
            </div>

            <!-- Toolbar Pencarian Nama Dosen / Bidang Keahlian -->
            <div class="dosen-filter-toolbar" role="search" aria-label="Pencarian Dosen Pembimbing">
                <div class="dosen-search-wrap">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input
                        type="search"
                        id="dosen-search-input"
                        name="q"
                        value="<?= e($searchKeyword) ?>"
                        class="dosen-search-input"
                        placeholder="Cari nama dosen pembimbing, NIDN, atau bidang keahlian..."
                        aria-label="Cari nama dosen pembimbing"
                        autocomplete="off"
                    >
                </div>
            </div>

            <!-- Bar Info Filter Aktif & Tombol Reset -->
            <div class="active-filter-bar" id="active-dosen-filter-bar" style="<?= $hasActiveFilter ? 'display: flex;' : 'display: none;' ?> margin-bottom: 1.25rem;">
                <span class="active-filter-text" id="active-dosen-filter-label">
                    Filter Aktif:
                    <strong><?= e($activeBidang) ?></strong>
                    <?= $searchKeyword !== '' ? ' &bull; Kata kunci: "' . e($searchKeyword) . '"' : '' ?>
                </span>
                <button type="button" class="btn-reset-inline" id="btn-clear-dosen-filter">Reset Semua Filter</button>
            </div>

            <!-- Indikator Loading State -->
            <div class="dosen-loading-state" id="dosen-loading-state" role="status" aria-live="polite">
                <div class="dosen-spinner" aria-hidden="true"></div>
                <span>Memuat daftar dosen pembimbing...</span>
            </div>

            <!-- Grid 3 Kolom Kartu Dosen Pembimbing -->
            <div class="dosen-grid-3" id="dosen-grid">
                <?php foreach ($semuaDosen as $dosen):
                    $idDosen = (int) ($dosen['id_dosen'] ?? 0);
                    $isMatched = isset($matchedIdsMap[$idDosen]);
                ?>
                    <?php renderDosenCard($dosen, [
                        'extraClass' => 'dosen-card-item',
                        'hidden'     => !$isMatched,
                    ]); ?>
                <?php endforeach; ?>
            </div>

            <!-- Empty State jika tidak ada dosen di bidang tersebut -->
            <div
                class="dash-empty-state dosen-empty-state"
                id="dosen-empty-state"
                role="status"
                aria-live="polite"
                style="<?= empty($dosenList) ? 'display: flex;' : 'display: none;' ?>"
            >
                <div class="dosen-empty-icon" aria-hidden="true">
                    <svg width="46" height="46" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <line x1="17" y1="11" x2="23" y2="11"></line>
                    </svg>
                </div>
                <h3 id="empty-dosen-title">Belum Ada Dosen Pembimbing yang Sesuai</h3>
                <p id="empty-dosen-msg"><?= e($initialEmptyMessage) ?></p>
                <button
                    type="button"
                    class="btn-reset-filter"
                    id="btn-reset-empty-dosen"
                    onclick="if (typeof window.resetDosenFilters === 'function') window.resetDosenFilters();"
                >
                    Tampilkan Semua Dosen / Reset Filter
                </button>
            </div>
        </section>
    </main>
</div>

<script src="<?= url('public/js/cari-dosen.js') ?>" defer></script>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>

