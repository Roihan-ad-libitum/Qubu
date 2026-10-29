<?php
/**
 * View Dashboard Utama Mahasiswa / Peserta Qubu
 * Presisi mengikuti mockup Figma UI:
 * - Top Navbar di kanan atas (Info Lomba, Cari Partner, Dosen Pembimbing, Avatar)
 * - Hero Banner (Hi {Nama}! Ayo Cari Lomba Impianmu, Search bar, Category pills)
 * - Section Rekomendasi Rekan (Sesuai bidang minat profil user)
 * - Section Semua Lomba (Katalog lengkap dengan filter dinamis)
 * - Modal Detail Lomba interaktif
 */
$title = $title ?? 'Dashboard Info Lomba - ' . APP_NAME;
$extraCss = ['public/css/components.css', 'public/css/dashboard.css'];
$searchKeyword = $searchKeyword ?? '';
$activeBidang  = $activeBidang ?? 'Semua Bidang';
$categoryPills = $categoryPills ?? array_merge(
    ['Semua Bidang'],
    defined('BIDANG_QUBU') ? BIDANG_QUBU : ['Frontend', 'Backend', 'UI/UX Design', 'Data Science', 'Robotic']
);

// Helper format tanggal bahasa Indonesia persis mockup ("15 Juli 2025")
$formatTanggalIndo = function (?string $datetime): string {
    if (empty($datetime)) return '-';
    $timestamp = strtotime($datetime);
    if (!$timestamp) return '-';
    $bulanIndo = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    $d = date('j', $timestamp);
    $m = (int)date('n', $timestamp);
    $y = date('Y', $timestamp);
    return $d . ' ' . ($bulanIndo[$m] ?? date('M', $timestamp)) . ' ' . $y;
};

// Helper format partisipasi persis mockup ("Individu & Tim")
$formatPartisipasi = function (?string $partisipasi): string {
    if ($partisipasi === 'Individu/Tim') {
        return 'Individu & Tim';
    }
    return (string)$partisipasi;
};

require_once __DIR__ . '/../components/lomba-card.php';
require_once __DIR__ . '/../layouts/headers.php';
?>

<div class="dash-page">
    <!-- ========================================================================= -->
    <!-- 1. TOP NAVBAR SIKLUS LOGIN (LOGO KIRI, MENU & PROFIL KANAN ATAS)          -->
    <!-- ========================================================================= -->
    <header class="dash-navbar">
        <div class="dash-nav-container">
            <!-- Logo QUBU di Kiri -->
            <a href="<?= url('?page=mahasiswa-dashboard') ?>" class="dash-logo-link" aria-label="Dashboard Qubu">
                <svg class="dash-logo-svg" viewBox="0 0 160 55" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="navLogoBlue" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#38bdf8" />
                            <stop offset="100%" stop-color="#2563eb" />
                        </linearGradient>
                        <linearGradient id="navLogoPurple" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#c084fc" />
                            <stop offset="100%" stop-color="#818cf8" />
                        </linearGradient>
                    </defs>
                    <circle cx="16" cy="11" r="5.5" fill="url(#navLogoBlue)" />
                    <circle cx="28" cy="11" r="5.5" fill="url(#navLogoPurple)" />
                    <path d="M 10 24 C 7 32, 14 42, 22 42 C 30 42, 36 34, 34 25 C 32 17, 23 17, 18 20 C 13 23, 11 31, 14 36" 
                          stroke="url(#navLogoBlue)" stroke-width="4.5" stroke-linecap="round" fill="none" />
                    <path d="M 22 42 C 26 42, 33 40, 36 34 C 38 29, 36 22, 30 20" 
                          stroke="url(#navLogoPurple)" stroke-width="4.5" stroke-linecap="round" fill="none" />
                    <path d="M 28 35 L 36 43" 
                          stroke="url(#navLogoPurple)" stroke-width="4.5" stroke-linecap="round" />
                    <text x="48" y="36" font-family="'Plus Jakarta Sans', system-ui, sans-serif" font-size="28" font-weight="900" fill="#ffffff" letter-spacing="-0.03em">QUBU</text>
                </svg>
            </a>

            <!-- Menu Navigasi di Kanan Atas Persis Mockup -->
            <nav class="dash-nav-menu" aria-label="Menu Utama">
                <a href="<?= url('?page=mahasiswa-dashboard') ?>" class="dash-nav-link is-active">Info Lomba</a>
                <a href="<?= url('?page=cari-partner') ?>" class="dash-nav-link" id="link-cari-partner">Cari Partner</a>
                <a href="<?= url('?page=cari-dosen') ?>" class="dash-nav-link" id="link-dosen-pembimbing">Dosen Pembimbing</a>

                <!-- Profil Avatar User -->
                <div class="dash-avatar-wrapper" id="dash-avatar-wrapper">
                    <button type="button" class="dash-avatar-btn" id="dash-avatar-btn" aria-label="Menu Pengguna" aria-expanded="false" title="<?= e($namaMahasiswa) ?>">
                        <?php if (!empty($userFoto) && file_exists(__DIR__ . '/../../' . $userFoto)): ?>
                            <img src="<?= e(url($userFoto)) ?>" alt="Avatar" class="dash-avatar-img">
                        <?php else: ?>
                            <!-- Ikon Siluet Bulat Outline Persis Mockup -->
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        <?php endif; ?>
                    </button>

                    <!-- Dropdown Menu Profil -->
                    <div class="dash-dropdown-menu" id="dash-dropdown-menu" role="menu">
                        <div class="dash-dropdown-header">
                            <span class="dash-dropdown-name"><?= e($namaMahasiswa) ?></span>
                            <span class="dash-dropdown-role">Mahasiswa &bull; <?= e($profile['program_studi'] ?? 'Informatika') ?></span>
                            <span class="dash-dropdown-bidang">Minat: <?= e($userBidangMinat) ?></span>
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
    <!-- 2. HERO GREETING & SEARCH BANNER (LATAR BIRU GRADASI NOISE FIGMA)         -->
    <!-- ========================================================================= -->
    <section class="dash-hero-section">
        <div class="dash-hero-noise"></div>
        <div class="dash-hero-content">
            <!-- Headline: Hi Fitria! Ayo Cari Lomba Impianmu -->
            <h1 class="dash-hero-greeting">Hi <?= e($userFirstName) ?>!</h1>
            <h2 class="dash-hero-subgreeting">Ayo Cari Lomba Impianmu</h2>

            <!-- Bar Pencarian Pill dengan Tombol Biru "Cari" -->
            <form action="javascript:void(0);" class="dash-search-form" id="dash-search-form" role="search">
                <div class="dash-search-box">
                    <span class="dash-search-icon" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </span>
                    <input 
                        type="text" 
                        id="dash-search-input" 
                        name="q"
                        value="<?= e($searchKeyword) ?>"
                        class="dash-search-input" 
                        placeholder="Cari nama lomba, bidang, atau penyelenggara..." 
                        aria-label="Cari nama lomba atau bidang"
                        autocomplete="off"
                    >
                    <button type="submit" class="dash-btn-cari" id="dash-btn-cari">
                        Cari
                    </button>
                </div>
            </form>

            <!-- Kategori Filter Pills di Bawah Search Bar (WAI-ARIA Tablist & Keyboard Accessible) -->
            <div class="dash-pills-row" id="dash-pills-container" role="tablist" aria-label="Filter Kategori Bidang">
                <?php foreach ($categoryPills as $idx => $pill):
                    $isPillActive = (strcasecmp((string) $activeBidang, (string) $pill) === 0)
                        || ($idx === 0 && empty($activeBidang));
                ?>
                    <button 
                        type="button" 
                        id="pill-tab-<?= (int) $idx ?>"
                        class="dash-pill-btn <?= $isPillActive ? 'is-active' : '' ?>" 
                        data-bidang="<?= e($pill) ?>"
                        role="tab"
                        aria-selected="<?= $isPillActive ? 'true' : 'false' ?>"
                        aria-controls="semua-lomba-grid"
                        tabindex="<?= $isPillActive ? '0' : '-1' ?>"
                    >
                        <?= e($pill) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- 3. KONTEN UTAMA DASHBOARD                                                 -->
    <!-- ========================================================================= -->
    <main class="dash-main-container">

        <!-- ===================================================================== -->
        <!-- SECTION 1: REKOMENDASI REKAN (BIDANG SAMA DENGAN PROFIL MAHASISWA)    -->
        <!-- ===================================================================== -->
        <section class="dash-section" id="section-rekomendasi">
            <div class="dash-section-header">
                <div>
                    <h2 class="dash-section-title">Rekomendasi Rekan</h2>
                    <p class="dash-section-desc">
                        Rekomendasi disesuaikan dengan bidang peminatan profilmu: 
                        <strong class="dash-badge-user-minat"><?= e($userBidangMinat) ?></strong>
                    </p>
                </div>

                <!-- Tab Pemilih: Lomba Rekomendasi vs Rekan Mahasiswa -->
                <div class="dash-tab-switch" role="tablist">
                    <button type="button" class="dash-tab-btn is-active" id="tab-rekan-lomba" onclick="switchRekanTab('lomba')">
                        Lomba Sesuai Minat (<?= count($rekomendasiLomba) ?>)
                    </button>
                    <button type="button" class="dash-tab-btn" id="tab-rekan-teman" onclick="switchRekanTab('teman')">
                        Rekan Mahasiswa (<?= count($rekomendasiRekan) ?>)
                    </button>
                </div>
            </div>

            <!-- TAMPILAN 1A: KARTU LOMBA REKOMENDASI (HORIZONTAL SCROLLABLE SLIDER) -->
            <div class="dash-slider-wrapper" id="view-rekan-lomba">
                <!-- Tombol Navigasi Panah Slider Kiri & Kanan -->
                <button type="button" class="dash-slider-nav nav-prev" onclick="scrollSlider('rekan-slider', -340)" aria-label="Geser ke Kiri">‹</button>
                <button type="button" class="dash-slider-nav nav-next" onclick="scrollSlider('rekan-slider', 340)" aria-label="Geser ke Kanan">›</button>

                <div class="dash-slider-track" id="rekan-slider">
                    <?php if (!empty($rekomendasiLomba)): ?>
                        <?php foreach ($rekomendasiLomba as $item): ?>
                            <?php renderLombaCard($item, ['mode' => 'modal']); ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="dash-empty-state">
                            <p>Belum ada rekomendasi lomba untuk bidang minatmu saat ini.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- TAMPILAN 1B: KARTU TEMAN REKAN TIM MAHASISWA DENGAN BIDANG SAMA (TAB 2) -->
            <div class="dash-slider-wrapper" id="view-rekan-teman" style="display: none;">
                <button type="button" class="dash-slider-nav nav-prev" onclick="scrollSlider('teman-slider', -340)" aria-label="Geser ke Kiri">‹</button>
                <button type="button" class="dash-slider-nav nav-next" onclick="scrollSlider('teman-slider', 340)" aria-label="Geser ke Kanan">›</button>

                <div class="dash-slider-track" id="teman-slider">
                    <?php if (!empty($rekomendasiRekan)): ?>
                        <?php foreach ($rekomendasiRekan as $rekan): ?>
                            <article class="rekan-student-card">
                                <div class="rekan-card-top">
                                    <div class="rekan-avatar-circle">
                                        <?php if (!empty($rekan['foto']) && file_exists(__DIR__ . '/../../' . $rekan['foto'])): ?>
                                            <img src="<?= e(url($rekan['foto'])) ?>" alt="<?= e($rekan['nama_mahasiswa']) ?>" class="rekan-avatar-img">
                                        <?php else: ?>
                                            <span class="rekan-avatar-initials">
                                                <?= e(strtoupper(substr((string) ($rekan['nama_mahasiswa'] ?? ''), 0, 2))) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="rekan-info">
                                        <h4 class="rekan-name"><?= e($rekan['nama_mahasiswa']) ?></h4>
                                        <span class="rekan-study"><?= e($rekan['program_studi']) ?> &bull; <?= e($rekan['angkatan']) ?></span>
                                    </div>
                                </div>

                                <div class="rekan-skill-box">
                                    <span class="rekan-skill-label">Bidang Minat:</span>
                                    <span class="badge-pill badge-orange"><?= e($rekan['bidang_minat']) ?></span>
                                </div>

                                <p class="rekan-bio">
                                    <?= !empty($rekan['bio']) ? e($rekan['bio']) : 'Mahasiswa aktif bersemangat mengikuti kompetisi teknologi.' ?>
                                </p>

                                <div class="rekan-card-actions">
                                    <a href="mailto:<?= e($rekan['email']) ?>?subject=Ajakan%20Kolaborasi%20Lomba%20Qubu" class="btn-card-collab">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="9" cy="7" r="4"></circle>
                                            <line x1="19" y1="8" x2="19" y2="14"></line>
                                            <line x1="22" y1="11" x2="16" y2="11"></line>
                                        </svg>
                                        <span>Ajak Jadi Partner</span>
                                    </a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="dash-empty-state">
                            <p>Belum ada rekan mahasiswa terdaftar pada bidang peminatan ini.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- ===================================================================== -->
        <!-- SECTION 2: SEMUA LOMBA (KATALOG GRID LENGKAP DENGAN FILTER AKTIF)     -->
        <!-- ===================================================================== -->
        <?php
        $semuaKatalogCards = !empty($katalogLengkap) ? $katalogLengkap : $semuaLomba;
        $matchedIdsSet = array_flip(array_map(fn($r) => (int) ($r['id_lomba'] ?? 0), $semuaLomba));
        $hasInitialFilter = ($activeBidang !== 'Semua Bidang') || ($searchKeyword !== '');
        $isInitialEmpty = count($semuaLomba) === 0;

        if ($searchKeyword !== '' && $activeBidang !== 'Semua Bidang') {
            $initialEmptyDesc = 'Tidak ditemukan lomba dengan kata kunci "' . $searchKeyword . '" pada bidang "' . $activeBidang . '".';
        } elseif ($searchKeyword !== '') {
            $initialEmptyDesc = 'Tidak ditemukan lomba yang cocok dengan kata kunci "' . $searchKeyword . '".';
        } elseif ($activeBidang !== 'Semua Bidang') {
            $initialEmptyDesc = 'Belum ada lomba yang tersedia pada bidang "' . $activeBidang . '" saat ini.';
        } else {
            $initialEmptyDesc = 'Coba kata kunci lain atau pilih filter kategori "Semua Bidang".';
        }
        ?>
        <section class="dash-section" id="section-semua-lomba">
            <div class="dash-section-header">
                <div>
                    <h2 class="dash-section-title">Semua Lomba</h2>
                    <p class="dash-section-desc">Eksplorasi seluruh kompetisi nasional dan internasional yang tersedia di Qubu.</p>
                </div>
                <div class="dash-count-badge" id="lomba-count-badge" aria-live="polite" aria-atomic="true">
                    Menampilkan <span id="count-number"><?= count($semuaLomba) ?></span> Lomba
                </div>
            </div>

            <!-- Bar Status Kombinasi Filter Bidang + Kata Kunci Aktif -->
            <div class="dash-active-filter-bar" id="active-filter-bar" aria-live="polite" style="<?= $hasInitialFilter ? 'display: flex;' : 'display: none;' ?>">
                <div class="dash-active-filter-info" id="active-filter-text">
                    <span class="active-filter-label">Filter Aktif:</span>
                    <span class="active-filter-chip" id="chip-active-bidang" style="<?= $activeBidang !== 'Semua Bidang' ? 'display: inline-flex;' : 'display: none;' ?>">
                        Bidang: <strong id="chip-bidang-val"><?= e($activeBidang) ?></strong>
                    </span>
                    <span class="active-filter-chip" id="chip-active-query" style="<?= $searchKeyword !== '' ? 'display: inline-flex;' : 'display: none;' ?>">
                        Kata Kunci: <strong id="chip-query-val">"<?= e($searchKeyword) ?>"</strong>
                    </span>
                </div>
                <button type="button" class="btn-clear-inline" id="btn-clear-inline" onclick="resetFilters()">
                    Reset Filter &times;
                </button>
            </div>

            <!-- Grid 2-3 Kolom Semua Lomba (Seluruh kartu katalog dimuat di DOM untuk sinkronisasi instan) -->
            <div class="semua-lomba-grid" id="semua-lomba-grid" role="tabpanel" aria-label="Daftar Kompetisi" style="<?= $isInitialEmpty ? 'display: none;' : 'display: grid;' ?>">
                <?php foreach ($semuaKatalogCards as $item): ?>
                    <?php
                    $itemId = (int) ($item['id_lomba'] ?? 0);
                    $isVisible = isset($matchedIdsSet[$itemId]);
                    renderLombaCard($item, [
                        'extraClass' => 'grid-card-item',
                        'mode'       => 'modal',
                        'hidden'     => !$isVisible,
                    ]);
                    ?>
                <?php endforeach; ?>
            </div>

            <!-- Pesan Kosong (Empty State UI) Jika Filter / Pencarian Tidak Menemukan Hasil -->
            <div class="dash-empty-search" id="empty-search-state" role="status" aria-live="polite" style="<?= $isInitialEmpty ? 'display: flex;' : 'display: none;' ?>">
                <div class="dash-empty-illustration" aria-hidden="true">
                    <svg width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="32" cy="32" r="30" fill="#EFF6FF" stroke="#BFDBFE" stroke-width="2" stroke-dasharray="4 4"/>
                        <rect x="18" y="16" width="24" height="30" rx="5" fill="#DBEAFE" stroke="#3B82F6" stroke-width="2"/>
                        <line x1="23" y1="24" x2="35" y2="24" stroke="#2563EB" stroke-width="2" stroke-linecap="round"/>
                        <line x1="23" y1="30" x2="31" y2="30" stroke="#60A5FA" stroke-width="2" stroke-linecap="round"/>
                        <circle cx="39" cy="39" r="9" fill="#FFFFFF" stroke="#1D4ED8" stroke-width="2.5"/>
                        <line x1="45.5" y1="45.5" x2="51" y2="51" stroke="#1D4ED8" stroke-width="3" stroke-linecap="round"/>
                        <path d="M36.5 39H41.5" stroke="#EF4444" stroke-width="2.2" stroke-linecap="round"/>
                    </svg>
                </div>
                <h3 id="empty-search-title">Tidak ada lomba yang sesuai</h3>
                <p id="empty-search-desc"><?= e($initialEmptyDesc) ?></p>
                <div class="dash-empty-actions">
                    <button type="button" class="btn-reset-filter" id="btn-reset-filter" onclick="resetFilters()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <polyline points="1 4 1 10 7 10"></polyline>
                            <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
                        </svg>
                        <span>Tampilkan Semua Lomba / Reset Filter</span>
                    </button>
                </div>
            </div>
        </section>
    </main>

    <!-- ========================================================================= -->
    <!-- 4. MODAL DETAIL LOMBA INTERAKTIF (HERO POSTER HD, COUNTDOWN, CTA & SHARE) -->
    <!-- ========================================================================= -->
    <div class="dash-modal-overlay" id="detail-modal-overlay" style="display: none;" onclick="closeDetailModal(event)">
        <div
            class="dash-modal-card"
            role="dialog"
            aria-modal="true"
            aria-labelledby="modal-title"
            aria-describedby="modal-desc"
            onclick="event.stopPropagation()"
        >
            <button type="button" class="dash-modal-close" onclick="closeDetailModal()" aria-label="Tutup Detail">✕</button>

            <div class="dash-modal-header">
                <div class="modal-badges-row">
                    <span class="modal-badge-cat" id="modal-cat">-</span>
                    <span class="modal-badge-level" id="modal-tingkat-badge">-</span>
                    <span class="modal-badge-countdown" id="modal-countdown-badge">-</span>
                </div>
                <h3 class="modal-title" id="modal-title">-</h3>
                <p class="modal-organizer" id="modal-organizer">-</p>
            </div>

            <div class="dash-modal-media" id="modal-media-box">
                <img src="" alt="Poster Lomba" id="modal-img" class="modal-poster-img" style="display: none;">
                <div id="modal-placeholder" class="modal-poster-placeholder">
                    <strong id="modal-placeholder-title"></strong>
                    <span id="modal-placeholder-cat"></span>
                </div>
            </div>

            <div class="dash-modal-body">
                <h4 class="modal-subheading">Deskripsi Kompetisi</h4>
                <div class="modal-desc" id="modal-desc">-</div>

                <h4 class="modal-subheading">Informasi &amp; Persyaratan</h4>
                <div class="modal-info-grid">
                    <div class="modal-info-item">
                        <span class="info-item-label">Deadline Pendaftaran</span>
                        <span class="info-item-val" id="modal-deadline">-</span>
                    </div>
                    <div class="modal-info-item">
                        <span class="info-item-label">Tanggal Pelaksanaan</span>
                        <span class="info-item-val" id="modal-pelaksanaan">-</span>
                    </div>
                    <div class="modal-info-item">
                        <span class="info-item-label">Biaya Registrasi</span>
                        <span class="info-item-val" id="modal-biaya">-</span>
                    </div>
                    <div class="modal-info-item">
                        <span class="info-item-label">Bentuk Partisipasi</span>
                        <span class="info-item-val" id="modal-partisipasi">-</span>
                    </div>
                    <div class="modal-info-item">
                        <span class="info-item-label">Tingkat Kompetisi</span>
                        <span class="info-item-val" id="modal-tingkat">-</span>
                    </div>
                    <div class="modal-info-item">
                        <span class="info-item-label">Bidang Keilmuan</span>
                        <span class="info-item-val" id="modal-bidang">-</span>
                    </div>
                </div>

                <div class="dash-modal-actions">
                    <a href="#" target="_blank" rel="noopener noreferrer" class="btn-modal-primary" id="modal-link-resmi">
                        <span>Daftar Sekarang (Link Resmi)</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                            <polyline points="15 3 21 3 21 9"></polyline>
                            <line x1="10" y1="14" x2="21" y2="3"></line>
                        </svg>
                    </a>
                    <a href="#" target="_blank" rel="noopener noreferrer" class="btn-modal-secondary" id="modal-link-guidebook" style="display: none;">
                        <span>Unduh Guidebook</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                    </a>
                    <div class="modal-no-links-notice" id="modal-no-links-notice" style="display: none;">
                        Tautan pendaftaran resmi &amp; guidebook belum tersedia dari penyelenggara.
                    </div>
                </div>

                <div class="dash-modal-subactions">
                    <button type="button" class="btn-modal-share" id="modal-btn-copy-link" onclick="copyModalLombaLink()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                        </svg>
                        <span id="modal-copy-label">Salin Link Lomba</span>
                    </button>
                    <a href="<?= url('?page=cari-partner') ?>" class="btn-modal-fullpage" id="modal-link-cari-tim">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <span>Cari Tim</span>
                    </a>
                    <a href="#" class="btn-modal-fullpage" id="modal-link-fullpage">
                        <span>Buka Halaman Penuh</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 5. JAVASCRIPT MODULE UNTUK DEBOUNCED SEARCH (300ms), FILTER PILLS & MODAL -->
<!-- ========================================================================= -->
<script src="<?= url('public/js/dashboard.js') ?>"></script>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>