<?php

declare(strict_types=1);

readonly class RequiredMetadataRule implements ValidationRuleInterface
{
    /**
     * @param string[] $requiredFields
     */
    public function __construct(private array $requiredFields) {}

    /**
     * @param Document $document
     * @return string|null
     */
    public function validate(Document $document): ?string
    {
        $missing = [];
        foreach ($this->requiredFields as $field) {
            if (!array_key_exists($field, $document->metadata)) {
                $missing[] = $field;
            }
        }

        return empty($missing) ? null : "Missing required metadata fields: " . implode(', ', $missing) . ".";
    }
}
