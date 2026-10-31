<?php

$title = $title ?? 'Ingin mendaftar sebagai apa? - ' . APP_NAME;
$extraCss = ['public/css/components.css', 'public/css/register.css'];

require_once __DIR__ . '/../layouts/headers.php';
?>

<?php if ($role === 'mahasiswa'): ?>
    <?php if ($step === 2): ?>

        <div class="mhs-register-wrapper mhs-step2-wrapper">

            <header class="mhs-brand-header">
                <a href="<?= url('index.php') ?>" class="mhs-logo-link" aria-label="Beranda Qubu">
                    <svg class="mhs-logo-svg" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="mhsBlue" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#38bdf8" />
                                <stop offset="100%" stop-color="#2563eb" />
                            </linearGradient>
                            <linearGradient id="mhsPurple" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#c084fc" />
                                <stop offset="100%" stop-color="#818cf8" />
                            </linearGradient>
                        </defs>
                        <circle cx="13" cy="10" r="5" fill="url(#mhsBlue)" />
                        <circle cx="23" cy="10" r="5" fill="url(#mhsPurple)" />
                        <path d="M 8 20 C 5 27, 11 34, 18 34 C 25 34, 30 28, 28 21 C 26 14, 18 14, 14 16 C 9 19, 8 25, 10 30" 
                              stroke="url(#mhsBlue)" stroke-width="3" stroke-linecap="round" fill="none" />
                        <path d="M 18 34 C 22 34, 28 32, 30 27 C 32 23, 30 18, 25 16" 
                              stroke="url(#mhsPurple)" stroke-width="3" stroke-linecap="round" fill="none" />
                        <path d="M 23 28 L 30 35" 
                              stroke="url(#mhsPurple)" stroke-width="3" stroke-linecap="round" />
                    </svg>
                    <span class="mhs-logo-text">QUBU</span>
                </a>
            </header>

            <main class="mhs-step2-main">

                <h1 class="mhs-profile-headline">Lengkapi Profil</h1>

                <?php if (!empty($error)): ?>
                    <div class="auth-alert auth-alert-error" role="alert" style="margin-bottom: 1.5rem;">
                        <?= e($error) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="auth-alert auth-alert-success" role="alert" style="margin-bottom: 1.5rem;">
                        <?= e($success) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="?page=register" id="form-register-mhs-step2" class="mhs-step2-form" enctype="multipart/form-data" novalidate>
                    <input type="hidden" name="_csrf" value="<?= e($csrfToken ?? Csrf::token()) ?>">
                    <input type="hidden" name="role" value="mahasiswa">
                    <input type="hidden" name="step" value="2">

                    <input type="hidden" name="npm" value="<?= e($step1Data['npm'] ?? $old['npm'] ?? '') ?>">
                    <input type="hidden" name="nama_mahasiswa" value="<?= e($step1Data['nama_mahasiswa'] ?? $old['nama_mahasiswa'] ?? '') ?>">
                    <input type="hidden" name="program_studi" value="<?= e($step1Data['program_studi'] ?? $old['program_studi'] ?? '') ?>">
                    <input type="hidden" name="angkatan" value="<?= e($step1Data['angkatan'] ?? $old['angkatan'] ?? '') ?>">

                    <div class="mhs-step2-grid">

                        <div class="mhs-photo-column">
                            <h2 class="mhs-photo-title">FOTO PROFIL</h2>

                            <div class="avatar-dropzone" id="avatar-dropzone" title="Drag & drop foto ke sini atau klik untuk mengambil dari penyimpanan">
                                <div class="avatar-inner" id="avatar-inner">

                                    <svg class="avatar-default-icon" id="avatar-default-icon" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="24" cy="16" r="8" stroke="#334155" stroke-width="2.5" stroke-dasharray="3 3" stroke-linecap="round"/>
                                        <path d="M10 40 C10 30, 16 26, 24 26 C32 26, 38 30, 38 40" stroke="#334155" stroke-width="2.5" stroke-dasharray="3 3" stroke-linecap="round"/>
                                    </svg>

                                    <img id="avatar-preview-img" class="avatar-preview-img" src="" alt="Preview Foto Profil" style="display: none;">
                                </div>

                                <div class="avatar-camera-badge" id="avatar-camera-badge" title="Pilih foto dari penyimpanan">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                        <circle cx="12" cy="13" r="4"></circle>
                                    </svg>
                                </div>
                            </div>

                            <button type="button" class="btn-upload-photo" id="btn-trigger-upload">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"></path>
                                    <path d="M12 12v9"></path>
                                    <path d="m16 16-4-4-4 4"></path>
                                </svg>
                                <span>Unggah Foto</span>
                            </button>

                            <input type="file" name="foto_profil" id="foto_profil" accept="image/png, image/jpeg, image/jpg, image/webp" style="display: none;">

                            <div class="avatar-file-info" id="avatar-file-info" style="display: none;">
                                <span class="avatar-filename" id="avatar-filename"></span>
                                <button type="button" class="avatar-btn-remove" id="btn-remove-photo" title="Hapus foto terpilih">✕ Hapus</button>
                            </div>
                        </div>

                        <div class="mhs-form-column">

                            <div class="mhs-form-group">
                                <label for="username" class="mhs-form-label">Username</label>
                                <input 
                                    type="text" 
                                    name="username" 
                                    id="username" 
                                    class="mhs-form-input" 
                                    placeholder="" 
                                    value="<?= e($old['username'] ?? '') ?>" 
                                    required
                                    autocomplete="username"
                                >
                            </div>

                            <div class="mhs-form-group">
                                <label for="email-mahasiswa" class="mhs-form-label">Email</label>
                                <input
                                    type="email"
                                    name="email"
                                    id="email-mahasiswa"
                                    class="mhs-form-input"
                                    value="<?= e($old['email'] ?? '') ?>"
                                    placeholder="nama@email.com"
                                    autocomplete="email"
                                    required
                                >
                            </div>

                            <div class="mhs-form-group">
                                <label for="github-mahasiswa" class="mhs-form-label">GitHub <span style="font-weight: 500; color: #64748b; font-size: 0.85em;">(Opsional)</span></label>
                                <input
                                    type="text"
                                    name="github"
                                    id="github-mahasiswa"
                                    class="mhs-form-input"
                                    value="<?= e($old['github'] ?? '') ?>"
                                    placeholder="https://github.com/username atau username"
                                    autocomplete="url"
                                >
                            </div>

                            <div class="mhs-form-group">
                                <label for="no-hp-mahasiswa" class="mhs-form-label">Nomor Telepon</label>
                                <input
                                    type="tel"
                                    name="no_hp"
                                    id="no-hp-mahasiswa"
                                    class="mhs-form-input"
                                    value="<?= e($old['no_hp'] ?? '') ?>"
                                    placeholder="08xxxxxxxxxx"
                                    autocomplete="tel"
                                    inputmode="tel"
                                    required
                                >
                            </div>

                            <div class="mhs-form-group">
                                <label for="bidang_minat" class="mhs-form-label">Bidang Peminatan</label>
                                <div class="mhs-select-wrapper">
                                    <select name="bidang_minat" id="bidang_minat" class="mhs-form-input mhs-select" required>
                                        <option value="" disabled <?= empty($old['bidang_minat']) ? 'selected' : '' ?>>Pilih Bidang Peminatan...</option>
                                        <?php foreach ($peminatanOptions as $opt): ?>
                                            <option value="<?= e($opt) ?>" <?= (($old['bidang_minat'] ?? '') === $opt) ? 'selected' : '' ?>>
                                                <?= e($opt) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="mhs-select-chevron">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="6 9 12 15 18 9"></polyline>
                                        </svg>
                                    </span>
                                </div>
                            </div>

                            <div class="mhs-form-group">
                                <label for="password" class="mhs-form-label">Kata Sandi</label>
                                <div class="password-control">
                                    <input
                                        type="password"
                                        name="password"
                                        id="password"
                                        class="mhs-form-input"
                                        placeholder=""
                                        required
                                        autocomplete="new-password"
                                        data-password-visibility
                                    >
                                    <button class="password-visibility-toggle" type="button" aria-controls="password" aria-label="Tampilkan kata sandi" aria-pressed="false" data-password-toggle>
                                        <svg data-password-icon="show" aria-hidden="true" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                        <svg data-password-icon="hide" aria-hidden="true" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" hidden>
                                            <path d="m3 3 18 18"></path>
                                            <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"></path>
                                            <path d="M9.9 5.2A10.7 10.7 0 0 1 12 5c6.4 0 10 7 10 7a16 16 0 0 1-3.1 3.8"></path>
                                            <path d="M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7c1.4 0 2.7-.4 3.8-1"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="mhs-form-group">
                                <label for="password_confirm" class="mhs-form-label">Konfirmasi Kata Sandi</label>
                                <div class="password-control">
                                    <input
                                        type="password"
                                        name="password_confirm"
                                        id="password_confirm"
                                        class="mhs-form-input"
                                        placeholder=""
                                        required
                                        autocomplete="new-password"
                                        data-password-visibility
                                    >
                                    <button class="password-visibility-toggle" type="button" aria-controls="password_confirm" aria-label="Tampilkan kata sandi" aria-pressed="false" data-password-toggle>
                                        <svg data-password-icon="show" aria-hidden="true" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                        <svg data-password-icon="hide" aria-hidden="true" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" hidden>
                                            <path d="m3 3 18 18"></path>
                                            <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"></path>
                                            <path d="M9.9 5.2A10.7 10.7 0 0 1 12 5c6.4 0 10 7 10 7a16 16 0 0 1-3.1 3.8"></path>
                                            <path d="M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7c1.4 0 2.7-.4 3.8-1"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="mhs-btn-daftar-container">
                                <button type="submit" class="mhs-btn-daftar-dark" id="btn-submit-mhs-step2">
                                    Daftar
                                </button>
                            </div>

                            <div style="text-align: center; margin-top: 1.25rem;">
                                <a href="<?= url('?page=register&role=mahasiswa&step=1') ?>" class="mhs-back-link">
                                    ← Kembali ke Biodata (Langkah 1)
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </main>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dropzone     = document.getElementById('avatar-dropzone');
            const fileInput    = document.getElementById('foto_profil');
            const triggerBtn   = document.getElementById('btn-trigger-upload');
            const cameraBadge  = document.getElementById('avatar-camera-badge');
            const previewImg   = document.getElementById('avatar-preview-img');
            const defaultIcon  = document.getElementById('avatar-default-icon');
            const fileInfo     = document.getElementById('avatar-file-info');
            const filenameSpan = document.getElementById('avatar-filename');
            const removeBtn    = document.getElementById('btn-remove-photo');

            if (!dropzone || !fileInput) return;

            function openStoragePicker(e) {
                if (e) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                fileInput.click();
            }

            dropzone.addEventListener('click', openStoragePicker);
            if (triggerBtn) triggerBtn.addEventListener('click', openStoragePicker);
            if (cameraBadge) cameraBadge.addEventListener('click', openStoragePicker);

            function handleImageFile(file) {
                if (!file) return;

                if (!file.type.startsWith('image/')) {
                    alert('File yang dipilih harus berupa gambar (JPG, PNG, atau WEBP).');
                    return;
                }

                if (file.size > 5 * 1024 * 1024) {
                    alert('Ukuran foto maksimal adalah 5MB.');
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewImg.style.display = 'block';
                    defaultIcon.style.display = 'none';

                    if (fileInfo && filenameSpan) {
                        filenameSpan.textContent = file.name;
                        fileInfo.style.display = 'inline-flex';
                    }
                };
                reader.readAsDataURL(file);
            }

            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    handleImageFile(this.files[0]);
                }
            });

            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.add('dragover');
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.remove('dragover');
                }, false);
            });

            dropzone.addEventListener('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const dt = e.dataTransfer;
                if (dt && dt.files && dt.files.length > 0) {
                    fileInput.files = dt.files; // Simpan file drop ke input form
                    handleImageFile(dt.files[0]);
                }
            });

            if (removeBtn) {
                removeBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    fileInput.value = '';
                    previewImg.src = '';
                    previewImg.style.display = 'none';
                    defaultIcon.style.display = 'block';
                    if (fileInfo) fileInfo.style.display = 'none';
                });
            }
        });
        </script>

    <?php else: ?>

        <div class="mhs-register-wrapper">

            <header class="mhs-brand-header">
                <a href="<?= url('index.php') ?>" class="mhs-logo-link" aria-label="Beranda Qubu">
                    <svg class="mhs-logo-svg" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="mhsBlue" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#38bdf8" />
                                <stop offset="100%" stop-color="#2563eb" />
                            </linearGradient>
                            <linearGradient id="mhsPurple" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#c084fc" />
                                <stop offset="100%" stop-color="#818cf8" />
                            </linearGradient>
                        </defs>
                        <circle cx="13" cy="10" r="5" fill="url(#mhsBlue)" />
                        <circle cx="23" cy="10" r="5" fill="url(#mhsPurple)" />
                        <path d="M 8 20 C 5 27, 11 34, 18 34 C 25 34, 30 28, 28 21 C 26 14, 18 14, 14 16 C 9 19, 8 25, 10 30" 
                              stroke="url(#mhsBlue)" stroke-width="3" stroke-linecap="round" fill="none" />
                        <path d="M 18 34 C 22 34, 28 32, 30 27 C 32 23, 30 18, 25 16" 
                              stroke="url(#mhsPurple)" stroke-width="3" stroke-linecap="round" fill="none" />
                        <path d="M 23 28 L 30 35" 
                              stroke="url(#mhsPurple)" stroke-width="3" stroke-linecap="round" />
                    </svg>
                    <span class="mhs-logo-text">QUBU</span>
                </a>
            </header>

            <main class="mhs-content-wrapper">

                <h1 class="mhs-form-headline">
                    Lanjutkan pendaftaran dengan <span class="mhs-highlight">mengisi</span><br>biodata di bawah ini
                </h1>

                <?php if (!empty($error)): ?>
                    <div class="auth-alert auth-alert-error" role="alert" style="margin-bottom: 1.5rem;">
                        <?= e($error) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="auth-alert auth-alert-success" role="alert" style="margin-bottom: 1.5rem;">
                        <?= e($success) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="?page=register" id="form-register-mhs-step1" class="mhs-biodata-form" novalidate>
                    <input type="hidden" name="_csrf" value="<?= e($csrfToken ?? Csrf::token()) ?>">
                    <input type="hidden" name="role" value="mahasiswa">
                    <input type="hidden" name="step" value="1">

                    <div class="mhs-form-group">
                        <label for="npm" class="mhs-form-label">NPM</label>
                        <input 
                            type="text" 
                            name="npm" 
                            id="npm" 
                            class="mhs-form-input" 
                            placeholder="" 
                            value="<?= e($step1Data['npm'] ?? $old['npm'] ?? '') ?>" 
                            required 
                            autofocus
                        >
                    </div>

                    <div class="mhs-form-group">
                        <label for="nama_mahasiswa" class="mhs-form-label">Nama Lengkap</label>
                        <input 
                            type="text" 
                            name="nama_mahasiswa" 
                            id="nama_mahasiswa" 
                            class="mhs-form-input" 
                            placeholder="" 
                            value="<?= e($step1Data['nama_mahasiswa'] ?? $old['nama_mahasiswa'] ?? $old['nama_lengkap'] ?? '') ?>" 
                            required
                        >
                    </div>

                    <div class="mhs-form-group">
                        <label for="program_studi" class="mhs-form-label">Program Studi</label>
                        <?php
                        $regProdiOpts = $prodiOptions ?? (defined('PRODI_FASILKOM') ? PRODI_FASILKOM : ['Informatika', 'Sistem Informasi']);
                        $currentProdiVal = $step1Data['program_studi'] ?? ($old['program_studi'] ?? '');
                        ?>
                        <div class="mhs-select-wrapper">
                            <select name="program_studi" id="program_studi" class="mhs-form-input mhs-select" required>
                                <option value="" disabled <?= $currentProdiVal === '' ? 'selected' : '' ?>>Pilih Program Studi...</option>
                                <?php foreach ($regProdiOpts as $pOpt): ?>
                                    <option value="<?= e($pOpt) ?>" <?= strcasecmp((string) $currentProdiVal, (string) $pOpt) === 0 ? 'selected' : '' ?>>
                                        <?= e($pOpt) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="mhs-select-chevron">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </span>
                        </div>
                    </div>

                    <div class="mhs-form-group">
                        <label for="angkatan" class="mhs-form-label">Angkatan</label>
                        <input 
                            type="text" 
                            name="angkatan" 
                            id="angkatan" 
                            class="mhs-form-input" 
                            placeholder="" 
                            value="<?= e($step1Data['angkatan'] ?? $old['angkatan'] ?? '') ?>" 
                            required
                        >
                    </div>

                    <div class="mhs-btn-container">
                        <button type="submit" class="mhs-btn-submit" id="btn-submit-mhs-step1">
                            Lanjut
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 6px;">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </button>
                    </div>

                    <p class="mhs-form-footer">
                        Sudah Punya Akun? <a href="<?= url('?page=login') ?>" class="mhs-login-link">Masuk</a>
                    </p>
                </form>
            </main>
        </div>
    <?php endif; ?>

<?php elseif ($role === 'dosen'): ?>
    <?php if ($step === 2): ?>
        <div class="mhs-register-wrapper mhs-step2-wrapper">
            <header class="mhs-brand-header">
                <a href="<?= url('index.php') ?>" class="mhs-logo-link" aria-label="Beranda Qubu">
                    <svg class="mhs-logo-svg" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="dsnStep2Blue" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#38bdf8" /><stop offset="100%" stop-color="#2563eb" />
                            </linearGradient>
                            <linearGradient id="dsnStep2Purple" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#c084fc" /><stop offset="100%" stop-color="#818cf8" />
                            </linearGradient>
                        </defs>
                        <circle cx="13" cy="10" r="5" fill="url(#dsnStep2Blue)" />
                        <circle cx="23" cy="10" r="5" fill="url(#dsnStep2Purple)" />
                        <path d="M 8 20 C 5 27, 11 34, 18 34 C 25 34, 30 28, 28 21 C 26 14, 18 14, 14 16 C 9 19, 8 25, 10 30" stroke="url(#dsnStep2Blue)" stroke-width="3" stroke-linecap="round" fill="none" />
                        <path d="M 18 34 C 22 34, 28 32, 30 27 C 32 23, 30 18, 25 16" stroke="url(#dsnStep2Purple)" stroke-width="3" stroke-linecap="round" fill="none" />
                        <path d="M 23 28 L 30 35" stroke="url(#dsnStep2Purple)" stroke-width="3" stroke-linecap="round" />
                    </svg>
                    <span class="mhs-logo-text">QUBU</span>
                </a>
            </header>

            <main class="mhs-step2-main">
                <h1 class="mhs-profile-headline">Lengkapi Profil Dosen</h1>
                <?php if (!empty($error)): ?><div class="auth-alert auth-alert-error" role="alert" style="margin-bottom: 1.5rem;"><?= e($error) ?></div><?php endif; ?>
                <?php if (!empty($success)): ?><div class="auth-alert auth-alert-success" role="alert" style="margin-bottom: 1.5rem;"><?= e($success) ?></div><?php endif; ?>

                <form method="POST" action="?page=register" id="form-register-dosen-step2" class="mhs-step2-form" enctype="multipart/form-data" novalidate>
                    <input type="hidden" name="_csrf" value="<?= e($csrfToken ?? Csrf::token()) ?>">
                    <input type="hidden" name="role" value="dosen">
                    <input type="hidden" name="step" value="2">
                    <input type="hidden" name="nidn" value="<?= e($step1Data['nidn'] ?? $old['nidn'] ?? '') ?>">
                    <input type="hidden" name="nama_dosen" value="<?= e($step1Data['nama_dosen'] ?? $old['nama_dosen'] ?? '') ?>">

                    <div class="mhs-step2-grid">
                        <div class="mhs-photo-column">
                            <h2 class="mhs-photo-title">FOTO PROFIL</h2>
                            <div class="avatar-dropzone" id="avatar-dropzone" title="Tarik foto ke sini atau klik untuk memilih foto">
                                <div class="avatar-inner">
                                    <svg class="avatar-default-icon" id="avatar-default-icon" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="24" cy="16" r="8" stroke="#334155" stroke-width="2.5" stroke-dasharray="3 3" stroke-linecap="round"/>
                                        <path d="M10 40 C10 30, 16 26, 24 26 C32 26, 38 30, 38 40" stroke="#334155" stroke-width="2.5" stroke-dasharray="3 3" stroke-linecap="round"/>
                                    </svg>
                                    <img id="avatar-preview-img" class="avatar-preview-img" src="" alt="Preview Foto Profil" style="display: none;">
                                </div>
                                <div class="avatar-camera-badge" id="avatar-camera-badge" title="Pilih foto">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle>
                                    </svg>
                                </div>
                            </div>
                            <button type="button" class="btn-upload-photo" id="btn-trigger-upload">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"></path><path d="M12 12v9"></path><path d="m16 16-4-4-4 4"></path>
                                </svg>
                                <span>Unggah Foto</span>
                            </button>
                            <input type="file" name="foto_profil" id="foto_profil" accept="image/png, image/jpeg, image/webp" style="display:none;">
                            <div class="avatar-file-info" id="avatar-file-info" style="display:none;">
                                <span class="avatar-filename" id="avatar-filename"></span>
                                <button type="button" class="avatar-btn-remove" id="btn-remove-photo">✕ Hapus</button>
                            </div>
                        </div>

                        <div class="mhs-form-column">
                            <div class="mhs-form-group">
                                <label for="username-dosen" class="mhs-form-label">Username</label>
                                <input type="text" name="username" id="username-dosen" class="mhs-form-input" value="<?= e($old['username'] ?? '') ?>" autocomplete="username" required>
                            </div>
                            <div class="mhs-form-group">
                                <label for="email-dosen" class="mhs-form-label">Email</label>
                                <input type="email" name="email" id="email-dosen" class="mhs-form-input" value="<?= e($old['email'] ?? '') ?>" placeholder="nama@email.com" autocomplete="email" required>
                            </div>
                            <div class="mhs-form-group">
                                <label for="no-hp-dosen" class="mhs-form-label">Nomor Telepon</label>
                                <input type="tel" name="no_hp" id="no-hp-dosen" class="mhs-form-input" value="<?= e($old['no_hp'] ?? '') ?>" placeholder="08xxxxxxxxxx" autocomplete="tel" inputmode="tel" required>
                            </div>
                            <div class="mhs-form-group">
                                <label for="bidang-keahlian-dosen" class="mhs-form-label">Bidang Keahlian &amp; Fokus Riset</label>
                                <div class="mhs-select-wrapper">
                                    <select name="bidang_keahlian" id="bidang-keahlian-dosen" class="mhs-form-input mhs-select" required>
                                        <option value="" disabled <?= empty($old['bidang_keahlian']) ? 'selected' : '' ?>>Pilih Bidang Keahlian...</option>
                                        <?php foreach ($peminatanOptions as $opt): ?>
                                            <option value="<?= e($opt) ?>" <?= (($old['bidang_keahlian'] ?? '') === $opt) ? 'selected' : '' ?>><?= e($opt) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="mhs-select-chevron"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg></span>
                                </div>
                            </div>
                            <div class="mhs-form-group">
                                <label for="password-dosen" class="mhs-form-label">Kata Sandi</label>
                                <div class="password-control">
                                    <input type="password" name="password" id="password-dosen" class="mhs-form-input" minlength="6" autocomplete="new-password" required data-password-visibility>
                                    <button class="password-visibility-toggle" type="button" aria-controls="password-dosen" aria-label="Tampilkan kata sandi" aria-pressed="false" data-password-toggle>
                                        <svg data-password-icon="show" aria-hidden="true" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                        <svg data-password-icon="hide" aria-hidden="true" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" hidden><path d="m3 3 18 18"></path><path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"></path><path d="M9.9 5.2A10.7 10.7 0 0 1 12 5c6.4 0 10 7 10 7a16 16 0 0 1-3.1 3.8"></path><path d="M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7c1.4 0 2.7-.4 3.8-1"></path></svg>
                                    </button>
                                </div>
                            </div>
                            <div class="mhs-form-group">
                                <label for="password-confirm-dosen" class="mhs-form-label">Konfirmasi Kata Sandi</label>
                                <div class="password-control">
                                    <input type="password" name="password_confirm" id="password-confirm-dosen" class="mhs-form-input" minlength="6" autocomplete="new-password" required data-password-visibility>
                                    <button class="password-visibility-toggle" type="button" aria-controls="password-confirm-dosen" aria-label="Tampilkan kata sandi" aria-pressed="false" data-password-toggle>
                                        <svg data-password-icon="show" aria-hidden="true" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                        <svg data-password-icon="hide" aria-hidden="true" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" hidden><path d="m3 3 18 18"></path><path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"></path><path d="M9.9 5.2A10.7 10.7 0 0 1 12 5c6.4 0 10 7 10 7a16 16 0 0 1-3.1 3.8"></path><path d="M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7c1.4 0 2.7-.4 3.8-1"></path></svg>
                                    </button>
                                </div>
                            </div>
                            <div class="mhs-btn-daftar-container"><button type="submit" class="mhs-btn-daftar-dark" id="btn-submit-dosen">Daftar</button></div>
                            <div style="text-align:center; margin-top:1.25rem;">
                                <a href="<?= url('?page=register&role=dosen&step=1') ?>" class="mhs-back-link">← Kembali ke Biodata (Langkah 1)</a>
                            </div>
                            <p class="mhs-form-footer" style="margin-top:1.25rem;">Sudah Punya Akun? <a href="<?= url('?page=login') ?>" class="mhs-login-link">Masuk</a></p>
                        </div>
                    </div>
                </form>
            </main>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dropzone = document.getElementById('avatar-dropzone');
            const input = document.getElementById('foto_profil');
            const trigger = document.getElementById('btn-trigger-upload');
            const preview = document.getElementById('avatar-preview-img');
            const placeholder = document.getElementById('avatar-default-icon');
            const fileInfo = document.getElementById('avatar-file-info');
            const filename = document.getElementById('avatar-filename');
            const remove = document.getElementById('btn-remove-photo');
            if (!dropzone || !input || !preview || !placeholder) return;
            const pickFile = () => input.click();
            dropzone.addEventListener('click', (event) => {
                if (event.target.closest('button')) return;
                pickFile();
            });
            trigger?.addEventListener('click', pickFile);
            const showFile = (file) => {
                if (!file.type.startsWith('image/')) {
                    alert('Silakan pilih file gambar.');
                    input.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = () => {
                    preview.src = reader.result;
                    preview.style.display = 'block';
                    placeholder.style.display = 'none';
                    if (fileInfo) fileInfo.style.display = 'flex';
                    if (filename) filename.textContent = file.name;
                };
                reader.readAsDataURL(file);
            };
            input.addEventListener('change', () => input.files?.[0] && showFile(input.files[0]));
            ['dragenter', 'dragover'].forEach((name) => dropzone.addEventListener(name, (event) => {
                event.preventDefault();
                dropzone.classList.add('dragover');
            }));
            ['dragleave', 'drop'].forEach((name) => dropzone.addEventListener(name, (event) => {
                event.preventDefault();
                dropzone.classList.remove('dragover');
            }));
            dropzone.addEventListener('drop', (event) => {
                if (event.dataTransfer?.files?.[0]) {
                    input.files = event.dataTransfer.files;
                    showFile(event.dataTransfer.files[0]);
                }
            });
            remove?.addEventListener('click', () => {
                input.value = '';
                preview.src = '';
                preview.style.display = 'none';
                placeholder.style.display = 'block';
                if (fileInfo) fileInfo.style.display = 'none';
            });
        });
        </script>
    <?php else: ?>
        <div class="mhs-register-wrapper">
            <header class="mhs-brand-header">
                <a href="<?= url('index.php') ?>" class="mhs-logo-link" aria-label="Beranda Qubu">
                    <svg class="mhs-logo-svg" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="dsnBlue" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#38bdf8" /><stop offset="100%" stop-color="#2563eb" /></linearGradient>
                            <linearGradient id="dsnPurple" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#c084fc" /><stop offset="100%" stop-color="#818cf8" /></linearGradient>
                        </defs>
                        <circle cx="13" cy="10" r="5" fill="url(#dsnBlue)" /><circle cx="23" cy="10" r="5" fill="url(#dsnPurple)" />
                        <path d="M 8 20 C 5 27, 11 34, 18 34 C 25 34, 30 28, 28 21 C 26 14, 18 14, 14 16 C 9 19, 8 25, 10 30" stroke="url(#dsnBlue)" stroke-width="3" stroke-linecap="round" fill="none" />
                        <path d="M 18 34 C 22 34, 28 32, 30 27 C 32 23, 30 18, 25 16" stroke="url(#dsnPurple)" stroke-width="3" stroke-linecap="round" fill="none" />
                        <path d="M 23 28 L 30 35" stroke="url(#dsnPurple)" stroke-width="3" stroke-linecap="round" />
                    </svg>
                    <span class="mhs-logo-text">QUBU</span>
                </a>
            </header>
            <main class="mhs-content-wrapper">
                <h1 class="mhs-form-headline">Lanjutkan pendaftaran dengan <span class="mhs-highlight">mengisi</span><br>biodata di bawah ini</h1>
                <?php if (!empty($error)): ?><div class="auth-alert auth-alert-error" role="alert" style="margin-bottom: 1.5rem;"><?= e($error) ?></div><?php endif; ?>
                <form method="POST" action="?page=register" id="form-register-dosen-step1" class="mhs-biodata-form" novalidate>
                    <input type="hidden" name="_csrf" value="<?= e($csrfToken ?? Csrf::token()) ?>">
                    <input type="hidden" name="role" value="dosen">
                    <input type="hidden" name="step" value="1">
                    <div class="mhs-form-group">
                        <label for="nidn" class="mhs-form-label">NIDN/NIP</label>
                        <input type="text" name="nidn" id="nidn" class="mhs-form-input" value="<?= e($step1Data['nidn'] ?? $old['nidn'] ?? '') ?>" maxlength="20" required>
                    </div>
                    <div class="mhs-form-group">
                        <label for="nama_dosen" class="mhs-form-label">Nama Lengkap &amp; Gelar</label>
                        <input type="text" name="nama_dosen" id="nama_dosen" class="mhs-form-input" value="<?= e($step1Data['nama_dosen'] ?? $old['nama_dosen'] ?? $old['nama_lengkap'] ?? '') ?>" maxlength="100" required>
                    </div>
                    <div class="mhs-btn-container">
                        <button type="submit" class="mhs-btn-submit" id="btn-submit-dosen-step1">
                            Lanjut
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-left:6px;"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                        </button>
                    </div>
                    <p class="mhs-form-footer">Sudah Punya Akun? <a href="<?= url('?page=login') ?>" class="mhs-login-link">Masuk</a></p>
                </form>
            </main>
        </div>
    <?php endif; ?>

<?php else: ?>

    <div class="register-page">

        <div class="register-hero-bg"></div>

        <header class="register-nav">

            <a href="<?= url('index.php') ?>" class="register-logo-link" aria-label="Qubu Homepage">
                <svg class="home-logo-svg" viewBox="0 0 160 55" fill="none" xmlns="http://www.w3.org/2000/svg" style="height: 40px;">
                    <defs>
                        <linearGradient id="regLogoBlue" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#38bdf8" />
                            <stop offset="100%" stop-color="#2563eb" />
                        </linearGradient>
                        <linearGradient id="regLogoPurple" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#c084fc" />
                            <stop offset="100%" stop-color="#818cf8" />
                        </linearGradient>
                    </defs>
                    <circle cx="16" cy="11" r="5.5" fill="url(#regLogoBlue)" />
                    <circle cx="28" cy="11" r="5.5" fill="url(#regLogoPurple)" />
                    <path d="M 10 24 C 7 32, 14 42, 22 42 C 30 42, 36 34, 34 25 C 32 17, 23 17, 18 20 C 13 23, 11 31, 14 36" 
                          stroke="url(#regLogoBlue)" stroke-width="4.5" stroke-linecap="round" fill="none" />
                    <path d="M 22 42 C 26 42, 33 40, 36 34 C 38 29, 36 22, 30 20" 
                          stroke="url(#regLogoPurple)" stroke-width="4.5" stroke-linecap="round" fill="none" />
                    <path d="M 28 35 L 36 43" 
                          stroke="url(#regLogoPurple)" stroke-width="4.5" stroke-linecap="round" />
                    <text x="48" y="36" font-family="'Plus Jakarta Sans', system-ui, sans-serif" font-size="28" font-weight="900" fill="#ffffff" letter-spacing="-0.03em">QUBU</text>
                </svg>
            </a>

            <a href="<?= url('?page=login') ?>" class="register-nav-link">
                Sudah punya akun? Masuk
            </a>
        </header>

        <main class="role-select-wrapper">
            <h1 class="role-select-title">Ingin mendaftar sebagai apa?</h1>

            <a href="<?= url('?page=register&role=mahasiswa') ?>" class="role-card" id="card-pilih-mahasiswa">
                <div class="role-card-left">
                    <div class="role-avatar-placeholder" aria-hidden="true">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="8" r="4"></circle>
                            <path d="M5 21v-2a7 7 0 0 1 14 0v2"></path>
                        </svg>
                    </div>
                    <div class="role-card-info">
                        <div class="role-card-title">Mahasiswa</div>
                        <div class="role-card-desc">Temukan partner lomba, bangun tim, dan kembangkan portofoliomu.</div>
                    </div>
                </div>
                <div class="role-radio-circle" aria-hidden="true">
                    <div class="role-radio-dot"></div>
                </div>
            </a>

            <a href="<?= url('?page=register&role=dosen') ?>" class="role-card" id="card-pilih-dosen">
                <div class="role-card-left">

                    <div class="role-avatar-placeholder" aria-hidden="true">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                            <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                        </svg>
                    </div>
                    <div class="role-card-info">
                        <div class="role-card-title">Dosen</div>
                        <div class="role-card-desc">Bagikan keahlian dan bimbing tim berprestasi.</div>
                    </div>
                </div>
                <div class="role-radio-circle" aria-hidden="true">
                    <div class="role-radio-dot"></div>
                </div>
            </a>
        </main>
    </div>
<?php endif; ?>

<script src="<?= url('public/js/password-visibility.js') ?>?v=<?= (int) filemtime(__DIR__ . '/../../public/js/password-visibility.js') ?>" defer></script>
<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
