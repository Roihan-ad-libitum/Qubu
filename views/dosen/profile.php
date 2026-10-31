<?php

$title             = $title ?? 'Informasi Profil Dosen - ' . APP_NAME;
$extraCss          = ['public/css/components.css', 'public/css/dashboard.css', 'public/css/profile.css'];
$activeDosenNavKey = 'profile';

require_once __DIR__ . '/../components/profile-form-cards.php';
require_once __DIR__ . '/../layouts/headers.php';
?>

<div class="profile-page">
    <div class="profile-hero-bg" aria-hidden="true"></div>

    <?php require __DIR__ . '/../components/dosen-navbar.php'; ?>

    <main class="profile-main-container">
        <?php renderProfileFlashAlerts($error ?? null, $success ?? null); ?>

        <form method="POST" action="<?= url('?page=dosen-profile') ?>" id="form-update-dosen-profile" enctype="multipart/form-data">
            <input type="hidden" name="_csrf" value="<?= e($csrfToken ?? Csrf::token()) ?>">
            <div class="profile-grid">

                <?php renderProfileLeftCard([
                    'ariaLabel'       => 'Informasi Ringkas Profil Dosen',
                    'displayName'     => (string) ($data['nama_dosen'] ?? ''),
                    'hiddenInputName' => 'nama_dosen',
                    'hiddenInputId'   => 'input-nama-dosen',
                    'username'        => (string) ($data['username'] ?? ''),
                    'roleLabel'       => 'Dosen',
                    'foto'            => $data['foto'] ?? null,
                ]); ?>

                <section class="profile-card-right" aria-label="Formulir Detail Dosen">
                    <div class="profile-form-group">
                        <label for="nidn" class="profile-form-label">NIDN / NIP</label>
                        <input
                            type="text"
                            name="nidn"
                            id="nidn"
                            class="profile-input"
                            value="<?= e($data['nidn']) ?>"
                            placeholder="Masukkan NIDN/NIP"
                            required
                        >
                    </div>

                    <div class="profile-form-group">
                        <label for="email" class="profile-form-label">Email Kontak Bimbingan</label>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            class="profile-input"
                            value="<?= e($data['email'] ?? '') ?>"
                            placeholder="nama@dosen.unsika.ac.id"
                            required
                        >
                    </div>

                    <div class="profile-form-group">
                        <label for="no_hp" class="profile-form-label">Nomor Telepon / WhatsApp</label>
                        <input
                            type="tel"
                            name="no_hp"
                            id="no_hp"
                            class="profile-input"
                            value="<?= e(($data['no_hp'] ?? '') === '-' ? '' : ($data['no_hp'] ?? '')) ?>"
                            placeholder="081234567890"
                            required
                        >
                    </div>

                    <div class="profile-form-group">
                        <label for="status" class="profile-form-label">Ketersediaan</label>
                        <select name="status" id="status" class="profile-input profile-select" required>
                            <option value="bersedia" <?= (strtolower($data['status']) === 'bersedia') ? 'selected' : '' ?>>Bersedia</option>
                            <option value="tidak_bersedia" <?= (strtolower($data['status']) === 'tidak_bersedia') ? 'selected' : '' ?>>Tidak Bersedia</option>
                        </select>
                    </div>

                    <div class="profile-form-group advisor-contact-consent">
                        <label class="profile-form-label" for="contact_visible">Informasi Kontak</label>
                        <label class="advisor-consent-option">
                            <input type="checkbox" name="contact_visible" id="contact_visible" value="1" <?= !empty($data['contact_visible']) ? 'checked' : '' ?>>
                            <span>Izinkan mahasiswa melihat email dan nomor telepon saya di direktori dosen.</span>
                        </label>
                        <p class="advisor-consent-help">Kontak hanya tampil jika status ketersediaan Anda “Bersedia”.</p>
                    </div>

                    <?php renderProfileTagsField([
                        'label'            => 'Bidang Keahlian',
                        'tags'             => $tags ?? [],
                        'availableOptions' => $availableOptions ?? BIDANG_QUBU,
                        'placeholder'      => 'Pilih Bidang Keahlian...',
                        'hiddenInputName'  => 'bidang_keahlian',
                        'hiddenInputId'    => 'input-bidang-keahlian',
                        'removeTitle'      => 'Hapus keahlian',
                    ]); ?>

                    <?php renderProfileSaveActions(); ?>
                </section>
            </div>
        </form>
    </main>
</div>

<script src="<?= url('public/js/profile-editor.js') ?>?v=<?= (int) @filemtime(__DIR__ . '/../../public/js/profile-editor.js') ?>" defer></script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
