<?php
$activeAdminPage = 'lomba';
require_once __DIR__ . '/../layouts/headers.php';
?>
<div class="admin-page">
<?php require __DIR__ . '/_nav.php'; ?>
    <main class="admin-panel">
        <div class="admin-heading">
            <div>
                <h1>Kelola Perlombaan</h1>
                <p>Tambah, cari, perbarui, dan hapus informasi perlombaan.</p>
            </div>
            <a class="admin-primary-button" href="<?= url('?page=admin-lomba-form') ?>">Tambah Perlombaan</a>
        </div>

        <?php if ($error): ?><div class="admin-alert error" role="alert"><?= e($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="admin-alert success" role="status"><?= e($success) ?></div><?php endif; ?>

        <section class="admin-panel-card">
            <div class="admin-card-heading">
                <h2>Daftar perlombaan</h2>
                <form class="admin-search-form" method="get" action="<?= url('index.php') ?>">
                    <input type="hidden" name="page" value="admin-lomba">
                    <input type="search" name="q" value="<?= e($keyword) ?>" placeholder="Cari nama, penyelenggara, bidang...">
                    <button type="submit">Cari</button>
                </form>
            </div>
            <?php if (!$competitions): ?>
                <div class="admin-empty">Tidak ada perlombaan yang cocok dengan pencarian.</div>
            <?php else: ?>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead><tr><th>Perlombaan</th><th>Penyelenggara</th><th>Bidang / Tingkat</th><th>Deadline</th><th>Aksi</th></tr></thead>
                        <tbody>
                        <?php foreach ($competitions as $competition): ?>
                            <tr>
                                <td><strong><?= e($competition['nama_lomba'] ?? '') ?></strong><small><?= e($competition['partisipasi'] ?? '') ?></small></td>
                                <td><?= e($competition['penyelenggara'] ?? '') ?></td>
                                <td><?= e($competition['bidang'] ?? '') ?><small><?= e($competition['tingkat'] ?? '') ?></small></td>
                                <td>
                                    <?= !empty($competition['deadline_daftar']) ? e(date('d M Y', strtotime($competition['deadline_daftar']))) : '—' ?>
                                    <?php if (isLombaRegistrationClosed($competition['deadline_daftar'] ?? null)): ?>
                                        <small>Pendaftaran Ditutup</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="admin-table-actions">
                                        <a class="admin-secondary-button" href="<?= url('?page=admin-lomba-form&id=' . (int) $competition['id_lomba']) ?>">Edit</a>
                                        <form method="post" action="<?= url('?page=admin-lomba') ?>" onsubmit="return confirm('Hapus perlombaan ini? Tindakan ini tidak dapat dibatalkan.');">
                                            <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
                                            <input type="hidden" name="id" value="<?= (int) $competition['id_lomba'] ?>">
                                            <button class="admin-danger-button" type="submit">Hapus</button>
                                        </form>
                                    </div>
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
