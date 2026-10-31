<?php
$studentNavName = $profile['nama_mahasiswa'] ?? Session::get('nama_lengkap', 'Mahasiswa');
$studentNavPhoto = $profile['foto'] ?? (Session::get('demo_profile_data')['foto'] ?? null);
$studentNavProgram = $profile['program_studi'] ?? 'Informatika';
$studentNavInterest = $profile['bidang_minat'] ?? '';
$activeStudentNavKey = 'teams';
$extraCss = ['public/css/components.css', 'public/css/dashboard.css', 'public/css/team-flow.css'];
require_once __DIR__ . '/../layouts/headers.php';
$fieldOptions = defined('BIDANG_QUBU') ? BIDANG_QUBU : [];
?>
<div class="dash-page team-flow-page">
    <?php require __DIR__ . '/../components/student-navbar.php'; ?>

    <section class="dash-hero-section team-flow-hero dash-hero-compact">
        <div class="dash-hero-noise"></div>
        <div class="dash-hero-content team-flow-hero-inner">
            <h1 class="dash-hero-greeting" style="font-size: clamp(1.75rem, 3vw, 2.35rem);">Buat Posting Cari Tim</h1>
            <p class="team-flow-hero-description">Publikasikan kebutuhan anggota tim Anda agar mahasiswa lain yang sebidang dapat menghubungi Anda.</p>
        </div>
    </section>

    <main class="team-form-main" style="margin-top: 1.5rem;">
        <a class="team-back-link" href="<?= e(url('?page=team-posts' . (!empty($selectedId) ? '&id_lomba=' . (int) $selectedId : ''))) ?>">← Kembali ke Cari Partner &amp; Tim</a>
        <section class="team-form-card">
            <span class="team-eyebrow team-eyebrow-dark">POSTING PENCARIAN TIM</span>
            <h1>Buat Posting Cari Tim</h1>
            <p class="team-form-intro">
                Jelaskan kebutuhan anggota tim Anda. Selama posting berstatus <strong>Open</strong>, minimal satu kanal kontak (WhatsApp atau Email) wajib ditampilkan agar calon rekan tim dapat menghubungi Anda.
            </p>
            <?php if (!empty($error)): ?>
                <div class="team-alert team-alert-error" role="alert"><?= e($error) ?></div>
            <?php endif; ?>
            <form method="post" action="<?= url('?page=team-post-create') ?>" class="team-post-form">
                <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
                <label for="id_lomba">Perlombaan Kategori Tim</label>
                <select id="id_lomba" name="id_lomba" required>
                    <option value="">Pilih perlombaan...</option>
                    <?php foreach ($competitions as $competition): ?>
                        <?php if (in_array($competition['partisipasi'] ?? '', ['Tim', 'Individu/Tim'], true)): ?>
                            <option value="<?= (int) $competition['id_lomba'] ?>" <?= (int) ($selectedId ?? ($old['id_lomba'] ?? 0)) === (int) $competition['id_lomba'] ? 'selected' : '' ?>>
                                <?= e($competition['nama_lomba']) ?> (<?= e($competition['bidang']) ?>)
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>

                <fieldset class="team-skill-fieldset">
                    <legend>Bidang yang Dibutuhkan (Pilih minimal 1)</legend>
                    <div class="team-skill-options">
                        <?php foreach ($fieldOptions as $field): ?>
                            <?php $selectedFields = (array) ($old['bidang_dibutuhkan'] ?? []); ?>
                            <label><input type="checkbox" name="bidang_dibutuhkan[]" value="<?= e($field) ?>" <?= in_array($field, $selectedFields, true) ? 'checked' : '' ?>> <span><?= e($field) ?></span></label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>

                <label for="jumlah_anggota">Jumlah Anggota Tambahan yang Dibutuhkan</label>
                <input type="number" id="jumlah_anggota" name="jumlah_anggota" min="1" max="20" value="<?= e((string) ($old['jumlah_anggota'] ?? '2')) ?>" required>

                <label for="deskripsi">Deskripsi Kebutuhan &amp; Kriteria Rekan Tim</label>
                <textarea id="deskripsi" name="deskripsi" rows="5" maxlength="2000" placeholder="Jelaskan peran yang dibutuhkan, target lomba, atau kriteria kolaborasi..." required><?= e($old['deskripsi'] ?? '') ?></textarea>

                <label class="team-consent-option">
                    <input type="checkbox" name="contact_opt_in" value="1" <?= !empty($old['contact_opt_in']) ? 'checked' : '' ?>>
                    <span>Gunakan kontak khusus untuk posting ini (opsional)</span>
                </label>
                <label for="contact_whatsapp">Nomor WhatsApp Khusus (opsional jika menggunakan kontak akun)</label>
                <input type="tel" id="contact_whatsapp" name="contact_whatsapp" maxlength="30" value="<?= e($old['contact_whatsapp'] ?? '') ?>" placeholder="081234567890">
                <label for="contact_email">Alamat Email Khusus (opsional jika menggunakan kontak akun)</label>
                <input type="email" id="contact_email" name="contact_email" maxlength="100" value="<?= e($old['contact_email'] ?? '') ?>" placeholder="nama@email.com">
                <p class="team-consent-help">
                    Apabila opsi kontak khusus tidak dicentang, sistem menampilkan kontak WhatsApp (<strong><?= e($profile['no_hp'] ?? '-') ?></strong>) dan Email (<strong><?= e($profile['email'] ?? Session::get('email', '-')) ?></strong>) dari akun Anda. Setelah kebutuhan anggota terpenuhi, Anda dapat mengubah status posting menjadi <strong>Team Complete</strong>.
                </p>

                <button type="submit" class="team-primary-button team-submit-button">Terbitkan Posting (Status: Open)</button>
            </form>
        </section>
    </main>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
