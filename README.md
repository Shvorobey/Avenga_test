# Multi-Tenant Document Validation Component

A robust, highly extendable, and strictly object-oriented document validation component built with **PHP 8.1+** and covered by **PHPUnit 10** tests. Designed as a solution for a multi-tenant document processing platform scenario.

## Architecture & Design Reasoning

The core architecture isolates verification logic from the platform's orchestration layer, adhering strictly to **SOLID** principles:

- **Strategy Pattern (`ValidationRuleInterface`)**: Each validation constraint (size limit, mandatory metadata, restricted phrasing) is encapsulated within its own standalone class inside the `src/Rules/` directory. This guarantees full compliance with the **Open/Closed Principle**, enabling the frictionless addition of new rules without modification of the core validation engine.
- **Value Object (`ValidationResult`)**: Encapsulates the evaluation output into an immutable data structure, preventing accidental property overrides and standardizing data flow across the pipeline.
- **Modular Directory Structure**: Transitioned from a single monolithic file setup to a highly maintainable, modern workspace layout. Classes are fully separated into dedicated module targets under the `src/` container.
- **Custom PSR-4 Style Autoloader**: Orchestrated natively inside `Controller.php` via `spl_autoload_register`, resolving internal code linkages seamlessly without dragging structural dependencies or overhead inside production configurations.
- **Resilience & Fault Tolerance**: Robust error handling isolates runtime anomalies inside individual third-party or custom rules, safely logging exceptions without compromising the entire orchestration lifecycle.

---

## Component Architecture Overview

### Data Models
- **`Document`**: Readonly data object containing `id`, `tenantId`, `content`, and `metadata`.
- **`ValidationResult`**: Standardized immutable response object delivering a `bool $isValid` state and an array of granular error logs.

### Validation Strategies (`src/Rules/`)
- `MaxLengthRule`: Evaluates character payload bounds.
- `RequiredMetadataRule`: Assures existence of tenant-specified keys inside the document metadata layout.
- `ProhibitedWordsRule`: Performs case-insensitive needle-in-haystack substrate searches to block forbidden lexicon phrases.

---

## Directory Structure

```text
├── compose.yaml                # Docker infrastructure setup
├── index.html                  # Interactive, centered AJAX layout with blur-triggered field resets
├── Controller.php              # Clean controller handler with a built-in native autoloader
├── src/                        # Isolated production codebase assets
│   ├── Document.php
│   ├── ValidationResult.php
│   ├── DocumentValidator.php
│   └── Rules/
│       ├── ValidationRuleInterface.php
│       ├── MaxSizeRule.php
│       ├── RequiredMetadataRule.php
│       └── ProhibitedWordsRule.php
└── tests/
    └── DocumentValidatorTest.php  # Updated granular PHPUnit automated test cases
```

---

## Getting Started & Deployment

### 1. Spin up the Environment
Deploy the Apache web server using Docker Compose:
```bash
docker compose up -d
```
The application will immediately become accessible in your browser at: `http://localhost:8080/index.html`.

### 2. Form UI Interaction
The interface features a responsive, vertically and horizontally centered validation card:
- Select a subscription plan (**Basic** or **Premium**) to swap underlying evaluation parameters.
- Submit incorrect entries (e.g., matching the word `confidential` or skipping metadata fields) to see input boundaries dynamically glow red with error strings contextualized right below the specific elements.
- Input elements track **`blur` events**, meaning individual validation alert frames disappear automatically the moment a developer shifts focus away from an field they began correcting.

---

## Executing the Test Suite

Granular behaviors, rule combinations, and extreme execution parameters (Edge Cases) are covered via isolated test models.

Run the test suite via the local vendor environment bundled in the container workspace:
```bash
docker compose exec web ./vendor/bin/phpunit --bootstrap Controller.php tests
```

### Expected Output:
```text
PHPUnit 10.5.64 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.3.33

........                                                            8 / 8 (100%)

Time: 00:00.010, Memory: 8.00 MB

OK (8 tests, 17 assertions)

```
