<?php

require_once __DIR__ . '/../layouts/headers.php';

$adminBidangOpts = defined('BIDANG_QUBU') ? BIDANG_QUBU : ['Frontend', 'Backend', 'UI/UX Design', 'Data Science', 'Robotic'];
$selectedBidangs = parseSkillTags((string) ($lombaData['bidang'] ?? ''), true, []);
?>

<div class="admin-page admin-lomba-page">
<?php
$activeAdminPage = 'lomba';
require __DIR__ . '/_nav.php';
?>

    <main class="admin-content-wrap">
        <div class="admin-heading">
            <div>
                <h1><?= empty($lombaData['id_lomba']) ? 'Tambah Perlombaan' : 'Edit Perlombaan' ?></h1>
                <p>Lengkapi detail lomba, tanggal, biaya, kategori bidang, dan tautan pendaftarannya.</p>
                <?php if ($isLombaClosed): ?>
                    <span class="badge-pill badge-red">Pendaftaran Ditutup</span>
                <?php endif; ?>
            </div>
            <a class="admin-secondary-button" href="<?= url('?page=admin-lomba') ?>">Kembali ke daftar</a>
        </div>
        <?php if (!empty($error)): ?>
            <div class="admin-alert-box admin-alert-error" role="alert">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= url('?page=admin-lomba-save') ?>" id="form-crud-lomba" enctype="multipart/form-data">
            <input type="hidden" name="_csrf" value="<?= e($csrfToken ?? '') ?>">
            <input type="hidden" name="id_lomba" value="<?= e($lombaData['id_lomba']) ?>">

            <div class="admin-top-columns">

                <section class="admin-card-box card-periwinkle-theme" aria-label="Identitas dan Poster Perlombaan">
                    <div class="dropzone-upload-poster" id="poster-dropzone" title="Klik atau tarik file gambar untuk mengunggah poster lomba">
                        <?php if (!empty($lombaData['poster']) && hasPublicFile($lombaData['poster'])): ?>
                            <img src="<?= url($lombaData['poster']) ?>" alt="Poster Lomba" class="poster-preview-display" id="poster-preview" style="display: block;">
                        <?php else: ?>
                            <img src="" alt="Preview Poster" class="poster-preview-display" id="poster-preview">
                        <?php endif; ?>

                        <div id="dropzone-placeholder" style="<?= !empty($lombaData['poster']) && hasPublicFile($lombaData['poster']) ? 'display:none;' : '' ?>">
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

                    <div class="section-pill-header">
                        <div class="section-pill-tag">
                            <span class="pill-number">01</span>
                            <span class="pill-title">IDENTITAS &amp; POSTER</span>
                        </div>
                        <span class="pill-chevron" title="Ciutkan / Bentangkan">∧</span>
                    </div>

                    <div class="admin-field-group">
                        <label for="nama_lomba" class="admin-field-label">Nama / Judul Perlombaan</label>
                        <input 
                            type="text" 
                            name="nama_lomba" 
                            id="nama_lomba" 
                            class="admin-field-input admin-input-bold-title" 
                            value="<?= e($lombaData['nama_lomba']) ?>" 
                            placeholder="Semantik Informatics Competition" 
                            maxlength="100"
                            required
                        >
                    </div>

                    <div class="admin-field-group">
                        <label for="penyelenggara" class="admin-field-label">Penyelenggara</label>
                        <input 
                            type="text" 
                            name="penyelenggara" 
                            id="penyelenggara" 
                            class="admin-field-input" 
                            value="<?= e($lombaData['penyelenggara']) ?>" 
                            placeholder="Himpunan Mahasiswa Informatika" 
                            maxlength="100"
                            required
                        >
                    </div>
                </section>

                <section class="admin-card-box card-periwinkle-theme" aria-label="Ketentuan dan Kategori Perlombaan">
                    <div class="section-pill-header">
                        <div class="section-pill-tag">
                            <span class="pill-number">02</span>
                            <span class="pill-title">KETENTUAN &amp; KATEGORI</span>
                        </div>
                        <span class="pill-chevron" title="Ciutkan / Bentangkan">∧</span>
                    </div>

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
                                type="date" 
                                name="deadline_daftar" 
                                id="deadline_daftar" 
                                class="admin-field-input has-icon" 
                                value="<?= !empty($lombaData['deadline_daftar']) ? e(date('Y-m-d', strtotime($lombaData['deadline_daftar']))) : '' ?>" 
                                required
                            >
                        </div>
                    </div>

                    <div class="admin-field-group">
                        <label for="tanggal_pelaksanaan" class="admin-field-label">Tanggal Pelaksanaan</label>
                        <input
                            type="date"
                            name="tanggal_pelaksanaan"
                            id="tanggal_pelaksanaan"
                            class="admin-field-input"
                            value="<?= !empty($lombaData['tanggal_pelaksanaan']) ? e(date('Y-m-d', strtotime($lombaData['tanggal_pelaksanaan']))) : '' ?>"
                            required
                        >
                    </div>

                    <div class="admin-field-group">
                        <label class="admin-field-label">Biaya</label>
                        <?php
                        $isBerbayar = ($lombaData['jenis_biaya'] ?? ((float) $lombaData['biaya'] > 0 ? 'berbayar' : 'gratis')) === 'berbayar';
                        $feeDisplayValue = array_key_exists('biaya', $old ?? [])
                            ? $lombaData['biaya']
                            : ($isBerbayar ? 'Rp ' . number_format((float) $lombaData['biaya'], 0, ',', '.') : 'Rp 0');
                        ?>
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
                            value="<?= e($feeDisplayValue) ?>" 
                            placeholder="Rp 50.000"
                        >
                    </div>

                    <div class="admin-field-group">
                        <label class="admin-field-label">Partisipasi</label>
                        <?php $partisipasiVal = $lombaData['partisipasi'] ?? ''; ?>
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
                                <span>Individu &amp; Tim</span>
                            </button>
                        </div>
                        <input type="hidden" name="partisipasi" id="input-partisipasi" value="<?= e($partisipasiVal) ?>">
                    </div>

                    <div class="admin-field-group">
                        <label for="tingkat" class="admin-field-label">Tingkat</label>
                        <input 
                            type="text" 
                            name="tingkat" 
                            id="tingkat" 
                            class="admin-field-input" 
                            value="<?= e($lombaData['tingkat'] ?? '') ?>" 
                            placeholder="Nasional"
                            maxlength="50"
                            required
                        >
                    </div>

                    <div class="admin-field-group">
                        <label class="admin-field-label" for="bidang-primary">Bidang Perlombaan</label>
                        <div
                            id="bidang-multiselect-list"
                            class="bidang-multiselect-list"
                            data-options="<?= e((string) json_encode($adminBidangOpts, JSON_UNESCAPED_UNICODE)) ?>"
                        >
                            <?php foreach ($selectedBidangs as $bIdx => $selectedB): ?>
                                <div class="bidang-select-row">
                                    <select name="bidang[]" class="admin-field-input bidang-select-item" <?= $bIdx === 0 ? 'id="bidang-primary"' : '' ?> aria-label="Bidang Perlombaan">
                                        <option value="" disabled>Pilih bidang perlombaan...</option>
                                        <?php foreach ($adminBidangOpts as $bOpt): ?>
                                            <option value="<?= e($bOpt) ?>" <?= strcasecmp($selectedB, $bOpt) === 0 ? 'selected' : '' ?>>
                                                <?= e($bOpt) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="button" class="bidang-remove-btn" title="Hapus bidang <?= e($selectedB) ?>" aria-label="Hapus bidang <?= e($selectedB) ?>">✕</button>
                                </div>
                            <?php endforeach; ?>

                            <?php if (count($selectedBidangs) < count($adminBidangOpts)): ?>
                                <div class="bidang-select-row">
                                    <select
                                        name="bidang[]"
                                        class="admin-field-input bidang-select-item"
                                        <?= empty($selectedBidangs) ? 'id="bidang-primary" required' : '' ?>
                                        aria-label="Tambah Bidang Perlombaan"
                                    >
                                        <option value="" selected><?= empty($selectedBidangs) ? 'Pilih bidang perlombaan...' : '+ Tambah bidang perlombaan lainnya...' ?></option>
                                        <?php foreach ($adminBidangOpts as $bOpt): ?>
                                            <?php if (!in_array($bOpt, $selectedBidangs, true)): ?>
                                                <option value="<?= e($bOpt) ?>"><?= e($bOpt) ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="bidang-pills-preview" id="display-tag-bidang-list">
                            <?php foreach ($selectedBidangs as $bTag): ?>
                                <span class="kategori-amber-pill"><?= e($bTag) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>

            </div>

            <section class="admin-card-box card-white-theme" aria-label="Deskripsi dan Tautan Perlombaan">
                <div class="section-pill-header">
                    <div class="section-pill-tag">
                        <span class="pill-number">03</span>
                        <span class="pill-title">DESKRIPSI &amp; TAUTAN</span>
                    </div>
                    <span class="pill-chevron" title="Ciutkan / Bentangkan">∧</span>
                </div>

                <div class="admin-field-group">
                    <label for="deskripsi-editor" class="admin-field-label">Deskripsi Kegiatan</label>
                    <div class="editor-frame">
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

                        <textarea 
                            name="deskripsi" 
                            id="deskripsi-editor" 
                            class="editor-input-area" 
                            rows="5" 
                            placeholder="Tuliskan detail syarat dan timeline..."
                        ><?= e($lombaData['deskripsi']) ?></textarea>
                    </div>
                </div>

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
                            value="<?= e($lombaData['link_resmi'] ?? '') ?>" 
                            placeholder="https://bit.ly/daftar-lomba"
                        >
                    </div>
                </div>

            </section>

            <div class="admin-action-row">
                <button type="submit" class="btn-posting-action" id="btn-posting">
                    <?= empty($lombaData['id_lomba']) ? 'Simpan Perlombaan' : 'Simpan Perubahan' ?>
                </button>
            </div>

        </form>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.section-pill-header').forEach(function (header) {
        header.style.cursor = 'pointer';
        header.addEventListener('click', function () {
            const card = header.closest('.admin-card-box');
            if (!card) return;
            const chevron = header.querySelector('.pill-chevron');
            const isCollapsed = card.classList.toggle('is-collapsed');
            Array.from(card.children).forEach(function (child) {
                if (child !== header) {
                    child.style.display = isCollapsed ? 'none' : '';
                }
            });
            if (chevron) {
                chevron.textContent = isCollapsed ? '∨' : '∧';
            }
        });
    });

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

    if (inputBiaya) {
        inputBiaya.addEventListener('input', function () {
            let val = this.value.replace(/[^0-9]/g, '');
            if (val) {
                this.value = 'Rp ' + parseInt(val, 10).toLocaleString('id-ID');
            } else {
                this.value = 'Rp 0';
            }
        });
    }

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

    const bidangListEl = document.getElementById('bidang-multiselect-list');
    const pillsPreviewEl = document.getElementById('display-tag-bidang-list');

    if (bidangListEl) {
        let allBidangOptions = [];
        try {
            allBidangOptions = JSON.parse(bidangListEl.dataset.options || '[]');
        } catch (_) {
            allBidangOptions = ['Frontend', 'Backend', 'UI/UX Design', 'Data Science', 'Robotic'];
        }

        function getSelectedValues() {
            const values = [];
            bidangListEl.querySelectorAll('.bidang-select-item').forEach(function (sel) {
                const v = (sel.value || '').trim();
                if (v !== '' && values.indexOf(v) === -1) {
                    values.push(v);
                }
            });
            return values;
        }

        function renderBidangMultiselect(selectedValues) {
            while (bidangListEl.firstChild) {
                bidangListEl.removeChild(bidangListEl.firstChild);
            }

            selectedValues.forEach(function (val, idx) {
                const row = document.createElement('div');
                row.className = 'bidang-select-row';

                const select = document.createElement('select');
                select.name = 'bidang[]';
                select.className = 'admin-field-input bidang-select-item';
                if (idx === 0) {
                    select.id = 'bidang-primary';
                }
                select.setAttribute('aria-label', 'Bidang Perlombaan ' + (idx + 1));

                const placeholderOpt = document.createElement('option');
                placeholderOpt.value = '';
                placeholderOpt.disabled = true;
                placeholderOpt.textContent = 'Pilih bidang perlombaan...';
                select.appendChild(placeholderOpt);

                allBidangOptions.forEach(function (opt) {
                    if (opt === val || selectedValues.indexOf(opt) === -1) {
                        const o = document.createElement('option');
                        o.value = opt;
                        o.textContent = opt;
                        if (opt === val) o.selected = true;
                        select.appendChild(o);
                    }
                });

                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'bidang-remove-btn';
                removeBtn.title = 'Hapus bidang ' + val;
                removeBtn.setAttribute('aria-label', 'Hapus bidang ' + val);
                removeBtn.textContent = '✕';
                removeBtn.addEventListener('click', function () {
                    const updated = selectedValues.filter(function (item) { return item !== val; });
                    renderBidangMultiselect(updated);
                });

                select.addEventListener('change', function () {
                    renderBidangMultiselect(getSelectedValues());
                });

                row.appendChild(select);
                row.appendChild(removeBtn);
                bidangListEl.appendChild(row);
            });

            if (selectedValues.length < allBidangOptions.length) {
                const nextRow = document.createElement('div');
                nextRow.className = 'bidang-select-row';

                const nextSelect = document.createElement('select');
                nextSelect.name = 'bidang[]';
                nextSelect.className = 'admin-field-input bidang-select-item';
                if (selectedValues.length === 0) {
                    nextSelect.id = 'bidang-primary';
                    nextSelect.required = true;
                }
                nextSelect.setAttribute('aria-label', selectedValues.length === 0 ? 'Pilih Bidang Perlombaan' : 'Tambah Bidang Perlombaan');

                const defaultOpt = document.createElement('option');
                defaultOpt.value = '';
                defaultOpt.selected = true;
                defaultOpt.textContent = selectedValues.length === 0
                    ? 'Pilih bidang perlombaan...'
                    : '+ Tambah bidang perlombaan lainnya...';
                nextSelect.appendChild(defaultOpt);

                allBidangOptions.forEach(function (opt) {
                    if (selectedValues.indexOf(opt) === -1) {
                        const o = document.createElement('option');
                        o.value = opt;
                        o.textContent = opt;
                        nextSelect.appendChild(o);
                    }
                });

                nextSelect.addEventListener('change', function () {
                    if (nextSelect.value) {
                        renderBidangMultiselect(getSelectedValues());
                    }
                });

                nextRow.appendChild(nextSelect);
                bidangListEl.appendChild(nextRow);
            }

            if (pillsPreviewEl) {
                while (pillsPreviewEl.firstChild) {
                    pillsPreviewEl.removeChild(pillsPreviewEl.firstChild);
                }
                selectedValues.forEach(function (val) {
                    const span = document.createElement('span');
                    span.className = 'kategori-amber-pill';
                    span.textContent = val;
                    pillsPreviewEl.appendChild(span);
                });
            }
        }

        renderBidangMultiselect(getSelectedValues());
    }

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
