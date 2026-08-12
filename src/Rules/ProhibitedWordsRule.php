<?php

declare(strict_types=1);

readonly class ProhibitedWordsRule implements ValidationRuleInterface
{
    private const PROHIBITED_WORDS = [
        'test',
        'strawberries',
    ];

    /**
     * @param Document $document
     * @return string|null
     */
    public function validate(Document $document): ?string
    {
        $result = null;

        foreach (self::PROHIBITED_WORDS as $word) {
            $pattern = '/\b' . preg_quote($word, '/') . '\b/i';

            if (preg_match($pattern, $document->content) === 1) {
                $result = "Document contains prohibited word: '{$word}'.";
                break;
            }
        }
        return $result;
    }
}
