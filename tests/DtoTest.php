<?php

declare(strict_types=1);

namespace TapPay\Payment\Tests;

use PHPUnit\Framework\TestCase;
use TapPay\Payment\Dto\Cardholder;
use TapPay\Payment\Dto\Money;
use TapPay\Payment\Dto\PaymentResponse;
use TapPay\Payment\Dto\RecordQueryResponse;
use TapPay\Payment\Dto\ResultUrl;
use TapPay\Payment\Exception\ValidationException;

final class DtoTest extends TestCase
{
    // =====================
    // Cardholder Tests
    // =====================

    public function testCardholderToPayload(): void
    {
        $cardholder = new Cardholder(
            phoneNumber: '+886912345678',
            name: '王小明',
            email: 'test@example.com',
            zipCode: '100',
            address: '台北市中正區',
            nationalId: 'A123456789'
        );

        $payload = $cardholder->toPayload();

        $this->assertSame('+886912345678', $payload['phone_number']);
        $this->assertSame('王小明', $payload['name']);
        $this->assertSame('test@example.com', $payload['email']);
        $this->assertSame('100', $payload['zip_code']);
        $this->assertSame('台北市中正區', $payload['address']);
        $this->assertSame('A123456789', $payload['national_id']);
    }

    public function testCardholderFromArray(): void
    {
        $cardholder = Cardholder::fromArray([
            'phone_number' => '+886912345678',
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->assertSame('+886912345678', $cardholder->phoneNumber);
        $this->assertSame('Test User', $cardholder->name);
        $this->assertSame('test@example.com', $cardholder->email);
    }

    public function testCardholderPartialPayload(): void
    {
        $cardholder = new Cardholder(
            phoneNumber: '+886912345678',
            name: 'Test User'
        );

        $payload = $cardholder->toPayload();

        $this->assertCount(2, $payload);
        $this->assertArrayHasKey('phone_number', $payload);
        $this->assertArrayHasKey('name', $payload);
        $this->assertArrayNotHasKey('email', $payload);
    }

    public function testEmptyCardholderReturnsNull(): void
    {
        $cardholder = new Cardholder();

        $this->assertNull($cardholder->toPayload());
        $this->assertTrue($cardholder->isEmpty());
    }

    // =====================
    // ResultUrl Tests
    // =====================

    public function testResultUrlToPayload(): void
    {
        $resultUrl = new ResultUrl(
            frontendRedirectUrl: 'https://example.com/success',
            backendNotifyUrl: 'https://example.com/api/notify',
            goBackUrl: 'https://example.com/back'
        );

        $payload = $resultUrl->toPayload();

        $this->assertSame('https://example.com/success', $payload['frontend_redirect_url']);
        $this->assertSame('https://example.com/api/notify', $payload['backend_notify_url']);
        $this->assertSame('https://example.com/back', $payload['go_back_url']);
    }

    public function testResultUrlFromArray(): void
    {
        $resultUrl = ResultUrl::fromArray([
            'frontend_redirect_url' => 'https://example.com/success',
            'backend_notify_url' => 'https://example.com/api/notify',
        ]);

        $this->assertSame('https://example.com/success', $resultUrl->frontendRedirectUrl);
        $this->assertSame('https://example.com/api/notify', $resultUrl->backendNotifyUrl);
    }

    public function testResultUrlValidatesHttps(): void
    {
        $resultUrl = new ResultUrl(
            frontendRedirectUrl: 'http://example.com/success',
            backendNotifyUrl: 'https://example.com/api/notify'
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('frontend_redirect_url must use HTTPS');

        $resultUrl->validate();
    }

    public function testResultUrlFromArrayMissingRequired(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('frontend_redirect_url is required');

        ResultUrl::fromArray([
            'backend_notify_url' => 'https://example.com/api/notify',
        ]);
    }

    // =====================
    // Money Tests
    // =====================

    public function testMoneyTWD(): void
    {
        $money = Money::TWD(100);

        $this->assertSame(100, $money->toApiAmount());
        $this->assertSame('TWD', $money->getCurrency());
        $this->assertSame('TWD 100', $money->format());
    }

    public function testMoneyUSDMultipliedBy100(): void
    {
        $money = Money::USD(1.50);

        $this->assertSame(150, $money->toApiAmount());
        $this->assertSame('USD', $money->getCurrency());
        $this->assertSame('USD 1.50', $money->format());
    }

    public function testMoneyJPYNoConversion(): void
    {
        $money = Money::JPY(1000);

        $this->assertSame(1000, $money->toApiAmount());
        $this->assertSame('JPY', $money->getCurrency());
        $this->assertSame('JPY 1000', $money->format());
    }

    public function testMoneyEUR(): void
    {
        $money = Money::EUR(10.99);

        $this->assertSame(1099, $money->toApiAmount());
        $this->assertSame('EUR', $money->getCurrency());
    }

    public function testMoneyOf(): void
    {
        $money = Money::of(25.50, 'GBP');

        $this->assertSame(2550, $money->toApiAmount());
        $this->assertSame('GBP', $money->getCurrency());
    }

    public function testMoneyNegativeThrows(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Amount cannot be negative');

        Money::TWD(-100);
    }

    public function testMoneyIsZero(): void
    {
        $money = Money::TWD(0);

        $this->assertTrue($money->isZero());
        $this->assertFalse($money->isPositive());
    }

    public function testMoneyIsPositive(): void
    {
        $money = Money::TWD(100);

        $this->assertFalse($money->isZero());
        $this->assertTrue($money->isPositive());
    }

    public function testMoneyEnsurePositiveThrows(): void
    {
        $money = Money::TWD(0);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Amount must be greater than zero');

        $money->ensurePositive();
    }

    public function testMoneyToString(): void
    {
        $money = Money::USD(19.99);

        $this->assertSame('USD 19.99', (string) $money);
    }

    // =====================
    // Response DTO Resilience Tests
    // =====================

    public function testPaymentResponseFromArrayIgnoresNonArrayCardFields(): void
    {
        $response = PaymentResponse::fromArray([
            'status' => 0,
            'msg' => 'success',
            'card_secret' => 'not-an-array',
            'card_info' => 123,
        ]);

        $this->assertTrue($response->isSuccess());
        $this->assertNull($response->cardSecret);
        $this->assertNull($response->cardInfo);
    }

    public function testRecordQueryResponseFromArrayIgnoresNonArrayTradeRecords(): void
    {
        $response = RecordQueryResponse::fromArray([
            'status' => 0,
            'records_per_page' => 50,
            'page' => 0,
            'trade_records' => 'not-an-array',
        ]);

        $this->assertTrue($response->isSuccess());
        $this->assertSame([], $response->tradeRecords);
    }
}
