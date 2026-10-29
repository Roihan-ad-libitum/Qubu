<?php
/**
 * View Halaman Detail Lomba Mandiri (Shareable Deep-Link: ?page=lomba-detail&id={id})
 * Menampilkan:
 * - Top Navbar Qubu
 * - Hero Detail Lomba (Poster HD di kiri, Identitas & Tabel Kapsul Metadata & Tombol CTA di kanan)
 * - Deskripsi Lengkap Perlombaan di bawah
 * - Section Rekomendasi Lomba Terkait (Related Competitions)
 * - Tampilan 404 Bersahabat jika ID tidak valid atau lomba tidak ditemukan
 */

$title = $title ?? 'Detail Perlombaan - ' . APP_NAME;
$extraCss = $extraCss ?? ['public/css/components.css', 'public/css/dashboard.css'];

require_once __DIR__ . '/../components/lomba-card.php';
require __DIR__ . '/../layouts/headers.php';
?>

<style>
.detail-page-wrapper {
    max-width: 1240px;
    margin: 2rem auto 4rem;
    padding: 0 1.5rem;
}
.detail-breadcrumb {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.btn-back-catalog {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    padding: 0.65rem 1.25rem;
    border-radius: 999px;
    background: #ffffff;
    color: #0f172a;
    font-weight: 700;
    font-size: 0.92rem;
    text-decoration: none;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.07);
    border: 1px solid #e2e8f0;
    transition: all 0.2s ease;
}
.btn-back-catalog:hover {
    background: #0f172a;
    color: #ffffff;
    transform: translateY(-1px);
}
.detail-main-card {
    background: #ffffff;
    border-radius: 36px;
    padding: 2rem;
    box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
    border: 1px solid #eef2f6;
    margin-bottom: 2rem;
}
.detail-hero-grid {
    display: grid;
    grid-template-columns: 380px 1fr;
    gap: 2.25rem;
    align-items: start;
}
.detail-poster-box {
    border-radius: 24px;
    overflow: hidden;
    background: linear-gradient(135deg, #0b192c 0%, #1e3e62 100%);
    aspect-ratio: 3 / 4;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 28px rgba(15, 23, 42, 0.14);
}
.detail-poster-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.detail-info-col {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}
.detail-badges-top {
    display: flex;
    flex-wrap: wrap;
    gap: 0.6rem;
    align-items: center;
}
.detail-title {
    font-size: 2rem;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.22;
    margin: 0;
}
.detail-organizer {
    font-size: 1rem;
    color: #475569;
    font-weight: 600;
    margin: 0;
}
.detail-cta-row {
    display: flex;
    flex-wrap: wrap;
    gap: 0.85rem;
    margin-top: 0.5rem;
}
.btn-share-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.85rem 1.35rem;
    border-radius: 14px;
    border: 1.5px solid #cbd5e1;
    background: #f8fafc;
    color: #1e293b;
    font-weight: 700;
    font-size: 0.92rem;
    cursor: pointer;
    transition: all 0.2s ease;
}
.btn-share-link:hover {
    background: #e2e8f0;
}
.detail-desc-section {
    margin-top: 2rem;
    padding-top: 1.75rem;
    border-top: 1.5px solid #e2e8f0;
}
.detail-desc-heading {
    font-size: 1.2rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 0.85rem;
}
.detail-desc-body {
    font-size: 1rem;
    line-height: 1.75;
    color: #334155;
    white-space: pre-line;
}
.detail-404-card {
    background: #ffffff;
    border-radius: 36px;
    padding: 3.5rem 2rem;
    text-align: center;
    max-width: 640px;
    margin: 3rem auto;
    box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
    border: 1px solid #e2e8f0;
}
.detail-404-title {
    font-size: 1.75rem;
    font-weight: 800;
    color: #0f172a;
    margin: 1rem 0 0.5rem;
}
.detail-404-desc {
    color: #64748b;
    font-size: 1rem;
    margin-bottom: 1.75rem;
}
@media (max-width: 900px) {
    .detail-hero-grid {
        grid-template-columns: 1fr;
    }
    .detail-poster-box {
        max-width: 360px;
        margin: 0 auto;
        width: 100%;
    }
    .detail-title {
        font-size: 1.55rem;
    }
}
</style>

<div class="dash-page">
    <!-- TOP NAVBAR -->
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
                <a href="<?= url('?page=mahasiswa-dashboard') ?>" class="dash-nav-link is-active">Info Lomba</a>
                <a href="<?= url('?page=cari-partner') ?>" class="dash-nav-link">Cari Partner</a>
                <a href="<?= url('?page=dosen-profile') ?>" class="dash-nav-link">Dosen Pembimbing</a>
                <?php if (Session::has('id_user')): ?>
                    <a href="<?= url('?page=mahasiswa-profile') ?>" class="dash-avatar-btn" title="<?= e($namaMahasiswa) ?>">
                        <?php if (!empty($userFoto) && file_exists(__DIR__ . '/../../' . $userFoto)): ?>
                            <img src="<?= url($userFoto) ?>" alt="Avatar" class="dash-avatar-img">
                        <?php else: ?>
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        <?php endif; ?>
                    </a>
                <?php else: ?>
                    <a href="<?= url('?page=login') ?>" class="dash-nav-link">Masuk</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="detail-page-wrapper">
        <div class="detail-breadcrumb">
            <a href="<?= url('?page=mahasiswa-dashboard') ?>" class="btn-back-catalog">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Kembali ke Katalog Lomba</span>
            </a>
        </div>

        <?php if (!empty($isNotFound) || empty($lomba)): ?>
            <!-- STATE 404: LOMBA TIDAK DITEMUKAN -->
            <section class="detail-404-card" role="alert">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <h1 class="detail-404-title">404 — Perlombaan Tidak Ditemukan</h1>
                <p class="detail-404-desc">
                    Maaf, perlombaan yang Anda cari tidak tersedia, telah berakhir, atau parameter ID tidak valid.
                </p>
                <a href="<?= url('?page=mahasiswa-dashboard') ?>" class="btn-modal-primary" style="display: inline-flex; text-decoration: none;">
                    <span>Jelajahi Semua Lomba di Qubu</span>
                </a>
            </section>
        <?php else: ?>
            <!-- KARTU UTAMA DETAIL PERLOMBAAN -->
            <article class="detail-main-card">
                <div class="detail-hero-grid">
                    <!-- Kolom Kiri: Poster Identitas Lomba -->
                    <div class="detail-poster-box">
                        <?php if (!empty($lomba['poster']) && file_exists(__DIR__ . '/../../' . $lomba['poster'])): ?>
                            <img src="<?= url($lomba['poster']) ?>" alt="<?= e($lomba['nama_lomba']) ?>" class="detail-poster-img">
                        <?php else: ?>
                            <div class="lomba-card-placeholder" style="width: 100%; height: 100%;">
                                <div class="placeholder-glow"></div>
                                <span class="placeholder-cat"><?= e($lomba['bidang']) ?></span>
                                <h4 class="placeholder-title"><?= e($lomba['nama_lomba']) ?></h4>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Kolom Kanan: Judul, Penyelenggara, Tabel Kapsul Ketentuan & Kategori, dan Tombol Aksi -->
                    <div class="detail-info-col">
                        <div class="detail-badges-top">
                            <span class="badge-pill badge-orange"><?= e($lomba['bidang']) ?></span>
                            <span class="badge-pill badge-purple"><?= e($lomba['tingkat']) ?></span>
                        </div>

                        <div>
                            <h1 class="detail-title"><?= e($lomba['nama_lomba']) ?></h1>
                            <p class="detail-organizer">Diselenggarakan oleh: <strong><?= e($lomba['penyelenggara']) ?></strong></p>
                        </div>

                        <!-- Wadah Abu-abu Kapsul Ketentuan & Kategori (Konsisten Desain Qubu) -->
                        <div class="lomba-meta-table">
                            <div class="lomba-meta-row">
                                <span class="meta-label">Deadline Pendaftaran</span>
                                <span class="badge-pill badge-red"><?= e($lomba['deadline_formatted']) ?></span>
                            </div>
                            <div class="lomba-meta-row">
                                <span class="meta-label">Tanggal Pelaksanaan</span>
                                <span class="badge-pill badge-blue"><?= e($lomba['tanggal_pelaksanaan_formatted']) ?></span>
                            </div>
                            <div class="lomba-meta-row">
                                <span class="meta-label">Biaya Pendaftaran</span>
                                <span class="badge-pill badge-green"><?= e($lomba['biaya_formatted']) ?></span>
                            </div>
                            <div class="lomba-meta-row">
                                <span class="meta-label">Kategori Partisipasi</span>
                                <span class="badge-pill badge-blue"><?= e($lomba['partisipasi_formatted']) ?></span>
                            </div>
                            <div class="lomba-meta-row">
                                <span class="meta-label">Bidang &amp; Tingkat</span>
                                <span class="badge-pill badge-orange"><?= e($lomba['bidang']) ?> &bull; <?= e($lomba['tingkat']) ?></span>
                            </div>
                        </div>

                        <!-- Tombol Aksi (CTA) -->
                        <div class="detail-cta-row">
                            <?php if (!empty($lomba['link_resmi'])): ?>
                                <a href="<?= e($lomba['link_resmi']) ?>" target="_blank" rel="noopener noreferrer" class="btn-modal-primary">
                                    <span>Daftar Sekarang (Link Resmi)</span>
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                        <polyline points="15 3 21 3 21 9"></polyline>
                                        <line x1="10" y1="14" x2="21" y2="3"></line>
                                    </svg>
                                </a>
                            <?php endif; ?>

                            <?php if (!empty($lomba['link_guidebook'])): ?>
                                <a href="<?= e($lomba['link_guidebook']) ?>" target="_blank" rel="noopener noreferrer" class="btn-modal-secondary">
                                    <span>Unduh Guidebook</span>
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="7 10 12 15 17 10"></polyline>
                                        <line x1="12" y1="15" x2="12" y2="3"></line>
                                    </svg>
                                </a>
                            <?php endif; ?>

                            <button type="button" class="btn-share-link" id="btn-copy-detail-link" onclick="copyShareableLink('<?= e($lomba['detail_url']) ?>')">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                                    <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                                </svg>
                                <span id="copy-link-label">Salin Link Lomba</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Bagian Bawah: Deskripsi Lengkap Perlombaan -->
                <div class="detail-desc-section">
                    <h2 class="detail-desc-heading">Deskripsi &amp; Ketentuan Perlombaan</h2>
                    <div class="detail-desc-body"><?= e($lomba['deskripsi'] ?: 'Belum ada rincian deskripsi tambahan untuk perlombaan ini.') ?></div>
                </div>
            </article>

            <!-- SECTION REKOMENDASI LOMBA TERKAIT (RELATED COMPETITIONS) -->
            <?php if (!empty($relatedLomba)): ?>
                <section class="dash-section" id="section-related-lomba">
                    <div class="dash-section-header">
                        <div>
                            <h2 class="dash-section-title">Lomba Terkait Lainnya</h2>
                            <p class="dash-section-desc">Rekomendasi kompetisi sejenis yang mungkin menarik untukmu.</p>
                        </div>
                    </div>

                    <div class="semua-lomba-grid">
                        <?php foreach ($relatedLomba as $rel): ?>
                            <?php renderLombaCard($rel, ['mode' => 'link']); ?>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        <?php endif; ?>
    </main>
</div>

<script>
function copyShareableLink(linkUrl) {
    const targetUrl = linkUrl || window.location.href;
    const labelEl = document.getElementById('copy-link-label');
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(targetUrl).then(() => {
            if (labelEl) {
                labelEl.textContent = 'Link Tersalin!';
                setTimeout(() => { labelEl.textContent = 'Salin Link Lomba'; }, 2000);
            }
        });
    }
}
</script>

<?php
require __DIR__ . '/../layouts/footer.php';
?>
