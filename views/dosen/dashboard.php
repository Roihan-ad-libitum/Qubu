<?php
$extraCss = [
    'public/css/components.css',
    'public/css/dashboard.css?v=' . (int) filemtime(__DIR__ . '/../../public/css/dashboard.css'),
    'public/css/team-flow.css?v=' . (int) filemtime(__DIR__ . '/../../public/css/team-flow.css'),
];
require_once __DIR__ . '/../layouts/headers.php';
$name = (string) ($profile['nama_dosen'] ?? Session::get('nama_lengkap', 'Dosen'));
$available = ($profile['status'] ?? '') === 'bersedia';
$contactVisible = $available && !empty($profile['contact_visible']);
?>
<div class="dash-page team-flow-page lecturer-dashboard-page">
    <?php $activeDosenNavKey = 'dashboard'; require __DIR__ . '/../components/dosen-navbar.php'; ?>

    <section class="dash-hero-section team-flow-hero lecturer-dashboard-hero">
        <div class="dash-hero-noise"></div>
        <div class="dash-hero-content team-flow-hero-inner">
            <h1 class="dash-hero-greeting">Selamat datang, <?= e($name) ?></h1>
            <p class="team-flow-hero-description">Kelola profil, bidang keahlian, ketersediaan, dan persetujuan informasi kontak Anda.</p>
        </div>
    </section>

    <main class="team-flow-main lecturer-dashboard-main">
        <?php if (!empty($success)): ?>
            <div class="team-alert team-alert-success" role="status"><?= e($success) ?></div>
        <?php endif; ?>

        <?php if (empty($profile)): ?>
            <section class="team-empty-state" role="alert">
                <h2>Profil dosen belum tersedia</h2>
                <p>Akun ini belum memiliki data profil dosen. Silakan hubungi administrator untuk memperbaikinya.</p>
            </section>
        <?php else: ?>
            <section class="lecturer-dashboard-card">
                <div class="lecturer-dashboard-card-heading">
                    <div>
                        <span class="team-eyebrow team-eyebrow-dark">PROFIL SAYA</span>
                        <h2><?= e($name) ?></h2>
                        <p><?= e($profile['bidang_keahlian'] ?? 'Bidang keahlian belum diatur') ?></p>
                    </div>
                    <span class="team-status-pill <?= $available ? 'is-open' : 'is-complete' ?>">
                        <?= $available ? 'Bersedia membimbing' : 'Tidak bersedia saat ini' ?>
                    </span>
                </div>

                <div class="lecturer-dashboard-details">
                    <div>
                        <span>NIDN/NIP</span>
                        <strong><?= e($profile['nidn'] ?? '-') ?></strong>
                    </div>
                    <div>
                        <span>Username</span>
                        <strong>@<?= e($profile['username'] ?? Session::get('username', '-')) ?></strong>
                    </div>
                    <div>
                        <span>Email Bimbingan</span>
                        <strong><?= e($profile['email'] ?? '-') ?></strong>
                    </div>
                    <div>
                        <span>WhatsApp / Telepon</span>
                        <strong><?= e($profile['no_hp'] ?? '-') ?></strong>
                    </div>
                    <div>
                        <span>Visibilitas Kontak di Direktori</span>
                        <strong><?= $contactVisible ? 'Ditampilkan kepada Mahasiswa' : 'Disembunyikan' ?></strong>
                    </div>
                </div>

                <div class="lecturer-dashboard-notice">
                    <?php if (!$available): ?>
                        Kontak Anda tidak ditampilkan di direktori selama status tidak bersedia.
                    <?php elseif (!$contactVisible): ?>
                        Kontak Anda tetap privat sampai Anda mengaktifkan persetujuan di halaman profil.
                    <?php else: ?>
                        Mahasiswa dapat melihat kontak Anda di direktori dosen saat status Anda bersedia.
                    <?php endif; ?>
                </div>

                <a class="team-primary-button lecturer-profile-link" href="<?= url('?page=dosen-profile') ?>">Kelola Profil dan Ketersediaan</a>
            </section>
        <?php endif; ?>
    </main>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
