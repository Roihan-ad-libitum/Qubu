<?php

$title = $title ?? 'Detail Perlombaan - ' . APP_NAME;
$extraCss = $extraCss ?? ['public/css/components.css', 'public/css/dashboard.css'];
$activeStudentNavKey = 'dashboard';

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
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 28px rgba(15, 23, 42, 0.14);
}
.detail-poster-box .lomba-card-placeholder {
    aspect-ratio: 3 / 4;
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
@media (max-width: 640px) {
    .detail-page-wrapper {
        padding: 0 1rem;
        margin: 1.25rem auto 3rem;
    }
    .detail-main-card {
        padding: 1.25rem;
        border-radius: 28px;
    }
    .detail-title {
        font-size: 1.32rem;
    }
    .detail-cta-row {
        flex-direction: column;
        align-items: stretch;
    }
    .detail-cta-row .btn-modal-primary,
    .detail-cta-row .btn-modal-secondary,
    .detail-cta-row .btn-share-link {
        width: 100%;
        justify-content: center;
        box-sizing: border-box;
    }
}
</style>

<div class="dash-page team-flow-page">
    <?php require __DIR__ . '/../components/student-navbar.php'; ?>

    <section class="dash-hero-section team-flow-hero dash-hero-compact">
        <div class="dash-hero-noise"></div>
        <div class="dash-hero-content team-flow-hero-inner">
            <h1 class="dash-hero-greeting" style="font-size: clamp(1.75rem, 3vw, 2.35rem);">Detail Informasi Perlombaan</h1>
            <p class="team-flow-hero-description">Pelajari ketentuan lomba, daftar melalui tautan resmi, atau temukan rekan tim untuk berkompetisi bersama.</p>
        </div>
    </section>

    <main class="detail-page-wrapper" style="margin-top: 1.5rem;">
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

            <article class="detail-main-card">
                <div class="detail-hero-grid">

                    <div class="detail-poster-box">
                        <?php if (!empty($lomba['poster']) && file_exists(__DIR__ . '/../../' . $lomba['poster'])): ?>
                            <img src="<?= e(url($lomba['poster'])) ?>" alt="<?= e($lomba['nama_lomba']) ?>" class="detail-poster-img">
                        <?php else: ?>
                            <div class="lomba-card-placeholder" style="width: 100%; height: 100%;">
                                <div class="placeholder-glow"></div>
                                <span class="placeholder-cat"><?= e($lomba['bidang']) ?></span>
                                <h4 class="placeholder-title"><?= e($lomba['nama_lomba']) ?></h4>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="detail-info-col">
                        <?php $detailBidangTags = parseSkillTags((string) ($lomba['bidang'] ?? ''), false, ['UI/UX Design']); ?>
                        <div class="detail-badges-top">
                            <?php foreach ($detailBidangTags as $bTag): ?>
                                <span class="badge-pill badge-orange"><?= e($bTag) ?></span>
                            <?php endforeach; ?>
                            <span class="badge-pill badge-purple"><?= e($lomba['tingkat']) ?></span>
                        </div>

                        <div>
                            <h1 class="detail-title"><?= e($lomba['nama_lomba']) ?></h1>
                            <p class="detail-organizer">Diselenggarakan oleh: <strong><?= e($lomba['penyelenggara']) ?></strong></p>
                        </div>

                        <div class="lomba-meta-table">
                            <div class="lomba-meta-row">
                                <span class="meta-label">Deadline Pendaftaran</span>
                                <span class="badge-pill badge-red"><?= e($lomba['deadline_formatted']) ?></span>
                                <?php if (isLombaRegistrationClosed($lomba['deadline_daftar'] ?? null)): ?>
                                    <span class="badge-pill badge-red">Pendaftaran Ditutup</span>
                                <?php endif; ?>
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

                        <?php
                        $safeLinkResmi = function_exists('safeExternalUrl') ? safeExternalUrl($lomba['link_resmi'] ?? null) : (string) ($lomba['link_resmi'] ?? '');
                        ?>
                        <div class="detail-cta-row">
                            <?php if ($safeLinkResmi !== '' && !isLombaRegistrationClosed($lomba['deadline_daftar'] ?? null)): ?>
                                <a href="<?= e($safeLinkResmi) ?>" target="_blank" rel="noopener noreferrer" class="btn-modal-primary">
                                    <span>Daftar Sekarang (Link Resmi)</span>
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                        <polyline points="15 3 21 3 21 9"></polyline>
                                        <line x1="10" y1="14" x2="21" y2="3"></line>
                                    </svg>
                                </a>
                            <?php endif; ?>

                            <?php if (!isLombaRegistrationClosed($lomba['deadline_daftar'] ?? null) && in_array($lomba['partisipasi'] ?? '', ['Tim', 'Individu/Tim'], true)): ?>
                                <a href="<?= e(url('?page=team-posts&id_lomba=' . (int) ($lomba['id_lomba'] ?? 0))) ?>" class="btn-modal-secondary" id="btn-detail-cari-tim">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="9" cy="7" r="4"></circle>
                                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                    </svg>
                                    <span>Cari Tim</span>
                                </a>
                            <?php endif; ?>

                            <?php if ($safeLinkResmi === '' && !isLombaRegistrationClosed($lomba['deadline_daftar'] ?? null)): ?>
                                <div class="modal-no-links-notice" id="detail-no-links-notice">
                                    Tautan pendaftaran resmi belum tersedia dari penyelenggara.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="detail-desc-section">
                    <h2 class="detail-desc-heading">Deskripsi &amp; Ketentuan Perlombaan</h2>
                    <div class="detail-desc-body"><?= e($lomba['deskripsi'] ?: 'Belum ada rincian deskripsi tambahan untuk perlombaan ini.') ?></div>
                </div>
            </article>

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

<?php
require __DIR__ . '/../layouts/footer.php';
?>
