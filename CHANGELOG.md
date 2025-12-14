# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.1.0] - 2025-12-14

### Added

- `CurlHttpClient` (requires `ext-curl`) and `Psr18HttpClientAdapter` (optional PSR-18/PSR-17 support)
- `TapPayEnvironment` and `Currency` enums for clearer, safer configuration and currency handling
- `Payload` helper to centralize request payload building and filtering
- GitHub Actions workflow to auto-create SemVer tags from PR merges

### Changed

- Improved JSON decode errors and HTTP connection error messages
- Standardized request payload filtering across DTOs (filters `null`, empty strings, and empty arrays)

## [1.0.0] - 2024-12-13

### Added

- Initial release of `carllee1983/tappay-payment-backend`
- **TapPayClient**: Main client class for TapPay API integration
  - `payByPrime()`: Pay using a Prime token from frontend SDK
  - `payByToken()`: Pay using saved card tokens (card_key + card_token)
  - `refund()`: Process full or partial refunds
  - `queryRecords()`: Query transaction records
- **ClientConfig**: Type-safe configuration management
  - Support for Sandbox and Production environments
  - Configurable Partner Key, Merchant ID, and Base URI
- **Request DTOs**:
  - `PrimePaymentRequest`: Request for Pay by Prime API
  - `TokenPaymentRequest`: Request for Pay by Token API
  - `RefundRequest`: Request for Refund API
  - `RecordQueryRequest`: Request for Record Query API
- **Response DTOs**:
  - `PaymentResponse`: Response from payment APIs
  - `RefundResponse`: Response from refund API
  - `RecordQueryResponse`: Response from record query API
- **Exception Classes**:
  - `TapPayException`: Base exception class
  - `HttpException`: HTTP-level errors
  - `SignatureException`: Invalid API key errors (401/403)
  - `ValidationException`: Input validation errors
- **HTTP Abstraction**:
  - `HttpClientInterface`: Injectable HTTP client interface
  - `NativeHttpClient`: Default implementation using file_get_contents
  - `HttpResponse`: HTTP response wrapper
- Comprehensive test suite using PHPUnit
- Full PHPDoc documentation for all public APIs

### Security

- API key validation for TapPay authentication
- Input validation for required fields
- Type-safe implementation with PHP 8.1+ strict types

[Unreleased]: https://github.com/CarlLee1983/tappay-backend-payment-php/compare/v1.1.0...HEAD
[1.1.0]: https://github.com/CarlLee1983/tappay-backend-payment-php/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/CarlLee1983/tappay-backend-payment-php/releases/tag/v1.0.0
