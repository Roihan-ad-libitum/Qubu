<?php
/**
 * View Halaman Registrasi & Pemilihan Peran (Mahasiswa / Dosen) Qubu
 * Presisi mengikuti mockup desain "Ingin mendaftar sebagai apa?"
 */
$title = $title ?? 'Ingin mendaftar sebagai apa? - ' . APP_NAME;
$extraCss = ['public/css/components.css', 'public/css/register.css'];

require_once __DIR__ . '/../layouts/headers.php';
?>

<div class="register-page">
    <!-- Latar Belakang Biru Gradasi Atas (Persis Desain Mockup) -->
    <div class="register-hero-bg"></div>

    <!-- 1. Top Navigation Bar -->
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

    <?php if (empty($role)): ?>
        <!-- ========================================================================= -->
        <!-- A. TAMPILAN PEMILIHAN PERAN (PERSIS MOCKUP SCREENSHOT)                    -->
        <!-- ========================================================================= -->
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

    <?php elseif ($role === 'dosen'): ?>
        <!-- ========================================================================= -->
        <!-- B. FORM PENDAFTARAN DOSEN PEMBIMBING                                     -->
        <!-- ========================================================================= -->
        <main class="register-form-container">
            <div class="register-form-card">
                <!-- Tombol Kembali ke Pemilihan Peran -->
                <a href="<?= url('?page=register') ?>" class="form-back-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    Ganti Peran
                </a>

                <div class="register-form-header">
                    <h1 class="register-form-title">Daftar Akun Dosen</h1>
                    <p class="register-form-subtitle">Bagikan keahlian dan bimbing tim mahasiswa berprestasi di Qubu</p>
                </div>

                <!-- Alert Notifikasi Flash Error / Success -->
                <?php if (!empty($error)): ?>
                    <div class="auth-alert auth-alert-error" role="alert">
                        <?= e($error) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="auth-alert auth-alert-success" role="alert">
                        <?= e($success) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="?page=register" id="form-register-dosen" novalidate>
                    <input type="hidden" name="role" value="dosen">

                    <!-- Field: Nama Lengkap Dosen -->
                    <div class="form-group">
                        <label for="nama_dosen" class="form-label">Nama Lengkap beserta Gelar</label>
                        <input 
                            type="text" 
                            name="nama_dosen" 
                            id="nama_dosen" 
                            class="form-input" 
                            placeholder="Contoh: Dr. Fitria Rahmawati, M.Kom." 
                            value="<?= e($old['nama_dosen'] ?? '') ?>" 
                            required 
                            autofocus
                        >
                    </div>

                    <!-- 2 Kolom: NIDN & Bidang Keahlian -->
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="nidn" class="form-label">NIDN</label>
                            <input 
                                type="text" 
                                name="nidn" 
                                id="nidn" 
                                class="form-input" 
                                placeholder="Nomor Induk Dosen" 
                                value="<?= e($old['nidn'] ?? '') ?>" 
                                required
                            >
                        </div>
                        <div class="form-group">
                            <label for="bidang_keahlian" class="form-label">Bidang Keahlian</label>
                            <input 
                                type="text" 
                                name="bidang_keahlian" 
                                id="bidang_keahlian" 
                                class="form-input" 
                                placeholder="Contoh: Web Development" 
                                value="<?= e($old['bidang_keahlian'] ?? '') ?>" 
                                required
                            >
                        </div>
                    </div>

                    <!-- 2 Kolom: Email & No HP -->
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="email" class="form-label">Email Kampus / Resmi</label>
                            <input 
                                type="email" 
                                name="email" 
                                id="email" 
                                class="form-input" 
                                placeholder="dosen@unsika.ac.id" 
                                value="<?= e($old['email'] ?? '') ?>" 
                                required
                            >
                        </div>
                        <div class="form-group">
                            <label for="no_hp" class="form-label">Nomor Handphone (WhatsApp)</label>
                            <input 
                                type="tel" 
                                name="no_hp" 
                                id="no_hp" 
                                class="form-input" 
                                placeholder="08xxxxxxxxxx" 
                                value="<?= e($old['no_hp'] ?? '') ?>" 
                                required
                            >
                        </div>
                    </div>

                    <!-- Field: Username -->
                    <div class="form-group">
                        <label for="username" class="form-label">Username Akun</label>
                        <input 
                            type="text" 
                            name="username" 
                            id="username" 
                            class="form-input" 
                            placeholder="Pilih username unik" 
                            value="<?= e($old['username'] ?? '') ?>" 
                            required
                        >
                    </div>

                    <!-- 2 Kolom: Password & Konfirmasi Password -->
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="password" class="form-label">Password</label>
                            <input 
                                type="password" 
                                name="password" 
                                id="password" 
                                class="form-input" 
                                placeholder="Minimal 6 karakter" 
                                required
                            >
                        </div>
                        <div class="form-group">
                            <label for="password_confirm" class="form-label">Konfirmasi Password</label>
                            <input 
                                type="password" 
                                name="password_confirm" 
                                id="password_confirm" 
                                class="form-input" 
                                placeholder="Ulangi password" 
                                required
                            >
                        </div>
                    </div>

                    <!-- Tombol Submit -->
                    <div class="auth-button-container" style="margin-top: 1.5rem;">
                        <button type="submit" class="btn-masuk" id="btn-submit-dosen">
                            Daftar Sebagai Dosen
                        </button>
                    </div>

                    <p class="auth-footer" style="margin-top: 1.5rem;">
                        Sudah punya akun? <a href="<?= url('?page=login') ?>" class="auth-register-link">Masuk Sekarang</a>
                    </p>
                </form>
            </div>
        </main>

    <?php elseif ($role === 'mahasiswa'): ?>
        <!-- ========================================================================= -->
        <!-- C. FORM PENDAFTARAN MAHASISWA                                            -->
        <!-- ========================================================================= -->
        <main class="register-form-container">
            <div class="register-form-card">
                <a href="<?= url('?page=register') ?>" class="form-back-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    Ganti Peran
                </a>

                <div class="register-form-header">
                    <h1 class="register-form-title">Daftar Akun Mahasiswa</h1>
                    <p class="register-form-subtitle">Temukan partner lomba dan bangun portofolio terbaikmu di Qubu</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="auth-alert auth-alert-error" role="alert">
                        <?= e($error) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="auth-alert auth-alert-success" role="alert">
                        <?= e($success) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="?page=register" id="form-register-mhs" novalidate>
                    <input type="hidden" name="role" value="mahasiswa">

                    <div class="form-group">
                        <label for="nama_mahasiswa" class="form-label">Nama Lengkap Mahasiswa</label>
                        <input 
                            type="text" 
                            name="nama_mahasiswa" 
                            id="nama_mahasiswa" 
                            class="form-input" 
                            placeholder="Nama Lengkap Anda" 
                            value="<?= e($old['nama_mahasiswa'] ?? '') ?>" 
                            required 
                            autofocus
                        >
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="npm" class="form-label">NPM</label>
                            <input 
                                type="text" 
                                name="npm" 
                                id="npm" 
                                class="form-input" 
                                placeholder="Nomor Pokok Mahasiswa" 
                                value="<?= e($old['npm'] ?? '') ?>" 
                                required
                            >
                        </div>
                        <div class="form-group">
                            <label for="program_studi" class="form-label">Program Studi</label>
                            <input 
                                type="text" 
                                name="program_studi" 
                                id="program_studi" 
                                class="form-input" 
                                placeholder="Contoh: Informatika" 
                                value="<?= e($old['program_studi'] ?? '') ?>" 
                                required
                            >
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="angkatan" class="form-label">Tahun Angkatan</label>
                            <input 
                                type="text" 
                                name="angkatan" 
                                id="angkatan" 
                                class="form-input" 
                                placeholder="Contoh: 2025" 
                                value="<?= e($old['angkatan'] ?? '') ?>" 
                                required
                            >
                        </div>
                        <div class="form-group">
                            <label for="bidang_minat" class="form-label">Bidang Minat Lomba</label>
                            <input 
                                type="text" 
                                name="bidang_minat" 
                                id="bidang_minat" 
                                class="form-input" 
                                placeholder="Web, AI, UI/UX, dsb." 
                                value="<?= e($old['bidang_minat'] ?? '') ?>"
                            >
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="email" class="form-label">Email Mahasiswa</label>
                            <input 
                                type="email" 
                                name="email" 
                                id="email" 
                                class="form-input" 
                                placeholder="nama@student.unsika.ac.id" 
                                value="<?= e($old['email'] ?? '') ?>" 
                                required
                            >
                        </div>
                        <div class="form-group">
                            <label for="no_hp" class="form-label">Nomor Handphone (WhatsApp)</label>
                            <input 
                                type="tel" 
                                name="no_hp" 
                                id="no_hp" 
                                class="form-input" 
                                placeholder="08xxxxxxxxxx" 
                                value="<?= e($old['no_hp'] ?? '') ?>" 
                                required
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="username" class="form-label">Username Akun</label>
                        <input 
                            type="text" 
                            name="username" 
                            id="username" 
                            class="form-input" 
                            placeholder="Pilih username unik" 
                            value="<?= e($old['username'] ?? '') ?>" 
                            required
                        >
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="password" class="form-label">Password</label>
                            <input 
                                type="password" 
                                name="password" 
                                id="password" 
                                class="form-input" 
                                placeholder="Minimal 6 karakter" 
                                required
                            >
                        </div>
                        <div class="form-group">
                            <label for="password_confirm" class="form-label">Konfirmasi Password</label>
                            <input 
                                type="password" 
                                name="password_confirm" 
                                id="password_confirm" 
                                class="form-input" 
                                placeholder="Ulangi password" 
                                required
                            >
                        </div>
                    </div>

                    <div class="auth-button-container" style="margin-top: 1.5rem;">
                        <button type="submit" class="btn-masuk" id="btn-submit-mhs">
                            Daftar Sebagai Mahasiswa
                        </button>
                    </div>

                    <p class="auth-footer" style="margin-top: 1.5rem;">
                        Sudah punya akun? <a href="<?= url('?page=login') ?>" class="auth-register-link">Masuk Sekarang</a>
                    </p>
                </form>
            </div>
        </main>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
