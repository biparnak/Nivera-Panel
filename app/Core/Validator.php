<?php
declare(strict_types=1);

namespace App\Core;

final class Validator
{
    private array $errors = [];

    public function make(array $data, array $rules): bool
    {
        $this->errors = [];
        foreach ($rules as $field => $ruleList) {
            $ruleset = is_string($ruleList) ? explode('|', $ruleList) : $ruleList;
            $value = $data[$field] ?? null;
            foreach ($ruleset as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }
        return empty($this->errors);
    }

    public function firstError(): string
    {
        return reset($this->errors) ?: 'Validation failed.';
    }

    public function errors(): array { return $this->errors; }

    private function applyRule(string $field, mixed $value, string $rule): void
    {
        $params = [];
        if (str_contains($rule, ':')) {
            [$name, $paramStr] = explode(':', $rule, 2);
            $params = explode(',', $paramStr);
            $rule = $name;
        }

        return match ($rule) {
            'required' => $this->validateRequired($field, $value),
            'email' => $this->validateEmail($field, $value),
            'min' => $this->validateMin($field, $value, (int)($params[0] ?? 0)),
            'max' => $this->validateMax($field, $value, (int)($params[0] ?? 255)),
            'confirmed' => $this->validateConfirmed($field, $value),
            'unique' => $this->validateUnique($field, $value, $params[0] ?? '', $params[1] ?? $field),
            'numeric' => $this->validateNumeric($field, $value),
            'integer' => $this->validateInteger($field, $value),
            'in' => $this->validateIn($field, $value, $params),
            default => null,
        };
    }

    private function validateRequired(string $field, mixed $value): void
    {
        if ($value === null || $value === '') {
            $this->errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
        }
    }

    private function validateEmail(string $field, mixed $value): void
    {
        if ($value && !filter_var((string)$value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = 'The ' . str_replace('_', ' ', $field) . ' must be a valid email.';
        }
    }

    private function validateMin(string $field, mixed $value, int $min): void
    {
        if ($value !== null && strlen((string)$value) < $min) {
            $this->errors[$field] = ucfirst(str_replace('_', ' ', $field)) . " must be at least {$min} characters.";
        }
    }

    private function validateMax(string $field, mixed $value, int $max): void
    {
        if ($value !== null && strlen((string)$value) > $max) {
            $this->errors[$field] = ucfirst(str_replace('_', ' ', $field)) . " must not exceed {$max} characters.";
        }
    }

    private function validateConfirmed(string $field, mixed $value): void
    {
        global $_POST;
        if (($value ?? '') !== ($_POST[$field . '_confirmation'] ?? '')) {
            $this->errors[$field] = 'The ' . str_replace('_', ' ', $field) . ' confirmation does not match.';
        }
    }

    private function validateUnique(string $field, mixed $value, string $table, string $column): void
    {
        if ($value === null || $value === '') return;
        $row = Database::fetchOne("SELECT COUNT(*) AS c FROM `{$table}` WHERE `{$column}` = ?", [(string)$value]);
        if ($row && (int)$row['c'] > 0) {
            $this->errors[$field] = 'This ' . str_replace('_', ' ', $field) . ' is already taken.';
        }
    }

    private function validateNumeric(string $field, mixed $value): void
    {
        if ($value !== null && $value !== '' && !is_numeric($value)) {
            $this->errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' must be numeric.';
        }
    }

    private function validateInteger(string $field, mixed $value): void
    {
        if ($value !== null && $value !== '' && !ctype_digit((string)$value)) {
            $this->errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' must be an integer.';
        }
    }

    private function validateIn(string $field, mixed $value, array $allowed): void
    {
        if ($value !== null && !in_array((string)$value, $allowed, true)) {
            $this->errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is not a valid option.';
        }
    }
}
