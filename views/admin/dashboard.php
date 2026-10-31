<?php
$activeAdminPage = 'dashboard';
require_once __DIR__ . '/../layouts/headers.php';
?>
<div class="admin-page">
<?php require __DIR__ . '/_nav.php'; ?>
    <main class="admin-panel">
        <div class="admin-heading">
            <div>
                <h1>Dashboard Admin</h1>
                <p>Ringkasan aktivitas dan pengelolaan platform Qubu.</p>
            </div>
            <a class="admin-primary-button" href="<?= url('?page=admin-lomba-form') ?>">Tambah Perlombaan</a>
        </div>

        <section class="admin-stat-grid" aria-label="Ringkasan platform">
            <article class="admin-stat-card"><span>Total pengguna</span><strong><?= number_format($summary['users'], 0, ',', '.') ?></strong></article>
            <article class="admin-stat-card"><span>Mahasiswa</span><strong><?= number_format($summary['students'], 0, ',', '.') ?></strong></article>
            <article class="admin-stat-card"><span>Dosen</span><strong><?= number_format($summary['lecturers'], 0, ',', '.') ?></strong></article>
            <article class="admin-stat-card"><span>Perlombaan</span><strong><?= number_format($summary['competitions'], 0, ',', '.') ?></strong></article>
            <article class="admin-stat-card"><span>Posting tim terbuka</span><strong><?= number_format($summary['open_team_posts'], 0, ',', '.') ?></strong></article>
        </section>

        <section class="admin-panel-card">
            <div class="admin-card-heading">
                <h2>Perlombaan terbaru</h2>
                <a class="admin-secondary-button" href="<?= url('?page=admin-lomba') ?>">Kelola semua lomba</a>
            </div>
            <?php if (!$recentCompetitions): ?>
                <div class="admin-empty">Belum ada data perlombaan.</div>
            <?php else: ?>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead><tr><th>Perlombaan</th><th>Penyelenggara</th><th>Bidang</th><th>Deadline</th></tr></thead>
                        <tbody>
                        <?php foreach ($recentCompetitions as $competition): ?>
                            <tr>
                                <td><strong><?= e($competition['nama_lomba'] ?? '') ?></strong></td>
                                <td><?= e($competition['penyelenggara'] ?? '') ?></td>
                                <td><?= e($competition['bidang'] ?? '') ?></td>
                                <td>
                                    <?= !empty($competition['deadline_daftar']) ? e(date('d M Y', strtotime($competition['deadline_daftar']))) : '—' ?>
                                    <?php if (isLombaRegistrationClosed($competition['deadline_daftar'] ?? null)): ?>
                                        <small>Pendaftaran Ditutup</small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
