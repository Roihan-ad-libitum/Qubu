<?php
$activeAdminPage = 'users';
require_once __DIR__ . '/../layouts/headers.php';
?>
<div class="admin-page">
<?php require __DIR__ . '/_nav.php'; ?>
    <main class="admin-panel">
        <div class="admin-heading">
            <div>
                <h1>Pengguna</h1>
                <p>Data akun ditampilkan untuk pemantauan. Perubahan dan penghapusan akun belum tersedia.</p>
            </div>
        </div>

        <section class="admin-panel-card">
            <div class="admin-card-heading">
                <h2>Daftar pengguna <span class="admin-muted">(maksimal 200 hasil)</span></h2>
                <form class="admin-search-form" method="get" action="<?= url('index.php') ?>">
                    <input type="hidden" name="page" value="admin-users">
                    <input type="search" name="q" value="<?= e($keyword) ?>" placeholder="Nama, username, email, NPM/NIDN...">
                    <select name="role" aria-label="Filter peran">
                        <option value="">Semua peran</option>
                        <option value="peserta" <?= $role === 'peserta' ? 'selected' : '' ?>>Mahasiswa</option>
                        <option value="dosen" <?= $role === 'dosen' ? 'selected' : '' ?>>Dosen</option>
                        <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                    <button type="submit">Terapkan</button>
                </form>
            </div>
            <?php if (!$users): ?>
                <div class="admin-empty">Tidak ada pengguna yang cocok dengan pencarian.</div>
            <?php else: ?>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead><tr><th>Nama / Akun</th><th>Peran</th><th>Identitas akademik</th><th>Kontak akun</th><th>Status dosen</th></tr></thead>
                        <tbody>
                        <?php foreach ($users as $user): ?>
                            <?php
                            $displayName  = $user['nama_mahasiswa'] ?: ($user['nama_dosen'] ?: $user['username']);
                            $roleLabel    = ['peserta' => 'Mahasiswa', 'dosen' => 'Dosen', 'admin' => 'Admin'][$user['role']] ?? $user['role'];
                            $userFoto     = $user['role'] === 'peserta' ? ($user['mahasiswa_foto'] ?? null) : ($user['dosen_foto'] ?? null);
                            $photoUrl     = resolveProfilePhotoUrl($userFoto);
                            $userInitials = buildInitials($displayName, $user['role'] === 'dosen' ? 'DS' : 'QB');
                            ?>
                            <tr>
                                <td>
                                    <div class="admin-user-cell">
                                        <div class="admin-user-avatar" aria-hidden="true">
                                            <?php if ($photoUrl !== null): ?>
                                                <img src="<?= e($photoUrl) ?>" alt="Foto <?= e($displayName) ?>" class="admin-user-avatar-img" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                                                <span class="admin-user-avatar-initials" style="display: none;"><?= e($userInitials) ?></span>
                                            <?php else: ?>
                                                <span class="admin-user-avatar-initials"><?= e($userInitials) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <strong><?= e($displayName) ?></strong>
                                            <small>@<?= e($user['username']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="admin-badge"><?= e($roleLabel) ?></span></td>
                                <td>
                                    <?php if ($user['role'] === 'peserta'): ?>
                                        <?= e($user['npm'] ?? '—') ?><small><?= e($user['program_studi'] ?? '') ?><?= !empty($user['angkatan']) ? ' · ' . e($user['angkatan']) : '' ?></small>
                                    <?php elseif ($user['role'] === 'dosen'): ?>
                                        <?= e($user['nidn'] ?? '—') ?><small><?= e($user['bidang_keahlian'] ?? '') ?></small>
                                    <?php else: ?>—<?php endif; ?>
                                </td>
                                <td><?= e($user['email']) ?><small><?= e($user['no_hp']) ?></small></td>
                                <td>
                                    <?php if ($user['role'] !== 'dosen'): ?>—<?php else: ?>
                                        <span class="admin-badge <?= ($user['dosen_status'] ?? '') === 'bersedia' ? 'success' : 'muted' ?>">
                                            <?= ($user['dosen_status'] ?? '') === 'bersedia' ? 'Bersedia' : 'Tidak bersedia' ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p class="admin-note">Tidak ada kata sandi atau data autentikasi yang ditampilkan pada halaman ini.</p>
            <?php endif; ?>
        </section>
    </main>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
