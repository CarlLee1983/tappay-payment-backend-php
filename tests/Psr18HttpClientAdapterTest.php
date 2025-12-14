<?php

declare(strict_types=1);

namespace TapPay\Payment\Tests;

use PHPUnit\Framework\TestCase;
use TapPay\Payment\Exception\HttpException;
use TapPay\Payment\Http\Psr18HttpClientAdapter;

final class Psr18HttpClientAdapterTest extends TestCase
{
    public function testConstructorThrowsWhenPsrDependenciesAreMissing(): void
    {
        if (interface_exists('Psr\\Http\\Client\\ClientInterface')) {
            $this->markTestSkipped('PSR interfaces are present; this test expects missing optional dependencies.');
        }

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Missing dependency');

        new Psr18HttpClientAdapter(new \stdClass(), new \stdClass(), new \stdClass());
    }
}

