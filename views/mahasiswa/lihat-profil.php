<?php

$title               = $title ?? 'Profil Mahasiswa - ' . APP_NAME;
$extraCss            = ['public/css/components.css', 'public/css/dashboard.css', 'public/css/cari-partner.css', 'public/css/team-flow.css'];
$isNotFound          = $isNotFound ?? empty($kandidat);
$activeStudentNavKey = 'teams';
$studentNavName      = $namaMahasiswa ?? Session::get('nama_lengkap', 'Mahasiswa');
$studentNavPhoto     = $userFoto ?? null;

require_once __DIR__ . '/../layouts/headers.php';
?>

<div class="dash-page team-flow-page">
    <?php require __DIR__ . '/../components/student-navbar.php'; ?>

    <section class="dash-hero-section team-flow-hero dash-hero-compact">
        <div class="dash-hero-noise"></div>
        <div class="dash-hero-content team-flow-hero-inner">
            <h1 class="dash-hero-greeting" style="font-size: clamp(1.75rem, 3vw, 2.35rem);">Profil Kandidat &amp; Kolaborator</h1>
            <p class="team-flow-hero-description">Tinjau biodata akademik, bidang keahlian, dan fokus kolaborasi sebelum menghubungi rekan tim.</p>
        </div>
    </section>

    <main class="public-profile-wrapper" style="margin-top: 1.5rem;">
        <div class="public-profile-back" style="display:flex; gap:0.75rem; flex-wrap:wrap;">
            <a href="<?= url('?page=team-posts') ?>" class="btn-reset-inline public-profile-back-link">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Kembali ke Posting Pencarian Tim</span>
            </a>
            <a href="<?= url('?page=cari-partner') ?>" class="btn-reset-inline public-profile-back-link">
                <span>Direktori Kandidat Partner</span>
            </a>
        </div>

        <?php if ($isNotFound || empty($kandidat)): ?>
            <section class="public-profile-card public-profile-not-found" role="alert">
                <svg width="58" height="58" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <h1>404 — Profil Mahasiswa Tidak Ditemukan</h1>
                <p>Maaf, profil mahasiswa yang Anda cari tidak tersedia atau parameter ID tidak valid.</p>
                <a href="<?= url('?page=team-posts') ?>" class="btn-modal-primary public-profile-back-action">
                    <span>Kembali ke Cari Partner &amp; Tim</span>
                </a>
            </section>
        <?php else:
            $fotoRel    = trim((string) ($kandidat['foto'] ?? ''));
            $photoUrl   = resolveProfilePhotoUrl($fotoRel);
            $hasFoto    = $photoUrl !== null;
            $bidangTags = !empty($kandidat['bidang_tags']) && is_array($kandidat['bidang_tags'])
                ? $kandidat['bidang_tags']
                : parseSkillTags((string) ($kandidat['bidang_minat'] ?? ''), false, ['Frontend']);
            $waUrl = buildWhatsAppUrl(
                (string) ($kandidat['no_hp'] ?? ''),
                'Halo ' . ($kandidat['nama_mahasiswa'] ?? '') . ', saya melihat profil Anda di Qubu dan tertarik berdiskusi mengenai kolaborasi tim lomba.'
            );
            $emailUrl = buildMailtoUrl(
                (string) ($kandidat['email'] ?? ''),
                'Kolaborasi Tim Lomba via Qubu'
            );
            $githubUrl     = normalizeGithubUrl($kandidat['github'] ?? '') ?: '';
            $githubDisplay = formatGithubDisplay($githubUrl);
            $initials      = (string) ($kandidat['inisial'] ?? buildInitials((string) ($kandidat['nama_mahasiswa'] ?? '')));
        ?>
            <article class="public-profile-card">
                <div class="public-profile-top">
                    <div class="public-profile-avatar" aria-hidden="true">
                        <?php if ($hasFoto): ?>
                            <img src="<?= e($photoUrl) ?>" alt="Foto <?= e($kandidat['nama_mahasiswa']) ?>" class="pcard-avatar-img" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='inline';">
                            <span style="display: none;"><?= e($initials) ?></span>
                        <?php else: ?>
                            <span><?= e($initials) ?></span>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h1 class="public-profile-name"><?= e($kandidat['nama_mahasiswa']) ?></h1>
                        <p class="public-profile-sub">
                            @<?= e($kandidat['username'] ?? '-') ?> &bull; <?= e($kandidat['program_studi'] ?? 'Informatika') ?> &bull; Angkatan <?= e($kandidat['angkatan'] ?? '2025') ?>
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
                    <?php if ($githubUrl !== '' && $githubDisplay !== ''): ?>
                        <div class="lomba-meta-row">
                            <span class="meta-label">GitHub</span>
                            <a href="<?= e($githubUrl) ?>" target="_blank" rel="noopener noreferrer" class="badge-pill badge-blue" style="text-decoration: none;">
                                <?= e($githubDisplay) ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    <div class="lomba-meta-row">
                        <span class="meta-label">Bidang Peminatan &amp; Keahlian</span>
                        <div class="pcard-skills-list public-profile-skills">
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

                <?php if ($waUrl !== '' || $emailUrl !== '' || $githubUrl !== ''): ?>
                    <div style="display:flex; flex-wrap:wrap; gap:0.75rem; margin-top:1.25rem;">
                        <?php if ($waUrl !== ''): ?>
                            <a href="<?= e($waUrl) ?>" target="_blank" rel="noopener noreferrer" class="team-primary-button">
                                <span>Hubungi via WhatsApp</span>
                            </a>
                        <?php endif; ?>
                        <?php if ($emailUrl !== ''): ?>
                            <a href="<?= e($emailUrl) ?>" class="team-secondary-button">
                                <span>Hubungi via Email</span>
                            </a>
                        <?php endif; ?>
                        <?php if ($githubUrl !== ''): ?>
                            <a href="<?= e($githubUrl) ?>" target="_blank" rel="noopener noreferrer" class="team-secondary-button">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" style="margin-right: 6px; flex-shrink: 0;">
                                    <path d="M12 0.5C5.37 0.5 0 5.87 0 12.5c0 5.3 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.295 24 17.795 24 12.5 24 5.87 18.63 0.5 12 0.5z"/>
                                </svg>
                                <span>Kunjungi GitHub</span>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($kandidatOpenPosts)): ?>
                    <div style="margin-top: 1.75rem; padding-top: 1.25rem; border-top: 1px solid #e2e8f0;">
                        <h2 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin: 0 0 0.75rem;">
                            Posting Pencarian Tim Aktif (<?= count($kandidatOpenPosts) ?> Open)
                        </h2>
                        <div style="display: grid; gap: 0.75rem;">
                            <?php foreach ($kandidatOpenPosts as $op): ?>
                                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 0.95rem 1.1rem;">
                                    <div style="display:flex; justify-content:space-between; align-items:center; gap:0.5rem; margin-bottom:0.35rem;">
                                        <strong style="color:#0f172a; font-size:0.95rem;"><?= e($op['nama_lomba']) ?></strong>
                                        <span class="team-status-pill is-open">Open</span>
                                    </div>
                                    <p style="margin:0 0 0.35rem; font-size:0.84rem; color:#475569;">
                                        Membutuhkan <strong><?= (int) $op['jumlah_anggota'] ?> anggota</strong> pada bidang: <strong><?= e($op['bidang_dibutuhkan']) ?></strong>
                                    </p>
                                    <p style="margin:0; font-size:0.86rem; color:#334155;"><?= e($op['deskripsi']) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </article>
        <?php endif; ?>
    </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
