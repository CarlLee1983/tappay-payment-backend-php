<?php

declare(strict_types=1);

namespace TapPay\Payment;

use TapPay\Payment\Dto\PaymentResponse;
use TapPay\Payment\Dto\PrimePaymentRequest;
use TapPay\Payment\Dto\RecordQueryRequest;
use TapPay\Payment\Dto\RecordQueryResponse;
use TapPay\Payment\Dto\RefundRequest;
use TapPay\Payment\Dto\RefundResponse;
use TapPay\Payment\Dto\TokenPaymentRequest;
use TapPay\Payment\Exception\HttpException;
use TapPay\Payment\Exception\SignatureException;
use TapPay\Payment\Http\HttpClientInterface;
use TapPay\Payment\Http\HttpResponse;
use TapPay\Payment\Http\NativeHttpClient;
use JsonException;

/**
 * TapPay Backend Payment API client.
 *
 * Provides high-level methods for Pay by Prime, Pay by Token, Refund, and Record Query.
 */
final class TapPayClient
{
    private HttpClientInterface $httpClient;

    public function __construct(private ClientConfig $config, ?HttpClientInterface $httpClient = null)
    {
        $this->httpClient = $httpClient ?? new NativeHttpClient();
    }

    /**
     * Process a "Pay by Prime" transaction.
     *
     * @param PrimePaymentRequest $request The request DTO containing payment details.
     * @return PaymentResponse The response DTO containing the transaction result.
     *
     * @throws \TapPay\Payment\Exception\ValidationException If the request data is invalid.
     * @throws SignatureException If the API key signature is rejected.
     * @throws HttpException If the HTTP request fails or returns an error status.
     */
    public function payByPrime(PrimePaymentRequest $request): PaymentResponse
    {
        $payload = $request->toPayload($this->config);
        $data = $this->postJson('/tpc/payment/pay-by-prime', $payload, $payload['partner_key']);

        return PaymentResponse::fromArray($data);
    }

    /**
     * Process a "Pay by Token" transaction.
     *
     * @param TokenPaymentRequest $request The request DTO containing token payment details.
     * @return PaymentResponse The response DTO containing the transaction result.
     *
     * @throws \TapPay\Payment\Exception\ValidationException If the request data is invalid.
     * @throws SignatureException If the API key signature is rejected.
     * @throws HttpException If the HTTP request fails or returns an error status.
     */
    public function payByToken(TokenPaymentRequest $request): PaymentResponse
    {
        $payload = $request->toPayload($this->config);
        $data = $this->postJson('/tpc/payment/pay-by-token', $payload, $payload['partner_key']);

        return PaymentResponse::fromArray($data);
    }

    /**
     * Process a Refund.
     *
     * @param RefundRequest $request The request DTO containing refund details.
     * @return RefundResponse The response DTO containing the refund result.
     *
     * @throws \TapPay\Payment\Exception\ValidationException If the request data is invalid.
     * @throws SignatureException If the API key signature is rejected.
     * @throws HttpException If the HTTP request fails or returns an error status.
     */
    public function refund(RefundRequest $request): RefundResponse
    {
        $payload = $request->toPayload($this->config);
        $data = $this->postJson('/tpc/transaction/refund', $payload, $payload['partner_key']);

        return RefundResponse::fromArray($data);
    }

    /**
     * Query transaction records.
     *
     * @param RecordQueryRequest $request The request DTO containing query filters.
     * @return RecordQueryResponse The response DTO containing the query results.
     *
     * @throws \TapPay\Payment\Exception\ValidationException If the request data is invalid.
     * @throws SignatureException If the API key signature is rejected.
     * @throws HttpException If the HTTP request fails or returns an error status.
     */
    public function queryRecords(RecordQueryRequest $request): RecordQueryResponse
    {
        $payload = $request->toPayload($this->config);
        $data = $this->postJson('/tpc/transaction/query', $payload, $payload['partner_key']);

        return RecordQueryResponse::fromArray($data);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function postJson(string $path, array $payload, string $partnerKey): array
    {
        $headers = [
            'Content-Type' => 'application/json',
            'x-api-key' => $partnerKey,
        ];

        $response = $this->httpClient->request(
            'POST',
            $this->config->baseUri() . $path,
            $headers,
            $payload
        );

        return $this->handleResponse($response);
    }

    /**
     * @return array<string, mixed>
     */
    private function handleResponse(HttpResponse $response): array
    {
        if (in_array($response->statusCode, [401, 403], true)) {
            throw new SignatureException('Invalid x-api-key signature.');
        }

        if ($response->statusCode >= 500) {
            throw new HttpException(
                sprintf('TapPay service unavailable (HTTP %d)', $response->statusCode),
                $response->statusCode,
                $response->body
            );
        }

        if ($response->statusCode >= 400) {
            throw new HttpException(
                sprintf('HTTP error %d returned by TapPay', $response->statusCode),
                $response->statusCode,
                $response->body
            );
        }

        try {
            /** @var mixed $decoded */
            $decoded = json_decode($response->body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new HttpException(
                sprintf('Unable to decode TapPay response: %s', $e->getMessage()),
                $response->statusCode,
                $response->body,
                $e
            );
        }

        if (!is_array($decoded)) {
            throw new HttpException(
                'Unable to decode TapPay response: decoded JSON is not an object.',
                $response->statusCode,
                $response->body
            );
        }

        return $decoded;
    }
}
