<?php
$activeAdminPage = 'dosen';
require_once __DIR__ . '/../layouts/headers.php';
?>
<div class="admin-page">
<?php require __DIR__ . '/_nav.php'; ?>
    <main class="admin-panel">
        <div class="admin-heading">
            <div>
                <h1>Dosen Pembimbing</h1>
                <p>Pantau profil dan kesediaan dosen. Halaman ini tidak mengubah data profil.</p>
            </div>
        </div>

        <section class="admin-panel-card">
            <div class="admin-card-heading">
                <h2>Daftar dosen <span class="admin-muted">(maksimal 200 hasil)</span></h2>
                <form class="admin-search-form" method="get" action="<?= url('index.php') ?>">
                    <input type="hidden" name="page" value="admin-dosen">
                    <input type="search" name="q" value="<?= e($keyword) ?>" placeholder="Nama, NIDN, bidang keahlian...">
                    <button type="submit">Cari</button>
                </form>
            </div>
            <?php if (!$lecturers): ?>
                <div class="admin-empty">Belum ada data dosen yang cocok dengan pencarian.</div>
            <?php else: ?>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead><tr><th>Dosen</th><th>NIDN</th><th>Bidang keahlian</th><th>Kesediaan</th><th>Kontak</th></tr></thead>
                        <tbody>
                        <?php foreach ($lecturers as $lecturer): ?>
                            <?php
                            $isAvailable    = ($lecturer['status'] ?? '') === 'bersedia';
                            $canShowContact = $isAvailable && (bool) ($lecturer['contact_visible'] ?? false);
                            $photoUrl       = resolveProfilePhotoUrl($lecturer['foto'] ?? null);
                            $initials       = buildInitials($lecturer['nama_dosen'], 'DS');
                            ?>
                            <tr>
                                <td>
                                    <div class="admin-user-cell">
                                        <div class="admin-user-avatar" aria-hidden="true">
                                            <?php if ($photoUrl !== null): ?>
                                                <img src="<?= e($photoUrl) ?>" alt="Foto <?= e($lecturer['nama_dosen']) ?>" class="admin-user-avatar-img" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                                                <span class="admin-user-avatar-initials" style="display: none;"><?= e($initials) ?></span>
                                            <?php else: ?>
                                                <span class="admin-user-avatar-initials"><?= e($initials) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <strong><?= e($lecturer['nama_dosen']) ?></strong>
                                            <small>@<?= e($lecturer['username']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?= e($lecturer['nidn']) ?></td>
                                <td><?= e($lecturer['bidang_keahlian']) ?></td>
                                <td><span class="admin-badge <?= $isAvailable ? 'success' : 'muted' ?>"><?= $isAvailable ? 'Bersedia' : 'Tidak bersedia' ?></span></td>
                                <td>
                                    <?php if (!$isAvailable): ?>
                                        <span class="admin-muted">Dosen tidak bersedia saat ini</span>
                                    <?php elseif (!$canShowContact): ?>
                                        <span class="admin-muted">Kontak disembunyikan oleh pemilik</span>
                                    <?php else: ?>
                                        <?= e($lecturer['email']) ?><small><?= e($lecturer['no_hp']) ?></small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p class="admin-note">Kontak hanya ditampilkan jika dosen bersedia dan telah mengaktifkan persetujuan tampil.</p>
            <?php endif; ?>
        </section>
    </main>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
