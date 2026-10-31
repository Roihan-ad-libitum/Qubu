<?php

$title               = $title ?? 'Informasi Profil Mahasiswa - ' . APP_NAME;
$extraCss            = ['public/css/components.css', 'public/css/dashboard.css', 'public/css/profile.css'];
$activeStudentNavKey = 'profile';
$prodiOptions        = $prodiOptions ?? (defined('PRODI_FASILKOM') ? PRODI_FASILKOM : ['Informatika', 'Sistem Informasi']);

require_once __DIR__ . '/../components/profile-form-cards.php';
require_once __DIR__ . '/../layouts/headers.php';
?>

<div class="profile-page">
    <div class="profile-hero-bg" aria-hidden="true"></div>

    <?php require __DIR__ . '/../components/student-navbar.php'; ?>

    <main class="profile-main-container">
        <?php renderProfileFlashAlerts($error ?? null, $success ?? null); ?>

        <form method="POST" action="<?= url('?page=mahasiswa-profile') ?>" id="form-update-profile" enctype="multipart/form-data">
            <input type="hidden" name="_csrf" value="<?= e($csrfToken ?? Csrf::token()) ?>">
            <div class="profile-grid">

                <?php renderProfileLeftCard([
                    'ariaLabel'       => 'Informasi Ringkas Profil',
                    'displayName'     => (string) ($data['nama_mahasiswa'] ?? ''),
                    'hiddenInputName' => 'nama_mahasiswa',
                    'hiddenInputId'   => 'input-nama-mahasiswa',
                    'username'        => (string) ($data['username'] ?? ''),
                    'roleLabel'       => 'Mahasiswa',
                    'foto'            => $data['foto'] ?? null,
                    'github'          => $data['github'] ?? '',
                ]); ?>

                <section class="profile-card-right" aria-label="Formulir Detail Mahasiswa">
                    <div class="profile-form-group">
                        <label for="npm" class="profile-form-label">NPM</label>
                        <input
                            type="text"
                            name="npm"
                            id="npm"
                            class="profile-input"
                            value="<?= e($data['npm']) ?>"
                            placeholder="Masukkan NPM"
                            required
                        >
                    </div>

                    <div class="profile-form-group">
                        <label for="program_studi" class="profile-form-label">Program Studi</label>
                        <select name="program_studi" id="program_studi" class="profile-input" required>
                            <?php foreach ($prodiOptions as $prodiOpt): ?>
                                <option value="<?= e($prodiOpt) ?>" <?= strcasecmp((string) $data['program_studi'], (string) $prodiOpt) === 0 ? 'selected' : '' ?>>
                                    <?= e($prodiOpt) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="profile-form-group">
                        <label for="angkatan" class="profile-form-label">Angkatan</label>
                        <input
                            type="text"
                            name="angkatan"
                            id="angkatan"
                            class="profile-input"
                            value="<?= e($data['angkatan']) ?>"
                            placeholder="Masukkan Tahun Angkatan"
                            required
                        >
                    </div>

                    <div class="profile-form-group">
                        <label for="email" class="profile-form-label">Email Kontak</label>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            class="profile-input"
                            value="<?= e($data['email'] ?? '') ?>"
                            placeholder="nama@student.unsika.ac.id"
                            required
                        >
                    </div>

                    <div class="profile-form-group">
                        <label for="github" class="profile-form-label">GitHub <span style="font-weight: 500; color: #64748b; font-size: 0.85em;">(Opsional)</span></label>
                        <input
                            type="text"
                            name="github"
                            id="github"
                            class="profile-input"
                            value="<?= e($data['github'] ?? '') ?>"
                            placeholder="https://github.com/username atau username"
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
                        <label for="bio" class="profile-form-label">Bio Singkat</label>
                        <textarea
                            name="bio"
                            id="bio"
                            class="profile-input profile-bio-input"
                            rows="4"
                            maxlength="1000"
                            placeholder="Ceritakan minat, pengalaman, atau tujuanmu mengikuti lomba..."
                        ><?= e($data['bio']) ?></textarea>
                    </div>

                    <?php renderProfileTagsField([
                        'label'            => 'Bidang Peminatan',
                        'tags'             => $tags ?? [],
                        'availableOptions' => $availableOptions ?? BIDANG_QUBU,
                        'placeholder'      => 'Pilih Bidang Peminatan...',
                        'hiddenInputName'  => 'bidang_minat',
                        'hiddenInputId'    => 'input-bidang-minat',
                        'removeTitle'      => 'Hapus minat',
                    ]); ?>

                    <?php renderProfileSaveActions(); ?>
                </section>
            </div>
        </form>
    </main>
</div>

<script src="<?= url('public/js/profile-editor.js') ?>?v=<?= (int) @filemtime(__DIR__ . '/../../public/js/profile-editor.js') ?>" defer></script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
