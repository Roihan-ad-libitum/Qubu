<?php

$studentNavName      = $profile['nama_mahasiswa'] ?? Session::get('nama_lengkap', 'Mahasiswa');
$studentNavPhoto = $profile['foto'] ?? (Session::get('demo_profile_data')['foto'] ?? null);
$studentNavProgram   = $profile['program_studi'] ?? 'Informatika';
$studentNavInterest  = $profile['bidang_minat'] ?? '';
$activeStudentNavKey = 'teams';
$activeBidang        = $activeBidang ?? 'Semua Bidang';
$categoryPills       = $categoryPills ?? getCategoryPills();

$extraCss = [
    'public/css/components.css',
    'public/css/dashboard.css',
    'public/css/cari-partner.css',
    'public/css/team-flow.css',
];

require_once __DIR__ . '/../components/partner-card.php';
require_once __DIR__ . '/../components/unified-directory-filter.php';
require_once __DIR__ . '/../layouts/headers.php';

$selectedCompetitionId = !empty($selectedCompetition['id_lomba']) ? (int) $selectedCompetition['id_lomba'] : null;
$currentUserId         = (int) Session::get('id_user');

$posts    = $posts ?? [];
$allPosts = $allPosts ?? $posts;

$matchedPostIds = [];
foreach ($posts as $matchedPost) {
    $matchedPostIds[(int) ($matchedPost['id_post'] ?? 0)] = true;
}

$competitionSelectOptions = ['' => 'Semua Perlombaan Tim'];
foreach ($competitions as $competition) {
    if (in_array($competition['partisipasi'] ?? '', ['Tim', 'Individu/Tim'], true)) {
        $compId = (string) (int) $competition['id_lomba'];
        $competitionSelectOptions[$compId] = $competition['nama_lomba'] . ' (' . $competition['bidang'] . ')';
    }
}

$hasActiveFilter       = ($selectedCompetitionId !== null) || ($activeBidang !== 'Semua Bidang');
$selectedCompNameLabel = $selectedCompetition ? (string) $selectedCompetition['nama_lomba'] : '';
?>
<div class="dash-page team-flow-page">
    <?php require __DIR__ . '/../components/student-navbar.php'; ?>

    <section class="dash-hero-section team-flow-hero">
        <div class="dash-hero-noise"></div>
        <div class="dash-hero-content team-flow-hero-inner">
            <h1 class="dash-hero-greeting">Cari Partner &amp; Tim Lomba</h1>
            <p class="team-flow-hero-description">
                Temukan rekan satu frekuensi, tinjau profil pembuat posting, dan hubungi langsung via WhatsApp atau Email untuk membangun tim kompetisi terbaikmu.
            </p>

            <?php renderCollabModeTabs('team-posts', $selectedCompetitionId); ?>
        </div>
    </section>

    <main
        class="team-flow-main"
        id="team-posts-main-container"
        data-create-base-url="<?= e(url('?page=team-post-create')) ?>"
    >
        <?php if (!empty($error)): ?>
            <div class="team-alert team-alert-error" role="alert"><?= e($error) ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="team-alert team-alert-success" role="status"><?= e($success) ?></div>
        <?php endif; ?>

        <?php renderUnifiedDirectoryFilter([
            'ariaLabel'       => 'Filter Berdasarkan Perlombaan dan Bidang',
            'heading'         => 'Filter Berdasarkan Perlombaan & Bidang',
            'descriptionHtml' => 'Tampilkan semua lowongan tim berstatus <strong>Open</strong> atau saring khusus untuk kompetisi tertentu.',
            'formId'          => 'team-posts-filter-form',
            'pageName'        => 'team-posts',
            'showSearch'      => false,
            'selects'         => [
                [
                    'id'          => 'team-competition',
                    'name'        => 'id_lomba',
                    'label'       => 'Perlombaan',
                    'ariaLabel'   => 'Filter Perlombaan Tim',
                    'options'     => $competitionSelectOptions,
                    'activeValue' => $selectedCompetitionId !== null ? (string) $selectedCompetitionId : '',
                ],
                [
                    'id'          => 'team-bidang-select',
                    'name'        => 'bidang',
                    'label'       => 'Bidang',
                    'ariaLabel'   => 'Filter Bidang Lomba',
                    'options'     => $categoryPills,
                    'activeValue' => $activeBidang,
                ],
            ],
        ]); ?>

        <section class="team-section-heading">
            <div>
                <span class="team-eyebrow team-eyebrow-dark" id="team-posts-eyebrow">
                    <?= $selectedCompetition ? 'KOMPETISI TERPILIH' : 'SEMUA LOWONGAN TIM AKTIF' ?>
                </span>
                <h2 id="team-posts-heading-title"><?= $selectedCompetition ? e($selectedCompetition['nama_lomba']) : 'Daftar Posting Pencarian Anggota Tim (Open)' ?></h2>
                <?php if ($selectedCompetitionClosed): ?>
                    <span class="badge-pill badge-red">Pendaftaran Ditutup</span>
                <?php endif; ?>
                <p id="team-posts-count-badge">
                    <?= $canSearchTeams
                        ? 'Menampilkan ' . count($posts) . ' posting berstatus Open. Klik nama atau tombol Lihat Profil untuk meninjau pembuat posting sebelum menghubungi.'
                        : 'Kompetisi ini merupakan kategori Individu sehingga tidak membuka rekrutmen tim.' ?>
                </p>
            </div>
            <?php if ($canSearchTeams && !$selectedCompetitionClosed): ?>
                <a class="team-primary-button" id="btn-posting-cari-tim" href="<?= e(url('?page=team-post-create' . ($selectedCompetitionId ? '&id_lomba=' . $selectedCompetitionId : ''))) ?>">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="margin-right: 6px;">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Buat Posting Cari Tim</span>
                </a>
            <?php endif; ?>
        </section>

        <?php renderUnifiedActiveFilterBar([
            'barId'           => 'active-team-filter-bar',
            'labelId'         => 'active-team-filter-label',
            'clearBtnId'      => 'btn-clear-team-filter',
            'hasActiveFilter' => $hasActiveFilter,
            'primaryLabel'    => $activeBidang,
            'secondaryPrefix' => 'Perlombaan',
            'secondaryValue'  => $selectedCompNameLabel,
        ]); ?>

        <?php renderUnifiedLoadingState('team-posts-loading-state', 'Memuat posting pencarian tim...'); ?>

        <?php if ($canSearchTeams): ?>
            <div class="partner-grid-3 team-post-grid" id="team-posts-grid" style="<?= empty($posts) ? 'display: none;' : '' ?>">
                <?php foreach ($allPosts as $post):
                    $idPost    = (int) ($post['id_post'] ?? 0);
                    $isMatched = isset($matchedPostIds[$idPost]);
                ?>
                    <?php renderTeamPostCard($post, $currentUserId, [
                        'extraClass' => 'team-post-card-item',
                        'hidden'     => !$isMatched,
                    ]); ?>
                <?php endforeach; ?>
            </div>

            <div
                class="team-empty-state partner-empty-state"
                id="team-posts-empty-state"
                role="status"
                aria-live="polite"
                style="<?= empty($posts) ? 'display: flex;' : 'display: none;' ?>"
            >
                <h3 id="empty-team-posts-title">Belum ada posting pencarian tim yang sesuai</h3>
                <p id="empty-team-posts-msg"><?= $selectedCompetitionClosed ? 'Pendaftaran Ditutup' : 'Jadilah yang pertama membuat posting pencarian anggota tim atau jelajahi Direktori Kandidat Partner.' ?></p>
            </div>
        <?php else: ?>
            <div class="team-empty-state">
                <h3>Kompetisi ini merupakan kategori Individu</h3>
                <p>Pilih kompetisi dengan format Tim atau Individu &amp; Tim untuk mencari rekan tim.</p>
            </div>
        <?php endif; ?>

        <section class="team-owner-section">
            <div class="team-section-heading">
                <div>
                    <span class="team-eyebrow team-eyebrow-dark">KELOLA POSTING SAYA</span>
                    <h2>Status Rekrutmen Tim Saya</h2>
                    <p>Setelah diskusi dan pembentukan tim selesai di luar aplikasi, tandai posting Anda menjadi <strong>Team Complete</strong> atau <strong>Archived</strong>.</p>
                </div>
            </div>
            <?php if ($ownerPosts): ?>
                <div class="team-owner-list">
                    <?php foreach ($ownerPosts as $post):
                        $st = (string) ($post['status'] ?? 'open');
                        $statusBadgeClass = $st === 'open' ? 'is-open' : ($st === 'team_complete' ? 'is-complete' : 'is-archived');
                        $statusBadgeLabel = $st === 'open' ? 'Open' : ($st === 'team_complete' ? 'Team Complete' : 'Archived');
                        $postCompetitionClosed = isLombaRegistrationClosed($post['deadline_daftar'] ?? null);
                        $statusActions = match ($st) {
                            'open' => [
                                ['status' => 'team_complete', 'label' => 'Tandai Team Complete', 'class' => 'team-primary-button'],
                                ['status' => 'archived',      'label' => 'Arsipkan (Archived)',  'class' => 'team-secondary-button'],
                            ],
                            'team_complete' => [
                                ['status' => 'open',     'label' => 'Buka Kembali (Open)', 'class' => 'team-secondary-button'],
                                ['status' => 'archived', 'label' => 'Arsipkan (Archived)', 'class' => 'team-secondary-button'],
                            ],
                            default => [
                                ['status' => 'open', 'label' => 'Aktifkan Kembali (Open)', 'class' => 'team-secondary-button'],
                            ],
                        };
                        if ($postCompetitionClosed) {
                            $statusActions = array_values(array_filter(
                                $statusActions,
                                static fn(array $action): bool => $action['status'] !== 'open'
                            ));
                        }
                    ?>
                        <article class="team-owner-row">
                            <div>
                                <span class="team-status-pill <?= $statusBadgeClass ?>">
                                    <?= e($statusBadgeLabel) ?>
                                </span>
                                <?php if ($postCompetitionClosed): ?>
                                    <span class="badge-pill badge-red">Pendaftaran Ditutup</span>
                                <?php endif; ?>
                                <h3><?= e($post['nama_lomba']) ?></h3>
                                <p>Bidang dibutuhkan: <strong><?= e($post['bidang_dibutuhkan']) ?></strong> &bull; <?= (int) $post['jumlah_anggota'] ?> anggota tambahan</p>
                            </div>

                            <div class="team-owner-actions" style="display:flex; flex-wrap:wrap; gap:0.55rem; align-items:center;">
                                <?php foreach ($statusActions as $action): ?>
                                    <form method="post" action="<?= url('?page=team-post-status') ?>">
                                        <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
                                        <input type="hidden" name="id_post" value="<?= (int) $post['id_post'] ?>">
                                        <input type="hidden" name="status" value="<?= e($action['status']) ?>">
                                        <button class="<?= e($action['class']) ?>" type="submit"><?= e($action['label']) ?></button>
                                    </form>
                                <?php endforeach; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="team-empty-compact">Anda belum membuat posting pencarian anggota tim.</div>
            <?php endif; ?>
        </section>
    </main>
</div>

<script src="<?= url('public/js/cari-partner.js') ?>?v=<?= (int) filemtime(__DIR__ . '/../../public/js/cari-partner.js') ?>" defer></script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
