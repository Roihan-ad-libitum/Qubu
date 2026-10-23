<?php
/**
 * Validator input form (mis. form login).
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
 * Rule yang didukung: required, email, min:n, max:n
 * Rule selain 'required' dilewati jika field kosong (field opsional).
 */
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

            default:
                throw new InvalidArgumentException("Rule validasi tidak dikenal: '$name'.");
        }
    }
}
