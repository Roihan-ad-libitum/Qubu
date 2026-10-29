<?php
/**
 * Validator input form & request parameter (Login, Register, InfoLomba Search/Filter/Detail).
 *
 * Contoh:
 *   $v = new Validator($_POST, [
 *       'identity' => 'required|max:100',
 *       'password' => 'required',
 *   ], [
 *       'identity' => 'Username atau email',
 *       'password' => 'Password',
 *   ]);
 *
 *   if (!$v->passes()) {
 *       $error = $v->getFirstError('identity');
 *   }
 *   $clean = $v->validated(); // data sudah di-trim (kecuali field password)
 *
 * Rule yang didukung: required, email, min:n, max:n, positive_int, int, bidang
 * Rule selain 'required' dilewati jika field kosong (field opsional).
 */

require_once __DIR__ . '/../config/app.php';

class Validator
{
    private array $data;
    private array $rules;
    private array $labels;
    private array $errors = [];
    private bool $validated = false;

    /**
     * @param array $data   Data input, biasanya $_POST
     * @param array $rules  ['field' => 'required|email|max:100']
     * @param array $labels ['field' => 'Nama tampilan'] untuk pesan error
     */
    public function __construct(array $data, array $rules, array $labels = [])
    {
        $this->data   = $data;
        $this->rules  = $rules;
        $this->labels = $labels;
    }

    public function passes(): bool
    {
        $this->run();
        return $this->errors === [];
    }

    public function fails(): bool
    {
        return !$this->passes();
    }

    /**
     * Semua error: ['field' => 'pesan pertama untuk field itu'].
     */
    public function errors(): array
    {
        $this->run();
        return $this->errors;
    }

    public function getFirstError(string $field): ?string
    {
        $this->run();
        return $this->errors[$field] ?? null;
    }

    /**
     * Nilai input yang sudah dibersihkan, hanya untuk field yang ada di rules.
     * String di-trim, kecuali field yang namanya mengandung "password"
     * (spasi di awal/akhir bisa jadi bagian dari password).
     */
    public function validated(): array
    {
        $clean = [];

        foreach (array_keys($this->rules) as $field) {
            $value = $this->data[$field] ?? null;

            if (is_string($value) && stripos($field, 'password') === false) {
                $value = trim($value);
            }

            $clean[$field] = $value;
        }

        return $clean;
    }

    /**
     * Untuk menampilkan ulang teks input di HTML. Gunakan saat OUTPUT,
     * bukan sebelum membandingkan/menyimpan data (agar tidak merusak
     * password atau karakter khusus).
     */
    public static function sanitize(?string $input): string
    {
        return htmlspecialchars((string) $input, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Sanitasi kata kunci pencarian (keyword) dari tag HTML/script, karakter kontrol,
     * dan spasi berlebih, serta membatasi panjang maksimal string.
     *
     * @param mixed $keyword   Input kata kunci dari request ($_GET['q'] dsb.)
     * @param int   $maxLength Panjang maksimal karakter (default 100)
     * @return string Kata kunci bersih siap pakai
     */
    public static function cleanKeyword(mixed $keyword, int $maxLength = 100): string
    {
        if (!is_string($keyword) && !is_numeric($keyword)) {
            return '';
        }

        $clean = (string) $keyword;
        // Hapus isi blok <script> dan <style> terlebih dahulu sebelum strip_tags
        $clean = (string) preg_replace('/<(script|style)\b[^>]*>.*?<\/\1>/is', ' ', $clean);
        // Bersihkan seluruh tag HTML dan PHP
        $clean = strip_tags($clean);
        // Hapus karakter kontrol / null bytes
        $clean = (string) preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $clean);
        // Rapikan spasi berlebih menjadi spasi tunggal lalu trim
        $clean = trim((string) preg_replace('/\s+/u', ' ', $clean));

        $safeMax = max(1, $maxLength);
        if (mb_strlen($clean, 'UTF-8') > $safeMax) {
            $clean = trim(mb_substr($clean, 0, $safeMax, 'UTF-8'));
        }

        return $clean;
    }

    /**
     * Validasi parameter bidang agar hanya bernilai salah satu dari whitelist BIDANG_QUBU
     * atau 'Semua Bidang'. Mengembalikan nama kanonik bidang jika valid, atau null jika tidak valid.
     *
     * @param mixed $bidang Input bidang dari request
     * @return string|null Nama bidang kanonik ('Semua Bidang' atau anggota BIDANG_QUBU), atau null jika di luar whitelist
     */
    public static function validateBidang(mixed $bidang): ?string
    {
        if ($bidang === null) {
            return 'Semua Bidang';
        }

        if (!is_string($bidang)) {
            return null;
        }

        $cleaned = trim(strip_tags($bidang));
        if ($cleaned === '' || in_array(strtolower($cleaned), ['semua', 'semua bidang', 'all'], true)) {
            return 'Semua Bidang';
        }

        $allowedBidang = defined('BIDANG_QUBU')
            ? BIDANG_QUBU
            : ['Frontend', 'Backend', 'UI/UX Design', 'Data Science', 'Robotic'];

        foreach ($allowedBidang as $validItem) {
            if (strcasecmp($validItem, $cleaned) === 0) {
                return $validItem;
            }
        }

        return null;
    }

    /**
     * Cek apakah nilai bidang valid (termasuk dalam BIDANG_QUBU atau 'Semua Bidang').
     */
    public static function isValidBidang(mixed $bidang): bool
    {
        return self::validateBidang($bidang) !== null;
    }

    /**
     * Validasi filter Program Studi terhadap daftar resmi PRODI_FASILKOM.
     * Mengembalikan nama kanonik ('Semua Prodi', 'Informatika', 'Sistem Informasi'),
     * atau null jika input prodi tidak termasuk dalam whitelist.
     */
    public static function validateProdi(mixed $prodi): ?string
    {
        if (!is_string($prodi) && !is_numeric($prodi) && $prodi !== null) {
            return null;
        }

        $trimmed = trim((string) ($prodi ?? ''));

        if ($trimmed === '' || strcasecmp($trimmed, 'Semua Prodi') === 0 || strcasecmp($trimmed, 'semua') === 0) {
            return 'Semua Prodi';
        }

        $allowed = defined('PRODI_FASILKOM') ? PRODI_FASILKOM : [
            'Informatika',
            'Sistem Informasi',
        ];

        foreach ($allowed as $validItem) {
            if (strcasecmp($trimmed, $validItem) === 0) {
                return $validItem;
            }
        }

        return null;
    }

    /**
     * Cek apakah nilai prodi valid (termasuk dalam PRODI_FASILKOM atau 'Semua Prodi').
     */
    public static function isValidProdi(mixed $prodi): bool
    {
        return self::validateProdi($prodi) !== null;
    }

    /**
     * Validasi ID atau parameter angka agar harus berupa integer positif (>= 1).
     *
     * @param mixed $value Input ID dari request
     * @return int|null Integer positif jika valid, atau null jika tidak valid
     */
    public static function validatePositiveInt(mixed $value): ?int
    {
        if (!is_int($value) && !is_string($value)) {
            return null;
        }

        if (is_string($value)) {
            $value = trim($value);
            if ($value === '') {
                return null;
            }
        }

        $validated = filter_var($value, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);

        return $validated !== false ? (int) $validated : null;
    }

    // ------------------------------------------------------------------

    private function run(): void
    {
        if ($this->validated) {
            return;
        }
        $this->validated = true;

        foreach ($this->rules as $field => $ruleString) {
            $value = $this->data[$field] ?? null;
            $label = $this->labels[$field] ?? ucfirst(str_replace('_', ' ', $field));

            // Input array (mis. password[]=x) bukan input yang valid untuk form teks.
            if (is_array($value)) {
                $this->errors[$field] = "$label tidak valid.";
                continue;
            }

            $value = (string) ($value ?? '');
            // Password tidak di-trim; field lain di-trim sebelum dicek.
            $checked = stripos($field, 'password') === false ? trim($value) : $value;

            foreach (explode('|', $ruleString) as $rule) {
                $rule = trim($rule);
                if ($rule === '') {
                    continue;
                }

                [$name, $param] = array_pad(explode(':', $rule, 2), 2, null);

                if ($name === 'required') {
                    if ($checked === '') {
                        $this->errors[$field] = "$label wajib diisi.";
                        break;
                    }
                    continue;
                }

                // Rule lain dilewati untuk field kosong yang tidak 'required'.
                if ($checked === '') {
                    continue;
                }

                $error = $this->check($name, $param, $checked, $label);
                if ($error !== null) {
                    $this->errors[$field] = $error;
                    break;
                }
            }
        }
    }

    private function check(string $name, ?string $param, string $value, string $label): ?string
    {
        switch ($name) {
            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL)
                    ? null
                    : "$label harus berupa alamat email yang valid.";

            case 'min':
                $min = (int) $param;
                return mb_strlen($value) >= $min
                    ? null
                    : "$label minimal $min karakter.";

            case 'max':
                $max = (int) $param;
                return mb_strlen($value) <= $max
                    ? null
                    : "$label maksimal $max karakter.";

            case 'int':
            case 'positive_int':
                return self::validatePositiveInt($value) !== null
                    ? null
                    : "$label harus berupa angka bulat positif.";

            case 'bidang':
                return self::isValidBidang($value)
                    ? null
                    : "$label harus berupa kategori bidang resmi Qubu.";

            case 'prodi':
                return self::isValidProdi($value)
                    ? null
                    : "$label harus berupa program studi Fasilkom yang valid (Informatika atau Sistem Informasi).";

            default:
                throw new InvalidArgumentException("Rule validasi tidak dikenal: '$name'.");
        }
    }
}
