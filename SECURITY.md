# Security Policy

## Supported Versions

We actively support the following versions of `carllee1983/tappay-payment-backend`:

| Version | Supported          |
| ------- | ------------------ |
| 1.x.x   | :white_check_mark: |

## Reporting a Vulnerability

We take the security of `carllee1983/tappay-payment-backend` seriously. If you discover a security vulnerability, please follow these steps:

### 1. **Do Not** Open a Public Issue

Please do not report security vulnerabilities through public GitHub issues. This helps prevent exploitation before a fix is available.

### 2. Report Privately

Send your vulnerability report to:

- **GitHub Security Advisories**: [Create a security advisory](https://github.com/CarlLee1983/tappay-backend-payment-php/security/advisories/new)
- **Subject**: `[SECURITY] carllee1983/tappay-payment-backend: [Brief Description]`

### 3. Include Details

Please include the following information in your report:

- **Description**: A clear description of the vulnerability
- **Impact**: The potential impact and severity
- **Steps to Reproduce**: Detailed steps to reproduce the issue
- **Affected Versions**: Which versions are affected
- **Suggested Fix**: If you have a suggestion for fixing the issue (optional)
- **Proof of Concept**: Any code or examples demonstrating the vulnerability (optional)

### 4. What to Expect

- **Acknowledgment**: We will acknowledge receipt of your vulnerability report within 48 hours
- **Updates**: We will keep you informed about the progress of addressing the vulnerability
- **Timeline**: We aim to release a fix within 30 days for critical vulnerabilities
- **Credit**: We will credit you (if desired) in the security advisory and release notes

### 5. Security Best Practices

When using `carllee1983/tappay-payment-backend`, please follow these security best practices:

#### Partner Key Protection
```php
// ❌ DON'T: Hard-code secrets
$config = new ClientConfig(
    partnerKey: 'partner_key_12345',  // Never hard-code!
    merchantId: 'merchant_id'
);

// ✅ DO: Use environment variables
$config = new ClientConfig(
    partnerKey: getenv('TAPPAY_PARTNER_KEY'),
    merchantId: getenv('TAPPAY_MERCHANT_ID')
);
```

#### Transaction Verification
Always verify transaction status from TapPay backend, never trust frontend data:

```php
use TapPay\Payment\Dto\RecordQueryRequest;

// Verify transaction after receiving frontend callback
$records = $client->queryRecords(new RecordQueryRequest(
    filters: ['rec_trade_id' => $transactionId]
));

if ($records->tradeRecords[0]['status'] !== 0) {
    throw new Exception('Transaction verification failed');
}
```

#### HTTPS Only
Always use HTTPS for TapPay API communications. The SDK uses HTTPS endpoints by default.

#### Keep Dependencies Updated
Regularly update `carllee1983/tappay-payment-backend` and its dependencies to receive security patches:

```bash
composer update carllee1983/tappay-payment-backend
```

## Security Features

`carllee1983/tappay-payment-backend` includes the following security features:

### 1. API Key Validation
Validates x-api-key header for authentication with TapPay servers.

### 2. Input Validation
- Required field validation
- Amount validation (must be positive)
- Configuration parameter validation
- Type-safe API with PHP 8.1+ strict types

### 3. Error Handling
Custom exception classes that don't leak sensitive information in error messages:
- `SignatureException`: For 401/403 API key errors
- `ValidationException`: For input validation errors
- `HttpException`: For HTTP-level errors

### 4. Minimal Dependencies
Only essential dependencies (ext-json) to reduce the attack surface.

## Security Updates

Security updates will be released as patch versions and announced through:

- GitHub Security Advisories
- Release notes
- Packagist package updates

## Scope

This security policy applies to:

- The `carllee1983/tappay-payment-backend` package
- Security issues in the core library code
- Security issues in the build process and distribution

This policy does **not** cover:

- Security issues in applications built using this library (unless caused by the library itself)
- Issues in TapPay's backend services
- Social engineering attacks

## Additional Resources

- [TapPay Backend Integration Documentation](https://docs.tappaysdk.com/tutorial/zh/back.html)
- [PHP Security Best Practices](https://www.php.net/manual/en/security.php)
- [Composer Security Advisories](https://packagist.org/advisories)

## Questions?

If you have questions about this security policy, please open a GitHub issue (for non-security questions) or contact the maintainers directly.

---

**Last Updated**: December 13, 2024
