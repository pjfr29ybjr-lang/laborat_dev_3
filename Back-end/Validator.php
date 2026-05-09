<?php
/**
 * utils/Validator.php
 * Regras de validação reutilizáveis para os dados recebidos do frontend.
 */

declare(strict_types=1);

class Validator
{
    private array $errors = [];
    private array $data   = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function required(string $field): static
    {
        if (empty($this->data[$field])) {
            $this->errors[$field] = "O campo '$field' é obrigatório.";
        }
        return $this;
    }

    public function email(string $field): static
    {
        if (!isset($this->data[$field]) || $this->data[$field] === '') { 

         }

    public function minLength(string $field, int $min): static
    {
        $value = $this->data[$field] ?? '';
        if (strlen($value) < $min) {
            $this->errors[$field] = "O campo '$field' deve ter pelo menos $min caracteres.";
        }
        return $this;
    }

    public function maxLength(string $field, int $max): static
    {
        $value = $this->data[$field] ?? '';
        if (strlen($value) > $max) {
            $this->errors[$field] = "O campo '$field' deve ter no máximo $max caracteres.";
        }
        return $this;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function get(string $field): mixed
    {
        return $this->data[$field] ?? null;
    }

    /** Retorna body JSON decodificado da requisição */
    public static function bodyJson(): array
    {
        $raw = file_get_contents('php://input');
        return (array) json_decode($raw, true);
    }
}