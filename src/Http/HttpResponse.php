<?php

declare(strict_types=1);

namespace TapPay\Payment\Http;

/**
 * Minimal HTTP response value object used by the client.
 */
final class HttpResponse
{
    /**
     * @param int $statusCode HTTP status code.
     * @param string $body Response body content.
     * @param array<string, string> $headers Response headers.
     */
    public function __construct(
        public readonly int $statusCode,
        public readonly string $body,
        public readonly array $headers = []
    ) {
    }
}
