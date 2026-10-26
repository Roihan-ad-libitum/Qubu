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
    <!-- ========================================================================= -->
    <!-- C. TAMPILAN PENDAFTARAN MAHASISWA (PERSIS MOCKUP BIODATA FIGMA)           -->
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

            <form method="POST" action="?page=register" id="form-register-mhs" class="mhs-biodata-form" novalidate>
                <input type="hidden" name="role" value="mahasiswa">

                <!-- 1. NPM -->
                <div class="mhs-form-group">
                    <label for="npm" class="mhs-form-label">NPM</label>
                    <input 
                        type="text" 
                        name="npm" 
                        id="npm" 
                        class="mhs-form-input" 
                        placeholder="" 
                        value="<?= e($old['npm'] ?? '') ?>" 
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
                        value="<?= e($old['nama_mahasiswa'] ?? $old['nama_lengkap'] ?? '') ?>" 
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
                        value="<?= e($old['program_studi'] ?? '') ?>" 
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
                        value="<?= e($old['angkatan'] ?? '') ?>" 
                        required
                    >
                </div>

                <!-- 5. Bidang Peminatan -->
                <div class="mhs-form-group">
                    <label for="bidang_minat" class="mhs-form-label">Bidang Peminatan</label>
                    <input 
                        type="text" 
                        name="bidang_minat" 
                        id="bidang_minat" 
                        class="mhs-form-input" 
                        placeholder="" 
                        value="<?= e($old['bidang_minat'] ?? $old['bidang_peminatan'] ?? '') ?>"
                    >
                </div>

                <!-- Tombol Submit: Daftar -->
                <div class="mhs-btn-container">
                    <button type="submit" class="mhs-btn-submit" id="btn-submit-mhs">
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
