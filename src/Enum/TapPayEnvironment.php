<?php

declare(strict_types=1);

namespace TapPay\Payment\Enum;

enum TapPayEnvironment: string
{
    case Sandbox = 'sandbox';
    case Production = 'production';

    public function baseUri(): string
    {
        return match ($this) {
            self::Sandbox => 'https://sandbox.tappaysdk.com',
            self::Production => 'https://prod.tappaysdk.com',
        };
    }
}

