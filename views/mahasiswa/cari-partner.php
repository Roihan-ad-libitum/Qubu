<?php

$title               = $title ?? 'Cari Partner & Tim Lomba - ' . APP_NAME;
$extraCss            = [
    'public/css/components.css',
    'public/css/dashboard.css',
    'public/css/cari-partner.css',
    'public/css/team-flow.css',
];
$activeStudentNavKey = 'teams';
$searchKeyword       = $searchKeyword ?? '';
$activeBidang        = $activeBidang ?? 'Semua Bidang';
$activeProdi         = $activeProdi ?? 'Semua Prodi';
$targetLomba         = $targetLomba ?? null;

$categoryPills = $categoryPills ?? getCategoryPills();
$prodiOptions  = $prodiOptions ?? getProdiOptions();

$partnerList          = $partnerList ?? [];
$semuaKandidatPartner = $semuaKandidatPartner ?? $partnerList;

$matchedIdsMap = [];
foreach ($partnerList as $matchedItem) {
    $matchedIdsMap[(int) ($matchedItem['id_mahasiswa'] ?? 0)] = true;
}

$hasActiveFilter = ($activeBidang !== 'Semua Bidang') || ($activeProdi !== 'Semua Prodi') || ($searchKeyword !== '');

$emptyFilterParts = [];
if ($activeBidang !== 'Semua Bidang') {
    $emptyFilterParts[] = 'bidang "' . $activeBidang . '"';
}
if ($activeProdi !== 'Semua Prodi') {
    $emptyFilterParts[] = 'Program Studi "' . $activeProdi . '"';
}
if ($searchKeyword !== '') {
    $emptyFilterParts[] = 'kata kunci "' . $searchKeyword . '"';
}
$initialEmptyMessage = !empty($emptyFilterParts)
    ? ('Tidak ditemukan kandidat mahasiswa untuk kombinasi ' . implode(', ', $emptyFilterParts) . '. Coba sesuaikan atau reset filter Anda.')
    : 'Coba gunakan kata kunci lain, pilih bidang minat berbeda, atau ubah filter Program Studi.';

$targetLombaId = !empty($targetLomba['id_lomba']) ? (int) $targetLomba['id_lomba'] : null;
$targetLombaClosed = $targetLomba !== null
    && isLombaRegistrationClosed($targetLomba['deadline_daftar'] ?? null);
$createPostUrl = url('?page=team-post-create' . ($targetLombaId && !$targetLombaClosed ? '&id_lomba=' . $targetLombaId : ''));

require_once __DIR__ . '/../components/partner-card.php';
require_once __DIR__ . '/../components/unified-directory-filter.php';
require_once __DIR__ . '/../layouts/headers.php';
?>

<div class="dash-page team-flow-page">
    <?php require __DIR__ . '/../components/student-navbar.php'; ?>

    <section class="dash-hero-section team-flow-hero">
        <div class="dash-hero-noise"></div>
        <div class="dash-hero-content team-flow-hero-inner">
            <h1 class="dash-hero-greeting">Cari Partner &amp; Tim Lomba</h1>
            <p class="team-flow-hero-description">
                Jelajahi direktori mahasiswa Fasilkom lintas program studi, tinjau keahlian serta bio mereka, lalu hubungi langsung melalui WhatsApp atau Email.
            </p>

            <?php renderCollabModeTabs('cari-partner', $targetLombaId); ?>
        </div>
    </section>

    <main
        class="team-flow-main"
        id="partner-main-container"
        data-api-url="<?= e(url('?page=api-partners')) ?>"
        data-id-lomba="<?= $targetLombaId ?: '' ?>"
    >
        <?php if (!empty($targetLomba)): ?>
            <div class="partner-lomba-context" id="partner-lomba-context">
                <div class="partner-lomba-context-info">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1d4ed8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="8" r="7"></circle>
                        <polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline>
                    </svg>
                    <span>Mencari rekan tim untuk lomba: <strong><?= e($targetLomba['nama_lomba']) ?></strong> (Bidang: <?= e($targetLomba['bidang']) ?>)</span>
                    <?php if ($targetLombaClosed): ?>
                        <span class="badge-pill badge-red">Pendaftaran Ditutup</span>
                    <?php endif; ?>
                </div>
                <div style="display: flex; align-items: center; gap: 0.65rem;">
                    <a href="<?= e(url('?page=detail-lomba&id=' . (int) $targetLomba['id_lomba'])) ?>" class="team-secondary-button">
                        Detail Lomba
                    </a>
                    <a href="<?= e(url('?page=cari-partner')) ?>" class="team-secondary-button">
                        Hapus Konteks Lomba
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <?php renderUnifiedDirectoryFilter([
            'ariaLabel'         => 'Filter Kandidat Partner',
            'heading'           => 'Cari Kandidat Partner',
            'description'       => 'Saring mahasiswa berdasarkan nama, keahlian, atau Program Studi Fasilkom.',
            'formId'            => 'partner-filter-form',
            'pageName'          => 'cari-partner',
            'hiddenParams'      => [
                'id_lomba' => $targetLombaId ?: '',
                'bidang'   => $activeBidang !== 'Semua Bidang' ? $activeBidang : '',
            ],
            'searchInputId'     => 'partner-search-input',
            'searchName'        => 'q',
            'searchKeyword'     => $searchKeyword,
            'searchPlaceholder' => 'Cari nama, keahlian, atau bio...',
            'searchAriaLabel'   => 'Cari kandidat partner',
            'selectId'          => 'partner-prodi-select',
            'selectName'        => 'prodi',
            'selectLabel'       => 'Program Studi',
            'selectAriaLabel'   => 'Filter Program Studi',
            'selectOptions'     => $prodiOptions,
            'activeSelectValue' => $activeProdi,
        ]); ?>

        <section class="team-section-heading">
            <div>
                <span class="team-eyebrow team-eyebrow-dark">DIREKTORI KANDIDAT PARTNER</span>
                <h2>Daftar Mahasiswa Fasilkom</h2>
                <p id="partner-count-badge">Menampilkan <?= count($partnerList) ?> rekan yang siap berkolaborasi</p>
            </div>
            <a class="team-primary-button" id="btn-posting-cari-tim" href="<?= e($createPostUrl) ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="margin-right: 6px;">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Buat Posting Cari Tim</span>
            </a>
        </section>

        <?php renderUnifiedActiveFilterBar([
            'barId'           => 'active-partner-filter-bar',
            'labelId'         => 'active-partner-filter-label',
            'clearBtnId'      => 'btn-clear-partner-filter',
            'hasActiveFilter' => $hasActiveFilter,
            'primaryLabel'    => $activeBidang,
            'secondaryPrefix' => 'Prodi',
            'secondaryValue'  => $activeProdi,
            'searchKeyword'   => $searchKeyword,
        ]); ?>

        <?php renderUnifiedLoadingState('partner-loading-state', 'Memuat kandidat rekan tim...'); ?>

        <div class="team-post-grid partner-grid-3" id="partner-grid" style="<?= empty($partnerList) ? 'display: none;' : '' ?>">
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

        <div
            class="team-empty-state partner-empty-state"
            id="partner-empty-state"
            role="status"
            aria-live="polite"
            style="<?= empty($partnerList) ? 'display: flex;' : 'display: none;' ?>"
        >
            <h3 id="empty-partner-title">Belum Ada Kandidat Partner yang Sesuai</h3>
            <p id="empty-partner-msg"><?= e($initialEmptyMessage) ?></p>
            <div style="display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap;">
                <button
                    type="button"
                    class="team-secondary-button"
                    id="btn-reset-empty-partner"
                    onclick="if (typeof window.resetPartnerFilters === 'function') window.resetPartnerFilters();"
                >
                    Reset Filter Pencarian
                </button>
                <a class="team-primary-button" href="<?= e($createPostUrl) ?>">
                    + Buat Posting Cari Tim
                </a>
            </div>
        </div>
    </main>
</div>

<div
    class="dash-modal-overlay modal-backdrop"
    id="partner-profile-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="pmodal-name"
    aria-hidden="true"
    style="display: none;"
>
    <div class="dash-modal-card modal-container" style="max-width: 560px; padding: 1.75rem;" onclick="event.stopPropagation()">
        <button type="button" class="dash-modal-close modal-close-btn" id="pmodal-close-btn" aria-label="Tutup Modal Profil" onclick="closePartnerProfileModal()">✕</button>

        <div style="display: flex; flex-direction: column; gap: 1.25rem;">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div class="pcard-avatar" id="pmodal-avatar-box" style="width: 60px; height: 60px; font-size: 1.25rem;">
                    <span id="pmodal-avatar-initials">QB</span>
                    <img id="pmodal-avatar-img" src="" alt="" class="pcard-avatar-img" style="display: none;">
                </div>
                <div>
                    <h2 id="pmodal-name" style="font-size: 1.3rem; font-weight: 800; color: #0f172a; margin: 0 0 4px 0;">Nama Mahasiswa</h2>
                    <p id="pmodal-sub" style="font-size: 0.88rem; font-weight: 600; color: #475569; margin: 0;">Informatika &bull; Angkatan 2025</p>
                </div>
            </div>

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
                <div class="lomba-meta-row" id="pmodal-github-row" style="display: none;">
                    <span class="meta-label">GitHub</span>
                    <a href="#" id="pmodal-github-link" target="_blank" rel="noopener noreferrer" class="badge-pill badge-blue" style="text-decoration: none;">-</a>
                </div>
                <div class="lomba-meta-row">
                    <span class="meta-label">Bidang Keahlian</span>
                    <div id="pmodal-skills" class="pcard-skills-list" style="justify-content: flex-end;"></div>
                </div>
            </div>

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 1rem;">
                <span style="display: block; font-size: 0.74rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 0.35rem;">Tentang / Bio Kolaborasi</span>
                <p id="pmodal-bio" style="font-size: 0.9rem; line-height: 1.6; color: #334155; margin: 0;">
                    Siap berkolaborasi dalam kompetisi tingkat nasional dan pengembangan proyek tim lintas prodi.
                </p>
            </div>

            <div class="team-post-actions" style="margin-top: 0.25rem;">
                <a href="#" id="pmodal-full-link" class="team-secondary-button" style="flex: 1;">
                    <span>Halaman Profil Lengkap</span>
                </a>
                <a
                    href="#"
                    id="pmodal-btn-github"
                    class="team-secondary-button"
                    style="flex: 1; display: none;"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" style="margin-right: 6px; flex-shrink: 0;">
                        <path d="M12 0.5C5.37 0.5 0 5.87 0 12.5c0 5.3 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.295 24 17.795 24 12.5 24 5.87 18.63 0.5 12 0.5z"/>
                    </svg>
                    <span>Kunjungi GitHub</span>
                </a>
                <a
                    href="#"
                    id="pmodal-btn-invite"
                    class="team-primary-button"
                    style="flex: 1;"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    Hubungi Kandidat
                </a>
            </div>
        </div>
    </div>
</div>

<script src="<?= url('public/js/cari-partner.js') ?>?v=<?= (int) filemtime(__DIR__ . '/../../public/js/cari-partner.js') ?>" defer></script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
