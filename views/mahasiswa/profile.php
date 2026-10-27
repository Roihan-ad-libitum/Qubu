<?php
/**
 * View Halaman Profil Mahasiswa Qubu
 * Presisi mengikuti mockup UI: Dua Kartu (Informasi Profil & Form Data NPM/Studi/Peminatan)
 */
$title = $title ?? 'Informasi Profil Mahasiswa - ' . APP_NAME;
$extraCss = ['public/css/components.css', 'public/css/profile.css'];

require_once __DIR__ . '/../layouts/headers.php';
?>

<div class="profile-page">
    <!-- 1. Background Biru Gradasi Atas dengan Tekstur Noise -->
    <div class="profile-hero-bg" aria-hidden="true"></div>

    <!-- 2. Top Navigation Bar Sesuai Mockup -->
    <nav class="profile-nav" aria-label="Navigasi Utama">
        <!-- Logo QUBU Kiri -->
        <a href="<?= url('index.php') ?>" class="profile-logo-link" aria-label="Beranda Qubu">
            <svg class="profile-logo-svg" viewBox="0 0 160 55" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="logoBlue" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#38bdf8" />
                        <stop offset="100%" stop-color="#2563eb" />
                    </linearGradient>
                    <linearGradient id="logoPurple" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#c084fc" />
                        <stop offset="100%" stop-color="#818cf8" />
                    </linearGradient>
                </defs>
                <circle cx="16" cy="11" r="5.5" fill="url(#logoBlue)" />
                <circle cx="28" cy="11" r="5.5" fill="url(#logoPurple)" />
                <path d="M 10 24 C 7 32, 14 42, 22 42 C 30 42, 36 34, 34 25 C 32 17, 23 17, 18 20 C 13 23, 11 31, 14 36" 
                      stroke="url(#logoBlue)" stroke-width="4.5" stroke-linecap="round" fill="none" />
                <path d="M 22 42 C 26 42, 33 40, 36 34 C 38 29, 36 22, 30 20" 
                      stroke="url(#logoPurple)" stroke-width="4.5" stroke-linecap="round" fill="none" />
                <path d="M 28 35 L 36 43" 
                      stroke="url(#logoPurple)" stroke-width="4.5" stroke-linecap="round" />
                <text x="48" y="36" font-family="'Plus Jakarta Sans', system-ui, sans-serif" font-size="28" font-weight="900" fill="#38bdf8" letter-spacing="-0.03em">QUBU</text>
            </svg>
        </a>

        <!-- Menu Navigasi Tengah & Kanan -->
        <div class="profile-nav-menu">
            <a href="<?= url('?page=home') ?>" class="profile-nav-link">Info Lomba</a>
            <a href="<?= url('?page=cari-partner') ?>" class="profile-nav-link">Cari Partner</a>
            <a href="<?= url('?page=dosen-pembimbing') ?>" class="profile-nav-link">Dosen Pembimbing</a>

            <!-- Avatar User Dropdown di Kanan Atas Sesuai Mockup -->
            <div class="profile-nav-user">
                <button type="button" class="nav-avatar-btn" id="nav-avatar-btn" aria-label="Menu Pengguna" aria-expanded="false">
                    <?php if (!empty($data['foto']) && file_exists(__DIR__ . '/../../' . $data['foto'])): ?>
                        <img src="<?= url($data['foto']) ?>" alt="Avatar" class="nav-avatar-img">
                    <?php else: ?>
                        <!-- Ikon Avatar Outline Hitam Persis Mockup -->
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    <?php endif; ?>
                </button>

                <!-- Dropdown Menu Profil -->
                <div class="user-dropdown-menu" id="user-dropdown-menu">
                    <div class="user-dropdown-header">
                        <p class="user-dropdown-name"><?= e($data['nama_mahasiswa']) ?></p>
                        <p class="user-dropdown-handle">@<?= e($data['username']) ?></p>
                    </div>
                    <a href="<?= url('?page=mahasiswa-dashboard') ?>" class="user-dropdown-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                        Dashboard
                    </a>
                    <a href="<?= url('?page=mahasiswa-profile') ?>" class="user-dropdown-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        Profil Saya
                    </a>
                    <a href="<?= url('?page=logout') ?>" class="user-dropdown-item logout">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        Keluar
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- 3. Form Kontainer Utama Dua Kartu -->
    <main class="profile-main-container">
        <!-- Alert Flash Notification -->
        <?php if (!empty($error)): ?>
            <div class="auth-alert auth-alert-error" role="alert" style="margin-bottom: 2rem;">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="auth-alert auth-alert-success" role="alert" style="margin-bottom: 2rem;">
                <?= e($success) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= url('?page=mahasiswa-profile') ?>" id="form-update-profile" enctype="multipart/form-data">
            <div class="profile-grid">
                
                <!-- ============================================================= -->
                <!-- KARTU 1 (KIRI): INFORMASI PROFIL                              -->
                <!-- ============================================================= -->
                <section class="profile-card-left" aria-label="Informasi Ringkas Profil">
                    <header class="profile-card-header">
                        <h1 class="profile-card-title">Informasi Profil</h1>
                    </header>

                    <div class="profile-card-body">
                        <!-- Lingkaran Avatar Abu-abu Sesuai Mockup -->
                        <div class="profile-avatar-wrapper" title="Klik untuk mengganti foto profil">
                            <div class="profile-avatar-circle" id="profile-avatar-circle">
                                <?php if (!empty($data['foto']) && file_exists(__DIR__ . '/../../' . $data['foto'])): ?>
                                    <img src="<?= url($data['foto']) ?>" alt="Foto <?= e($data['nama_mahasiswa']) ?>" class="profile-avatar-img" id="profile-img-preview">
                                <?php else: ?>
                                    <!-- Placeholder Abu-abu Solid Persis Mockup -->
                                    <img src="" alt="Foto Profil" class="profile-avatar-img" id="profile-img-preview" style="display: none;">
                                <?php endif; ?>
                            </div>

                            <!-- Tombol Overlay Kamera untuk Ganti Foto -->
                            <label for="input-profile-photo" class="profile-avatar-overlay" title="Pilih foto baru dari penyimpanan">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                    <circle cx="12" cy="13" r="4"></circle>
                                </svg>
                            </label>
                            <input type="file" name="foto" id="input-profile-photo" accept="image/png, image/jpeg, image/jpg, image/webp" style="display: none;">
                        </div>

                        <!-- Nama Lengkap Mahasiswa Sesuai Mockup -->
                        <h2 class="profile-name" id="display-nama"><?= e($data['nama_mahasiswa']) ?></h2>
                        <input type="hidden" name="nama_mahasiswa" id="input-nama-mahasiswa" value="<?= e($data['nama_mahasiswa']) ?>">

                        <!-- Username Sesuai Mockup -->
                        <p class="profile-handle" id="display-handle">@<?= e($data['username']) ?></p>

                        <!-- Badge Pill Biru Mahasiswa Sesuai Mockup -->
                        <div class="profile-role-badge">
                            Mahasiswa
                        </div>
                    </div>
                </section>

                <!-- ============================================================= -->
                <!-- KARTU 2 (KANAN): DETAIL FORM PROFIL (NPM, PRODI, ANGKATAN, MINAT) -->
                <!-- ============================================================= -->
                <section class="profile-card-right" aria-label="Formulir Detail Mahasiswa">
                    <!-- 1. NPM -->
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

                    <!-- 2. Program Studi -->
                    <div class="profile-form-group">
                        <label for="program_studi" class="profile-form-label">Program Studi</label>
                        <input 
                            type="text" 
                            name="program_studi" 
                            id="program_studi" 
                            class="profile-input" 
                            value="<?= e($data['program_studi']) ?>" 
                            placeholder="Masukkan Program Studi"
                            required
                        >
                    </div>

                    <!-- 3. Angkatan -->
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

                    <!-- 4. Bidang Peminatan (Pill Tags Sesuai Mockup) -->
                    <div class="profile-form-group">
                        <label class="profile-form-label">Bidang Peminatan</label>
                        
                        <div class="profile-tags-container" id="profile-tags-container">
                            <?php foreach ($tags as $tag): ?>
                                <span class="profile-tag-pill" data-tag="<?= e($tag) ?>">
                                    <span class="tag-label"><?= e($tag) ?></span>
                                    <button type="button" class="tag-remove-btn" title="Hapus minat" aria-label="Hapus <?= e($tag) ?>">✕</button>
                                </span>
                            <?php endforeach; ?>

                            <!-- Tombol Tambah + Sesuai Mockup -->
                            <button type="button" class="btn-add-tag" id="btn-show-add-tag">
                                <span>Tambah</span>
                                <span style="font-size: 1.1rem; line-height: 1;">+</span>
                            </button>
                        </div>

                        <!-- Box Tambah Pilihan Minat (Fokus pada kartu index.php) -->
                        <div class="add-tag-box" id="add-tag-box">
                            <select id="select-new-tag" class="add-tag-select">
                                <option value="" disabled selected>Pilih Bidang Peminatan...</option>
                                <?php foreach ($availableOptions as $opt): ?>
                                    <option value="<?= e($opt) ?>"><?= e($opt) ?></option>
                                <?php endforeach; ?>
                            </select>

                            <button type="button" class="btn-confirm-add-tag" id="btn-confirm-add-tag">Tambahkan</button>
                            <button type="button" class="btn-cancel-add-tag" id="btn-cancel-add-tag">Batal</button>
                        </div>

                        <!-- Input Hidden untuk Menyimpan Nilai Tags -->
                        <input type="hidden" name="bidang_minat" id="input-bidang-minat" value="<?= e(implode(', ', $tags)) ?>">
                    </div>

                    <!-- Tombol Aksi Simpan Profil -->
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
                </section>

            </div>
        </form>
    </main>
</div>

<!-- Script Interaktif untuk Pengelolaan Tag Minat & Upload Foto -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Toggle User Dropdown Menu di Top-Right
    const avatarBtn = document.getElementById('nav-avatar-btn');
    const dropdownMenu = document.getElementById('user-dropdown-menu');

    if (avatarBtn && dropdownMenu) {
        avatarBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdownMenu.classList.toggle('show');
            const isExpanded = dropdownMenu.classList.contains('show');
            avatarBtn.setAttribute('aria-expanded', isExpanded);
        });

        document.addEventListener('click', function(e) {
            if (!dropdownMenu.contains(e.target) && e.target !== avatarBtn) {
                dropdownMenu.classList.remove('show');
                avatarBtn.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // 2. Pengelolaan Tags Bidang Peminatan
    const tagsContainer = document.getElementById('profile-tags-container');
    const inputHiddenMinat = document.getElementById('input-bidang-minat');
    const btnShowAddTag = document.getElementById('btn-show-add-tag');
    const addTagBox = document.getElementById('add-tag-box');
    const selectNewTag = document.getElementById('select-new-tag');
    const inputCustomTag = document.getElementById('input-custom-tag');
    const btnConfirmAdd = document.getElementById('btn-confirm-add-tag');
    const btnCancelAdd = document.getElementById('btn-cancel-add-tag');

    // Sinkronisasi data tag ke input hidden form
    function syncHiddenInput() {
        const pills = tagsContainer.querySelectorAll('.profile-tag-pill');
        const values = [];
        pills.forEach(pill => {
            const val = pill.getAttribute('data-tag');
            if (val) values.push(val);
        });
        inputHiddenMinat.value = values.join(', ');
    }

    // Tambahkan event remove ke tag yang sudah ada
    tagsContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('tag-remove-btn')) {
            const pill = e.target.closest('.profile-tag-pill');
            if (pill) {
                pill.style.opacity = '0';
                pill.style.transform = 'scale(0.8)';
                setTimeout(() => {
                    pill.remove();
                    syncHiddenInput();
                }, 150);
            }
        }
    });

    // Buka kotak tambah tag
    if (btnShowAddTag && addTagBox) {
        btnShowAddTag.addEventListener('click', function() {
            addTagBox.classList.add('show');
            selectNewTag.focus();
        });
    }

    // Batal tambah tag
    if (btnCancelAdd) {
        btnCancelAdd.addEventListener('click', function() {
            addTagBox.classList.remove('show');
            selectNewTag.value = '';
        });
    }

    // Konfirmasi penambahan tag baru (hanya dari pilihan kartu index.php)
    if (btnConfirmAdd) {
        btnConfirmAdd.addEventListener('click', function() {
            const tagValue = selectNewTag.value ? selectNewTag.value.trim() : '';

            if (!tagValue) {
                alert('Silakan pilih salah satu bidang peminatan dari daftar.');
                return;
            }

            // Cek apakah tag sudah ada
            const existingPills = tagsContainer.querySelectorAll('.profile-tag-pill');
            let isDuplicate = false;
            existingPills.forEach(p => {
                if (p.getAttribute('data-tag').toLowerCase() === tagValue.toLowerCase()) {
                    isDuplicate = true;
                }
            });

            if (isDuplicate) {
                alert('Bidang peminatan "' + tagValue + '" sudah ditambahkan.');
                return;
            }

            // Buat elemen tag baru
            const newPill = document.createElement('span');
            newPill.className = 'profile-tag-pill';
            newPill.setAttribute('data-tag', tagValue);
            newPill.innerHTML = `
                <span class="tag-label">${tagValue}</span>
                <button type="button" class="tag-remove-btn" title="Hapus minat" aria-label="Hapus ${tagValue}">✕</button>
            `;

            // Sisipkan sebelum tombol Tambah +
            tagsContainer.insertBefore(newPill, btnShowAddTag);
            syncHiddenInput();

            // Reset form tambah tag
            selectNewTag.value = '';
            inputCustomTag.value = '';
            inputCustomTag.style.display = 'none';
            addTagBox.classList.remove('show');
        });
    }

    // 3. Live Preview Ganti Foto Profil
    const inputPhoto = document.getElementById('input-profile-photo');
    const imgPreview = document.getElementById('profile-img-preview');
    const avatarCircle = document.getElementById('profile-avatar-circle');

    if (inputPhoto && imgPreview) {
        // Klik lingkaran avatar membuka dialog foto
        avatarCircle.addEventListener('click', function() {
            inputPhoto.click();
        });

        inputPhoto.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                if (!file.type.startsWith('image/')) {
                    alert('Harap pilih file gambar (JPG, PNG, WEBP).');
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    imgPreview.src = e.target.result;
                    imgPreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
