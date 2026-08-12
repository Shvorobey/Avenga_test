<?php

declare(strict_types=1);

readonly class Document
{
    /**
     * @param string $id
     * @param string $tenantId
     * @param string $content
     * @param array $metadata
     */
    public function __construct(
        public string $id,
        public string $tenantId,
        public string $content,
        public array  $metadata
    ) {}
}
