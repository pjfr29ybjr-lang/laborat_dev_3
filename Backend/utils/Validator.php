<?php
/**
 * Input Validator
 * weather-system/backend/utils/Validator.php
 */

class Validator {

    private array $errors = [];
    private array $data   = [];

    public function __construct(array $data) {
        $this->data = $data;
    }

    // ── Rules ──────────────────────────────────────────────

    public function required(string $field, string $label = ''): static {
        $label = $label ?: $field;
        if (!isset($this->data[$field]) || trim((string)$this->data[$field]) === '') {
            $this->errors[$field][] = "$label é obrigatório.";
        }
        return $this;
    }

    public function email(string $field, string $label = 'Email'): static {
        if (isset($this->data[$field]) && $this->data[$field] !== '') {
            if (!filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
                $this->errors[$field][] = "$label inválido.";
            }
        }
        return $this;
    }

    public function minLength(string $field, int $min, string $label = ''): static {
        $label = $label ?: $field;
        if (isset($this->data[$field]) && mb_strlen((string)$this->data[$field]) < $min) {
            $this->errors[$field][] = "$label deve ter pelo menos $min caracteres.";
        }
        return $this;
    }

    public function maxLength(string $field, int $max, string $label = ''): static {
        $label = $label ?: $field;
        if (isset($this->data[$field]) && mb_strlen((string)$this->data[$field]) > $max) {
            $this->errors[$field][] = "$label deve ter no máximo $max caracteres.";
        }
        return $this;
    }

    public function strongPassword(string $field = 'password'): static {
        $val = $this->data[$field] ?? '';
        if ($val === '') return $this;
        $errors = [];
        if (mb_strlen($val) < PASSWORD_MIN_LENGTH)   $errors[] = 'mínimo 8 caracteres';
        if (!preg_match('/[A-Z]/', $val))             $errors[] = 'uma letra maiúscula';
        if (!preg_match('/[a-z]/', $val))             $errors[] = 'uma letra minúscula';
        if (!preg_match('/[0-9]/', $val))             $errors[] = 'um número';
        if (!preg_match('/[\W_]/', $val))             $errors[] = 'um caractere especial';
        if ($errors) {
            $this->errors[$field][] = 'A senha deve conter: ' . implode(', ', $errors) . '.';
        }
        return $this;
    }

    public function matches(string $field, string $field2, string $label = ''): static {
        $label = $label ?: "$field e $field2";
        if (($this->data[$field] ?? '') !== ($this->data[$field2] ?? '')) {
            $this->errors[$field][] = "$label não coincidem.";
        }
        return $this;
    }

    // ── Results ────────────────────────────────────────────

    public function fails(): bool { return !empty($this->errors); }
    public function passes(): bool { return empty($this->errors); }
    public function errors(): array { return $this->errors; }

    public function sanitize(string $field): string {
        return htmlspecialchars(strip_tags(trim((string)($this->data[$field] ?? ''))), ENT_QUOTES, 'UTF-8');
    }

    public function get(string $field, mixed $default = null): mixed {
        return $this->data[$field] ?? $default;
    }
}