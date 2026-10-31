<?php

$studentNavName      = $profile['nama_mahasiswa'] ?? Session::get('nama_lengkap', 'Mahasiswa');
$studentNavPhoto = $profile['foto'] ?? (Session::get('demo_profile_data')['foto'] ?? null);
$studentNavProgram   = $profile['program_studi'] ?? 'Informatika';
$studentNavInterest  = $profile['bidang_minat'] ?? '';
$activeStudentNavKey = 'advisors';
$searchKeyword       = $searchKeyword ?? '';
$activeBidang        = $activeBidang ?? 'Semua Bidang';
$categoryPills       = $categoryPills ?? getCategoryPills();

$advisors    = $advisors ?? [];
$allAdvisors = $allAdvisors ?? $advisors;

$matchedAdvisorIds = [];
foreach ($advisors as $matchedAdv) {
    $matchedAdvisorIds[(int) ($matchedAdv['id_dosen'] ?? 0)] = true;
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
    ? ('Tidak ditemukan profil dosen pembimbing untuk kombinasi ' . implode(' dan ', $emptyFilterParts) . '. Coba sesuaikan atau reset filter Anda.')
    : 'Coba gunakan kata kunci pencarian lain atau pilih kategori "Semua Bidang".';

$extraCss = [
    'public/css/components.css',
    'public/css/dashboard.css',
    'public/css/cari-partner.css',
    'public/css/team-flow.css',
];

require_once __DIR__ . '/../components/partner-card.php';
require_once __DIR__ . '/../components/unified-directory-filter.php';
require_once __DIR__ . '/../layouts/headers.php';
?>
<div class="dash-page team-flow-page">
    <?php require __DIR__ . '/../components/student-navbar.php'; ?>

    <section class="dash-hero-section team-flow-hero advisor-hero">
        <div class="dash-hero-noise"></div>
        <div class="dash-hero-content team-flow-hero-inner">
            <h1 class="dash-hero-greeting">Direktori Dosen Pembimbing</h1>
            <p class="team-flow-hero-description">
                Temukan dosen pembimbing sesuai bidang kompetisi tim Anda, lalu hubungi melalui Email atau WhatsApp yang tersedia untuk berdiskusi mengenai bimbingan lomba.
            </p>
        </div>
    </section>

    <main class="team-flow-main" id="advisor-main-container">
        <?php renderUnifiedDirectoryFilter([
            'ariaLabel'         => 'Filter Dosen Pembimbing',
            'heading'           => 'Cari Dosen Pembimbing',
            'description'       => 'Saring berdasarkan nama dosen atau fokus bidang keahlian perlombaan.',
            'formId'            => 'advisor-filter-form',
            'pageName'          => 'advisor-directory',
            'searchInputId'     => 'advisor-search-input',
            'searchName'        => 'q',
            'searchKeyword'     => $searchKeyword,
            'searchPlaceholder' => 'Cari nama dosen atau bidang keahlian...',
            'searchAriaLabel'   => 'Cari nama dosen atau bidang keahlian',
            'selectId'          => 'advisor-bidang-select',
            'selectName'        => 'bidang',
            'selectLabel'       => 'Bidang Keahlian',
            'selectAriaLabel'   => 'Filter Bidang Keahlian Dosen',
            'selectOptions'     => $categoryPills,
            'activeSelectValue' => $activeBidang,
        ]); ?>

        <section class="team-section-heading">
            <div>
                <span class="team-eyebrow team-eyebrow-dark">DIREKTORI PEMBIMBING KOMPETISI</span>
                <h2>Daftar Dosen Pembimbing</h2>
                <p id="advisor-count-badge">Menampilkan <?= count($advisors) ?> profil dosen pembimbing<?= $activeBidang !== 'Semua Bidang' ? ' pada bidang ' . e($activeBidang) : '' ?>.</p>
            </div>
        </section>

        <?php renderUnifiedActiveFilterBar([
            'barId'           => 'active-advisor-filter-bar',
            'labelId'         => 'active-advisor-filter-label',
            'clearBtnId'      => 'btn-clear-advisor-filter',
            'hasActiveFilter' => $hasActiveFilter,
            'primaryLabel'    => $activeBidang,
            'searchKeyword'   => $searchKeyword,
        ]); ?>

        <?php renderUnifiedLoadingState('advisor-loading-state', 'Memuat direktori dosen pembimbing...'); ?>

        <div class="team-post-grid partner-grid-3" id="advisor-grid" style="<?= empty($advisors) ? 'display: none;' : '' ?>">
            <?php foreach ($allAdvisors as $advisor):
                $idDosen   = (int) ($advisor['id_dosen'] ?? 0);
                $isMatched = isset($matchedAdvisorIds[$idDosen]);
            ?>
                <?php renderAdvisorCard($advisor, [
                    'extraClass' => 'advisor-card-item',
                    'hidden'     => !$isMatched,
                ]); ?>
            <?php endforeach; ?>
        </div>

        <div
            class="team-empty-state partner-empty-state"
            id="advisor-empty-state"
            role="status"
            aria-live="polite"
            style="<?= empty($advisors) ? 'display: flex;' : 'display: none;' ?>"
        >
            <h3 id="empty-advisor-title">Tidak Ada Profil Dosen Pembimbing yang Cocok</h3>
            <p id="empty-advisor-msg"><?= e($initialEmptyMessage) ?></p>
            <div style="display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap;">
                <button
                    type="button"
                    class="team-secondary-button"
                    id="btn-reset-empty-advisor"
                    onclick="if (typeof window.resetAdvisorFilters === 'function') window.resetAdvisorFilters();"
                >
                    Reset Filter Pencarian
                </button>
            </div>
        </div>
    </main>
</div>

<script src="<?= url('public/js/cari-partner.js') ?>?v=<?= (int) filemtime(__DIR__ . '/../../public/js/cari-partner.js') ?>" defer></script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
