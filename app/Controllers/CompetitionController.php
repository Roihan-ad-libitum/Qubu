<?php
/**
 * Controller Pengelolaan Pencarian, Filter, dan Detail InfoLomba Qubu
 * Menangani validasi input, sanitasi request parameter, dan orkestrasi query ke CompetitionModel.
 */

require_once __DIR__ . '/../Models/CompetitionModel.php';
require_once __DIR__ . '/../Models/MahasiswaModel.php';
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

        $rawPage = $query['p'] ?? ($query['page'] ?? 1);
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

    /**
     * Format tanggal ke bahasa Indonesia (mis. "15 Juli 2025")
     */
    private function formatTanggalIndo(?string $datetime): string
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

    /**
     * Memperkaya satu record lomba dengan atribut tampilan terformat untuk konsumsi JSON API / Frontend JS
     */
    public function formatCompetitionItem(array $item): array
    {
        $idLomba     = (int) ($item['id_lomba'] ?? 0);
        $biaya       = (float) ($item['biaya'] ?? 0);
        $partisipasi = (string) ($item['partisipasi'] ?? 'Individu/Tim');
        $rawPoster   = trim((string) ($item['poster'] ?? ''));
        $posterPath  = $rawPoster !== '' ? $rawPoster : 'public/images/semantik-poster.jpg';

        $item['id_lomba']                      = $idLomba;
        $item['biaya']                         = $biaya;
        $item['biaya_formatted']               = $biaya <= 0 ? 'Gratis' : 'Rp ' . number_format($biaya, 0, ',', '.');
        $item['deadline_formatted']            = $this->formatTanggalIndo($item['deadline_daftar'] ?? null);
        $item['tanggal_pelaksanaan_formatted'] = $this->formatTanggalIndo($item['tanggal_pelaksanaan'] ?? null);
        $item['partisipasi_formatted']         = $partisipasi === 'Individu/Tim' ? 'Individu & Tim' : $partisipasi;
        $item['poster_url']                    = function_exists('url') ? url($posterPath) : $posterPath;
        $item['detail_url']                    = function_exists('url')
            ? url('?page=lomba-detail&id=' . $idLomba)
            : '?page=lomba-detail&id=' . $idLomba;

        if (isset($item['related']) && is_array($item['related'])) {
            $item['related'] = array_map([$this, 'formatCompetitionItem'], $item['related']);
        }

        return $item;
    }

    /**
     * Menyusun struktur respons JSON API untuk pencarian/filter lomba atau detail lomba tunggal.
     *
     * @param array $query Parameter query (biasanya $_GET)
     * @return array ['http_code' => int, 'body' => array]
     */
    public function buildApiPayload(array $query = []): array
    {
        // Mode 1: Jika parameter 'id' dikirimkan, ambil detail 1 lomba beserta related competitions
        if (array_key_exists('id', $query) && $query['id'] !== '' && $query['id'] !== null) {
            $validId = $this->validateIdRequest($query['id']);
            if ($validId === null) {
                return [
                    'http_code' => 400,
                    'body'      => [
                        'success' => false,
                        'message' => 'Parameter ID lomba harus berupa angka bulat positif.',
                        'total'   => 0,
                        'data'    => null,
                    ],
                ];
            }

            $detail = $this->getDetail($validId);
            if ($detail === null) {
                return [
                    'http_code' => 404,
                    'body'      => [
                        'success' => false,
                        'message' => 'Data perlombaan tidak ditemukan.',
                        'total'   => 0,
                        'data'    => null,
                    ],
                ];
            }

            return [
                'http_code' => 200,
                'body'      => [
                    'success' => true,
                    'total'   => 1,
                    'data'    => $this->formatCompetitionItem($detail),
                ],
            ];
        }

        // Mode 2: Live Search & Filter Katalog Lomba (?q={keyword}&bidang={bidang}&page={page})
        $result = $this->filterCompetitions($query);
        $formattedData = array_map([$this, 'formatCompetitionItem'], $result['data']);

        return [
            'http_code' => 200,
            'body'      => [
                'success' => true,
                'total'   => $result['total'],
                'filters' => [
                    'keyword'         => $result['keyword'],
                    'bidang'          => $result['bidang'],
                    'is_valid_bidang' => $result['is_valid_bidang'],
                    'page'            => $result['page'],
                    'limit'           => $result['limit'],
                ],
                'data'    => $formattedData,
            ],
        ];
    }

    /**
     * Handler HTTP Endpoint JSON API untuk Live Search & Filter Async (?page=api-competitions / api/competitions.php)
     *
     * @param array|null $query Parameter request (default $_GET)
     */
    public function apiCompetitions(?array $query = null): void
    {
        $requestQuery = $query ?? $_GET;
        $payload = $this->buildApiPayload($requestQuery);

        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            http_response_code($payload['http_code']);
        }

        echo json_encode($payload['body'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Menampilkan Halaman Detail Lomba Mandiri (?page=lomba-detail&id={id})
     * Mendukung shareable deep-link, menampilkan rincian lomba, countdown deadline,
     * rekomendasi lomba terkait, serta halaman 404 bersahabat jika ID tidak valid/tidak ditemukan.
     *
     * @param mixed $rawId Parameter ID lomba (default $_GET['id'] ?? null)
     */
    public function detail(mixed $rawId = null): void
    {
        Session::start();

        $targetId = $rawId ?? ($_GET['id'] ?? null);
        $rawDetail = $this->getDetail($targetId, 4);
        $lomba = $rawDetail !== null ? $this->formatCompetitionItem($rawDetail) : null;
        $relatedLomba = $lomba['related'] ?? [];
        $isNotFound = ($lomba === null);

        if ($isNotFound && !headers_sent()) {
            http_response_code(404);
        }

        // Ambil informasi pengguna aktif untuk Top Navbar (jika sedang login)
        $idUser = (int) Session::get('id_user', 0);
        $profile = null;
        if ($idUser > 0) {
            $mahasiswaModel = new MahasiswaModel();
            $profile = $mahasiswaModel->findByUserId($idUser);
        }

        $namaMahasiswa   = $profile['nama_mahasiswa'] ?? (Session::get('nama_lengkap') ?? 'Mahasiswa Qubu');
        $userBidangMinat = $profile['bidang_minat'] ?? (Session::get('demo_profile_data')['bidang_minat'] ?? 'UI/UX Design');
        $userFoto        = $profile['foto'] ?? (Session::get('demo_profile_data')['foto'] ?? null);

        $title = $lomba
            ? ($lomba['nama_lomba'] . ' - Detail Perlombaan | ' . APP_NAME)
            : ('Perlombaan Tidak Ditemukan (404) - ' . APP_NAME);
        $extraCss = ['public/css/components.css', 'public/css/dashboard.css'];

        require __DIR__ . '/../../views/mahasiswa/detail-lomba.php';
    }
}

