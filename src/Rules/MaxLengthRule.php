<?php

declare(strict_types=1);

readonly class MaxLengthRule implements ValidationRuleInterface
{
    /**
     * @param int $maxLength
     */
    public function __construct(private int $maxLength) {}

    /**
     * @param Document $document
     * @return string|null
     */
    public function validate(Document $document): ?string
    {
        return (strlen($document->content) > $this->maxLength)
            ? "The document exceeds the maximum permitted length of {$this->maxLength} characters."
            : null;
    }
}
