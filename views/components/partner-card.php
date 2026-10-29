<?php
/**
 * Komponen Kartu Partner Reusable (views/components/partner-card.php)
 * Presisi mengikuti mockup desain Cari Partner Qubu:
 * - Kontainer putih rounded 28px dengan bayangan lembut
 * - Header: Avatar lingkaran biru berinisial (mis. RF) / foto profil + Nama Lengkap + Prodi & Angkatan
 * - Box Abu-abu: Konteks lomba ("Mencari Tim Untuk: ...") atau fokus bidang kolaborasi
 * - Deretan Pill Bidang / Keahlian (dari bidang_minat mahasiswa)
 * - 2 Tombol Aksi Penuh di Bawah:
 *   1. Tombol Outline Putih/Abu: "Lihat Profil" (membuka modal / halaman profil lengkap read-only)
 *   2. Tombol Hijau (#22c55e): "Ajak ke Tim" (disiapkan sebagai placeholder untuk fitur Manajemen Tim)
 */

if (!function_exists('renderPartnerCard')) {
    /**
     * Render satu kartu kandidat partner mahasiswa.
     *
     * @param array $mhs     Data mahasiswa yang sudah diformat oleh MahasiswaController::formatPartnerItem
     * @param array $options Opsi tambahan:
     *                       - 'extraClass' (string): class tambahan pada <article> (mis. 'partner-card-item')
     *                       - 'hidden' (bool): apakah kartu disembunyikan saat SSR awal
     *                       - 'targetLomba' (array|null): data lomba konteks jika masuk dari Detail Lomba
     */
    function renderPartnerCard(array $mhs, array $options = []): void
    {
        $extraClass  = trim((string) ($options['extraClass'] ?? ''));
        $isHidden    = !empty($options['hidden']);
        $targetLomba = $options['targetLomba'] ?? null;

        $idMahasiswa = (int) ($mhs['id_mahasiswa'] ?? 0);
        $idUser      = (int) ($mhs['id_user'] ?? 0);
        $nama        = (string) ($mhs['nama_mahasiswa'] ?? 'Mahasiswa Qubu');
        $prodi       = (string) ($mhs['program_studi'] ?? 'Informatika');
        $angkatan    = (string) ($mhs['angkatan'] ?? '2025');
        $bidangMinat = (string) ($mhs['bidang_minat'] ?? 'Frontend');
        $bio         = trim((string) ($mhs['bio'] ?? ''));
        $fotoRel     = trim((string) ($mhs['foto'] ?? ''));
        $inisial     = (string) ($mhs['inisial'] ?? strtoupper(substr($nama, 0, 2)));

        $bidangTags = !empty($mhs['bidang_tags']) && is_array($mhs['bidang_tags'])
            ? $mhs['bidang_tags']
            : array_values(array_filter(array_map('trim', explode(',', $bidangMinat))));
        if (empty($bidangTags)) {
            $bidangTags = ['Frontend'];
        }

        $hasFoto = $fotoRel !== '' && file_exists(__DIR__ . '/../../' . $fotoRel);
        $profilUrl = $mhs['profil_url']
            ?? (function_exists('url') ? url('?page=lihat-profil&id=' . $idMahasiswa) : '?page=lihat-profil&id=' . $idMahasiswa);

        $lombaNama = $targetLomba['nama_lomba']
            ?? ($mhs['target_lomba_nama'] ?? ('Kompetisi ' . ($bidangTags[0] ?? 'Teknologi') . ' Nasional'));

        $cardClass = 'partner-card' . ($extraClass !== '' ? ' ' . $extraClass : '');

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
            data-prodi="<?= e(strtolower($prodi)) ?>"
            data-bidang="<?= e(strtolower($bidangMinat)) ?>"
            data-bio="<?= e(strtolower($bio)) ?>"
            <?= $isHidden ? 'style="display: none;"' : '' ?>
        >
            <!-- 1. Header Kartu: Lingkaran Inisial Biru + Nama & Prodi -->
            <div class="pcard-header">
                <div class="pcard-avatar" aria-hidden="true">
                    <?php if ($hasFoto): ?>
                        <img src="<?= e(url($fotoRel)) ?>" alt="<?= e($nama) ?>" class="pcard-avatar-img" loading="lazy">
                    <?php else: ?>
                        <span class="pcard-avatar-initials"><?= e($inisial) ?></span>
                    <?php endif; ?>
                </div>
                <div class="pcard-identity">
                    <h3 class="pcard-name"><?= e($nama) ?></h3>
                    <p class="pcard-sub"><?= e($prodi) ?> &bull; <?= e($angkatan) ?></p>
                </div>
            </div>

            <!-- 2. Kotak Info Target Lomba / Kolaborasi Persis Mockup -->
            <div class="pcard-target-box">
                <span class="pcard-target-label">Mencari Tim Untuk:</span>
                <strong class="pcard-target-title"><?= e($lombaNama) ?></strong>
            </div>

            <!-- 3. Deretan Tag Keahlian / Bidang Minat -->
            <div class="pcard-skills-section">
                <span class="pcard-skills-label">Bidang Keahlian:</span>
                <div class="pcard-skills-list">
                    <?php foreach ($bidangTags as $tag): ?>
                        <span class="pcard-skill-pill"><?= e($tag) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- 4. Dua Tombol Aksi di Bawah Kartu (Lihat Profil & Placeholder Ajak ke Tim) -->
            <div class="pcard-actions">
                <button
                    type="button"
                    class="btn-pcard-outline"
                    onclick="openPartnerProfileModal(<?= htmlspecialchars($encodedProfileJson, ENT_QUOTES, 'UTF-8') ?>)"
                >
                    Lihat Profil
                </button>
                <button
                    type="button"
                    class="btn-pcard-green"
                    data-placeholder="true"
                    title="Segera hadir di fitur Manajemen Tim"
                    onclick="showPartnerPlaceholderNotice('Fitur Ajak ke Tim akan diaktifkan pada modul Manajemen Tim.')"
                >
                    Ajak ke Tim
                </button>
            </div>
        </article>
        <?php
    }
}

