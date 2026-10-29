<?php
/**
 * Controller Pengelolaan Pencarian, Filter, dan Detail InfoLomba Qubu
 * Menangani validasi input, sanitasi request parameter, dan orkestrasi query ke CompetitionModel.
 */

require_once __DIR__ . '/../Models/CompetitionModel.php';
require_once __DIR__ . '/../../core/Validator.php';
require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/../../config/app.php';

class CompetitionController
{
    private CompetitionModel $competitionModel;

    public function __construct(?CompetitionModel $competitionModel = null)
    {
        $this->competitionModel = $competitionModel ?? new CompetitionModel();
    }

    /**
     * Validasi & sanitasi parameter request untuk pencarian dan filter lomba.
     * - Keyword dibersihkan dari tag HTML/script dan di-trim.
     * - Bidang divalidasi ketat terhadap whitelist BIDANG_QUBU atau 'Semua Bidang'.
     * - Limit dan page divalidasi sebagai integer positif.
     *
     * @param array $query Array parameter request (biasanya $_GET)
     * @return array Parameter yang sudah bersih dan tervalidasi
     */
    public function validateSearchRequest(array $query = []): array
    {
        $rawKeyword = $query['q'] ?? ($query['keyword'] ?? '');
        $cleanKeyword = Validator::cleanKeyword($rawKeyword, 100);

        $rawBidang = $query['bidang'] ?? null;
        $validatedBidang = Validator::validateBidang($rawBidang);
        $isValidBidang = ($validatedBidang !== null);

        $rawLimit = $query['limit'] ?? 50;
        $validLimit = Validator::validatePositiveInt($rawLimit) ?? 50;
        $safeLimit = max(1, min($validLimit, 100));

        $rawPage = $query['page'] ?? 1;
        $validPage = Validator::validatePositiveInt($rawPage) ?? 1;

        return [
            'keyword'         => $cleanKeyword,
            'bidang'          => $validatedBidang ?? 'Semua Bidang',
            'raw_bidang'      => is_string($rawBidang) ? trim(strip_tags($rawBidang)) : '',
            'is_valid_bidang' => $isValidBidang,
            'limit'           => $safeLimit,
            'page'            => $validPage,
        ];
    }

    /**
     * Validasi parameter ID lomba agar harus berupa integer positif (>= 1).
     *
     * @param mixed $rawId Nilai ID dari request ($_GET['id'] dsb.)
     * @return int|null Integer positif jika valid, atau null jika tidak valid
     */
    public function validateIdRequest(mixed $rawId): ?int
    {
        return Validator::validatePositiveInt($rawId);
    }

    /**
     * Menjalankan pencarian dan filter lomba dengan input yang telah divalidasi & disanitasi.
     *
     * @param array $query Parameter request (mis. $_GET)
     * @return array Hasil terstruktur berisi metadata filter dan daftar kompetisi
     */
    public function filterCompetitions(array $query = []): array
    {
        $validated = $this->validateSearchRequest($query);

        // Jika parameter bidang tidak ada di whitelist BIDANG_QUBU, kembalikan hasil kosong secara aman
        if (!$validated['is_valid_bidang']) {
            return [
                'keyword'         => $validated['keyword'],
                'bidang'          => $validated['bidang'],
                'is_valid_bidang' => false,
                'limit'           => $validated['limit'],
                'page'            => $validated['page'],
                'total'           => 0,
                'data'            => [],
            ];
        }

        $items = $this->competitionModel->searchAndFilter(
            $validated['keyword'],
            $validated['bidang'],
            $validated['limit']
        );

        return [
            'keyword'         => $validated['keyword'],
            'bidang'          => $validated['bidang'],
            'is_valid_bidang' => true,
            'limit'           => $validated['limit'],
            'page'            => $validated['page'],
            'total'           => count($items),
            'data'            => $items,
        ];
    }

    /**
     * Mengambil detail lengkap satu perlombaan beserta rekomendasi lomba terkait
     * setelah memvalidasi ID sebagai integer positif.
     *
     * @param mixed $rawId        ID lomba dari request
     * @param int   $relatedLimit Batas jumlah lomba terkait (default 4)
     * @return array|null Data detail lomba beserta key 'related', atau null jika ID tidak valid / tidak ditemukan
     */
    public function getDetail(mixed $rawId, int $relatedLimit = 4): ?array
    {
        $validId = $this->validateIdRequest($rawId);
        if ($validId === null) {
            return null;
        }

        $safeRelatedLimit = Validator::validatePositiveInt($relatedLimit) ?? 4;
        return $this->competitionModel->getDetailWithRelated($validId, $safeRelatedLimit);
    }
}

