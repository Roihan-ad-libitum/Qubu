<?php
/**
 * View Halaman CRUD Lomba Khusus Admin Qubu
 * Presisi mengikuti mockup UI:
 * - Navbar di Kanan Atas: User, Dosen Pembimbing, Lomba (Aktif)
 * - Sebelah Kiri: Tambahkan Poster, Identitas Poster, Nama Lomba, dan Penyelenggara
 * - Sebelah Kanan: Ketentuan & Kategori (Deadline, Biaya, Partisipasi, Tingkat, Bidang)
 * - Di Bawah: Deskripsi & Tautan Lomba
 * - Tombol: Posting di Kanan Bawah
 */
$title = 'Kelola Perlombaan - Admin ' . APP_NAME;
$extraCss = 'public/css/admin-lomba.css';

require_once __DIR__ . '/../layouts/headers.php';
?>

<div class="admin-lomba-page">

    <!-- ========================================================================= -->
    <!-- 1. NAVBAR DI KANAN ATAS                                                   -->
    <!-- ========================================================================= -->
    <header class="admin-top-nav" aria-label="Navigasi Admin">
        <!-- Logo QUBU Kiri -->
        <a href="<?= url('index.php') ?>" class="admin-nav-logo-link" aria-label="Beranda Qubu">
            <svg class="admin-nav-logo-svg" viewBox="0 0 160 55" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="adminLogoBlue" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#38bdf8" />
                        <stop offset="100%" stop-color="#2563eb" />
                    </linearGradient>
                    <linearGradient id="adminLogoPurple" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#c084fc" />
                        <stop offset="100%" stop-color="#818cf8" />
                    </linearGradient>
                </defs>
                <circle cx="16" cy="11" r="5.5" fill="url(#adminLogoBlue)" />
                <circle cx="28" cy="11" r="5.5" fill="url(#adminLogoPurple)" />
                <path d="M 10 24 C 7 32, 14 42, 22 42 C 30 42, 36 34, 34 25 C 32 17, 23 17, 18 20 C 13 23, 11 31, 14 36" 
                      stroke="url(#adminLogoBlue)" stroke-width="4.5" stroke-linecap="round" fill="none" />
                <path d="M 22 42 C 26 42, 33 40, 36 34 C 38 29, 36 22, 30 20" 
                      stroke="url(#adminLogoPurple)" stroke-width="4.5" stroke-linecap="round" fill="none" />
                <path d="M 28 35 L 36 43" 
                      stroke="url(#adminLogoPurple)" stroke-width="4.5" stroke-linecap="round" />
                <text x="48" y="36" font-family="'Plus Jakarta Sans', system-ui, sans-serif" font-size="28" font-weight="900" fill="#38bdf8" letter-spacing="-0.03em">QUBU</text>
            </svg>
        </a>

        <!-- Menu Navigasi di Kanan Atas: User, Dosen Pembimbing, Lomba -->
        <nav class="admin-nav-right-menu">
            <a href="<?= url('?page=admin-users') ?>" class="admin-nav-item">User</a>
            <a href="<?= url('?page=admin-dosen') ?>" class="admin-nav-item">Dosen Pembimbing</a>
            <a href="<?= url('?page=admin-lomba') ?>" class="admin-nav-item active">Lomba</a>
        </nav>
    </header>

    <!-- ========================================================================= -->
    <!-- 2. KONTEN FORM CRUD LOMBA                                                 -->
    <!-- ========================================================================= -->
    <main class="admin-content-wrap">
        
        <!-- Notifikasi Pesan Flash -->
        <?php if (!empty($error)): ?>
            <div class="admin-alert-box admin-alert-error" role="alert">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="admin-alert-box admin-alert-success" role="alert">
                <?= e($success) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= url('?page=admin-lomba') ?>" id="form-crud-lomba" enctype="multipart/form-data">
            <input type="hidden" name="id_lomba" value="<?= e($lombaData['id_lomba']) ?>">

            <!-- BARIS ATAS: DUA KARTU (KIRI: IDENTITAS & POSTER | KANAN: KETENTUAN & KATEGORI) -->
            <div class="admin-top-columns">
                
                <!-- ============================================================= -->
                <!-- SEBELAH KIRI: TAMBAHKAN POSTER, IDENTITAS, NAMA LOMBA, PENYELENGGARA -->
                <!-- ============================================================= -->
                <section class="admin-card-box card-periwinkle-theme" aria-label="Identitas dan Poster Perlombaan">
                    
                    <!-- Area Tambahkan Poster (Tarik File Kesini / Pilih File) -->
                    <div class="dropzone-upload-poster" id="poster-dropzone" title="Klik atau tarik file gambar untuk mengunggah poster lomba">
                        <?php if (!empty($lombaData['poster']) && file_exists(__DIR__ . '/../../' . $lombaData['poster'])): ?>
                            <img src="<?= url($lombaData['poster']) ?>" alt="Poster Lomba" class="poster-preview-display" id="poster-preview" style="display: block;">
                        <?php else: ?>
                            <img src="" alt="Preview Poster" class="poster-preview-display" id="poster-preview">
                        <?php endif; ?>

                        <!-- Ikon Placeholder Lanskap & Tombol Pilih File -->
                        <div id="dropzone-placeholder" style="<?= !empty($lombaData['poster']) ? 'display:none;' : '' ?>">
                            <svg class="poster-art-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                <polyline points="21 15 16 10 5 21"></polyline>
                            </svg>
                            <h3 class="poster-drop-prompt">Tarik File Kesini</h3>
                            <button type="button" class="btn-file-select" id="btn-trigger-file">Pilih File</button>
                        </div>

                        <input type="file" name="poster" id="poster-input" accept="image/png, image/jpeg, image/jpg, image/webp, image/svg+xml" style="display: none;">
                    </div>

                    <!-- Header Bagian 01 IDENTITAS & POSTER -->
                    <div class="section-pill-header">
                        <div class="section-pill-tag">
                            <span class="pill-number">01</span>
                            <span class="pill-title">IDENTITAS & POSTER</span>
                        </div>
                        <span class="pill-chevron" title="Ciutkan / Bentangkan">∧</span>
                    </div>

                    <!-- Input 1: Nama / Judul Perlombaan -->
                    <div class="admin-field-group">
                        <label for="nama_lomba" class="admin-field-label">Nama / Judul Perlombaan</label>
                        <input 
                            type="text" 
                            name="nama_lomba" 
                            id="nama_lomba" 
                            class="admin-field-input admin-input-bold-title" 
                            value="<?= e($lombaData['nama_lomba']) ?>" 
                            placeholder="Semantik Internformatics Competition" 
                            required
                        >
                    </div>

                    <!-- Input 2: Penyelenggara -->
                    <div class="admin-field-group">
                        <label for="penyelenggara" class="admin-field-label">Penyelenggara</label>
                        <input 
                            type="text" 
                            name="penyelenggara" 
                            id="penyelenggara" 
                            class="admin-field-input" 
                            value="<?= e($lombaData['penyelenggara']) ?>" 
                            placeholder="Himpunan Mahasiswa Informatika" 
                            required
                        >
                    </div>
                </section>

                <!-- ============================================================= -->
                <!-- SEBELAH KANAN: KETENTUAN & KATEGORI                            -->
                <!-- ============================================================= -->
                <section class="admin-card-box card-periwinkle-theme" aria-label="Ketentuan dan Kategori Perlombaan">
                    
                    <!-- Header Bagian 02 KETENTUAN & KATEGORI -->
                    <div class="section-pill-header">
                        <div class="section-pill-tag">
                            <span class="pill-number">02</span>
                            <span class="pill-title">KETENTUAN & KATEGORI</span>
                        </div>
                        <span class="pill-chevron" title="Ciutkan / Bentangkan">∧</span>
                    </div>

                    <!-- Input 1: Deadline -->
                    <div class="admin-field-group">
                        <label for="deadline_daftar" class="admin-field-label">Deadline</label>
                        <div class="deadline-input-container">
                            <span class="deadline-calendar-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                            </span>
                            <input 
                                type="text" 
                                name="deadline_daftar" 
                                id="deadline_daftar" 
                                class="admin-field-input has-icon" 
                                value="<?= e($lombaData['deadline_daftar'] ? date('d F Y', strtotime($lombaData['deadline_daftar'])) : '15 Juli 2026') ?>" 
                                placeholder="15 Juli 2026"
                                required
                            >
                        </div>
                    </div>

                    <!-- Input 2: Biaya (Toggle GRATIS / BERBAYAR & Input Nominal) -->
                    <div class="admin-field-group">
                        <label class="admin-field-label">Biaya</label>
                        
                        <?php $isBerbayar = (float)$lombaData['biaya'] > 0; ?>
                        <div class="biaya-segmented-switch">
                            <button type="button" class="biaya-switch-btn <?= !$isBerbayar ? 'active gratis' : '' ?>" id="btn-biaya-gratis">GRATIS</button>
                            <button type="button" class="biaya-switch-btn <?= $isBerbayar ? 'active berbayar' : '' ?>" id="btn-biaya-berbayar">BERBAYAR</button>
                        </div>
                        <input type="hidden" name="jenis_biaya" id="jenis_biaya" value="<?= $isBerbayar ? 'berbayar' : 'gratis' ?>">

                        <input 
                            type="text" 
                            name="biaya" 
                            id="biaya-input" 
                            class="admin-field-input" 
                            value="<?= $isBerbayar ? 'Rp ' . number_format((float)$lombaData['biaya'], 0, ',', '.') : 'Rp 0' ?>" 
                            placeholder="Rp 50.000"
                        >
                    </div>

                    <!-- Input 3: Partisipasi (Individu, Tim, Individu & Tim) -->
                    <div class="admin-field-group">
                        <label class="admin-field-label">Partisipasi</label>
                        <?php $partisipasiVal = $lombaData['partisipasi'] ?? 'Individu/Tim'; ?>
                        
                        <div class="partisipasi-pills-row">
                            <button type="button" class="partisipasi-pill-btn <?= ($partisipasiVal === 'Individu') ? 'active' : '' ?>" data-val="Individu">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                <span>Individu</span>
                            </button>

                            <button type="button" class="partisipasi-pill-btn <?= ($partisipasiVal === 'Tim') ? 'active' : '' ?>" data-val="Tim">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                                <span>Tim</span>
                            </button>

                            <button type="button" class="partisipasi-pill-btn <?= ($partisipasiVal === 'Individu/Tim') ? 'active' : '' ?>" data-val="Individu/Tim">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                                <span>Individu & Tim</span>
                            </button>
                        </div>
                        <input type="hidden" name="partisipasi" id="input-partisipasi" value="<?= e($partisipasiVal) ?>">
                    </div>

                    <!-- Input 4: Tingkat -->
                    <div class="admin-field-group">
                        <label for="tingkat" class="admin-field-label">Tingkat</label>
                        <input 
                            type="text" 
                            name="tingkat" 
                            id="tingkat" 
                            class="admin-field-input" 
                            value="<?= e($lombaData['tingkat'] ?? 'Nasional') ?>" 
                            placeholder="Nasional"
                            required
                        >
                    </div>

                    <!-- Input 5: Bidang Perlombaan -->
                    <div class="admin-field-group">
                        <label for="bidang" class="admin-field-label">Bidang Perlombaan</label>
                        <input 
                            type="text" 
                            name="bidang" 
                            id="bidang" 
                            class="admin-field-input" 
                            value="<?= e($lombaData['bidang'] ?? 'UI/UX Design') ?>" 
                            placeholder="UI/UX Design"
                            required
                        >
                        <!-- Pill Tag Kategori Kuning Sesuai Mockup -->
                        <div>
                            <span class="kategori-amber-pill" id="display-tag-bidang"><?= e($lombaData['bidang'] ?? 'UI/UX Design') ?></span>
                        </div>
                    </div>
                </section>

            </div>

            <!-- ================================================================= -->
            <!-- DI BAWAH: DESKRIPSI & TAUTAN LOMBA                                -->
            <!-- ================================================================= -->
            <section class="admin-card-box card-white-theme" aria-label="Deskripsi dan Tautan Perlombaan">
                
                <!-- Header Bagian 03 DESKRIPSI & TAUTAN -->
                <div class="section-pill-header">
                    <div class="section-pill-tag">
                        <span class="pill-number">03</span>
                        <span class="pill-title">DESKRIPSI & TAUTAN</span>
                    </div>
                    <span class="pill-chevron" title="Ciutkan / Bentangkan">∧</span>
                </div>

                <!-- Input 1: Deskripsi Kegiatan dengan Rich Toolbar -->
                <div class="admin-field-group">
                    <label for="deskripsi-editor" class="admin-field-label">Deskripsi Kegiatan</label>
                    <div class="editor-frame">
                        <!-- Toolbar Formatting: B, I, U, S, |, List, Ordered List, Undo -->
                        <div class="editor-action-bar" role="toolbar" aria-label="Format Teks">
                            <button type="button" class="editor-tool-button" data-cmd="bold" title="Tebal (Bold)"><b>B</b></button>
                            <button type="button" class="editor-tool-button" data-cmd="italic" title="Miring (Italic)"><i>I</i></button>
                            <button type="button" class="editor-tool-button" data-cmd="underline" title="Garis Bawah (Underline)"><u>U</u></button>
                            <button type="button" class="editor-tool-button" data-cmd="strike" title="Coret (Strikethrough)"><s>S</s></button>
                            
                            <span class="editor-v-line">|</span>
                            
                            <button type="button" class="editor-tool-button" data-cmd="bullet" title="Daftar Poin">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="9" y1="6" x2="20" y2="6"></line>
                                    <line x1="9" y1="12" x2="20" y2="12"></line>
                                    <line x1="9" y1="18" x2="20" y2="18"></line>
                                    <circle cx="4" cy="6" r="1.5" fill="currentColor"></circle>
                                    <circle cx="4" cy="12" r="1.5" fill="currentColor"></circle>
                                    <circle cx="4" cy="18" r="1.5" fill="currentColor"></circle>
                                </svg>
                            </button>
                            
                            <button type="button" class="editor-tool-button" data-cmd="number" title="Daftar Angka">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="10" y1="6" x2="21" y2="6"></line>
                                    <line x1="10" y1="12" x2="21" y2="12"></line>
                                    <line x1="10" y1="18" x2="21" y2="18"></line>
                                    <path d="M4 6h1v4"></path>
                                    <path d="M4 10h2"></path>
                                    <path d="M6 18H4c0-1 2-2 2-3s-1-1.5-2-1"></path>
                                </svg>
                            </button>

                            <button type="button" class="editor-tool-button" data-cmd="undo" title="Urungkan (Undo)">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="1 4 1 10 7 10"></polyline>
                                    <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Textarea Deskripsi -->
                        <textarea 
                            name="deskripsi" 
                            id="deskripsi-editor" 
                            class="editor-input-area" 
                            rows="5" 
                            placeholder="Tuliskan detail syarat dan timeline..."
                        ><?= e($lombaData['deskripsi']) ?></textarea>
                    </div>
                </div>

                <!-- Input 2: Link Guidebook (Icon Badge Biru) -->
                <div class="admin-field-group">
                    <label for="link_guidebook" class="admin-field-label">Link Guidebook</label>
                    <div class="link-box-row">
                        <span class="link-square-badge badge-cyan-link" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                            </svg>
                        </span>
                        <input 
                            type="url" 
                            name="link_guidebook" 
                            id="link_guidebook" 
                            class="link-url-field" 
                            value="<?= e($lombaData['link_guidebook'] ?? 'https://bit.ly/guidebook-lomba') ?>" 
                            placeholder="https://bit.ly/guidebook-lomba"
                        >
                    </div>
                </div>

                <!-- Input 3: Link Pendaftaran (Icon Badge Coral / Red-Orange) -->
                <div class="admin-field-group">
                    <label for="link_resmi" class="admin-field-label">Link Pendaftaran</label>
                    <div class="link-box-row">
                        <span class="link-square-badge badge-coral-ticket" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                        </span>
                        <input 
                            type="url" 
                            name="link_resmi" 
                            id="link_resmi" 
                            class="link-url-field" 
                            value="<?= e($lombaData['link_resmi'] ?? 'https://bit.ly/daftar-lomba') ?>" 
                            placeholder="https://bit.ly/daftar-lomba"
                        >
                    </div>
                </div>

            </section>

            <!-- ================================================================= -->
            <!-- TOMBOL AKSI: POSTING DI KANAN BAWAH                               -->
            <!-- ================================================================= -->
            <div class="admin-action-row">
                <button type="submit" class="btn-posting-action" id="btn-posting">
                    Posting
                </button>
            </div>

        </form>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Drag & Drop Poster Upload
    const dropzone = document.getElementById('poster-dropzone');
    const fileInput = document.getElementById('poster-input');
    const btnTrigger = document.getElementById('btn-trigger-file');
    const previewImg = document.getElementById('poster-preview');
    const placeholderBox = document.getElementById('dropzone-placeholder');

    if (btnTrigger && fileInput) {
        btnTrigger.addEventListener('click', function (e) {
            e.stopPropagation();
            fileInput.click();
        });
    }

    if (dropzone && fileInput) {
        dropzone.addEventListener('click', function () {
            fileInput.click();
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, function (e) {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.add('dragover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, function (e) {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.remove('dragover');
            }, false);
        });

        dropzone.addEventListener('drop', function (e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files.length > 0) {
                fileInput.files = files;
                handlePosterPreview(files[0]);
            }
        });

        fileInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                handlePosterPreview(this.files[0]);
            }
        });
    }

    function handlePosterPreview(file) {
        if (!file.type.startsWith('image/')) {
            alert('Silakan pilih file gambar.');
            return;
        }
        const reader = new FileReader();
        reader.onload = function (e) {
            previewImg.src = e.target.result;
            previewImg.style.display = 'block';
            if (placeholderBox) placeholderBox.style.display = 'none';
        };
        reader.readAsDataURL(file);
    }

    // 2. Toggle Biaya (GRATIS vs BERBAYAR)
    const btnGratis = document.getElementById('btn-biaya-gratis');
    const btnBerbayar = document.getElementById('btn-biaya-berbayar');
    const inputJenisBiaya = document.getElementById('jenis_biaya');
    const inputBiaya = document.getElementById('biaya-input');

    if (btnGratis && btnBerbayar) {
        btnGratis.addEventListener('click', function () {
            btnGratis.className = 'biaya-switch-btn active gratis';
            btnBerbayar.className = 'biaya-switch-btn';
            inputJenisBiaya.value = 'gratis';
            inputBiaya.value = 'Rp 0';
            inputBiaya.style.opacity = '0.6';
        });

        btnBerbayar.addEventListener('click', function () {
            btnBerbayar.className = 'biaya-switch-btn active berbayar';
            btnGratis.className = 'biaya-switch-btn';
            inputJenisBiaya.value = 'berbayar';
            if (inputBiaya.value === 'Rp 0' || !inputBiaya.value) {
                inputBiaya.value = 'Rp 50.000';
            }
            inputBiaya.style.opacity = '1';
            inputBiaya.focus();
        });
    }

    // Format Otomatis Input Biaya (Rupiah)
    if (inputBiaya) {
        inputBiaya.addEventListener('input', function (e) {
            let val = this.value.replace(/[^0-9]/g, '');
            if (val) {
                this.value = 'Rp ' + parseInt(val, 10).toLocaleString('id-ID');
            } else {
                this.value = 'Rp 0';
            }
        });
    }

    // 3. Toggle Partisipasi (Individu, Tim, Individu & Tim)
    const partisipasiBtns = document.querySelectorAll('.partisipasi-pill-btn');
    const inputPartisipasi = document.getElementById('input-partisipasi');

    partisipasiBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            partisipasiBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            if (inputPartisipasi) {
                inputPartisipasi.value = this.getAttribute('data-val');
            }
        });
    });

    // 4. Sinkronisasi Live Tag Bidang Perlombaan
    const inputBidang = document.getElementById('bidang');
    const displayTagBidang = document.getElementById('display-tag-bidang');

    if (inputBidang && displayTagBidang) {
        inputBidang.addEventListener('input', function () {
            displayTagBidang.textContent = this.value || 'Umum';
        });
    }

    // 5. Rich Text Toolbar untuk Deskripsi Kegiatan
    const textarea = document.getElementById('deskripsi-editor');
    const toolbarBtns = document.querySelectorAll('.editor-tool-button');

    toolbarBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const cmd = this.getAttribute('data-cmd');
            if (!textarea) return;

            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const selectedText = textarea.value.substring(start, end);

            let replacement = '';
            switch (cmd) {
                case 'bold':
                    replacement = selectedText ? `**${selectedText}**` : '**tebal**';
                    break;
                case 'italic':
                    replacement = selectedText ? `*${selectedText}*` : '*miring*';
                    break;
                case 'underline':
                    replacement = selectedText ? `<u>${selectedText}</u>` : '<u>garis bawah</u>';
                    break;
                case 'strike':
                    replacement = selectedText ? `~~${selectedText}~~` : '~~coret~~';
                    break;
                case 'bullet':
                    replacement = selectedText ? `\n• ${selectedText}` : '\n• Poin syarat';
                    break;
                case 'number':
                    replacement = selectedText ? `\n1. ${selectedText}` : '\n1. Tahap kegiatan';
                    break;
                case 'undo':
                    document.execCommand('undo');
                    return;
            }

            textarea.setRangeText(replacement, start, end, 'select');
            textarea.focus();
        });
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
