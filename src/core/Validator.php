<?php
/**
 * Input Validation Class
 */

class Validator
{
    private array $errors = [];
    private array $data = [];

    /**
     * Create a new validator instance
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Validate the data against rules
     */
    public function validate(array $rules): bool
    {
        $this->errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $this->data[$field] ?? null;
            $ruleList = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;

            foreach ($ruleList as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }

        return empty($this->errors);
    }

    /**
     * Apply a single rule
     */
    private function applyRule(string $field, $value, string $rule): void
    {
        // Parse rule and parameters
        $params = [];
        if (strpos($rule, ':') !== false) {
            [$rule, $paramStr] = explode(':', $rule, 2);
            $params = explode(',', $paramStr);
        }

        $method = 'validate' . ucfirst($rule);

        if (method_exists($this, $method)) {
            $this->$method($field, $value, $params);
        }
    }

    /**
     * Required field validation
     */
    private function validateRequired(string $field, $value, array $params): void
    {
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            $this->addError($field, 'Dieses Feld ist erforderlich');
        }
    }

    /**
     * Email validation
     */
    private function validateEmail(string $field, $value, array $params): void
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, 'Bitte geben Sie eine gültige E-Mail-Adresse ein');
        }
    }

    /**
     * Minimum length validation
     */
    private function validateMin(string $field, $value, array $params): void
    {
        $min = (int)($params[0] ?? 0);
        if ($value !== null && $value !== '' && mb_strlen((string)$value) < $min) {
            $this->addError($field, "Mindestens {$min} Zeichen erforderlich");
        }
    }

    /**
     * Maximum length validation
     */
    private function validateMax(string $field, $value, array $params): void
    {
        $max = (int)($params[0] ?? 255);
        if ($value !== null && mb_strlen((string)$value) > $max) {
            $this->addError($field, "Maximal {$max} Zeichen erlaubt");
        }
    }

    /**
     * Numeric validation
     */
    private function validateNumeric(string $field, $value, array $params): void
    {
        if ($value !== null && $value !== '' && !is_numeric($value)) {
            $this->addError($field, 'Dieses Feld muss eine Zahl sein');
        }
    }

    /**
     * Integer validation
     */
    private function validateInteger(string $field, $value, array $params): void
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_INT)) {
            $this->addError($field, 'Dieses Feld muss eine ganze Zahl sein');
        }
    }

    /**
     * Minimum value validation
     */
    private function validateMinValue(string $field, $value, array $params): void
    {
        $min = (int)($params[0] ?? 0);
        if ($value !== null && $value !== '' && (int)$value < $min) {
            $this->addError($field, "Der Wert muss mindestens {$min} sein");
        }
    }

    /**
     * Maximum value validation
     */
    private function validateMaxValue(string $field, $value, array $params): void
    {
        $max = (int)($params[0] ?? PHP_INT_MAX);
        if ($value !== null && $value !== '' && (int)$value > $max) {
            $this->addError($field, "Der Wert darf höchstens {$max} sein");
        }
    }

    /**
     * In list validation
     */
    private function validateIn(string $field, $value, array $params): void
    {
        if ($value !== null && $value !== '' && !in_array($value, $params, true)) {
            $this->addError($field, 'Der gewählte Wert ist ungültig');
        }
    }

    /**
     * Confirmed field validation (e.g., password confirmation)
     */
    private function validateConfirmed(string $field, $value, array $params): void
    {
        $confirmField = $field . '_confirmation';
        $confirmValue = $this->data[$confirmField] ?? null;

        if ($value !== $confirmValue) {
            $this->addError($field, 'Die Bestätigung stimmt nicht überein');
        }
    }

    /**
     * Password complexity validation
     * Ensures password contains uppercase, lowercase, number, and is minimum 8 chars
     */
    private function validatePassword(string $field, $value, array $params): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $password = (string)$value;
        $errors = [];

        // Check minimum length
        if (mb_strlen($password) < 8) {
            $errors[] = 'mindestens 8 Zeichen';
        }

        // Check for uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'einen Großbuchstaben';
        }

        // Check for lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'einen Kleinbuchstaben';
        }

        // Check for number
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'eine Zahl';
        }

        if (!empty($errors)) {
            $this->addError($field, 'Das Passwort muss enthalten: ' . implode(', ', $errors));
        }
    }

    /**
     * Allowed tables for unique validation (security whitelist)
     */
    private static array $allowedTables = [
        'users', 'games', 'materials', 'boxes', 'categories', 'tags', 'groups',
        'calendar_events', 'settings', 'password_resets', 'ip_bans', 'changelog'
    ];

    /**
     * Validate table and column names to prevent SQL injection
     */
    private function isValidIdentifier(string $name): bool
    {
        return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name) === 1;
    }

    /**
     * Unique validation (check database)
     */
    private function validateUnique(string $field, $value, array $params): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $table = $params[0] ?? '';
        $column = $params[1] ?? $field;
        $excludeId = isset($params[2]) ? (int)$params[2] : null;

        if (empty($table)) {
            return;
        }

        // Security: Validate table name against whitelist
        if (!in_array($table, self::$allowedTables, true)) {
            Logger::security("Invalid table name attempted in unique validation", [
                'table' => $table,
                'column' => $column
            ]);
            return;
        }

        // Security: Validate column name format
        if (!$this->isValidIdentifier($column)) {
            Logger::security("Invalid column name attempted in unique validation", [
                'table' => $table,
                'column' => $column
            ]);
            return;
        }

        $db = Database::getInstance();
        $sql = "SELECT 1 FROM `{$table}` WHERE `{$column}` = :value";
        $bindParams = ['value' => $value];

        if ($excludeId !== null) {
            $sql .= " AND id != :exclude_id";
            $bindParams['exclude_id'] = $excludeId;
        }

        $sql .= " LIMIT 1";

        $stmt = $db->prepare($sql);
        $stmt->execute($bindParams);

        if ($stmt->fetchColumn() !== false) {
            $this->addError($field, 'Dieser Wert existiert bereits');
        }
    }

    /**
     * Date validation
     */
    private function validateDate(string $field, $value, array $params): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $format = $params[0] ?? 'Y-m-d';
        $date = DateTime::createFromFormat($format, $value);

        if (!$date || $date->format($format) !== $value) {
            $this->addError($field, 'Bitte geben Sie ein gültiges Datum ein');
        }
    }

    /**
     * URL validation
     */
    private function validateUrl(string $field, $value, array $params): void
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->addError($field, 'Bitte geben Sie eine gültige URL ein');
        }
    }

    /**
     * Regex validation
     */
    private function validateRegex(string $field, $value, array $params): void
    {
        $pattern = $params[0] ?? '';
        if ($value !== null && $value !== '' && !preg_match($pattern, (string)$value)) {
            $this->addError($field, 'Das Format ist ungültig');
        }
    }

    /**
     * Array validation
     */
    private function validateArray(string $field, $value, array $params): void
    {
        if ($value !== null && !is_array($value)) {
            $this->addError($field, 'Dieses Feld muss ein Array sein');
        }
    }

    /**
     * Add an error message
     */
    public function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    /**
     * Get all errors
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Get errors for a specific field
     */
    public function getError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * Check if validation failed
     */
    public function fails(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Check if validation passed
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }

    /**
     * Get validated data (only fields with rules)
     */
    public function validated(): array
    {
        return $this->data;
    }

    /**
     * Static helper to validate data
     */
    public static function make(array $data, array $rules): self
    {
        $validator = new self($data);
        $validator->validate($rules);
        return $validator;
    }
}
