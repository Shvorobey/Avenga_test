<?php

declare(strict_types=1);

class DocumentValidator
{
    /**
     * @param Document $document
     * @param ValidationRuleInterface[] $rules
     * @return ValidationResult
     */
    public function validate(Document $document, array $rules): ValidationResult
    {
        $errors = [];

        foreach ($rules as $rule) {
            try {
                $error = $rule->validate($document);
                if ($error !== null) {
                    $errors[] = $error;
                }
            } catch (Throwable $e) {
                $errors[] = "Rule validation failed due to an internal error: " . $e->getMessage();
            }
        }

        return empty($errors) ? ValidationResult::success() : ValidationResult::failure($errors);
    }
}
