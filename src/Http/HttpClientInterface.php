<?php

declare(strict_types=1);

namespace TapPay\Payment\Http;

interface HttpClientInterface
{
    /**
     * Perform an HTTP request.
     *
     * @param string $method HTTP method (GET, POST, etc.).
     * @param string $url Target URL.
     * @param array<string, string|string[]> $headers HTTP headers.
     * @param array<string, mixed> $body JSON body data.
     * @return HttpResponse The HTTP response.
     *
     * @throws \TapPay\Payment\Exception\HttpException If the connection fails.
     */
    public function request(string $method, string $url, array $headers = [], array $body = []): HttpResponse;
}
