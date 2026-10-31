<?php

require_once __DIR__ . '/../config/app.php';

class Validator
{
    private array $data;
    private array $rules;
    private array $labels;
    private array $errors = [];
    private bool $validated = false;

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

    public static function sanitize(?string $input): string
    {
        return htmlspecialchars((string) $input, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function cleanKeyword(mixed $keyword, int $maxLength = 100): string
    {
        if (!is_string($keyword) && !is_numeric($keyword)) {
            return '';
        }

        $clean = (string) $keyword;

        $clean = (string) preg_replace('/<(script|style)\b[^>]*>.*?<\/\1>/is', ' ', $clean);

        $clean = strip_tags($clean);

        $clean = (string) preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $clean);

        $clean = trim((string) preg_replace('/\s+/u', ' ', $clean));

        $safeMax = max(1, $maxLength);
        if (mb_strlen($clean, 'UTF-8') > $safeMax) {
            $clean = trim(mb_substr($clean, 0, $safeMax, 'UTF-8'));
        }

        return $clean;
    }

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

        $allowedBidang = BIDANG_QUBU;

        foreach ($allowedBidang as $validItem) {
            if (strcasecmp($validItem, $cleaned) === 0) {
                return $validItem;
            }
        }

        return null;
    }

    public static function isValidBidang(mixed $bidang): bool
    {
        return self::validateBidang($bidang) !== null;
    }

    public static function validateProdi(mixed $prodi): ?string
    {
        if (!is_string($prodi) && !is_numeric($prodi) && $prodi !== null) {
            return null;
        }

        $trimmed = trim((string) ($prodi ?? ''));

        if ($trimmed === '' || strcasecmp($trimmed, 'Semua Prodi') === 0 || strcasecmp($trimmed, 'semua') === 0) {
            return 'Semua Prodi';
        }

        $allowed = PRODI_FASILKOM;

        foreach ($allowed as $validItem) {
            if (strcasecmp($trimmed, $validItem) === 0) {
                return $validItem;
            }
        }

        return null;
    }

    public static function isValidProdi(mixed $prodi): bool
    {
        return self::validateProdi($prodi) !== null;
    }

    public static function isValidAcademicProdi(mixed $prodi): bool
    {
        if (!is_string($prodi)) {
            return false;
        }
        $trimmed = trim($prodi);
        foreach (PRODI_FASILKOM as $validItem) {
            if (strcasecmp($trimmed, $validItem) === 0) {
                return true;
            }
        }
        return false;
    }

    public static function validateGithub(mixed $rawGithub): ?string
    {
        if ($rawGithub === null) {
            return '';
        }
        return function_exists('normalizeGithubUrl')
            ? normalizeGithubUrl((string) $rawGithub)
            : trim((string) $rawGithub);
    }

    public static function isValidPhone(mixed $phone): bool
    {
        if (!is_string($phone) && !is_numeric($phone)) {
            return false;
        }
        $raw = trim((string) $phone);
        if ($raw === '' || !preg_match('/^\+?[0-9\s().-]+$/', $raw)) {
            return false;
        }
        $digits = preg_replace('/\D+/', '', $raw);
        $len = strlen($digits);
        return $len >= 8 && $len <= 15;
    }

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

    private function run(): void
    {
        if ($this->validated) {
            return;
        }
        $this->validated = true;

        foreach ($this->rules as $field => $ruleString) {
            $value = $this->data[$field] ?? null;
            $label = $this->labels[$field] ?? ucfirst(str_replace('_', ' ', $field));

            if (is_array($value)) {
                $this->errors[$field] = "$label tidak valid.";
                continue;
            }

            $value = (string) ($value ?? '');

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
