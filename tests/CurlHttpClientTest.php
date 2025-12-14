<?php

declare(strict_types=1);

namespace TapPay\Payment\Http;

if (!defined('CURLOPT_URL')) {
    define('CURLOPT_URL', 10002);
}
if (!defined('CURLOPT_RETURNTRANSFER')) {
    define('CURLOPT_RETURNTRANSFER', 19913);
}
if (!defined('CURLOPT_HEADER')) {
    define('CURLOPT_HEADER', 42);
}
if (!defined('CURLOPT_CUSTOMREQUEST')) {
    define('CURLOPT_CUSTOMREQUEST', 10036);
}
if (!defined('CURLOPT_HTTPHEADER')) {
    define('CURLOPT_HTTPHEADER', 10023);
}
if (!defined('CURLOPT_POSTFIELDS')) {
    define('CURLOPT_POSTFIELDS', 10015);
}
if (!defined('CURLOPT_TIMEOUT')) {
    define('CURLOPT_TIMEOUT', 13);
}
if (!defined('CURLOPT_CONNECTTIMEOUT')) {
    define('CURLOPT_CONNECTTIMEOUT', 78);
}
if (!defined('CURLINFO_RESPONSE_CODE')) {
    define('CURLINFO_RESPONSE_CODE', 2097154);
}
if (!defined('CURLINFO_HEADER_SIZE')) {
    define('CURLINFO_HEADER_SIZE', 2097163);
}

final class CurlHandleFake
{
    /** @var array<int, mixed> */
    public array $options = [];
    public bool $execOk = true;
    public int $statusCode = 200;
    public int $headerSize = 0;
    public string $raw = '';
    public int $errno = 0;
    public string $error = '';
}

/** @var CurlHandleFake|null */
$__tappay_next_curl_handle = null;

function set_next_curl_handle(CurlHandleFake $handle): void
{
    global $__tappay_next_curl_handle;
    $__tappay_next_curl_handle = $handle;
}

function curl_init(): CurlHandleFake|false
{
    global $__tappay_next_curl_handle;
    if ($__tappay_next_curl_handle !== null) {
        $handle = $__tappay_next_curl_handle;
        $__tappay_next_curl_handle = null;
        return $handle;
    }
    return new CurlHandleFake();
}

/**
 * @param array<int, mixed> $options
 */
function curl_setopt_array(CurlHandleFake $handle, array $options): bool
{
    $handle->options = $options;
    return true;
}

function curl_exec(CurlHandleFake $handle): string|false
{
    return $handle->execOk ? $handle->raw : false;
}

function curl_getinfo(CurlHandleFake $handle, int $opt): int
{
    if ($opt === CURLINFO_RESPONSE_CODE) {
        return $handle->statusCode;
    }
    if ($opt === CURLINFO_HEADER_SIZE) {
        return $handle->headerSize;
    }
    return 0;
}

function curl_errno(CurlHandleFake $handle): int
{
    return $handle->errno;
}

function curl_error(CurlHandleFake $handle): string
{
    return $handle->error;
}

function curl_close(CurlHandleFake $handle): void
{
}

namespace TapPay\Payment\Tests;

use PHPUnit\Framework\TestCase;
use TapPay\Payment\Http\CurlHandleFake;
use TapPay\Payment\Http\CurlHttpClient;
use TapPay\Payment\Exception\HttpException;

final class CurlHttpClientTest extends TestCase
{
    public function testCurlHttpClientParsesStatusHeadersAndBody(): void
    {
        $client = new CurlHttpClient(timeoutSeconds: 2.0, connectTimeoutSeconds: 1.0);

        $handle = new CurlHandleFake();
        $rawHeaders = "HTTP/1.1 200 OK\r\nContent-Type: application/json\r\nX-Test: a\r\n\r\n";
        $rawBody = "{\"ok\":true}";
        $handle->raw = $rawHeaders . $rawBody;
        $handle->headerSize = strlen($rawHeaders);
        $handle->statusCode = 200;

        \TapPay\Payment\Http\set_next_curl_handle($handle);

        $clientResponse = $client->request(
            'POST',
            'https://example.test/endpoint',
            ['Content-Type' => 'application/json', 'X-Test' => 'a'],
            ['foo' => 'bar']
        );

        $this->assertSame(200, $clientResponse->statusCode);
        $this->assertSame($rawBody, $clientResponse->body);
        $this->assertSame('application/json', $clientResponse->headers['Content-Type'] ?? null);
        $this->assertSame('a', $clientResponse->headers['X-Test'] ?? null);
        $this->assertSame('POST', $handle->options[CURLOPT_CUSTOMREQUEST] ?? null);
        $this->assertSame('https://example.test/endpoint', $handle->options[CURLOPT_URL] ?? null);
        $this->assertSame('{"foo":"bar"}', $handle->options[CURLOPT_POSTFIELDS] ?? null);
    }

    public function testCurlHttpClientThrowsOnExecFailure(): void
    {
        $client = new CurlHttpClient();

        $handle = new CurlHandleFake();
        $handle->execOk = false;
        $handle->errno = 28;
        $handle->error = 'timeout';
        \TapPay\Payment\Http\set_next_curl_handle($handle);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('cURL request failed (28): timeout');

        $client->request(
            'POST',
            'https://example.test/endpoint',
            ['Content-Type' => 'application/json'],
            ['foo' => 'bar']
        );
    }
}
