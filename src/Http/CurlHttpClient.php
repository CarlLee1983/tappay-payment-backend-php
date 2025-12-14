<?php

declare(strict_types=1);

namespace TapPay\Payment\Http;

use TapPay\Payment\Exception\HttpException;

/**
 * cURL-based HTTP client implementation.
 *
 * Optional alternative to NativeHttpClient for environments where ext-curl is available.
 */
final class CurlHttpClient implements HttpClientInterface
{
    public function __construct(
        private float $timeoutSeconds = 10.0,
        private float $connectTimeoutSeconds = 5.0
    ) {
        if (
            !function_exists(__NAMESPACE__ . '\\curl_init')
            && !function_exists('curl_init')
        ) {
            throw new HttpException('ext-curl is required to use CurlHttpClient.');
        }
    }

    /**
     * @inheritDoc
     *
     * @throws HttpException If the HTTP request fails.
     * @throws \JsonException If the body cannot be encoded.
     */
    public function request(string $method, string $url, array $headers = [], array $body = []): HttpResponse
    {
        $handle = curl_init();

        if ($handle === false) {
            throw new HttpException('Failed to initialize cURL.');
        }

        $payload = $body === [] ? '' : json_encode($body, JSON_THROW_ON_ERROR);

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER => $this->formatHeaders($headers),
            CURLOPT_TIMEOUT => (int) ceil($this->timeoutSeconds),
            CURLOPT_CONNECTTIMEOUT => (int) ceil($this->connectTimeoutSeconds),
        ];

        if ($payload !== '') {
            $options[CURLOPT_POSTFIELDS] = $payload;
        }

        curl_setopt_array($handle, $options);

        $raw = curl_exec($handle);

        if ($raw === false) {
            $errno = curl_errno($handle);
            $error = curl_error($handle);
            curl_close($handle);
            throw new HttpException(sprintf('cURL request failed (%d): %s', $errno, $error));
        }

        $statusCode = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $headerSize = (int) curl_getinfo($handle, CURLINFO_HEADER_SIZE);
        curl_close($handle);

        $rawHeaders = substr($raw, 0, $headerSize);
        $bodyText = substr($raw, $headerSize);

        return new HttpResponse(
            $statusCode,
            $bodyText,
            $this->parseHeaders($rawHeaders)
        );
    }

    /**
     * @param array<string, string|string[]> $headers
     * @return list<string>
     */
    private function formatHeaders(array $headers): array
    {
        $formatted = [];
        foreach ($headers as $name => $values) {
            foreach ((array) $values as $value) {
                $formatted[] = sprintf('%s: %s', $name, $value);
            }
        }

        return $formatted;
    }

    /**
     * @return array<string, string>
     */
    private function parseHeaders(string $rawHeaders): array
    {
        $normalized = [];

        $lines = preg_split("/\\r\\n|\\n|\\r/", trim($rawHeaders)) ?: [];
        foreach ($lines as $line) {
            if (!str_contains($line, ':')) {
                continue;
            }

            [$name, $value] = explode(':', $line, 2);
            $normalized[trim($name)] = trim($value);
        }

        return $normalized;
    }
}

