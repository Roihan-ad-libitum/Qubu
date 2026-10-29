<?php
/**
 * Komponen Kartu Lomba Reusable (views/components/lomba-card.php)
 * Presisi mengikuti desain Figma Qubu:
 * - Kontainer putih 36px rounded dengan padding 16px
 * - Poster inset 24px rounded di bagian atas
 * - Judul lomba bold 2 baris di bawah poster
 * - Wadah abu-abu (#dfe6ee) kapsul metadata berisi 5 baris kapsul putih mandiri:
 *   1. Deadline          -> Badge Pill Merah Pastel (.badge-red)
 *   2. Biaya Pendaftaran -> Badge Pill Hijau Mint Pastel (.badge-green)
 *   3. Partisipasi       -> Badge Pill Biru Abu Pastel (.badge-blue)
 *   4. Bidang Lomba      -> Badge Pill Kuning/Krem Pastel (.badge-orange)
 *   5. Tingkat           -> Badge Pill Ungu/Indigo Pastel (.badge-purple)
 * - Tombol hitam navy "Detail Perlombaan" (.btn-card-detail)
 */

if (!function_exists('formatLombaTanggalIndo')) {
    function formatLombaTanggalIndo(?string $datetime): string
    {
        if (empty($datetime)) {
            return '-';
        }
        $timestamp = strtotime($datetime);
        if (!$timestamp) {
            return '-';
        }
        $bulanIndo = [
            1  => 'Januari',  2  => 'Februari', 3  => 'Maret',    4  => 'April',
            5  => 'Mei',      6  => 'Juni',     7  => 'Juli',     8  => 'Agustus',
            9  => 'September', 10 => 'Oktober',  11 => 'November', 12 => 'Desember',
        ];
        $d = date('j', $timestamp);
        $m = (int) date('n', $timestamp);
        $y = date('Y', $timestamp);

        return $d . ' ' . ($bulanIndo[$m] ?? date('M', $timestamp)) . ' ' . $y;
    }
}

if (!function_exists('formatLombaPartisipasi')) {
    function formatLombaPartisipasi(?string $partisipasi): string
    {
        $val = trim((string) $partisipasi);
        if ($val === '' || strcasecmp($val, 'Individu/Tim') === 0) {
            return 'Individu & Tim';
        }
        return $val;
    }
}

if (!function_exists('renderLombaCard')) {
    /**
     * Render satu kartu perlombaan sesuai desain Figma Qubu.
     *
     * @param array $item    Record data lomba dari CompetitionModel
     * @param array $options Opsi tambahan:
     *                       - 'extraClass' (string): class tambahan pada <article> (mis. 'grid-card-item')
     *                       - 'mode' (string): 'modal' (default, buka pop-up modal) atau 'link' (navigasi ke halaman detail)
     */
    function renderLombaCard(array $item, array $options = []): void
    {
        $extraClass  = trim((string) ($options['extraClass'] ?? ''));
        $mode        = $options['mode'] ?? 'modal';
        $isHidden    = !empty($options['hidden']);
        $idLomba     = (int) ($item['id_lomba'] ?? 0);
        $namaLomba   = (string) ($item['nama_lomba'] ?? '-');
        $penyelenggara = (string) ($item['penyelenggara'] ?? '-');
        $deskripsi   = (string) ($item['deskripsi'] ?? '');
        $bidang      = (string) ($item['bidang'] ?? 'UI/UX Design');
        $tingkat     = (string) ($item['tingkat'] ?? 'Nasional');
        $biaya       = (float) ($item['biaya'] ?? 0);
        $posterRel   = trim((string) ($item['poster'] ?? ''));

        $deadlineFormatted = $item['deadline_formatted']
            ?? formatLombaTanggalIndo($item['deadline_daftar'] ?? null);
        $biayaFormatted = $item['biaya_formatted']
            ?? ($biaya <= 0 ? 'Gratis' : 'Rp ' . number_format($biaya, 0, ',', '.'));
        $partisipasiFormatted = $item['partisipasi_formatted']
            ?? formatLombaPartisipasi($item['partisipasi'] ?? 'Individu/Tim');

        $hasPosterFile = $posterRel !== '' && file_exists(__DIR__ . '/../../' . $posterRel);
        $detailUrl = $item['detail_url']
            ?? (function_exists('url') ? url('?page=lomba-detail&id=' . $idLomba) : '?page=lomba-detail&id=' . $idLomba);

        $cardClass = 'lomba-card' . ($extraClass !== '' ? ' ' . $extraClass : '');
        ?>
        <article
            class="<?= e($cardClass) ?>"
            data-id="<?= $idLomba ?>"
            data-bidang="<?= e(strtolower($bidang)) ?>"
            data-title="<?= e(strtolower($namaLomba)) ?>"
            data-penyelenggara="<?= e(strtolower($penyelenggara)) ?>"
            data-desc="<?= e(strtolower($deskripsi)) ?>"
            data-tingkat="<?= e(strtolower($tingkat)) ?>"
            data-partisipasi="<?= e(strtolower($partisipasiFormatted)) ?>"
            <?= $isHidden ? 'style="display: none;"' : '' ?>
        >
            <!-- 1. Poster Lomba Inset (24px Rounded) -->
            <div class="lomba-card-media">
                <?php if ($hasPosterFile): ?>
                    <img src="<?= url($posterRel) ?>" alt="<?= e($namaLomba) ?>" class="lomba-card-img" loading="lazy">
                <?php else: ?>
                    <div class="lomba-card-placeholder">
                        <div class="placeholder-glow"></div>
                        <span class="placeholder-cat"><?= e($bidang) ?></span>
                        <h4 class="placeholder-title"><?= e($namaLomba) ?></h4>
                    </div>
                <?php endif; ?>
            </div>

            <!-- 2. Body Kartu: Judul, Wadah Kapsul Abu-abu (5 Baris Putih), & Tombol Detail -->
            <div class="lomba-card-body">
                <h3 class="lomba-card-title"><?= e($namaLomba) ?></h3>

                <div class="lomba-meta-table">
                    <!-- Baris 1: Deadline -->
                    <div class="lomba-meta-row">
                        <span class="meta-label">Deadline</span>
                        <span class="badge-pill badge-red"><?= e($deadlineFormatted) ?></span>
                    </div>

                    <!-- Baris 2: Biaya Pendaftaran -->
                    <div class="lomba-meta-row">
                        <span class="meta-label">Biaya Pendaftaran</span>
                        <span class="badge-pill badge-green"><?= e($biayaFormatted) ?></span>
                    </div>

                    <!-- Baris 3: Partisipasi -->
                    <div class="lomba-meta-row">
                        <span class="meta-label">Partisipasi</span>
                        <span class="badge-pill badge-blue"><?= e($partisipasiFormatted) ?></span>
                    </div>

                    <!-- Baris 4: Bidang Lomba -->
                    <div class="lomba-meta-row">
                        <span class="meta-label">Bidang Lomba</span>
                        <span class="badge-pill badge-orange"><?= e($bidang) ?></span>
                    </div>

                    <!-- Baris 5: Tingkat -->
                    <div class="lomba-meta-row">
                        <span class="meta-label">Tingkat</span>
                        <span class="badge-pill badge-purple"><?= e($tingkat) ?></span>
                    </div>
                </div>

                <!-- 3. Tombol Hitam Navy "Detail Perlombaan" -->
                <?php if ($mode === 'link'): ?>
                    <a href="<?= e($detailUrl) ?>" class="btn-card-detail" style="text-decoration: none;">
                        Detail Perlombaan
                    </a>
                <?php else: ?>
                    <button
                        type="button"
                        class="btn-card-detail"
                        onclick="openDetailModal(<?= htmlspecialchars((string) json_encode($item, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?>)"
                    >
                        Detail Perlombaan
                    </button>
                <?php endif; ?>
            </div>
        </article>
        <?php
    }
}

