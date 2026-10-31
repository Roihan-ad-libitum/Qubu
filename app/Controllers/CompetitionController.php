<?php

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

    public function validateIdRequest(mixed $rawId): ?int
    {
        return Validator::validatePositiveInt($rawId);
    }

    public function filterCompetitions(array $query = []): array
    {
        $validated = $this->validateSearchRequest($query);

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

    public function getDetail(mixed $rawId, int $relatedLimit = 4): ?array
    {
        $validId = $this->validateIdRequest($rawId);
        if ($validId === null) {
            return null;
        }

        return $this->competitionModel->getDetailWithRelated($validId, max(1, $relatedLimit));
    }

    private function formatTanggalIndo(?string $datetime): string
    {
        return formatLombaTanggalIndo($datetime);
    }

    public function formatCompetitionItem(array $item): array
    {
        $idLomba     = (int) ($item['id_lomba'] ?? 0);
        $biaya       = (float) ($item['biaya'] ?? 0);
        $partisipasi = (string) ($item['partisipasi'] ?? 'Individu/Tim');
        $rawPoster   = trim((string) ($item['poster'] ?? ''));
        $posterPath  = $rawPoster !== '' ? $rawPoster : 'public/images/semantik-poster.jpg';

        $item['id_lomba']                      = $idLomba;
        $item['biaya']                         = $biaya;
        $item['biaya_formatted']               = formatLombaBiaya($biaya);
        $item['deadline_formatted']            = $this->formatTanggalIndo($item['deadline_daftar'] ?? null);
        $item['is_closed']                     = isLombaRegistrationClosed($item['deadline_daftar'] ?? null);
        $item['tanggal_pelaksanaan_formatted'] = $this->formatTanggalIndo($item['tanggal_pelaksanaan'] ?? null);
        $item['partisipasi_formatted']         = formatLombaPartisipasi($partisipasi);
        $item['poster_url']                    = function_exists('url') ? url($posterPath) : $posterPath;
        $item['detail_url']                    = function_exists('url')
            ? url('?page=lomba-detail&id=' . $idLomba)
            : '?page=lomba-detail&id=' . $idLomba;
        $item['link_resmi']                    = function_exists('safeExternalUrl')
            ? safeExternalUrl($item['link_resmi'] ?? null)
            : (string) ($item['link_resmi'] ?? '');
        $item['link_guidebook']                = function_exists('safeExternalUrl')
            ? safeExternalUrl($item['link_guidebook'] ?? null)
            : (string) ($item['link_guidebook'] ?? '');

        if (isset($item['related']) && is_array($item['related'])) {
            $item['related'] = array_map([$this, 'formatCompetitionItem'], $item['related']);
        }

        return $item;
    }

    public function buildApiPayload(array $query = []): array
    {

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
