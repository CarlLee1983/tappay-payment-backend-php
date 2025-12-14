<?php

declare(strict_types=1);

namespace TapPay\Payment\Http;

use TapPay\Payment\Exception\HttpException;

/**
 * Optional PSR-18 adapter.
 *
 * This class does not require PSR packages at install-time, but will throw at runtime
 * unless the following packages are present:
 * - psr/http-client
 * - psr/http-factory
 * - a PSR-7 implementation (e.g. nyholm/psr7)
 */
final class Psr18HttpClientAdapter implements HttpClientInterface
{
    private object $client;
    private object $requestFactory;
    private object $streamFactory;

    public function __construct(object $client, object $requestFactory, object $streamFactory)
    {
        $this->assertDependencies();

        if (!$client instanceof \Psr\Http\Client\ClientInterface) {
            throw new HttpException('PSR-18 client must implement Psr\\Http\\Client\\ClientInterface.');
        }

        if (!$requestFactory instanceof \Psr\Http\Message\RequestFactoryInterface) {
            throw new HttpException('Request factory must implement Psr\\Http\\Message\\RequestFactoryInterface.');
        }

        if (!$streamFactory instanceof \Psr\Http\Message\StreamFactoryInterface) {
            throw new HttpException('Stream factory must implement Psr\\Http\\Message\\StreamFactoryInterface.');
        }

        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
    }

    /**
     * @inheritDoc
     *
     * @throws HttpException If the HTTP request fails.
     * @throws \JsonException If the body cannot be encoded.
     */
    public function request(string $method, string $url, array $headers = [], array $body = []): HttpResponse
    {
        /** @var \Psr\Http\Message\RequestFactoryInterface $requestFactory */
        $requestFactory = $this->requestFactory;
        /** @var \Psr\Http\Message\StreamFactoryInterface $streamFactory */
        $streamFactory = $this->streamFactory;

        $request = $requestFactory->createRequest(strtoupper($method), $url);

        foreach ($headers as $name => $values) {
            $request = $request->withHeader($name, $values);
        }

        if ($body !== []) {
            $payload = json_encode($body, JSON_THROW_ON_ERROR);
            $stream = $streamFactory->createStream($payload);
            $request = $request->withBody($stream);
        }

        try {
            /** @var \Psr\Http\Client\ClientInterface $client */
            $client = $this->client;
            $response = $client->sendRequest($request);
        } catch (\Psr\Http\Client\ClientExceptionInterface $e) {
            throw new HttpException(sprintf('PSR-18 request failed: %s', $e->getMessage()), 0, null, $e);
        }

        $headersOut = [];
        foreach ($response->getHeaders() as $name => $values) {
            $headersOut[$name] = implode(', ', $values);
        }

        return new HttpResponse(
            $response->getStatusCode(),
            (string) $response->getBody(),
            $headersOut
        );
    }

    private function assertDependencies(): void
    {
        if (!interface_exists('Psr\\Http\\Client\\ClientInterface')) {
            throw new HttpException('Missing dependency: psr/http-client.');
        }

        if (!interface_exists('Psr\\Http\\Client\\ClientExceptionInterface')) {
            throw new HttpException('Missing dependency: psr/http-client.');
        }

        if (!interface_exists('Psr\\Http\\Message\\RequestFactoryInterface')) {
            throw new HttpException('Missing dependency: psr/http-factory.');
        }

        if (!interface_exists('Psr\\Http\\Message\\StreamFactoryInterface')) {
            throw new HttpException('Missing dependency: psr/http-factory.');
        }
    }
}

