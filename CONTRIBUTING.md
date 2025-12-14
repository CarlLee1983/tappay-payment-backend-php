# Contributing to tappay-backend-payment-php

Thank you for your interest in contributing to `carllee1983/tappay-payment-backend`! We welcome contributions from the community.

[繁體中文](./CONTRIBUTING_ZH.md) | English

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [How Can I Contribute?](#how-can-i-contribute)
- [Development Setup](#development-setup)
- [Development Workflow](#development-workflow)
- [Coding Standards](#coding-standards)
- [Commit Guidelines](#commit-guidelines)
- [Pull Request Process](#pull-request-process)
- [Testing](#testing)
- [Documentation](#documentation)

## Code of Conduct

This project adheres to a Code of Conduct that all contributors are expected to follow. Please be respectful and constructive in all interactions.

## How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check existing issues to avoid duplicates. When creating a bug report, include:

- **Clear title**: Describe the issue briefly
- **Description**: Detailed description of the problem
- **Steps to reproduce**: Step-by-step instructions
- **Expected behavior**: What you expected to happen
- **Actual behavior**: What actually happened
- **Environment**: OS, PHP version, package version
- **Code samples**: Minimal code to reproduce (if applicable)

### Suggesting Enhancements

Enhancement suggestions are tracked as GitHub issues. When creating an enhancement suggestion:

- **Use a clear and descriptive title**
- **Provide a detailed description** of the suggested enhancement
- **Explain why this enhancement would be useful**
- **List any alternatives** you've considered

### Pull Requests

We actively welcome your pull requests:

1. Fork the repo and create your branch from `main`
2. If you've added code that should be tested, add tests
3. If you've changed APIs, update the documentation
4. Ensure the test suite passes
5. Make sure your code follows the coding standards
6. Issue the pull request

## Development Setup

### Prerequisites

- [PHP](https://www.php.net/) 8.1 or later
- [Composer](https://getcomposer.org/)
- [Git](https://git-scm.com/)
- A code editor (VS Code or PhpStorm recommended)

### Setup Steps

1. **Fork and clone the repository**

```bash
git clone https://github.com/YOUR_USERNAME/tappay-backend-payment-php.git
cd tappay-backend-payment-php
```

2. **Install dependencies**

```bash
composer install
```

3. **Run tests to ensure everything works**

```bash
composer test
```

## Development Workflow

### 1. Create a Feature Branch

```bash
git checkout -b feature/your-feature-name
# or
git checkout -b fix/your-bug-fix
```

### 2. Make Your Changes

- Write clean, readable code
- Follow the coding standards
- Add tests for new functionality
- Update documentation as needed

### 3. Test Your Changes

```bash
# Run tests
composer test

# Run tests with coverage (if available)
./vendor/bin/phpunit --coverage-text
```

### 4. Commit Your Changes

```bash
git add .
git commit -m "type: description"
```

See [Commit Guidelines](#commit-guidelines) for commit message format.

### 5. Push and Create Pull Request

```bash
git push origin feature/your-feature-name
```

Then create a Pull Request on GitHub.

## Coding Standards

### PHP

- Use PHP 8.1+ features
- Enable strict types (`declare(strict_types=1)`)
- Follow PSR-12 coding standard
- Document public APIs with PHPDoc comments

### Code Style

We follow PSR-12 and these conventions:

- **Indentation**: 4 spaces
- **Braces**: Same-line for classes and methods
- **Quotes**: Single quotes for strings
- **Naming Conventions**:
  - `PascalCase` for classes
  - `camelCase` for methods and variables
  - `UPPER_CASE` for constants

### Example

```php
<?php

declare(strict_types=1);

namespace TapPay\Payment;

/**
 * Creates a payment request for TapPay API.
 *
 * @param string $prime The prime token from frontend
 * @param int $amount The payment amount
 *
 * @throws \InvalidArgumentException When parameters are invalid
 */
public function createPayment(string $prime, int $amount): PaymentResponse
{
    if ($amount <= 0) {
        throw new \InvalidArgumentException('Amount must be positive');
    }
    // Implementation
}
```

## Commit Guidelines

We follow the [Conventional Commits](https://www.conventionalcommits.org/) specification.

### Commit Message Format

```
<type>(<scope>): <subject>

<body>

<footer>
```

### Types

- **feat**: A new feature
- **fix**: A bug fix
- **docs**: Documentation only changes
- **style**: Code style changes (formatting, semicolons, etc.)
- **refactor**: Code refactoring (neither fixes a bug nor adds a feature)
- **perf**: Performance improvements
- **test**: Adding or updating tests
- **chore**: Changes to build process or auxiliary tools

### Examples

```bash
# Feature
git commit -m "feat: add instalment payment support"

# Bug fix
git commit -m "fix: correct refund amount validation"

# Documentation
git commit -m "docs: update API reference for payByToken"

# Refactoring
git commit -m "refactor: extract HTTP client logic"
```

## Pull Request Process

1. **Update Documentation**: Ensure README and API docs are updated
2. **Add Tests**: All new features must include tests
3. **Pass All Checks**: Ensure tests pass
4. **Clean Commit History**: Squash or rebase if needed
5. **Reference Issues**: Link related issues in PR description
6. **Request Review**: Tag maintainers for review

### PR Title Format

Follow the same format as commit messages:

```
feat: add new utility function for parsing
fix: resolve API timeout handling
docs: improve README examples
```

## Testing

### Writing Tests

- Place tests in `tests/` directory
- Use PHPUnit for testing
- Aim for high code coverage
- Test edge cases and error conditions

### Test Example

```php
<?php

declare(strict_types=1);

namespace TapPay\Payment\Tests;

use PHPUnit\Framework\TestCase;
use TapPay\Payment\ClientConfig;
use TapPay\Payment\Dto\PrimePaymentRequest;
use TapPay\Payment\TapPayClient;

class TapPayClientTest extends TestCase
{
    public function testPayByPrimeSuccess(): void
    {
        // Setup mock HTTP client
        $client = $this->createClientWithMockedResponse([
            'status' => 0,
            'msg' => 'success',
            'rec_trade_id' => 'tr_test',
        ]);

        $result = $client->payByPrime(new PrimePaymentRequest(
            prime: 'test_prime',
            amount: 100
        ));

        $this->assertTrue($result->isSuccess());
    }
}
```

### Running Tests

```bash
# Run all tests
composer test

# Run specific test file
./vendor/bin/phpunit tests/TapPayClientTest.php

# Run with coverage
./vendor/bin/phpunit --coverage-text
```

## Documentation

### README Updates

- Keep README.md (English) and README_ZH.md (Traditional Chinese) in sync
- Include code examples for new features
- Update API reference section

### Code Documentation

- Use PHPDoc for public APIs
- Document parameters, return types, and exceptions
- Include usage examples in comments

### Example

```php
/**
 * Processes a payment using a Prime token.
 *
 * The Prime token is obtained from the TapPay frontend SDK's getPrime()
 * method and is valid for 90 seconds.
 *
 * @param PrimePaymentRequest $request Payment request details
 *
 * @return PaymentResponse Response containing transaction details
 *
 * @throws SignatureException When the API key is invalid
 * @throws HttpException When the TapPay service is unavailable
 * @throws ValidationException When required fields are missing
 *
 * @example
 * ```php
 * $response = $client->payByPrime(new PrimePaymentRequest(
 *     prime: 'prime_from_frontend',
 *     amount: 100,
 *     details: 'Order #12345'
 * ));
 * if ($response->isSuccess()) {
 *     // Save rec_trade_id for future refunds
 * }
 * ```
 */
public function payByPrime(PrimePaymentRequest $request): PaymentResponse
{
    // Implementation
}
```

## Questions?

If you have questions about contributing, feel free to:

- Open a [GitHub Discussion](https://github.com/CarlLee1983/tappay-backend-payment-php/discussions)
- Create an issue with the "question" label

## License

By contributing, you agree that your contributions will be licensed under the MIT License.

---

Thank you for contributing to `carllee1983/tappay-payment-backend`! 🎉
