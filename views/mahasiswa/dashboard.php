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
                <a href="javascript:void(0);" onclick="switchRekanTab('teman'); document.getElementById('section-rekomendasi').scrollIntoView({behavior: 'smooth'});" class="dash-nav-link" id="link-cari-partner">Cari Partner</a>
                <a href="<?= url('?page=dosen-profile') ?>" class="dash-nav-link">Dosen Pembimbing</a>

                <!-- Profil Avatar User -->
                <div class="dash-avatar-wrapper" id="dash-avatar-wrapper">
                    <button type="button" class="dash-avatar-btn" id="dash-avatar-btn" aria-label="Menu Pengguna" aria-expanded="false" title="<?= e($namaMahasiswa) ?>">
                        <?php if (!empty($userFoto) && file_exists(__DIR__ . '/../../' . $userFoto)): ?>
                            <img src="<?= url($userFoto) ?>" alt="Avatar" class="dash-avatar-img">
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

            <!-- Kategori Filter Pills di Bawah Search Bar -->
            <div class="dash-pills-row" id="dash-pills-container" role="tablist" aria-label="Filter Kategori Bidang">
                <?php foreach ($categoryPills as $idx => $pill): ?>
                    <button 
                        type="button" 
                        class="dash-pill-btn <?= $idx === 0 ? 'is-active' : '' ?>" 
                        data-bidang="<?= e($pill) ?>"
                        role="tab"
                        aria-selected="<?= $idx === 0 ? 'true' : 'false' ?>"
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
                            <article class="lomba-card" data-bidang="<?= e($item['bidang']) ?>" data-title="<?= e($item['nama_lomba']) ?>">
                                <!-- Poster Lomba -->
                                <div class="lomba-card-media">
                                    <?php if (!empty($item['poster']) && file_exists(__DIR__ . '/../../' . $item['poster'])): ?>
                                        <img src="<?= url($item['poster']) ?>" alt="<?= e($item['nama_lomba']) ?>" class="lomba-card-img" loading="lazy">
                                    <?php else: ?>
                                        <!-- Placeholder Gambar Futuristik Sesuai Bidang Mockup -->
                                        <div class="lomba-card-placeholder">
                                            <div class="placeholder-glow"></div>
                                            <span class="placeholder-cat"><?= e($item['bidang']) ?></span>
                                            <h4 class="placeholder-title"><?= e($item['nama_lomba']) ?></h4>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Konten Rincian Lomba Persis Mockup -->
                                <div class="lomba-card-body">
                                    <h3 class="lomba-card-title"><?= e($item['nama_lomba']) ?></h3>

                                    <div class="lomba-meta-table">
                                        <!-- Row 1: Deadline -->
                                        <div class="lomba-meta-row">
                                            <span class="meta-label">Deadline</span>
                                            <span class="badge-pill badge-red"><?= $formatTanggalIndo($item['deadline_daftar']) ?></span>
                                        </div>

                                        <!-- Row 2: Biaya Pendaftaran -->
                                        <div class="lomba-meta-row">
                                            <span class="meta-label">Biaya Pendaftaran</span>
                                            <?php if ((float)$item['biaya'] == 0): ?>
                                                <span class="badge-pill badge-green">Gratis</span>
                                            <?php else: ?>
                                                <span class="badge-pill badge-green">Rp <?= number_format((float)$item['biaya'], 0, ',', '.') ?></span>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Row 3: Partisipasi -->
                                        <div class="lomba-meta-row">
                                            <span class="meta-label">Partisipasi</span>
                                            <span class="badge-pill badge-blue"><?= e($formatPartisipasi($item['partisipasi'])) ?></span>
                                        </div>

                                        <!-- Row 4: Bidang Lomba -->
                                        <div class="lomba-meta-row">
                                            <span class="meta-label">Bidang Lomba</span>
                                            <span class="badge-pill badge-orange"><?= e($item['bidang']) ?></span>
                                        </div>

                                        <!-- Row 5: Tingkat -->
                                        <div class="lomba-meta-row">
                                            <span class="meta-label">Tingkat</span>
                                            <span class="badge-pill badge-purple"><?= e($item['tingkat']) ?></span>
                                        </div>
                                    </div>

                                    <!-- Tombol Detail Perlombaan Persis Mockup -->
                                    <button 
                                        type="button" 
                                        class="btn-card-detail" 
                                        onclick="openDetailModal(<?= htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8') ?>)"
                                    >
                                        Detail Perlombaan
                                    </button>
                                </div>
                            </article>
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
                                            <img src="<?= url($rekan['foto']) ?>" alt="<?= e($rekan['nama_mahasiswa']) ?>" class="rekan-avatar-img">
                                        <?php else: ?>
                                            <span class="rekan-avatar-initials">
                                                <?= strtoupper(substr($rekan['nama_mahasiswa'], 0, 2)) ?>
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
        <section class="dash-section" id="section-semua-lomba">
            <div class="dash-section-header">
                <div>
                    <h2 class="dash-section-title">Semua Lomba</h2>
                    <p class="dash-section-desc">Eksplorasi seluruh kompetisi nasional dan internasional yang tersedia di Qubu.</p>
                </div>
                <div class="dash-count-badge" id="lomba-count-badge">
                    Menampilkan <span id="count-number"><?= count($semuaLomba) ?></span> Lomba
                </div>
            </div>

            <!-- Grid 2-3 Kolom Semua Lomba -->
            <div class="semua-lomba-grid" id="semua-lomba-grid">
                <?php foreach ($semuaLomba as $item): ?>
                    <article 
                        class="lomba-card grid-card-item" 
                        data-bidang="<?= e(strtolower($item['bidang'])) ?>" 
                        data-title="<?= e(strtolower($item['nama_lomba'])) ?>"
                        data-penyelenggara="<?= e(strtolower($item['penyelenggara'])) ?>"
                        data-desc="<?= e(strtolower($item['deskripsi'])) ?>"
                    >
                        <!-- Poster Lomba -->
                        <div class="lomba-card-media">
                            <?php if (!empty($item['poster']) && file_exists(__DIR__ . '/../../' . $item['poster'])): ?>
                                <img src="<?= url($item['poster']) ?>" alt="<?= e($item['nama_lomba']) ?>" class="lomba-card-img" loading="lazy">
                            <?php else: ?>
                                <div class="lomba-card-placeholder">
                                    <div class="placeholder-glow"></div>
                                    <span class="placeholder-cat"><?= e($item['bidang']) ?></span>
                                    <h4 class="placeholder-title"><?= e($item['nama_lomba']) ?></h4>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Konten Rincian Lomba -->
                        <div class="lomba-card-body">
                            <h3 class="lomba-card-title"><?= e($item['nama_lomba']) ?></h3>

                            <div class="lomba-meta-table">
                                <div class="lomba-meta-row">
                                    <span class="meta-label">Deadline</span>
                                    <span class="badge-pill badge-red"><?= $formatTanggalIndo($item['deadline_daftar']) ?></span>
                                </div>

                                <div class="lomba-meta-row">
                                    <span class="meta-label">Biaya Pendaftaran</span>
                                    <?php if ((float)$item['biaya'] == 0): ?>
                                        <span class="badge-pill badge-green">Gratis</span>
                                    <?php else: ?>
                                        <span class="badge-pill badge-green">Rp <?= number_format((float)$item['biaya'], 0, ',', '.') ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="lomba-meta-row">
                                    <span class="meta-label">Partisipasi</span>
                                    <span class="badge-pill badge-blue"><?= e($formatPartisipasi($item['partisipasi'])) ?></span>
                                </div>

                                <div class="lomba-meta-row">
                                    <span class="meta-label">Bidang Lomba</span>
                                    <span class="badge-pill badge-orange"><?= e($item['bidang']) ?></span>
                                </div>

                                <div class="lomba-meta-row">
                                    <span class="meta-label">Tingkat</span>
                                    <span class="badge-pill badge-purple"><?= e($item['tingkat']) ?></span>
                                </div>
                            </div>

                            <button 
                                type="button" 
                                class="btn-card-detail" 
                                onclick="openDetailModal(<?= htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8') ?>)"
                            >
                                Detail Perlombaan
                            </button>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <!-- Pesan Kosong Jika Filter / Pencarian Tidak Menemukan Hasil -->
            <div class="dash-empty-search" id="empty-search-state" style="display: none;">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <h3>Tidak ada lomba yang sesuai</h3>
                <p>Coba kata kunci lain atau pilih filter kategori "Semua Bidang".</p>
                <button type="button" class="btn-reset-filter" onclick="resetFilters()">Tampilkan Semua Lomba</button>
            </div>
        </section>
    </main>

    <!-- ========================================================================= -->
    <!-- 4. MODAL DETAIL LOMBA INTERAKTIF                                          -->
    <!-- ========================================================================= -->
    <div class="dash-modal-overlay" id="detail-modal-overlay" style="display: none;" onclick="closeDetailModal(event)">
        <div class="dash-modal-card" onclick="event.stopPropagation()">
            <button type="button" class="dash-modal-close" onclick="closeDetailModal()" aria-label="Tutup Detail">✕</button>

            <div class="dash-modal-header">
                <span class="modal-badge-cat" id="modal-cat">-</span>
                <h3 class="modal-title" id="modal-title">-</h3>
                <p class="modal-organizer" id="modal-organizer">-</p>
            </div>

            <div class="dash-modal-media" id="modal-media-box">
                <img src="" alt="Poster Lomba" id="modal-img" class="modal-poster-img" style="display: none;">
                <div id="modal-placeholder" class="modal-poster-placeholder"></div>
            </div>

            <div class="dash-modal-body">
                <h4 class="modal-subheading">Deskripsi Kompetisi</h4>
                <p class="modal-desc" id="modal-desc">-</p>

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
                        <span>Kunjungi Web Resmi Lomba</span>
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
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 5. JAVASCRIPT LOGIC UNTUK SEARCH, FILTER PILLS & MODAL                    -->
<!-- ========================================================================= -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Dropdown Avatar Toggle
    const avatarBtn = document.getElementById('dash-avatar-btn');
    const avatarWrapper = document.getElementById('dash-avatar-wrapper');

    if (avatarBtn && avatarWrapper) {
        avatarBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            avatarWrapper.classList.toggle('is-open');
        });

        document.addEventListener('click', (e) => {
            if (!avatarWrapper.contains(e.target)) {
                avatarWrapper.classList.remove('is-open');
            }
        });
    }

    // 2. Filter Pills & Pencarian
    const pillButtons = document.querySelectorAll('.dash-pill-btn');
    const searchInput = document.getElementById('dash-search-input');
    const searchForm  = document.getElementById('dash-search-form');
    const gridCards   = document.querySelectorAll('.grid-card-item');
    const emptyState  = document.getElementById('empty-search-state');
    const countNumber = document.getElementById('count-number');

    let activeBidang = 'semua bidang';
    let searchQuery  = '';

    function filterCards() {
        let visibleCount = 0;

        gridCards.forEach(card => {
            const cardBidang = card.dataset.bidang || '';
            const cardTitle  = card.dataset.title || '';
            const cardPeny   = card.dataset.penyelenggara || '';
            const cardDesc   = card.dataset.desc || '';

            const matchBidang = (activeBidang === 'semua bidang') || cardBidang.includes(activeBidang) || activeBidang.includes(cardBidang);
            const matchSearch = !searchQuery || cardTitle.includes(searchQuery) || cardPeny.includes(searchQuery) || cardBidang.includes(searchQuery) || cardDesc.includes(searchQuery);

            if (matchBidang && matchSearch) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        if (countNumber) countNumber.textContent = visibleCount;
        if (emptyState) {
            emptyState.style.display = visibleCount === 0 ? 'flex' : 'none';
        }
    }

    // Event listener pill buttons
    pillButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            pillButtons.forEach(b => b.classList.remove('is-active'));
            btn.classList.add('is-active');
            activeBidang = (btn.dataset.bidang || '').toLowerCase().trim();
            filterCards();
        });
    });

    // Event listener input search
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            searchQuery = searchInput.value.toLowerCase().trim();
            filterCards();
        });
    }

    if (searchForm) {
        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            searchQuery = (searchInput.value || '').toLowerCase().trim();
            filterCards();
            const gridEl = document.getElementById('section-semua-lomba');
            if (gridEl) gridEl.scrollIntoView({ behavior: 'smooth' });
        });
    }

    window.resetFilters = function() {
        if (searchInput) searchInput.value = '';
        searchQuery = '';
        activeBidang = 'semua bidang';
        pillButtons.forEach((b, idx) => {
            b.classList.toggle('is-active', idx === 0);
        });
        filterCards();
    };
});

// 3. Tab Switch Rekomendasi Rekan (Lomba vs Teman Mahasiswa)
function switchRekanTab(tab) {
    const viewLomba = document.getElementById('view-rekan-lomba');
    const viewTeman = document.getElementById('view-rekan-teman');
    const btnLomba  = document.getElementById('tab-rekan-lomba');
    const btnTeman  = document.getElementById('tab-rekan-teman');

    if (tab === 'teman') {
        if (viewLomba) viewLomba.style.display = 'none';
        if (viewTeman) viewTeman.style.display = 'block';
        if (btnLomba)  btnLomba.classList.remove('is-active');
        if (btnTeman)  btnTeman.classList.add('is-active');
    } else {
        if (viewLomba) viewLomba.style.display = 'block';
        if (viewTeman) viewTeman.style.display = 'none';
        if (btnLomba)  btnLomba.classList.add('is-active');
        if (btnTeman)  btnTeman.classList.remove('is-active');
    }
}

// 4. Horizontal Slider Smooth Scrolling
function scrollSlider(sliderId, offset) {
    const slider = document.getElementById(sliderId);
    if (slider) {
        slider.scrollBy({ left: offset, behavior: 'smooth' });
    }
}

// 5. Modal Detail Lomba
function openDetailModal(data) {
    const modal = document.getElementById('detail-modal-overlay');
    if (!modal || !data) return;

    document.getElementById('modal-title').textContent = data.nama_lomba || '-';
    document.getElementById('modal-cat').textContent   = data.bidang || 'Umum';
    document.getElementById('modal-organizer').textContent = 'Diselenggarakan oleh: ' + (data.penyelenggara || '-');
    document.getElementById('modal-desc').textContent  = data.deskripsi || 'Belum ada deskripsi rinci.';
    
    // Formatting date
    const deadline = data.deadline_daftar ? new Date(data.deadline_daftar).toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'}) : '-';
    const pelaksanaan = data.tanggal_pelaksanaan ? new Date(data.tanggal_pelaksanaan).toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'}) : '-';
    
    document.getElementById('modal-deadline').textContent    = deadline;
    document.getElementById('modal-pelaksanaan').textContent = pelaksanaan;
    document.getElementById('modal-biaya').textContent       = (parseFloat(data.biaya) === 0) ? 'Gratis (Rp 0)' : 'Rp ' + Number(data.biaya).toLocaleString('id-ID');
    document.getElementById('modal-partisipasi').textContent = data.partisipasi || 'Individu/Tim';
    document.getElementById('modal-tingkat').textContent     = data.tingkat || 'Nasional';
    document.getElementById('modal-bidang').textContent      = data.bidang || 'Umum';

    // Poster Image
    const imgEl = document.getElementById('modal-img');
    const placeholderEl = document.getElementById('modal-placeholder');
    if (data.poster && data.poster.trim() !== '') {
        imgEl.src = data.poster;
        imgEl.style.display = 'block';
        if (placeholderEl) placeholderEl.style.display = 'none';
    } else {
        imgEl.style.display = 'none';
        if (placeholderEl) {
            placeholderEl.style.display = 'block';
            placeholderEl.innerHTML = '<strong>' + (data.nama_lomba || '') + '</strong><span>' + (data.bidang || '') + '</span>';
        }
    }

    // Links
    const linkResmi = document.getElementById('modal-link-resmi');
    if (linkResmi) {
        linkResmi.href = data.link_resmi || '#';
    }

    const linkGuidebook = document.getElementById('modal-link-guidebook');
    if (linkGuidebook) {
        if (data.link_guidebook && data.link_guidebook.trim() !== '') {
            linkGuidebook.href = data.link_guidebook;
            linkGuidebook.style.display = 'inline-flex';
        } else {
            linkGuidebook.style.display = 'none';
        }
    }

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeDetailModal(e) {
    if (e && e.target !== e.currentTarget && !e.target.classList.contains('dash-modal-close')) {
        return;
    }
    const modal = document.getElementById('detail-modal-overlay');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}
</script>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>