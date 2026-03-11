<?php
declare(strict_types=1);

/**
 * ServiceResult - Unified return type for all service layer methods.
 *
 * Decouples business logic results from HTTP concerns (flash messages, redirects).
 * Both web controllers and CLI commands consume ServiceResult uniformly.
 */
class ServiceResult
{
    public bool $success;
    public array $data;
    public array $errors;
    public ?string $message;

    private function __construct(bool $success, array $data, array $errors, ?string $message)
    {
        $this->success = $success;
        $this->data = $data;
        $this->errors = $errors;
        $this->message = $message;
    }

    /**
     * Create a successful result.
     */
    public static function ok(array $data = [], ?string $message = null): self
    {
        return new self(true, $data, [], $message);
    }

    /**
     * Create a failure result.
     *
     * @param array $errors Field-keyed validation errors ['field' => ['msg', ...]]
     */
    public static function fail(array $errors = [], ?string $message = null): self
    {
        return new self(false, [], $errors, $message);
    }

    public function failed(): bool
    {
        return !$this->success;
    }

    public function succeeded(): bool
    {
        return $this->success;
    }
}
