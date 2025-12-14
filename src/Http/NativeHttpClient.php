<?php

declare(strict_types=1);

namespace TapPay\Payment\Http;

use TapPay\Payment\Exception\HttpException;

final class NativeHttpClient implements HttpClientInterface
{
    public function __construct(private float $timeoutSeconds = 10.0)
    {
    }

    /**
     * @inheritDoc
     *
     * @throws HttpException If the HTTP request fails or the URL is unreachable.
     * @throws \JsonException If the body cannot be encoded.
     */
    public function request(string $method, string $url, array $headers = [], array $body = []): HttpResponse
    {
        $context = stream_context_create([
            'http' => [
                'method' => strtoupper($method),
                'header' => $this->formatHeaders($headers),
                'content' => $body === [] ? '' : json_encode($body, JSON_THROW_ON_ERROR),
                'ignore_errors' => true,
                'timeout' => $this->timeoutSeconds,
            ],
        ]);

        $raw = @file_get_contents($url, false, $context);

        if ($raw === false) {
            throw new HttpException(sprintf('Failed to connect to %s', $url));
        }

        $responseHeaders = $this->normalizeHeaders($http_response_header ?? []);

        return new HttpResponse(
            $this->extractStatusCode($http_response_header ?? []),
            $raw,
            $responseHeaders
        );
    }

    /**
     * @param array<int, string> $headers
     * @return array<string, string>
     */
    private function normalizeHeaders(array $headers): array
    {
        $normalized = [];
        foreach ($headers as $headerLine) {
            if (str_contains($headerLine, ':')) {
                [$name, $value] = explode(':', $headerLine, 2);
                $normalized[trim($name)] = trim($value);
            }
        }

        return $normalized;
    }

    /**
     * @param array<int, string> $headers
     */
    private function extractStatusCode(array $headers): int
    {
        foreach ($headers as $headerLine) {
            if (preg_match('/HTTP\\/\\d\\.\\d\\s+(\\d{3})/', $headerLine, $matches)) {
                return (int) $matches[1];
            }
        }

        return 0;
    }

    /**
     * @param array<string, string|string[]> $headers
     */
    private function formatHeaders(array $headers): string
    {
        $formatted = [];
        foreach ($headers as $name => $values) {
            foreach ((array) $values as $value) {
                $formatted[] = sprintf('%s: %s', $name, $value);
            }
        }

        return implode("\r\n", $formatted);
    }
}
