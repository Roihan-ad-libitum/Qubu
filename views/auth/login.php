<?php
/**
 * View Halaman Login Qubu
 * Presisi mengikuti mockup desain UI
 */
$title = 'Masuk Qubu - Platform Info Lomba & Kolaborasi';
$extraScript = 'public/js/auth.js';
require_once __DIR__ . '/../layouts/headers.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <!-- Tombol Tutup X -->
        <a href="javascript:void(0);" onclick="window.history.length > 1 ? window.history.back() : window.location.href='index.php';" class="auth-close-btn" title="Tutup" aria-label="Tutup">
            X
        </a>

        <!-- Header: Judul & Subjudul -->
        <div class="auth-header">
            <h1 class="auth-title">Masuk Qubu</h1>
            <p class="auth-subtitle">Temukan Lomba Dan Partner Tim Terbaikmu</p>
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

        <?php if (!empty($info)): ?>
            <div class="auth-alert" style="background:#eff6ff; border:1px solid #bfdbfe; color:#1d4ed8;" role="alert">
                <?= e($info) ?>
            </div>
        <?php endif; ?>

        <!-- Formulir Login -->
        <form method="POST" action="?page=login" id="form-login" novalidate>
            <!-- Field: Email atau username -->
            <div class="form-group">
                <label for="login-identity" class="form-label">Email atau username</label>
                <input 
                    type="text" 
                    name="identity" 
                    id="login-identity" 
                    class="form-input" 
                    placeholder="Masukan Email atau username" 
                    value="<?= e($oldIdentity ?? '') ?>" 
                    required 
                    autofocus
                    autocomplete="username"
                >
            </div>

            <!-- Field: Password -->
            <div class="form-group">
                <label for="login-password" class="form-label">Password</label>
                <input 
                    type="password" 
                    name="password" 
                    id="login-password" 
                    class="form-input" 
                    placeholder="Masukan Password" 
                    required
                    autocomplete="current-password"
                >
            </div>

            <!-- Tautan Lupa Password (Rata Kanan) -->
            <div class="auth-actions-row">
                <a href="javascript:void(0);" onclick="alert('Silakan hubungi administrator kampus untuk reset password.');" class="auth-forgot-link">
                    Lupa Password?
                </a>
            </div>

            <!-- Tombol Masuk (Pill Shaped Centered) -->
            <div class="auth-button-container">
                <button type="submit" class="btn-masuk" id="btn-login-submit">
                    Masuk
                </button>
            </div>

            <!-- Footer: Belum Punya Akun? Daftar Sekarang -->
            <p class="auth-footer">
                Belum Punya Akun? <a href="?page=register" class="auth-register-link">Daftar Sekarang</a>
            </p>

            <!-- Hint Akun Demo untuk Pengujian Cepat -->
            <div class="demo-accounts-box">
                <p style="margin-bottom: 0.35rem;">Akun Testing (Klik untuk auto-isi):</p>
                <span class="demo-pill" onclick="fillLogin('roihanz', 'roihanz123')" title="Klik untuk mengisi data Mahasiswa">Peserta: roihanz</span>
                <span class="demo-pill" onclick="fillLogin('fitriaz', 'fitriaz123')" title="Klik untuk mengisi data Dosen">Dosen: fitriaz</span>
                <span class="demo-pill" onclick="fillLogin('marcelloz', 'marceloz123')" title="Klik untuk mengisi data Admin">Admin: marcelloz</span>
            </div>
        </form>
    </div>
</div>

<script>
    // Helper kecil untuk klik pill demo
    function fillLogin(user, pass) {
        document.getElementById('login-identity').value = user;
        document.getElementById('login-password').value = pass;
    }
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
