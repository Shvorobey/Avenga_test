<?php

declare(strict_types=1);

readonly class ValidationResult
{
    /**
     * @param bool $isValid
     * @param string[] $errors
     */
    public function __construct(
        public bool  $isValid,
        public array $errors = []
    ) {}

    /**
     * @return self
     */
    public static function success(): self
    {
        return new self(true);
    }

    /**
     * @param string[] $errors
     * @return ValidationResult
     */
    public static function failure(array $errors): self
    {
        return new self(false, $errors);
    }
}
