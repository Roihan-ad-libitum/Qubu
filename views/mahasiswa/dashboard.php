<?php

$title               = $title ?? 'Dashboard Info Lomba - ' . APP_NAME;
$extraCss            = ['public/css/components.css', 'public/css/dashboard.css'];
$activeStudentNavKey = 'dashboard';
$searchKeyword       = $searchKeyword ?? '';
$activeBidang        = $activeBidang ?? 'Semua Bidang';
$categoryPills       = $categoryPills ?? getCategoryPills();
$formatTanggalIndo   = static fn(?string $datetime): string => formatLombaTanggalIndo($datetime);
$formatPartisipasi   = static fn(?string $partisipasi): string => formatLombaPartisipasi($partisipasi);

require_once __DIR__ . '/../components/lomba-card.php';
require_once __DIR__ . '/../components/unified-directory-filter.php';
require_once __DIR__ . '/../layouts/headers.php';
?>

<div class="dash-page">
    <?php require __DIR__ . '/../components/student-navbar.php'; ?>

    <section class="dash-hero-section">
        <div class="dash-hero-noise"></div>
        <div class="dash-hero-content">
            <h1 class="dash-hero-greeting">Hi <?= e($userFirstName) ?>!</h1>
            <h2 class="dash-hero-subgreeting">Ayo Cari Lomba Impianmu</h2>

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

            <?php renderCategoryPillsRow([
                'pills'        => $categoryPills,
                'activeBidang' => (string) $activeBidang,
                'ariaLabel'    => 'Filter Kategori Bidang',
                'containerId'  => 'dash-pills-container',
                'extraStyle'   => '',
                'idPrefix'     => 'pill-tab-',
                'ariaControls' => 'semua-lomba-grid',
            ]); ?>
        </div>
    </section>

    <main class="dash-main-container">

        <section class="dash-section" id="section-rekomendasi">
            <div class="dash-section-header">
                <div>
                    <h2 class="dash-section-title">Rekomendasi Lomba</h2>
                </div>

                <div class="dash-tab-switch" id="recommendation-tabs" role="tablist" aria-label="Kategori rekomendasi lomba">
                    <button
                        type="button"
                        class="dash-tab-btn is-active"
                        id="tab-lomba-minat"
                        role="tab"
                        aria-selected="true"
                        aria-controls="panel-lomba-minat"
                        tabindex="0"
                    >Lomba Sesuai Minat</button>
                    <button
                        type="button"
                        class="dash-tab-btn"
                        id="tab-lomba-terdekat"
                        role="tab"
                        aria-selected="false"
                        aria-controls="panel-lomba-terdekat"
                        tabindex="-1"
                    >Lomba dalam Waktu Dekat</button>
                </div>
            </div>

            <div
                class="dash-recommendation-panel"
                id="panel-lomba-minat"
                role="tabpanel"
                aria-labelledby="tab-lomba-minat"
                tabindex="0"
            >
                <p class="dash-section-desc">Rekomendasi berdasarkan bidang peminatan profilmu.</p>
                <div class="dash-slider-wrapper">
                    <button type="button" class="dash-slider-nav nav-prev" onclick="scrollSlider('interest-slider', -340)" aria-label="Geser ke kiri">‹</button>
                    <button type="button" class="dash-slider-nav nav-next" onclick="scrollSlider('interest-slider', 340)" aria-label="Geser ke kanan">›</button>
                    <div class="dash-slider-track" id="interest-slider">
                        <?php if (!empty($rekomendasiLomba)): ?>
                            <?php foreach ($rekomendasiLomba as $item): ?>
                                <?php renderLombaCard($item, ['mode' => 'modal']); ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="dash-empty-state">
                                <p>Belum ada perlombaan yang sesuai dengan bidang minatmu.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div
                class="dash-recommendation-panel"
                id="panel-lomba-terdekat"
                role="tabpanel"
                aria-labelledby="tab-lomba-terdekat"
                tabindex="0"
                hidden
            >
                <p class="dash-section-desc">Perlombaan dengan tenggat pendaftaran atau pelaksanaan yang paling dekat.</p>
                <div class="dash-slider-wrapper">
                    <button type="button" class="dash-slider-nav nav-prev" onclick="scrollSlider('upcoming-slider', -340)" aria-label="Geser ke kiri">‹</button>
                    <button type="button" class="dash-slider-nav nav-next" onclick="scrollSlider('upcoming-slider', 340)" aria-label="Geser ke kanan">›</button>
                    <div class="dash-slider-track" id="upcoming-slider">
                        <?php if (!empty($upcomingLomba)): ?>
                            <?php foreach ($upcomingLomba as $item): ?>
                                <?php renderLombaCard($item, ['mode' => 'modal']); ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="dash-empty-state">
                                <p>Belum ada jadwal perlombaan mendatang.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

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

                <div class="dash-modal-actions" style="display:flex; flex-wrap:wrap; gap:0.75rem; align-items:center;">
                    <a href="#" target="_blank" rel="noopener noreferrer" class="btn-modal-primary" id="modal-link-resmi">
                        <span>Daftar Sekarang (Link Resmi)</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                            <polyline points="15 3 21 3 21 9"></polyline>
                            <line x1="10" y1="14" x2="21" y2="3"></line>
                        </svg>
                    </a>
                    <a href="<?= url('?page=team-posts') ?>" class="btn-modal-secondary" id="modal-link-cari-tim">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <span>Cari Tim</span>
                    </a>
                    <div class="modal-no-links-notice" id="modal-no-links-notice" style="display: none;">
                        Tautan pendaftaran resmi belum tersedia dari penyelenggara.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= url('public/js/dashboard.js') ?>?v=<?= (int) filemtime(__DIR__ . '/../../public/js/dashboard.js') ?>"></script>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
