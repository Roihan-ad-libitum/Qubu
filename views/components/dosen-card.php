<?php
/**
 * Komponen Kartu Dosen Pembimbing Reusable (views/components/dosen-card.php)
 * Presisi mengikuti mockup desain Cari Dosen Pembimbing Qubu:
 * - Kontainer putih rounded 26px dengan bayangan lembut
 * - Header: Avatar lingkaran biru berinisial (mis. RF) / foto profil + Nama Dosen & Gelar + Bidang Keahlian
 * - Bar Status Ketersediaan: "Status: Bersedia" / "Tidak Bersedia"
 * - Tombol Hijau Penuh (#22c55e): "Ajukan Pembimbing"
 *   (Aktif jika `status === 'bersedia'`, dan otomatis `disabled` jika `status === 'tidak_bersedia'`)
 */

if (!function_exists('renderDosenCard')) {
    /**
     * Render satu kartu dosen pembimbing.
     *
     * @param array $dosen   Data dosen yang telah diformat oleh MahasiswaController::formatDosenItem
     * @param array $options Opsi tambahan:
     *                       - 'extraClass' (string): class tambahan pada <article> (mis. 'dosen-card-item')
     *                       - 'hidden' (bool): apakah kartu disembunyikan saat SSR awal
     */
    function renderDosenCard(array $dosen, array $options = []): void
    {
        $extraClass = trim((string) ($options['extraClass'] ?? ''));
        $isHidden   = !empty($options['hidden']);

        $idDosen        = (int) ($dosen['id_dosen'] ?? 0);
        $namaDosen      = (string) ($dosen['nama_dosen'] ?? 'Dosen Pembimbing');
        $nidn           = (string) ($dosen['nidn'] ?? '');
        $bidangKeahlian = (string) ($dosen['bidang_keahlian'] ?? 'Frontend');
        $status         = strtolower(trim((string) ($dosen['status'] ?? 'bersedia')));
        $isBersedia     = ($status === 'bersedia');
        $fotoRel        = trim((string) ($dosen['foto'] ?? ''));
        $inisial        = (string) ($dosen['inisial'] ?? strtoupper(substr($namaDosen, 0, 2)));

        $bidangTags = !empty($dosen['bidang_tags']) && is_array($dosen['bidang_tags'])
            ? $dosen['bidang_tags']
            : array_values(array_filter(array_map('trim', explode(',', $bidangKeahlian))));
        if (empty($bidangTags)) {
            $bidangTags = ['Frontend'];
        }

        $hasFoto   = $fotoRel !== '' && file_exists(__DIR__ . '/../../' . $fotoRel);
        $cardClass = 'dosen-card' . ($extraClass !== '' ? ' ' . $extraClass : '');

        $statusText  = $isBersedia ? 'Status: Bersedia' : 'Status: Tidak Bersedia';
        $statusClass = $isBersedia ? 'is-bersedia' : 'is-tidak-bersedia';

        $encodedDosenJson = (string) json_encode(
            $dosen,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        );
        ?>
        <article
            class="<?= e($cardClass) ?>"
            data-id="<?= $idDosen ?>"
            data-name="<?= e(strtolower($namaDosen)) ?>"
            data-nidn="<?= e(strtolower($nidn)) ?>"
            data-bidang="<?= e(strtolower($bidangKeahlian)) ?>"
            data-status="<?= e($status) ?>"
            <?= $isHidden ? 'style="display: none;"' : '' ?>
        >
            <!-- 1. Header Kartu: Lingkaran Inisial Biru + Nama Dosen & Bidang Keahlian -->
            <div class="dcard-header">
                <div class="dcard-avatar" aria-hidden="true">
                    <?php if ($hasFoto): ?>
                        <img src="<?= e(url($fotoRel)) ?>" alt="<?= e($namaDosen) ?>" class="dcard-avatar-img" loading="lazy">
                    <?php else: ?>
                        <span class="dcard-avatar-initials"><?= e($inisial) ?></span>
                    <?php endif; ?>
                </div>
                <div class="dcard-identity">
                    <h3 class="dcard-name" title="<?= e($namaDosen) ?>"><?= e($namaDosen) ?></h3>
                    <p class="dcard-sub">Informatika &bull; <?= e(implode(', ', $bidangTags)) ?></p>
                </div>
            </div>

            <!-- 2. Bar Status Ketersediaan Persis Gambar Mockup -->
            <div class="dcard-status-box <?= e($statusClass) ?>">
                <span><?= e($statusText) ?></span>
            </div>

            <!-- 3. Tombol Hijau "Ajukan Pembimbing" (Disabled jika tidak_bersedia) -->
            <div class="dcard-actions">
                <?php if ($isBersedia): ?>
                    <button
                        type="button"
                        class="btn-dcard-green"
                        data-dosen="<?= e($encodedDosenJson) ?>"
                        onclick="openAjukanBimbinganModal(<?= htmlspecialchars($encodedDosenJson, ENT_QUOTES, 'UTF-8') ?>)"
                    >
                        Ajukan Pembimbing
                    </button>
                <?php else: ?>
                    <button
                        type="button"
                        class="btn-dcard-green is-disabled"
                        disabled
                        aria-disabled="true"
                        title="Dosen sedang tidak bersedia menerima bimbingan baru"
                    >
                        Ajukan Pembimbing
                    </button>
                <?php endif; ?>
            </div>
        </article>
        <?php
    }
}

