<?php

declare(strict_types=1);

interface ValidationRuleInterface
{
    /**
     * @param Document $document
     * @return string|null
     */
    public function validate(Document $document): ?string;
}
