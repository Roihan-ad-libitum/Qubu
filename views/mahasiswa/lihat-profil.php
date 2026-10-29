<?php
/**
 * View Halaman Profil Publik Kandidat Partner Read-Only (?page=lihat-profil&id={id_mahasiswa})
 * Menampilkan:
 * - Top Navbar Qubu dengan menu "Cari Partner" aktif
 * - Tombol "Kembali ke Cari Partner"
 * - Kartu Profil Publik Read-Only (Avatar/Inisial, Nama, NPM, Prodi, Angkatan, Bidang Keahlian, Bio Singkat, Tombol Placeholder "Ajak ke Tim")
 * - Tampilan 404 Bersahabat jika ID mahasiswa tidak valid atau tidak ditemukan
 */

$title      = $title ?? 'Profil Kandidat Partner - ' . APP_NAME;
$extraCss   = $extraCss ?? ['public/css/components.css', 'public/css/dashboard.css', 'public/css/cari-partner.css'];
$isNotFound = $isNotFound ?? empty($kandidat);

require_once __DIR__ . '/../layouts/headers.php';
?>

<style>
.public-profile-wrapper {
    max-width: 820px;
    margin: 2.25rem auto 4rem;
    padding: 0 1.5rem;
}
.public-profile-card {
    background: #ffffff;
    border-radius: 32px;
    padding: 2.25rem;
    border: 1px solid #e2e8f0;
    box-shadow: 0 16px 38px rgba(15, 23, 42, 0.08);
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}
.public-profile-top {
    display: flex;
    align-items: center;
    gap: 1.35rem;
    flex-wrap: wrap;
}
.public-profile-avatar {
    width: 84px;
    height: 84px;
    border-radius: 50%;
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    font-weight: 800;
    overflow: hidden;
    flex-shrink: 0;
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.25);
}
.public-profile-name {
    font-size: 1.65rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 0.25rem 0;
}
.public-profile-sub {
    font-size: 0.96rem;
    font-weight: 600;
    color: #475569;
    margin: 0;
}
.public-profile-bio-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    padding: 1.25rem;
}
.public-profile-bio-label {
    display: block;
    font-size: 0.78rem;
    font-weight: 800;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    margin-bottom: 0.45rem;
}
.public-profile-bio-text {
    font-size: 0.96rem;
    line-height: 1.65;
    color: #334155;
    margin: 0;
}
@media (max-width: 640px) {
    .public-profile-wrapper {
        padding: 0 1rem;
    }
    .public-profile-card {
        padding: 1.35rem;
        border-radius: 24px;
    }
    .public-profile-name {
        font-size: 1.35rem;
    }
}
</style>

<div class="dash-page">
    <header class="dash-navbar">
        <div class="dash-nav-container">
            <a href="<?= url('?page=mahasiswa-dashboard') ?>" class="dash-logo-link" aria-label="Dashboard Qubu">
                <svg class="dash-logo-svg" viewBox="0 0 160 55" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="16" cy="11" r="5.5" fill="#38bdf8" />
                    <circle cx="28" cy="11" r="5.5" fill="#c084fc" />
                    <path d="M 10 24 C 7 32, 14 42, 22 42 C 30 42, 36 34, 34 25 C 32 17, 23 17, 18 20 C 13 23, 11 31, 14 36"
                          stroke="#38bdf8" stroke-width="4.5" stroke-linecap="round" fill="none" />
                    <path d="M 22 42 C 26 42, 33 40, 36 34 C 38 29, 36 22, 30 20"
                          stroke="#c084fc" stroke-width="4.5" stroke-linecap="round" fill="none" />
                    <path d="M 28 35 L 36 43"
                          stroke="#c084fc" stroke-width="4.5" stroke-linecap="round" />
                    <text x="48" y="36" font-family="'Plus Jakarta Sans', system-ui, sans-serif" font-size="28" font-weight="900" fill="#ffffff" letter-spacing="-0.03em">QUBU</text>
                </svg>
            </a>

            <nav class="dash-nav-menu" aria-label="Menu Utama">
                <a href="<?= url('?page=mahasiswa-dashboard') ?>" class="dash-nav-link">Info Lomba</a>
                <a href="<?= url('?page=cari-partner') ?>" class="dash-nav-link is-active">Cari Partner</a>
                <a href="<?= url('?page=cari-dosen') ?>" class="dash-nav-link" id="link-dosen-pembimbing">Dosen Pembimbing</a>
                <a href="<?= e(url('?page=mahasiswa-profile')) ?>" class="dash-avatar-btn" title="<?= e($namaMahasiswa ?? 'Mahasiswa') ?>">
                    <?php if (!empty($userFoto) && file_exists(__DIR__ . '/../../' . $userFoto)): ?>
                        <img src="<?= e(url($userFoto)) ?>" alt="Avatar" class="dash-avatar-img">
                    <?php else: ?>
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    <?php endif; ?>
                </a>
            </nav>
        </div>
    </header>

    <main class="public-profile-wrapper">
        <div style="margin-bottom: 1.35rem;">
            <a href="<?= url('?page=cari-partner') ?>" class="btn-reset-inline" style="display: inline-flex; align-items: center; gap: 0.45rem; text-decoration: none; padding: 0.55rem 1.15rem;">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Kembali ke Cari Partner</span>
            </a>
        </div>

        <?php if ($isNotFound || empty($kandidat)): ?>
            <section class="public-profile-card" style="text-align: center; align-items: center; padding: 3rem 1.75rem;" role="alert">
                <svg width="58" height="58" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <h1 style="font-size: 1.55rem; font-weight: 800; color: #0f172a; margin: 0;">404 — Profil Kandidat Tidak Ditemukan</h1>
                <p style="font-size: 0.96rem; color: #64748b; max-width: 460px; margin: 0;">
                    Maaf, profil mahasiswa yang Anda cari tidak tersedia atau parameter ID tidak valid.
                </p>
                <a href="<?= url('?page=cari-partner') ?>" class="btn-modal-primary" style="text-decoration: none;">
                    <span>Kembali ke Daftar Partner</span>
                </a>
            </section>
        <?php else:
            $fotoRel = trim((string) ($kandidat['foto'] ?? ''));
            $hasFoto = $fotoRel !== '' && file_exists(__DIR__ . '/../../' . $fotoRel);
            $bidangTags = !empty($kandidat['bidang_tags']) && is_array($kandidat['bidang_tags'])
                ? $kandidat['bidang_tags']
                : ['Frontend'];
        ?>
            <article class="public-profile-card">
                <div class="public-profile-top">
                    <div class="public-profile-avatar">
                        <?php if ($hasFoto): ?>
                            <img src="<?= e(url($fotoRel)) ?>" alt="<?= e($kandidat['nama_mahasiswa']) ?>" class="pcard-avatar-img">
                        <?php else: ?>
                            <span><?= e($kandidat['inisial'] ?? 'QB') ?></span>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h1 class="public-profile-name"><?= e($kandidat['nama_mahasiswa']) ?></h1>
                        <p class="public-profile-sub">
                            <?= e($kandidat['program_studi'] ?? 'Informatika') ?> &bull; Angkatan <?= e($kandidat['angkatan'] ?? '2025') ?>
                        </p>
                    </div>
                </div>

                <div class="lomba-meta-table">
                    <div class="lomba-meta-row">
                        <span class="meta-label">NPM</span>
                        <span class="badge-pill badge-blue"><?= e($kandidat['npm'] ?? '-') ?></span>
                    </div>
                    <div class="lomba-meta-row">
                        <span class="meta-label">Program Studi</span>
                        <span class="badge-pill badge-purple"><?= e($kandidat['program_studi'] ?? 'Informatika') ?></span>
                    </div>
                    <div class="lomba-meta-row">
                        <span class="meta-label">Angkatan</span>
                        <span class="badge-pill badge-green"><?= e($kandidat['angkatan'] ?? '2025') ?></span>
                    </div>
                    <div class="lomba-meta-row">
                        <span class="meta-label">Bidang Keahlian</span>
                        <div class="pcard-skills-list" style="justify-content: flex-end;">
                            <?php foreach ($bidangTags as $tag): ?>
                                <span class="pcard-skill-pill"><?= e($tag) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="public-profile-bio-box">
                    <span class="public-profile-bio-label">Tentang / Bio Kolaborasi</span>
                    <p class="public-profile-bio-text">
                        <?= e(!empty($kandidat['bio']) ? $kandidat['bio'] : 'Mahasiswa Fasilkom yang antusias berkolaborasi dalam kompetisi akademik, teknologi, dan pengembangan inovasi lintas program studi.') ?>
                    </p>
                </div>

                <div style="display: flex; flex-wrap: wrap; gap: 0.85rem; justify-content: flex-end;">
                    <button
                        type="button"
                        class="btn-pcard-green"
                        style="max-width: 240px; height: 42px; font-size: 0.9rem;"
                        onclick="showPartnerPlaceholderNotice('Fitur Ajak ke Tim akan diaktifkan pada modul Manajemen Tim.')"
                    >
                        Ajak ke Tim
                    </button>
                </div>
            </article>
        <?php endif; ?>
    </main>
</div>

<div class="partner-toast" id="partner-toast" role="status" aria-live="polite">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="12" y1="16" x2="12" y2="12"></line>
        <line x1="12" y1="8" x2="12.01" y2="8"></line>
    </svg>
    <span id="partner-toast-text">Fitur Ajak ke Tim akan diaktifkan pada modul Manajemen Tim.</span>
</div>

<script src="<?= url('public/js/cari-partner.js') ?>" defer></script>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>

