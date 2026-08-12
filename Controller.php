<?php
declare(strict_types=1);

header('Content-Type: application/json');

// Class autoloader
spl_autoload_register(static function (string $className) {
    $file = str_contains($className, 'Rule')
        ? __DIR__ . '/src/Rules/' . $className . '.php'
        : __DIR__ . '/src//' . $className . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

if (PHP_SAPI !== 'cli') {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        try {
            echo json_encode(['error' => 'Method not allowed'], JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            error_log('JSON encoding failed: ' . $e->getMessage());
            http_response_code(500);
            echo '{"error":"Internal Server Error during JSON generation"}';
        }
        exit;
    }

    $tenantConfigurations = [
        'tenant_premium' => [
            'content' => [new MaxLengthRule(100), new ProhibitedWordsRule()],
            'metadata' => [new RequiredMetadataRule(['author', 'license'])]
        ],
        'tenant_basic' => [
            'content' => [new MaxLengthRule(50)],
            'metadata' => [new RequiredMetadataRule(['author'])]
        ]
    ];

    try {
        $input = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
        error_log('JSON encoding failed: ' . $e->getMessage());
        http_response_code(500);
        echo '{"error":"Internal Server Error during JSON generation"}';
    }

    $tenantId = $input['tenantId'] ?? 'tenant_basic';
    $content = $input['content'] ?? '';
    $author = $input['author'] ?? '';
    $license = $input['license'] ?? '';

    $metadata = [];
    if (!empty($author)) {
        $metadata['author'] = $author;
    }

    if (!empty($license)) {
        $metadata['license'] = $license;
    }

    $document = new Document('doc_uploaded', $tenantId, $content, $metadata);
    $validator = new DocumentValidator();

    $fieldErrors = [];
    $config = $tenantConfigurations[$tenantId] ?? $tenantConfigurations['tenant_basic'];

    if (isset($config['content'])) {
        $result = $validator->validate($document, $config['content']);
        if (!$result->isValid) {
            $fieldErrors['content'] = $result->errors;
        }
    }

    if (isset($config['metadata'])) {
        $result = $validator->validate($document, $config['metadata']);
        if (!$result->isValid) {
            if (empty($author)) {
                $fieldErrors['author'] = ['Field "author" is required.'];
            }
            if (empty($license) && $tenantId === 'tenant_premium') {
                $fieldErrors['license'] = ['Field "license" is required for Premium tenants.'];
            }
        }
    }

    try {
        echo empty($fieldErrors)
            ? json_encode(['success' => true, 'message' => 'Document validated successfully!'], JSON_THROW_ON_ERROR)
            : json_encode(['success' => false, 'errors' => $fieldErrors], JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
        error_log('JSON encoding failed: ' . $e->getMessage());
        http_response_code(500);
        echo '{"error":"Internal Server Error during JSON generation"}';
    }

}
