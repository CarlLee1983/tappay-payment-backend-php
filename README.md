# TapPay Payment PHP Client

[![CI](https://github.com/CarlLee1983/tappay-backend-payment-php/actions/workflows/ci.yml/badge.svg)](https://github.com/CarlLee1983/tappay-backend-payment-php/actions/workflows/ci.yml)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-8892BF.svg)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

[繁體中文](./README_ZH.md) | English

A type-safe, PSR-4 compliant PHP library for TapPay Backend Payment APIs. Supports Pay by Prime, Pay by Token, Refund, and Record Query operations with injectable HTTP client for easy testing.

## Features

- 🚀 **PHP 8.1+** with strict types and readonly properties
- 📦 **PSR-4 Autoloading** with `TapPay\Payment` namespace
- 🔌 **Injectable HTTP Client** for easy mocking and testing
- ✅ **Type-safe DTOs** for requests and responses
- 🛡️ **Comprehensive Error Handling** with custom exceptions
- 📝 **Full PHPDoc Documentation** for IDE support

## Requirements

- PHP 8.1 or higher
- ext-json

## Installation

```bash
composer require carllee1983/tappay-payment-backend
```

## Quick Start

### Basic Configuration

```php
use TapPay\Payment\ClientConfig;
use TapPay\Payment\TapPayClient;

// Sandbox environment (default)
$client = new TapPayClient(new ClientConfig(
    partnerKey: getenv('TAPPAY_PARTNER_KEY'),
    merchantId: getenv('TAPPAY_MERCHANT_ID')
));

// Production environment
$client = new TapPayClient(new ClientConfig(
    partnerKey: getenv('TAPPAY_PARTNER_KEY'),
    merchantId: getenv('TAPPAY_MERCHANT_ID'),
    baseUri: 'https://prod.tappaysdk.com'
));
```

### Pay by Prime

Process a payment using a Prime token from the TapPay frontend SDK:

```php
use TapPay\Payment\Dto\PrimePaymentRequest;

$response = $client->payByPrime(new PrimePaymentRequest(
    prime: 'prime_from_frontend',
    amount: 100,
    currency: 'TWD',
    details: 'Order #12345',
    orderNumber: 'ORDER-12345',
    cardholder: [
        'phone_number' => '+886912345678',
        'name' => 'Test User',
        'email' => 'test@example.com',
    ],
    remember: true  // Save card for future payments
));

if ($response->isSuccess()) {
    // Save rec_trade_id for refunds or queries
    $recTradeId = $response->recTradeId;
    
    // If remember=true, save card tokens for Pay by Token
    $cardKey = $response->cardSecret['card_key'] ?? null;
    $cardToken = $response->cardSecret['card_token'] ?? null;
}
```

### Pay by Token

Process a payment using saved card tokens:

```php
use TapPay\Payment\Dto\TokenPaymentRequest;

$response = $client->payByToken(new TokenPaymentRequest(
    cardKey: $savedCardKey,
    cardToken: $savedCardToken,
    amount: 200,
    currency: 'TWD',
    details: 'Subscription renewal',
    orderNumber: 'SUB-12345'
));

if ($response->isSuccess()) {
    echo "Payment successful: " . $response->recTradeId;
}
```

### Refund

Process a full or partial refund:

```php
use TapPay\Payment\Dto\RefundRequest;

// Full refund
$response = $client->refund(new RefundRequest(
    recTradeId: $transactionId
));

// Partial refund
$response = $client->refund(new RefundRequest(
    recTradeId: $transactionId,
    amount: 50  // Refund 50 out of original amount
));

if ($response->isSuccess()) {
    echo "Refund successful: " . $response->refundId;
}
```

### Query Records

Query transaction records:

```php
use TapPay\Payment\Dto\RecordQueryRequest;

$response = $client->queryRecords(new RecordQueryRequest(
    recordsPerPage: 50,
    page: 0,
    filters: [
        'time' => [
            'start_time' => strtotime('-30 days') * 1000,
            'end_time' => time() * 1000,
        ],
    ],
    orderBy: [
        'attribute' => 'time',
        'is_descending' => true,
    ]
));

foreach ($response->tradeRecords as $record) {
    echo $record['rec_trade_id'] . ': ' . $record['amount'] . "\n";
}
```

## API Reference

### TapPayClient

| Method | Description |
|--------|-------------|
| `payByPrime(PrimePaymentRequest $request)` | Process payment using Prime token |
| `payByToken(TokenPaymentRequest $request)` | Process payment using saved card tokens |
| `refund(RefundRequest $request)` | Process full or partial refund |
| `queryRecords(RecordQueryRequest $request)` | Query transaction records |

### Request DTOs

#### PrimePaymentRequest

| Property | Type | Required | Description |
|----------|------|----------|-------------|
| `prime` | string | Yes | Prime token from frontend |
| `amount` | int | Yes | Payment amount |
| `currency` | string | No | Currency code (default: TWD) |
| `details` | string | No | Transaction description |
| `orderNumber` | string | No | Merchant order number |
| `cardholder` | array | No | Cardholder information |
| `remember` | bool | No | Save card for future use |
| `instalment` | int | No | Instalment period |
| `threeDomainSecure` | bool | No | Enable 3D Secure |
| `resultUrl` | array | No | 3D Secure result URLs |

#### TokenPaymentRequest

| Property | Type | Required | Description |
|----------|------|----------|-------------|
| `cardKey` | string | Yes | Card key from previous payment |
| `cardToken` | string | Yes | Card token from previous payment |
| `amount` | int | Yes | Payment amount |
| `currency` | string | Yes | Currency code |
| `details` | string | No | Transaction description |
| `orderNumber` | string | No | Merchant order number |

### Exceptions

| Exception | Description |
|-----------|-------------|
| `TapPayException` | Base exception class |
| `SignatureException` | Invalid API key (401/403) |
| `ValidationException` | Input validation errors |
| `HttpException` | HTTP-level errors |

## Error Handling

```php
use TapPay\Payment\Exception\SignatureException;
use TapPay\Payment\Exception\ValidationException;
use TapPay\Payment\Exception\HttpException;

try {
    $response = $client->payByPrime($request);
} catch (SignatureException $e) {
    // Invalid partner key
    error_log('API key error: ' . $e->getMessage());
} catch (ValidationException $e) {
    // Missing required fields
    error_log('Validation error: ' . $e->getMessage());
} catch (HttpException $e) {
    // TapPay service unavailable
    error_log('HTTP error: ' . $e->getMessage());
}
```

## Testing

This library includes an injectable HTTP client interface for easy testing:

```php
use TapPay\Payment\Http\HttpClientInterface;
use TapPay\Payment\Http\HttpResponse;

// Create a mock HTTP client
$mockClient = new class implements HttpClientInterface {
    public function request(
        string $method,
        string $url,
        array $headers = [],
        array $body = []
    ): HttpResponse {
        return new HttpResponse(200, json_encode([
            'status' => 0,
            'msg' => 'success',
            'rec_trade_id' => 'test_trade_id',
        ]));
    }
};

// Inject mock client for testing
$client = new TapPayClient($config, $mockClient);
```

## Running Tests

```bash
composer install
composer test
```

## Documentation

For detailed API reference, see [doc/API/README.md](./doc/API/README.md).

## Contributing

Please see [CONTRIBUTING.md](./CONTRIBUTING.md) for details.

## Security

For security vulnerabilities, please see [SECURITY.md](./SECURITY.md).

## License

This project is licensed under the MIT License - see the [LICENSE](./LICENSE) file for details.

## Links

- [TapPay Official Documentation](https://docs.tappaysdk.com/tutorial/zh/back.html)
- [GitHub Repository](https://github.com/CarlLee1983/tappay-backend-payment-php)
- [Packagist](https://packagist.org/packages/carllee1983/tappay-payment-backend)
