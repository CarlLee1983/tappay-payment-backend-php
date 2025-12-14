<?php

declare(strict_types=1);

namespace TapPay\Payment\Tests;

use PHPUnit\Framework\TestCase;
use TapPay\Payment\ClientConfig;
use TapPay\Payment\Dto\PrimePaymentRequest;
use TapPay\Payment\Exception\SignatureException;
use TapPay\Payment\Http\HttpClientInterface;
use TapPay\Payment\Http\HttpResponse;
use TapPay\Payment\TapPayClient;

final class TapPayClientTest extends TestCase
{
    public function testPayByPrimeSuccess(): void
    {
        $response = new HttpResponse(200, json_encode([
            'status' => 0,
            'msg' => 'success',
            'rec_trade_id' => 'tr_test_success',
            'bank_transaction_id' => 'bank_123',
            'amount' => 100,
            'currency' => 'TWD',
        ], JSON_THROW_ON_ERROR));

        $client = $this->makeClient($response, $captured);

        $result = $client->payByPrime(new PrimePaymentRequest(
            prime: 'prime_token',
            amount: 100,
            details: 'TapPay Test',
            remember: true
        ));

        $this->assertTrue($result->isSuccess());
        $this->assertSame('tr_test_success', $result->recTradeId);
        $this->assertSame('https://sandbox.tappaysdk.com/tpc/payment/pay-by-prime', $captured['url']);
        $this->assertSame('pk_test', $captured['headers']['x-api-key'] ?? null);
        $this->assertSame(100, $captured['body']['amount']);
        $this->assertSame('merchantA', $captured['body']['merchant_id']);
    }

    public function testPayByPrimeApiFailureIsReturned(): void
    {
        $response = new HttpResponse(200, json_encode([
            'status' => 1001,
            'msg' => 'Invalid prime',
        ], JSON_THROW_ON_ERROR));

        $client = $this->makeClient($response, $captured);
        $result = $client->payByPrime(new PrimePaymentRequest(
            prime: 'bad_prime',
            amount: 100
        ));

        $this->assertFalse($result->isSuccess());
        $this->assertSame(1001, $result->status);
        $this->assertSame('Invalid prime', $result->msg);
        $this->assertSame('bad_prime', $captured['body']['prime']);
    }

    public function testSignatureErrorThrowsException(): void
    {
        $response = new HttpResponse(401, json_encode([
            'status' => 401,
            'msg' => 'Invalid API key',
        ], JSON_THROW_ON_ERROR));

        $client = $this->makeClient($response);

        $this->expectException(SignatureException::class);
        $client->payByPrime(new PrimePaymentRequest(
            prime: 'prime_token',
            amount: 100
        ));
    }

    /**
     * @param array<string, mixed>|null $captured
     */
    private function makeClient(HttpResponse $response, ?array &$captured = null): TapPayClient
    {
        $captured = [
            'method' => null,
            'url' => null,
            'headers' => [],
            'body' => [],
        ];

        $httpClient = new class($response, $captured) implements HttpClientInterface {
            private HttpResponse $response;
            /** @var array<string, mixed> */
            private array $captured;

            /**
             * @param array<string, mixed> $captured
             */
            public function __construct(HttpResponse $response, array &$captured)
            {
                $this->response = $response;
                $this->captured = &$captured;
            }

            public function request(string $method, string $url, array $headers = [], array $body = []): HttpResponse
            {
                $this->captured = [
                    'method' => $method,
                    'url' => $url,
                    'headers' => $headers,
                    'body' => $body,
                ];

                return $this->response;
            }
        };

        return new TapPayClient(
            new ClientConfig(
                partnerKey: 'pk_test',
                merchantId: 'merchantA'
            ),
            $httpClient
        );
    }
}
