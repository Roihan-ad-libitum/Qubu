<?php

if (!function_exists('renderProfileFlashAlerts')) {
    function renderProfileFlashAlerts(?string $error = null, ?string $success = null): void
    {
        if (!empty($error)): ?>
            <div class="auth-alert auth-alert-error" role="alert" style="margin-bottom: 2rem;">
                <?= e($error) ?>
            </div>
        <?php endif;

        if (!empty($success)): ?>
            <div class="auth-alert auth-alert-success" role="alert" style="margin-bottom: 2rem;">
                <?= e($success) ?>
            </div>
        <?php endif;
    }
}

if (!function_exists('renderProfileLeftCard')) {

    function renderProfileLeftCard(array $config): void
    {
        $ariaLabel       = (string) ($config['ariaLabel'] ?? 'Informasi Ringkas Profil');
        $displayName     = (string) ($config['displayName'] ?? '');
        $hiddenInputName = (string) ($config['hiddenInputName'] ?? 'nama_mahasiswa');
        $hiddenInputId   = (string) ($config['hiddenInputId'] ?? 'input-nama-mahasiswa');
        $username        = ltrim((string) ($config['username'] ?? ''), '@');
        $roleLabel       = (string) ($config['roleLabel'] ?? 'Mahasiswa');
        $foto            = $config['foto'] ?? null;
        $photoUrl        = resolveProfilePhotoUrl($foto);
        $hasFoto         = $photoUrl !== null;
        $initials        = buildInitials($displayName, $roleLabel === 'Dosen' ? 'DS' : 'QB');
        ?>
        <section class="profile-card-left" aria-label="<?= e($ariaLabel) ?>">
            <header class="profile-card-header">
                <h1 class="profile-card-title">Informasi Profil</h1>
            </header>

            <div class="profile-card-body">
                <div class="profile-avatar-wrapper" title="Klik untuk mengganti foto profil">
                    <div class="profile-avatar-circle" id="profile-avatar-circle">
                        <img
                            src="<?= $hasFoto ? e($photoUrl) : '' ?>"
                            alt="Foto <?= e($displayName) ?>"
                            class="profile-avatar-img"
                            id="profile-img-preview"
                            style="<?= $hasFoto ? 'display: block;' : 'display: none;' ?>"
                            onerror="this.style.display='none'; var fb=document.getElementById('profile-avatar-fallback'); if(fb) fb.style.display='flex';"
                        >
                        <div
                            class="profile-avatar-fallback"
                            id="profile-avatar-fallback"
                            style="<?= $hasFoto ? 'display: none;' : 'display: flex;' ?>"
                            aria-hidden="true"
                        ><?= e($initials) ?></div>
                    </div>

                    <label for="input-profile-photo" class="profile-avatar-overlay" title="Pilih foto baru dari penyimpanan">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                            <circle cx="12" cy="13" r="4"></circle>
                        </svg>
                    </label>
                    <input type="file" name="foto" id="input-profile-photo" accept="image/png, image/jpeg, image/jpg, image/webp" style="display: none;">
                </div>

                <button
                    type="button"
                    class="profile-avatar-remove-btn"
                    id="btn-remove-profile-photo"
                    title="Hapus foto profil"
                    style="<?= $hasFoto ? 'display: inline-flex;' : 'display: none;' ?>"
                >
                    ✕ Hapus Foto
                </button>
                <input type="hidden" name="hapus_foto" id="input-hapus-foto" value="0">

                <h2 class="profile-name" id="display-nama"><?= e($displayName) ?></h2>
                <input type="hidden" name="<?= e($hiddenInputName) ?>" id="<?= e($hiddenInputId) ?>" value="<?= e($displayName) ?>">

                <p class="profile-handle" id="display-handle">@<?= e($username) ?></p>

                <div class="profile-role-badge">
                    <?= e($roleLabel) ?>
                </div>

                <?php
                $githubUrl     = normalizeGithubUrl($config['github'] ?? '') ?: '';
                $githubDisplay = formatGithubDisplay($githubUrl);
                if ($githubUrl !== '' && $githubDisplay !== ''):
                ?>
                    <a
                        href="<?= e($githubUrl) ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="profile-github-badge"
                        title="Buka profil GitHub"
                    >
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M12 0.5C5.37 0.5 0 5.87 0 12.5c0 5.3 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.295 24 17.795 24 12.5 24 5.87 18.63 0.5 12 0.5z"/>
                        </svg>
                        <span><?= e($githubDisplay) ?></span>
                    </a>
                <?php endif; ?>
            </div>
        </section>
        <?php
    }
}

if (!function_exists('renderProfileTagsField')) {

    function renderProfileTagsField(array $config): void
    {
        $label            = (string) ($config['label'] ?? 'Bidang Peminatan');
        $tags             = (array) ($config['tags'] ?? []);
        $availableOptions = !empty($config['availableOptions']) && is_array($config['availableOptions'])
            ? $config['availableOptions']
            : (defined('BIDANG_QUBU') ? BIDANG_QUBU : []);
        $placeholder      = (string) ($config['placeholder'] ?? 'Pilih Bidang...');
        $hiddenInputName  = (string) ($config['hiddenInputName'] ?? 'bidang_minat');
        $hiddenInputId    = (string) ($config['hiddenInputId'] ?? 'input-bidang-minat');
        $removeTitle      = (string) ($config['removeTitle'] ?? 'Hapus bidang');
        ?>
        <div class="profile-form-group">
            <label class="profile-form-label"><?= e($label) ?></label>

            <div class="profile-tags-container" id="profile-tags-container">
                <?php foreach ($tags as $tag): ?>
                    <span class="profile-tag-pill" data-tag="<?= e((string) $tag) ?>">
                        <span class="tag-label"><?= e((string) $tag) ?></span>
                        <button type="button" class="tag-remove-btn" title="<?= e($removeTitle) ?>" aria-label="Hapus <?= e((string) $tag) ?>">✕</button>
                    </span>
                <?php endforeach; ?>

                <button type="button" class="btn-add-tag" id="btn-show-add-tag">
                    <span>Tambah</span>
                    <span style="font-size: 1.1rem; line-height: 1;">+</span>
                </button>
            </div>

            <div class="add-tag-box" id="add-tag-box">
                <select id="select-new-tag" class="add-tag-select">
                    <option value="" disabled selected><?= e($placeholder) ?></option>
                    <?php foreach ($availableOptions as $opt): ?>
                        <option value="<?= e((string) $opt) ?>"><?= e((string) $opt) ?></option>
                    <?php endforeach; ?>
                </select>

                <button type="button" class="btn-confirm-add-tag" id="btn-confirm-add-tag">Tambahkan</button>
                <button type="button" class="btn-cancel-add-tag" id="btn-cancel-add-tag">Batal</button>
            </div>

            <input type="hidden" name="<?= e($hiddenInputName) ?>" id="<?= e($hiddenInputId) ?>" value="<?= e(implode(', ', $tags)) ?>">
        </div>
        <?php
    }
}

if (!function_exists('renderProfileSaveActions')) {
    function renderProfileSaveActions(): void
    {
        ?>
        <div class="profile-actions-container">
            <button type="submit" class="btn-save-profile" id="btn-save-profile">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                    <polyline points="7 3 7 8 15 8"></polyline>
                </svg>
                <span>Simpan Perubahan</span>
            </button>
        </div>
        <?php
    }
}
