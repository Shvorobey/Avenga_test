<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Document;
use DocumentValidator;
use MaxLengthRule;
use RequiredMetadataRule;
use ProhibitedWordsRule;
use ValidationRuleInterface;

class DocumentValidatorTest extends TestCase
{

    public function testMaxLengthRuleFailsWhenContentIsTooLong(): void
    {
        $rule = new MaxLengthRule(10);
        $document = new Document('id_1', 'tenant_1', 'MoreThanTenChars', []);

        $error = $rule->validate($document);

        $this->assertNotNull($error);
        $this->assertStringContainsString('The document exceeds the maximum permitted length of 10 characters', $error);
    }

    public function testMaxLengthRulePassesWhenContentIsWithinLimit(): void
    {
        $rule = new MaxLengthRule(10);
        $document = new Document('id_1', 'tenant_1', 'Short', []);

        $error = $rule->validate($document);

        $this->assertNull($error);
    }

    public function testRequiredMetadataRuleFailsWhenFieldsAreMissing(): void
    {
        $rule = new RequiredMetadataRule(['author', 'license']);
        // Only “author” is passed; “licence” is missing
        $document = new Document('id_2', 'tenant_1', 'Content', ['author' => 'Alex']);

        $error = $rule->validate($document);

        $this->assertNotNull($error);
        $this->assertStringContainsString('Missing required metadata fields: license', $error);
    }

    public function testProhibitedWordsRuleIsCaseInsensitive(): void
    {
        $rule = new ProhibitedWordsRule();
        // The text contains the prohibited lowercase word
        $document = new Document('id_3', 'tenant_1', 'Some prohibited TEST text', []);

        $error = $rule->validate($document);

        $this->assertNotNull($error);
        $this->assertStringContainsString("Document contains prohibited word: 'test'.", $error);
    }

    public function testProhibitedWordsRuleMatchesOnlyWholeWords(): void
    {
        $rule = new ProhibitedWordsRule();

        // Case A: The text contains the derived word ‘testing’.
        $allowedDocument = new Document('id_7', 'tenant_premium', 'We are testing the system integration.', []);
        $errorForTesting = $rule->validate($allowedDocument);

        $this->assertNull($errorForTesting, 'The word "testing" should not be blocked by "test" rule.');

        // Case B: The text contains the exact word “test”.
        $blockedDocument = new Document('id_8', 'tenant_premium', 'This is a critical test.', []);
        $errorForTest = $rule->validate($blockedDocument);

        $this->assertNotNull($errorForTest, 'The exact word "test" must be blocked.');
        $this->assertStringContainsString("contains prohibited word: 'test'", $errorForTest);
    }

    public function testValidatorReturnsSuccessWhenAllRulesPass(): void
    {
        $document = new Document('id_4', 'tenant_1', 'Clean content', []);
        $validator = new DocumentValidator();

        // Creating two rule mocks that return null (success)
        $ruleMock1 = $this->createMock(ValidationRuleInterface::class);
        $ruleMock1->method('validate')->willReturn(null);

        $ruleMock2 = $this->createMock(ValidationRuleInterface::class);
        $ruleMock2->method('validate')->willReturn(null);

        $result = $validator->validate($document, [$ruleMock1, $ruleMock2]);

        $this->assertTrue($result->isValid);
        $this->assertEmpty($result->errors);
    }

    public function testValidatorAccumulatesMultipleErrors(): void
    {
        $document = new Document('id_5', 'tenant_1', 'Content', []);
        $validator = new DocumentValidator();

        // Simulation failure of two rules
        $ruleMock1 = $this->createMock(ValidationRuleInterface::class);
        $ruleMock1->method('validate')->willReturn('Error from rule 1');

        $ruleMock2 = $this->createMock(ValidationRuleInterface::class);
        $ruleMock2->method('validate')->willReturn('Error from rule 2');

        $result = $validator->validate($document, [$ruleMock1, $ruleMock2]);

        $this->assertFalse($result->isValid);
        $this->assertCount(2, $result->errors);
        $this->assertSame(['Error from rule 1', 'Error from rule 2'], $result->errors);
    }

    /**
     * Edge Case Test: an unexpected exception to a rule
     */
    public function testValidatorHandlesUnexpectedExceptionsGracefully(): void
    {
        $document = new Document('id_6', 'tenant_1', 'Content', []);
        $validator = new DocumentValidator();

        $brokenRule = $this->createMock(ValidationRuleInterface::class);
        $brokenRule->method('validate')
            ->willThrowException(new \RuntimeException('Unexpected crash'));

        $result = $validator->validate($document, [$brokenRule]);

        $this->assertFalse($result->isValid);

        $this->assertStringContainsString(
            'Rule validation failed due to an internal error: Unexpected crash',
            $result->errors[0]
        );
    }

}
