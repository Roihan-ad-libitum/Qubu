<?php

if (!function_exists('renderCardIdentityHeader')) {

    function renderCardIdentityHeader(
        string $name,
        string $subtitle,
        ?string $fotoRel = null,
        ?string $profileUrl = null,
        string $fallbackInitials = 'QB',
        string $extraStyle = ''
    ): void {
        $photoUrl = resolveProfilePhotoUrl($fotoRel);
        $hasFoto  = $photoUrl !== null;
        $initials = buildInitials($name, $fallbackInitials);
        ?>
        <div class="pcard-header" <?= $extraStyle !== '' ? 'style="' . e($extraStyle) . '"' : '' ?>>
            <div class="pcard-avatar" aria-hidden="true">
                <?php if ($hasFoto): ?>
                    <img src="<?= e($photoUrl) ?>" alt="Foto <?= e($name) ?>" class="pcard-avatar-img" loading="lazy" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='inline';">
                    <span class="pcard-avatar-initials" style="display: none;"><?= e($initials) ?></span>
                <?php else: ?>
                    <span class="pcard-avatar-initials"><?= e($initials) ?></span>
                <?php endif; ?>
            </div>
            <div class="pcard-identity">
                <h3 class="pcard-name">
                    <?php if ($profileUrl !== null && $profileUrl !== ''): ?>
                        <a href="<?= e($profileUrl) ?>" style="color: inherit; text-decoration: none;"><?= e($name) ?></a>
                    <?php else: ?>
                        <?= e($name) ?>
                    <?php endif; ?>
                </h3>
                <p class="pcard-sub"><?= e($subtitle) ?></p>
            </div>
        </div>
        <?php
    }
}

if (!function_exists('renderCardSkillsSection')) {

    function renderCardSkillsSection(string $label, array $tags): void
    {
        ?>
        <div class="pcard-skills-section">
            <span class="pcard-skills-label"><?= e($label) ?></span>
            <div class="pcard-skills-list">
                <?php foreach ($tags as $tag): ?>
                    <span class="pcard-skill-pill"><?= e((string) $tag) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
}

if (!function_exists('renderPartnerCard')) {

    function renderPartnerCard(array $mhs, array $options = []): void
    {
        $extraClass  = trim((string) ($options['extraClass'] ?? ''));
        $isHidden    = !empty($options['hidden']);
        $targetLomba = $options['targetLomba'] ?? null;

        $idMahasiswa = (int) ($mhs['id_mahasiswa'] ?? 0);
        $idUser      = (int) ($mhs['id_user'] ?? 0);
        $nama        = (string) ($mhs['nama_mahasiswa'] ?? 'Mahasiswa Qubu');
        $npm         = (string) ($mhs['npm'] ?? '');
        $prodi       = (string) ($mhs['program_studi'] ?? 'Informatika');
        $angkatan    = (string) ($mhs['angkatan'] ?? '2025');
        $bidangMinat = (string) ($mhs['bidang_minat'] ?? 'Frontend');
        $bio         = trim((string) ($mhs['bio'] ?? ''));
        $fotoRel     = trim((string) ($mhs['foto'] ?? ''));

        $bidangTags = !empty($mhs['bidang_tags']) && is_array($mhs['bidang_tags'])
            ? $mhs['bidang_tags']
            : parseSkillTags($bidangMinat, false, ['Frontend']);

        $profilUrl = $mhs['profil_url']
            ?? (function_exists('url') ? url('?page=lihat-profil&id=' . $idMahasiswa) : '?page=lihat-profil&id=' . $idMahasiswa);

        $targetLombaNama = $targetLomba['nama_lomba'] ?? ($mhs['target_lomba_nama'] ?? null);
        $bioRingkas = $bio !== ''
            ? $bio
            : 'Siap berkolaborasi dalam tim kompetisi sesuai bidang peminatan dan keahlian.';

        $waHref = buildWhatsAppUrl(
            (string) ($mhs['no_hp'] ?? ''),
            'Halo ' . $nama . ', saya melihat profil Anda di Qubu dan ingin mengajak berdiskusi untuk kolaborasi tim lomba.'
        );
        $emailHref    = buildMailtoUrl((string) ($mhs['email'] ?? ''), 'Ajakan Kolaborasi Tim Lomba Qubu');
        $contactHref  = $waHref !== '' ? $waHref : $emailHref;
        $contactLabel = $waHref !== '' ? 'Hubungi via WhatsApp' : ($emailHref !== '' ? 'Hubungi via Email' : '');

        $cardClass = 'team-post-card partner-card' . ($extraClass !== '' ? ' ' . $extraClass : '');

        $encodedProfileJson = (string) json_encode(
            $mhs,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        );
        ?>
        <article
            class="<?= e($cardClass) ?>"
            data-id="<?= $idMahasiswa ?>"
            data-user-id="<?= $idUser ?>"
            data-name="<?= e(strtolower($nama)) ?>"
            data-npm="<?= e(strtolower($npm)) ?>"
            data-prodi="<?= e(strtolower($prodi)) ?>"
            data-bidang="<?= e(strtolower($bidangMinat)) ?>"
            data-bio="<?= e(strtolower($bio)) ?>"
            <?= $isHidden ? 'style="display: none;"' : '' ?>
        >
            <div class="team-post-topline">
                <span class="team-match-pill"><?= e($prodi) ?></span>
            </div>

            <?php renderCardIdentityHeader($nama, $prodi . ' • Angkatan ' . $angkatan, $fotoRel, $profilUrl, 'QB'); ?>

            <?php if (!empty($targetLombaNama)): ?>
                <div class="team-post-field">
                    <span>Konteks Perlombaan</span>
                    <strong><?= e((string) $targetLombaNama) ?></strong>
                </div>
            <?php endif; ?>

            <?php renderCardSkillsSection('Bidang Peminatan & Keahlian:', $bidangTags); ?>

            <p class="team-post-description"><?= e($bioRingkas) ?></p>

            <div class="team-post-actions">
                <button
                    type="button"
                    class="team-secondary-button"
                    data-profile="<?= e($encodedProfileJson) ?>"
                    onclick="openPartnerProfileModal(<?= htmlspecialchars($encodedProfileJson, ENT_QUOTES, 'UTF-8') ?>)"
                >
                    Lihat Profil
                </button>
                <?php if ($contactHref !== ''): ?>
                    <a
                        href="<?= e($contactHref) ?>"
                        class="team-primary-button"
                        <?= str_starts_with($contactHref, 'https:') ? 'target="_blank" rel="noopener noreferrer"' : '' ?>
                    >
                        <?= e($contactLabel) ?>
                    </a>
                <?php else: ?>
                    <a href="<?= e($profilUrl) ?>" class="team-primary-button">
                        Buka Profil Lengkap
                    </a>
                <?php endif; ?>
            </div>
        </article>
        <?php
    }
}

if (!function_exists('renderAdvisorCard')) {

    function renderAdvisorCard(array $advisor, array $options = []): void
    {
        $extraClass = trim((string) ($options['extraClass'] ?? ''));
        $isHidden   = !empty($options['hidden']);

        $idDosen        = (int) ($advisor['id_dosen'] ?? 0);
        $idUser         = (int) ($advisor['id_user'] ?? 0);
        $namaDosen      = (string) ($advisor['nama_dosen'] ?? 'Dosen Pembimbing');
        $username       = (string) ($advisor['username'] ?? 'dosen');
        $nidn           = trim((string) ($advisor['nidn'] ?? ''));
        $bidangKeahlian = (string) ($advisor['bidang_keahlian'] ?? '');
        $available      = ($advisor['status'] ?? '') === 'bersedia';
        $canContact     = $available && !empty($advisor['contact_visible']);
        $fotoRel        = trim((string) ($advisor['foto'] ?? ''));

        $skillTags = parseSkillTags($bidangKeahlian, false, ['Umum']);

        $waHref = buildWhatsAppUrl(
            (string) ($advisor['no_hp'] ?? ''),
            'Selamat pagi/siang Bapak/Ibu ' . $namaDosen . ', perkenalkan saya mahasiswa Fasilkom melalui platform Qubu yang ingin berkonsultasi mengenai kesediaan Bapak/Ibu sebagai Dosen Pembimbing kompetisi.'
        );
        $emailHref = buildMailtoUrl(
            (string) ($advisor['email'] ?? ''),
            'Permohonan Bimbingan Kompetisi Mahasiswa via Qubu'
        );

        $subtitle  = '@' . $username . ($nidn !== '' ? ' • NIDN ' . $nidn : '');
        $cardClass = 'team-post-card partner-card advisor-card' . ($extraClass !== '' ? ' ' . $extraClass : '');
        ?>
        <article
            class="<?= e($cardClass) ?>"
            data-id="<?= $idDosen ?>"
            data-user-id="<?= $idUser ?>"
            data-name="<?= e(strtolower($namaDosen)) ?>"
            data-username="<?= e(strtolower($username)) ?>"
            data-nidn="<?= e(strtolower($nidn)) ?>"
            data-bidang="<?= e(strtolower($bidangKeahlian)) ?>"
            <?= $isHidden ? 'style="display: none;"' : '' ?>
        >
            <div class="team-post-topline">
                <span class="team-status-pill <?= $available ? 'is-open' : 'is-complete' ?>">
                    <?= $available ? 'Bersedia Membimbing' : 'Tidak Bersedia' ?>
                </span>
                <span class="team-match-pill">Dosen Fasilkom</span>
            </div>

            <?php renderCardIdentityHeader($namaDosen, $subtitle, $fotoRel, null, 'DS'); ?>

            <?php renderCardSkillsSection('Bidang Keahlian & Fokus Riset:', $skillTags); ?>

            <?php if (!$available): ?>
                <p class="advisor-contact-message">Dosen sedang tidak menerima bimbingan tim baru saat ini.</p>
            <?php elseif (!$canContact): ?>
                <p class="advisor-contact-message">Dosen belum mengaktifkan tampilan kontak langsung pada direktori.</p>
            <?php else: ?>
                <div class="team-post-actions">
                    <?php if ($emailHref !== ''): ?>
                        <a class="team-secondary-button" href="<?= e($emailHref) ?>">
                            Hubungi via Email
                        </a>
                    <?php endif; ?>
                    <?php if ($waHref !== ''): ?>
                        <a class="team-primary-button" href="<?= e($waHref) ?>" target="_blank" rel="noopener noreferrer">
                            Hubungi via WhatsApp
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </article>
        <?php
    }
}

if (!function_exists('renderTeamPostCard')) {

    function renderTeamPostCard(array $post, int $currentUserId = 0, array $options = []): void
    {
        $extraClass   = trim((string) ($options['extraClass'] ?? ''));
        $isHidden     = !empty($options['hidden']);
        $links        = resolveTeamPostContactLinks($post);
        $creatorName  = (string) (($post['nama_mahasiswa'] ?? '') ?: ($post['username'] ?? 'Mahasiswa Qubu'));
        $creatorFoto  = trim((string) ($post['foto'] ?? ''));
        $creatorIdMhs = (int) ($post['id_mahasiswa'] ?? 0);
        $idLomba      = (int) ($post['id_lomba'] ?? 0);
        $namaLomba    = (string) ($post['nama_lomba'] ?? '');
        $bidangStr    = (string) ($post['bidang_dibutuhkan'] ?? '');
        $profileUrl   = $creatorIdMhs > 0 ? url('?page=lihat-profil&id=' . $creatorIdMhs) : null;
        $neededTags   = parseSkillTags($bidangStr, false, []);
        $isMyPost     = (int) ($post['id_user'] ?? 0) === $currentUserId && $currentUserId > 0;
        $subtitle     = (string) (($post['program_studi'] ?? '') ?: 'Informatika')
            . (!empty($post['angkatan']) ? ' • Angkatan ' . $post['angkatan'] : '');
        $cardClass    = 'partner-card team-post-card' . ($extraClass !== '' ? ' ' . $extraClass : '');
        ?>
        <article
            class="<?= e($cardClass) ?>"
            data-id="<?= (int) ($post['id_post'] ?? 0) ?>"
            data-lomba="<?= $idLomba ?>"
            data-lomba-name="<?= e($namaLomba) ?>"
            data-bidang="<?= e(strtolower($bidangStr)) ?>"
            <?= $isHidden ? 'style="display: none;"' : '' ?>
        >
            <div class="team-post-topline">
                <span class="team-status-pill is-open">Open</span>
                <?php if ((int) ($post['match_score'] ?? 0) > 0): ?>
                    <span class="team-match-pill">Bidang Cocok</span>
                <?php endif; ?>
                <?php if ($isMyPost): ?>
                    <span class="badge-pill badge-blue">Posting Anda</span>
                <?php endif; ?>
            </div>

            <?php renderCardIdentityHeader($creatorName, $subtitle, $creatorFoto, $profileUrl, 'QB'); ?>

            <div class="pcard-target-box">
                <span class="pcard-target-label">Target Perlombaan:</span>
                <strong class="pcard-target-title">
                    <a href="<?= e(url('?page=detail-lomba&id=' . $idLomba)) ?>" style="color: inherit; text-decoration: none;">
                        <?= e($namaLomba) ?>
                    </a>
                </strong>
                <span style="display:block; font-size:0.78rem; color:#475569; margin-top:2px;">
                    Membutuhkan <strong><?= (int) ($post['jumlah_anggota'] ?? 1) ?> anggota tambahan</strong>
                </span>
            </div>

            <?php renderCardSkillsSection('Bidang yang Dibutuhkan:', $neededTags); ?>

            <p class="team-post-description"><?= nl2br(e((string) ($post['deskripsi'] ?? ''))) ?></p>

            <div class="team-post-actions">
                <?php if ($profileUrl): ?>
                    <a class="team-secondary-button" href="<?= e($profileUrl) ?>">Lihat Profil</a>
                <?php endif; ?>
                <?php foreach ($links as $link): ?>
                    <a
                        class="<?= $link['type'] === 'whatsapp' ? 'team-primary-button' : 'team-secondary-button' ?>"
                        href="<?= e($link['href']) ?>"
                        <?= str_starts_with($link['href'], 'https:') ? 'target="_blank" rel="noopener noreferrer"' : '' ?>
                    >
                        <?= e($link['label']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </article>
        <?php
    }
}
