<?php

if (!function_exists('renderLombaCard')) {

    function renderLombaCard(array $item, array $options = []): void
    {
        $extraClass    = trim((string) ($options['extraClass'] ?? ''));
        $mode          = $options['mode'] ?? 'modal';
        $isHidden      = !empty($options['hidden']);
        $idLomba       = (int) ($item['id_lomba'] ?? 0);
        $namaLomba     = (string) ($item['nama_lomba'] ?? '-');
        $penyelenggara = (string) ($item['penyelenggara'] ?? '-');
        $deskripsi     = (string) ($item['deskripsi'] ?? '');
        $bidang        = (string) ($item['bidang'] ?? 'UI/UX Design');
        $bidangTags    = parseSkillTags($bidang, false, ['UI/UX Design']);
        $tingkat       = (string) ($item['tingkat'] ?? 'Nasional');
        $biaya         = (float) ($item['biaya'] ?? 0);
        $posterRel     = trim((string) ($item['poster'] ?? ''));
        $isClosed      = isLombaRegistrationClosed($item['deadline_daftar'] ?? null);

        $deadlineFormatted = $item['deadline_formatted']
            ?? formatLombaTanggalIndo($item['deadline_daftar'] ?? null);
        $biayaFormatted = $item['biaya_formatted']
            ?? formatLombaBiaya($biaya);
        $partisipasiFormatted = $item['partisipasi_formatted']
            ?? formatLombaPartisipasi($item['partisipasi'] ?? 'Individu/Tim');

        $hasPosterFile = hasPublicFile($posterRel);
        $detailUrl = $item['detail_url']
            ?? (function_exists('url') ? url('?page=lomba-detail&id=' . $idLomba) : '?page=lomba-detail&id=' . $idLomba);

        $cardClass = 'lomba-card' . ($extraClass !== '' ? ' ' . $extraClass : '');
        ?>
        <article
            class="<?= e($cardClass) ?>"
            data-id="<?= $idLomba ?>"
            data-bidang="<?= e(strtolower(implode(', ', $bidangTags))) ?>"
            data-title="<?= e(strtolower($namaLomba)) ?>"
            data-penyelenggara="<?= e(strtolower($penyelenggara)) ?>"
            data-desc="<?= e(strtolower($deskripsi)) ?>"
            data-tingkat="<?= e(strtolower($tingkat)) ?>"
            data-partisipasi="<?= e(strtolower($partisipasiFormatted)) ?>"
            <?= $isHidden ? 'style="display: none;"' : '' ?>
        >

            <div class="lomba-card-media">
                <?php if ($hasPosterFile): ?>
                    <img src="<?= e(url($posterRel)) ?>" alt="<?= e($namaLomba) ?>" class="lomba-card-img" loading="lazy">
                <?php else: ?>
                    <div class="lomba-card-placeholder">
                        <div class="placeholder-glow"></div>
                        <span class="placeholder-cat"><?= e(implode(' • ', $bidangTags)) ?></span>
                        <h4 class="placeholder-title"><?= e($namaLomba) ?></h4>
                    </div>
                <?php endif; ?>
            </div>

            <div class="lomba-card-body">
                <h3 class="lomba-card-title"><?= e($namaLomba) ?></h3>

                <div class="lomba-meta-table">

                    <div class="lomba-meta-row">
                        <span class="meta-label">Deadline</span>
                        <span class="badge-pill badge-red"><?= e($deadlineFormatted) ?></span>
                        <?php if ($isClosed): ?>
                            <span class="badge-pill badge-red">Pendaftaran Ditutup</span>
                        <?php endif; ?>
                    </div>

                    <div class="lomba-meta-row">
                        <span class="meta-label">Biaya Pendaftaran</span>
                        <span class="badge-pill badge-green"><?= e($biayaFormatted) ?></span>
                    </div>

                    <div class="lomba-meta-row">
                        <span class="meta-label">Partisipasi</span>
                        <span class="badge-pill badge-blue"><?= e($partisipasiFormatted) ?></span>
                    </div>

                    <div class="lomba-meta-row">
                        <span class="meta-label">Bidang Lomba</span>
                        <div style="display: flex; flex-wrap: wrap; gap: 4px; justify-content: flex-end;">
                            <?php foreach ($bidangTags as $bTag): ?>
                                <span class="badge-pill badge-orange"><?= e($bTag) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="lomba-meta-row">
                        <span class="meta-label">Tingkat</span>
                        <span class="badge-pill badge-purple"><?= e($tingkat) ?></span>
                    </div>
                </div>

                <?php if ($mode === 'link'): ?>
                    <a href="<?= e($detailUrl) ?>" class="btn-card-detail" style="text-decoration: none;">
                        Detail Perlombaan
                    </a>
                <?php else: ?>
                    <?php
                    $safeModalPayload = $item;
                    $safeModalPayload['bidang']      = implode(', ', $bidangTags);
                    $safeModalPayload['bidang_tags'] = $bidangTags;
                    $safeModalPayload['is_closed']   = $isClosed;
                    if (function_exists('safeExternalUrl')) {
                        $safeModalPayload['link_resmi'] = safeExternalUrl($item['link_resmi'] ?? null);
                    }
                    unset($safeModalPayload['link_guidebook']);
                    $encodedModalJson = (string) json_encode(
                        $safeModalPayload,
                        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
                    );
                    ?>
                    <button
                        type="button"
                        class="btn-card-detail"
                        onclick="openDetailModal(<?= htmlspecialchars($encodedModalJson, ENT_QUOTES, 'UTF-8') ?>)"
                    >
                        Detail Perlombaan
                    </button>
                <?php endif; ?>
            </div>
        </article>
        <?php
    }
}
