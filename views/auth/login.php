<?php

$title = 'Masuk Qubu - Platform Info Lomba & Kolaborasi';
require_once __DIR__ . '/../layouts/headers.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">

        <a href="javascript:void(0);" onclick="window.history.length > 1 ? window.history.back() : window.location.href='index.php';" class="auth-close-btn" title="Tutup" aria-label="Tutup">
            X
        </a>

        <div class="auth-header">
            <h1 class="auth-title">Masuk Qubu</h1>
            <p class="auth-subtitle">Temukan Lomba Dan Partner Tim Terbaikmu</p>
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

        <?php if (!empty($info)): ?>
            <div class="auth-alert" style="background:#eff6ff; border:1px solid #bfdbfe; color:#1d4ed8;" role="alert">
                <?= e($info) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="?page=login" id="form-login" novalidate>
            <input type="hidden" name="_csrf" value="<?= e($csrfToken ?? Csrf::token()) ?>">

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

            <div class="form-group">
                <label for="login-password" class="form-label">Password</label>
                <div class="password-control">
                    <input 
                        type="password" 
                        name="password" 
                        id="login-password" 
                        class="form-input" 
                        placeholder="Masukan Password" 
                        required
                        autocomplete="current-password"
                        data-password-visibility
                    >
                    <button class="password-visibility-toggle" type="button" aria-controls="login-password" aria-label="Tampilkan kata sandi" aria-pressed="false" data-password-toggle>
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

            <div class="auth-actions-row">
                <a href="javascript:void(0);" onclick="alert('Silakan hubungi administrator kampus untuk reset password.');" class="auth-forgot-link">
                    Lupa Password?
                </a>
            </div>

            <div class="auth-button-container">
                <button type="submit" class="btn-masuk" id="btn-login-submit">
                    Masuk
                </button>
            </div>

            <p class="auth-footer">
                Belum Punya Akun? <a href="?page=register" class="auth-register-link">Daftar Sekarang</a>
            </p>

            <div class="demo-accounts-box">
                <p style="margin-bottom: 0.35rem;">Akun Testing (Klik untuk auto-isi):</p>
                <span class="demo-pill" onclick="fillLogin('roihanz', 'roihanz123')" title="Klik untuk mengisi data Mahasiswa">Peserta: roihanz</span>
                <span class="demo-pill" onclick="fillLogin('fitriaz', 'fitriaz123')" title="Klik untuk mengisi data Dosen">Dosen: fitriaz</span>
                <span class="demo-pill" onclick="fillLogin('marcelloz', 'marceloz123')" title="Klik untuk mengisi data Admin">Admin: marcelloz</span>
            </div>
        </form>
    </div>
</div>

<script src="<?= url('public/js/auth.js') ?>" defer></script>
<script>
    function fillLogin(user, pass) {
        document.getElementById('login-identity').value = user;
        document.getElementById('login-password').value = pass;
    }
</script>
<script src="<?= url('public/js/password-visibility.js') ?>?v=<?= (int) filemtime(__DIR__ . '/../../public/js/password-visibility.js') ?>" defer></script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
