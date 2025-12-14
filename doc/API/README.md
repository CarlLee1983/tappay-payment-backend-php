## TapPay Payment PHP Client – Library API Overview

This document describes the public API of this library (DTOs, client, and HTTP adapters).

### TapPayClient

Create the client with a `ClientConfig`, and optionally inject an HTTP client:

```php
use TapPay\Payment\ClientConfig;
use TapPay\Payment\TapPayClient;

$client = new TapPayClient(new ClientConfig(
    partnerKey: getenv('TAPPAY_PARTNER_KEY'),
    merchantId: getenv('TAPPAY_MERCHANT_ID'),
    baseUri: 'https://sandbox.tappaysdk.com'
));
```

Supported operations:

- `payByPrime(Dto\PrimePaymentRequest): Dto\PaymentResponse`
- `payByToken(Dto\TokenPaymentRequest): Dto\PaymentResponse`
- `refund(Dto\RefundRequest): Dto\RefundResponse`
- `queryRecords(Dto\RecordQueryRequest): Dto\RecordQueryResponse`

### Amount & Currency (`Money`)

Request DTOs accept `int|Money $amount` and `Currency|string|null $currency`.

- If you pass an `int`, the value is sent as-is to TapPay and `currency` defaults to `TWD`.
- If you pass a `Money`, the library derives both API `amount` and `currency` from it.

Example:

```php
use TapPay\Payment\Enum\Currency;
use TapPay\Payment\Dto\Money;
use TapPay\Payment\Dto\PrimePaymentRequest;

$request = new PrimePaymentRequest(
    prime: 'prime_from_frontend',
    amount: Money::USD(10.99),
    details: 'Order #12345'
);
```

If you don't want `Money`, you can also pass a `Currency` enum:

```php
$request = new PrimePaymentRequest(
    prime: 'prime_from_frontend',
    amount: 100,
    currency: Currency::TWD,
    details: 'Order #12345'
);
```

### Request Payload Rules

When building API payloads, request DTOs omit:

- `null` values
- empty strings (after trimming)
- empty arrays

### HTTP Clients

The library uses `TapPay\Payment\Http\HttpClientInterface`.

- Default: `TapPay\Payment\Http\NativeHttpClient` (stream-based)
- Optional: `TapPay\Payment\Http\CurlHttpClient` (requires `ext-curl`)
- Optional: `TapPay\Payment\Http\Psr18HttpClientAdapter` (requires PSR-18 + PSR-17 + a PSR-7 implementation)

### Exceptions

- `TapPay\Payment\Exception\ValidationException`: request DTO validation failures
- `TapPay\Payment\Exception\SignatureException`: TapPay rejected `x-api-key` (HTTP 401/403)
- `TapPay\Payment\Exception\HttpException`: transport errors, HTTP errors, or invalid responses
