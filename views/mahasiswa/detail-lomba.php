<?php

$title               = $title ?? 'Detail Perlombaan - ' . APP_NAME;
$extraCss            = $extraCss ?? ['public/css/components.css', 'public/css/dashboard.css'];
$activeStudentNavKey = 'dashboard';

require_once __DIR__ . '/../components/lomba-card.php';
require __DIR__ . '/../layouts/headers.php';
?>

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
                        <?php if (!empty($lomba['poster']) && hasPublicFile($lomba['poster'])): ?>
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
                        $safeLinkResmi = safeExternalUrl($lomba['link_resmi'] ?? null);
                        $isLombaClosed = isLombaRegistrationClosed($lomba['deadline_daftar'] ?? null);
                        ?>
                        <div class="detail-cta-row">
                            <?php if ($safeLinkResmi !== '' && !$isLombaClosed): ?>
                                <a href="<?= e($safeLinkResmi) ?>" target="_blank" rel="noopener noreferrer" class="btn-modal-primary">
                                    <span>Daftar Sekarang (Link Resmi)</span>
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                        <polyline points="15 3 21 3 21 9"></polyline>
                                        <line x1="10" y1="14" x2="21" y2="3"></line>
                                    </svg>
                                </a>
                            <?php endif; ?>

                            <?php if (!$isLombaClosed): ?>
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

                            <?php if ($safeLinkResmi === '' && !$isLombaClosed): ?>
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
