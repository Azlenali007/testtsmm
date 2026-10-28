<?php
declare(strict_types=1);

namespace App\Core;

class Validator
{
    private array $data;
    private array $rules;
    private array $errors = [];

    public function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
    }

    public static function make(array $data, array $rules): self
    {
        return new self($data, $rules);
    }

    public function passes(): bool
    {
        $this->errors = [];

        foreach ($this->rules as $field => $fieldRules) {
            $ruleList = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;
            $value = $this->data[$field] ?? null;

            foreach ($ruleList as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }

                $rule = trim($rule);

                if ($rule === 'required' && (is_null($value) || $value === '' || (is_array($value) && empty($value)))) {
                    $this->addError($field, "The " . str_replace('_', ' ', $field) . " field is required.");
                    break;
                }

                if ($value !== null && $value !== '') {
                    if ($rule === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $this->addError($field, "The " . str_replace('_', ' ', $field) . " must be a valid email address.");
                    }

                    if ($rule === 'min') {
                        $min = (int)($params[0] ?? 0);
                        if (is_numeric($value) && (float)$value < $min) {
                            $this->addError($field, "The " . str_replace('_', ' ', $field) . " must be at least {$min}.");
                        } elseif (is_string($value) && mb_strlen($value) < $min) {
                            $this->addError($field, "The " . str_replace('_', ' ', $field) . " must have at least {$min} characters.");
                        }
                    }

                    if ($rule === 'max') {
                        $max = (int)($params[0] ?? 0);
                        if (is_numeric($value) && (float)$value > $max) {
                            $this->addError($field, "The " . str_replace('_', ' ', $field) . " cannot exceed {$max}.");
                        } elseif (is_string($value) && mb_strlen($value) > $max) {
                            $this->addError($field, "The " . str_replace('_', ' ', $field) . " cannot exceed {$max} characters.");
                        }
                    }

                    if ($rule === 'numeric' && !is_numeric($value)) {
                        $this->addError($field, "The " . str_replace('_', ' ', $field) . " must be a number.");
                    }

                    if ($rule === 'url' && !filter_var($value, FILTER_VALIDATE_URL)) {
                        $this->addError($field, "The " . str_replace('_', ' ', $field) . " must be a valid URL.");
                    }

                    if ($rule === 'unique') {
                        // table,column,exceptId
                        $table = $params[0] ?? '';
                        $col = $params[1] ?? $field;
                        $exceptId = $params[2] ?? null;

                        $sql = "SELECT COUNT(*) FROM `{$table}` WHERE `{$col}` = :val";
                        $bindings = ['val' => $value];
                        if ($exceptId !== null) {
                            $sql .= " AND id != :except";
                            $bindings['except'] = $exceptId;
                        }

                        $stmt = Database::pdo()->prepare($sql);
                        $stmt->execute($bindings);
                        if ((int)$stmt->fetchColumn() > 0) {
                            $this->addError($field, "The specified " . str_replace('_', ' ', $field) . " is already in use.");
                        }
                    }
                }
            }
        }

        return empty($this->errors);
    }

    public function fails(): bool
    {
        return !$this->passes();
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function first(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }
}
