<?php
/**
 * View Halaman Registrasi & Pemilihan Peran (Mahasiswa / Dosen) Qubu
 * Presisi mengikuti mockup desain UI
 */
$title = $title ?? 'Ingin mendaftar sebagai apa? - ' . APP_NAME;
$extraCss = ['public/css/components.css', 'public/css/register.css'];

require_once __DIR__ . '/../layouts/headers.php';
?>

<?php if ($role === 'mahasiswa'): ?>
    <?php if ($step === 2): ?>
        <!-- ========================================================================= -->
        <!-- C2. TAMPILAN LENGKAPI PROFIL MAHASISWA (LANGKAH 2 PERSIS FIGMA MOCKUP)    -->
        <!-- ========================================================================= -->
        <div class="mhs-register-wrapper mhs-step2-wrapper">
            <!-- Logo QUBU Kiri Atas Sesuai Mockup -->
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
                <!-- Headline Sesuai Mockup Baru: Lengkapi Profil -->
                <h1 class="mhs-profile-headline">Lengkapi Profil</h1>

                <!-- Alert Notifikasi Flash Error / Success -->
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
                    <input type="hidden" name="role" value="mahasiswa">
                    <input type="hidden" name="step" value="2">
                    
                    <!-- Data Biodata dari Langkah 1 (Tersimpan dari sesi / form) -->
                    <input type="hidden" name="npm" value="<?= e($step1Data['npm'] ?? $old['npm'] ?? '') ?>">
                    <input type="hidden" name="nama_mahasiswa" value="<?= e($step1Data['nama_mahasiswa'] ?? $old['nama_mahasiswa'] ?? '') ?>">
                    <input type="hidden" name="program_studi" value="<?= e($step1Data['program_studi'] ?? $old['program_studi'] ?? '') ?>">
                    <input type="hidden" name="angkatan" value="<?= e($step1Data['angkatan'] ?? $old['angkatan'] ?? '') ?>">

                    <div class="mhs-step2-grid">
                        <!-- ========================================================= -->
                        <!-- KOLOM KIRI: FOTO PROFIL DENGAN DRAG & DROP & UPLOAD FILE -->
                        <!-- ========================================================= -->
                        <div class="mhs-photo-column">
                            <h2 class="mhs-photo-title">FOTO PROFIL</h2>
                            
                            <!-- Lingkaran Dropzone Avatar -->
                            <div class="avatar-dropzone" id="avatar-dropzone" title="Drag & drop foto ke sini atau klik untuk mengambil dari penyimpanan">
                                <div class="avatar-inner" id="avatar-inner">
                                    <!-- Ikon Siluet Putus-putus Persis Mockup -->
                                    <svg class="avatar-default-icon" id="avatar-default-icon" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="24" cy="16" r="8" stroke="#334155" stroke-width="2.5" stroke-dasharray="3 3" stroke-linecap="round"/>
                                        <path d="M10 40 C10 30, 16 26, 24 26 C32 26, 38 30, 38 40" stroke="#334155" stroke-width="2.5" stroke-dasharray="3 3" stroke-linecap="round"/>
                                    </svg>
                                    
                                    <!-- Image Preview Live -->
                                    <img id="avatar-preview-img" class="avatar-preview-img" src="" alt="Preview Foto Profil" style="display: none;">
                                </div>

                                <!-- Camera Badge Biru di Kanan Bawah Lingkaran -->
                                <div class="avatar-camera-badge" id="avatar-camera-badge" title="Pilih foto dari penyimpanan">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                        <circle cx="12" cy="13" r="4"></circle>
                                    </svg>
                                </div>
                            </div>

                            <!-- Tombol Unggah Foto (Pill Putih Ikon Cloud Upload) -->
                            <button type="button" class="btn-upload-photo" id="btn-trigger-upload">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"></path>
                                    <path d="M12 12v9"></path>
                                    <path d="m16 16-4-4-4 4"></path>
                                </svg>
                                <span>Unggah Foto</span>
                            </button>

                            <!-- Input File Asli (Tersembunyi, mendukung ambil dari penyimpanan device) -->
                            <input type="file" name="foto_profil" id="foto_profil" accept="image/png, image/jpeg, image/jpg, image/webp" style="display: none;">
                            
                            <!-- Status File & Tombol Hapus -->
                            <div class="avatar-file-info" id="avatar-file-info" style="display: none;">
                                <span class="avatar-filename" id="avatar-filename"></span>
                                <button type="button" class="avatar-btn-remove" id="btn-remove-photo" title="Hapus foto terpilih">✕ Hapus</button>
                            </div>
                        </div>

                        <!-- ========================================================= -->
                        <!-- KOLOM KANAN: USERNAME, BIDANG MINAT (DROPDOWN), PASSWORD -->
                        <!-- ========================================================= -->
                        <div class="mhs-form-column">
                            <!-- 1. Username -->
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

                            <!-- 2. Bidang Peminatan (Dropdown Sesuai Database) -->
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

                            <!-- 3. Kata Sandi -->
                            <div class="mhs-form-group">
                                <label for="password" class="mhs-form-label">Kata Sandi</label>
                                <input 
                                    type="password" 
                                    name="password" 
                                    id="password" 
                                    class="mhs-form-input" 
                                    placeholder="" 
                                    required
                                    autocomplete="new-password"
                                >
                            </div>

                            <!-- 4. Konfirmasi Kata Sandi -->
                            <div class="mhs-form-group">
                                <label for="password_confirm" class="mhs-form-label">Konfirmasi Kata Sandi</label>
                                <input 
                                    type="password" 
                                    name="password_confirm" 
                                    id="password_confirm" 
                                    class="mhs-form-input" 
                                    placeholder="" 
                                    required
                                    autocomplete="new-password"
                                >
                            </div>

                            <!-- Tombol Submit: Daftar (Hitam/Navy Sesuai Mockup) -->
                            <div class="mhs-btn-daftar-container">
                                <button type="submit" class="mhs-btn-daftar-dark" id="btn-submit-mhs-step2">
                                    Daftar
                                </button>
                            </div>

                            <!-- Navigasi Kembali ke Langkah 1 -->
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

        <!-- Script Interaktif untuk Drag & Drop dan Upload Foto dari Penyimpanan -->
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

            // 1. Membuka dialog pemilih file dari penyimpanan device
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

            // 2. Fungsi memproses dan menampilkan preview foto terpilih
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

            // 3. Listener input file change (saat mengambil dari penyimpanan)
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    handleImageFile(this.files[0]);
                }
            });

            // 4. Drag & Drop events
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

            // 5. Tombol reset / hapus foto terpilih
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
        <!-- ========================================================================= -->
        <!-- C1. TAMPILAN BIODATA MAHASISWA (LANGKAH 1 PERSIS FIGMA MOCKUP)            -->
        <!-- ========================================================================= -->
        <div class="mhs-register-wrapper">
            <!-- Logo QUBU Kiri Atas Sesuai Mockup -->
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
                <!-- Judul Persis Mockup: Lanjutkan pendaftaran dengan mengisi biodata di bawah ini -->
                <h1 class="mhs-form-headline">
                    Lanjutkan pendaftaran dengan <span class="mhs-highlight">mengisi</span><br>biodata di bawah ini
                </h1>

                <!-- Alert Notifikasi Flash Error / Success -->
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
                    <input type="hidden" name="role" value="mahasiswa">
                    <input type="hidden" name="step" value="1">

                    <!-- 1. NPM -->
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

                    <!-- 2. Nama Lengkap -->
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

                    <!-- 3. Program Studi -->
                    <div class="mhs-form-group">
                        <label for="program_studi" class="mhs-form-label">Program Studi</label>
                        <input 
                            type="text" 
                            name="program_studi" 
                            id="program_studi" 
                            class="mhs-form-input" 
                            placeholder="" 
                            value="<?= e($step1Data['program_studi'] ?? $old['program_studi'] ?? '') ?>" 
                            required
                        >
                    </div>

                    <!-- 4. Angkatan -->
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

                    <!-- Tombol Submit: Lanjut (Menuju Step 2 Lengkapi Profil) -->
                    <div class="mhs-btn-container">
                        <button type="submit" class="mhs-btn-submit" id="btn-submit-mhs-step1">
                            Lanjut
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 6px;">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </button>
                    </div>

                    <!-- Footer: Sudah Punya Akun? Masuk -->
                    <p class="mhs-form-footer">
                        Sudah Punya Akun? <a href="<?= url('?page=login') ?>" class="mhs-login-link">Masuk</a>
                    </p>
                </form>
            </main>
        </div>
    <?php endif; ?>

<?php elseif ($role === 'dosen'): ?>
    <!-- ========================================================================= -->
    <!-- B. TAMPILAN PENDAFTARAN DOSEN (PERSIS MOCKUP BIODATA FIGMA)              -->
    <!-- ========================================================================= -->
    <div class="mhs-register-wrapper">
        <!-- Logo QUBU Kiri Atas Sesuai Mockup -->
        <header class="mhs-brand-header">
            <a href="<?= url('index.php') ?>" class="mhs-logo-link" aria-label="Beranda Qubu">
                <svg class="mhs-logo-svg" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="dsnBlue" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#38bdf8" />
                            <stop offset="100%" stop-color="#2563eb" />
                        </linearGradient>
                        <linearGradient id="dsnPurple" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#c084fc" />
                            <stop offset="100%" stop-color="#818cf8" />
                        </linearGradient>
                    </defs>
                    <circle cx="13" cy="10" r="5" fill="url(#dsnBlue)" />
                    <circle cx="23" cy="10" r="5" fill="url(#dsnPurple)" />
                    <path d="M 8 20 C 5 27, 11 34, 18 34 C 25 34, 30 28, 28 21 C 26 14, 18 14, 14 16 C 9 19, 8 25, 10 30" 
                          stroke="url(#dsnBlue)" stroke-width="3" stroke-linecap="round" fill="none" />
                    <path d="M 18 34 C 22 34, 28 32, 30 27 C 32 23, 30 18, 25 16" 
                          stroke="url(#dsnPurple)" stroke-width="3" stroke-linecap="round" fill="none" />
                    <path d="M 23 28 L 30 35" 
                          stroke="url(#dsnPurple)" stroke-width="3" stroke-linecap="round" />
                </svg>
                <span class="mhs-logo-text">QUBU</span>
            </a>
        </header>

        <main class="mhs-content-wrapper">
            <!-- Judul Persis Mockup: Lanjutkan pendaftaran dengan mengisi biodata di bawah ini -->
            <h1 class="mhs-form-headline">
                Lanjutkan pendaftaran dengan <span class="mhs-highlight">mengisi</span><br>biodata di bawah ini
            </h1>

            <!-- Alert Notifikasi Flash Error / Success -->
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

            <form method="POST" action="?page=register" id="form-register-dosen" class="mhs-biodata-form" novalidate>
                <input type="hidden" name="role" value="dosen">

                <!-- 1. NIDN/NIP -->
                <div class="mhs-form-group">
                    <label for="nidn" class="mhs-form-label">NIDN/NIP</label>
                    <input 
                        type="text" 
                        name="nidn" 
                        id="nidn" 
                        class="mhs-form-input" 
                        placeholder="" 
                        value="<?= e($old['nidn'] ?? '') ?>" 
                        required 
                        autofocus
                    >
                </div>

                <!-- 2. Nama Lengkap & Gelar -->
                <div class="mhs-form-group">
                    <label for="nama_dosen" class="mhs-form-label">Nama Lengkap &amp; Gelar</label>
                    <input 
                        type="text" 
                        name="nama_dosen" 
                        id="nama_dosen" 
                        class="mhs-form-input" 
                        placeholder="" 
                        value="<?= e($old['nama_dosen'] ?? $old['nama_lengkap'] ?? '') ?>" 
                        required
                    >
                </div>

                <!-- 3. Bidang Keahlian & Fokus Riset -->
                <div class="mhs-form-group">
                    <label for="bidang_keahlian" class="mhs-form-label">Bidang Keahlian &amp; Fokus Riset</label>
                    <input 
                        type="text" 
                        name="bidang_keahlian" 
                        id="bidang_keahlian" 
                        class="mhs-form-input" 
                        placeholder="" 
                        value="<?= e($old['bidang_keahlian'] ?? '') ?>" 
                        required
                    >
                </div>

                <!-- Tombol Submit: Daftar -->
                <div class="mhs-btn-container">
                    <button type="submit" class="mhs-btn-submit" id="btn-submit-dosen">
                        Daftar
                    </button>
                </div>

                <!-- Footer: Sudah Punya Akun? Masuk -->
                <p class="mhs-form-footer">
                    Sudah Punya Akun? <a href="<?= url('?page=login') ?>" class="mhs-login-link">Masuk</a>
                </p>
            </form>
        </main>
    </div>

<?php else: ?>
    <!-- ========================================================================= -->
    <!-- A. HALAMAN SELEKSI PERAN (LATAR BIRU GRADASI PERSIS MOCKUP FIGMA)         -->
    <!-- ========================================================================= -->
    <div class="register-page">
        <!-- Latar Belakang Biru Gradasi Atas (Persis Desain Mockup) -->
        <div class="register-hero-bg"></div>

        <!-- Top Navigation Bar -->
        <header class="register-nav">
            <!-- Logo Qubu Kiri -->
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

            <!-- Tautan Masuk ke Akun di Kanan -->
            <a href="<?= url('?page=login') ?>" class="register-nav-link">
                Sudah punya akun? Masuk
            </a>
        </header>

        <main class="role-select-wrapper">
            <h1 class="role-select-title">Ingin mendaftar sebagai apa?</h1>

            <!-- Pilihan 1: Mahasiswa -->
            <a href="<?= url('?page=register&role=mahasiswa') ?>" class="role-card" id="card-pilih-mahasiswa">
                <div class="role-card-left">
                    <div class="role-card-info">
                        <div class="role-card-title">Mahasiswa</div>
                        <div class="role-card-desc">Temukan partner lomba, bangun tim, dan kembangkan portofoliomu.</div>
                    </div>
                </div>
                <div class="role-radio-circle" aria-hidden="true">
                    <div class="role-radio-dot"></div>
                </div>
            </a>

            <!-- Pilihan 2: Dosen -->
            <a href="<?= url('?page=register&role=dosen') ?>" class="role-card" id="card-pilih-dosen">
                <div class="role-card-left">
                    <!-- Placeholder Kotak Abu-abu Sesuai Mockup -->
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

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
