<?php

declare(strict_types=1);

namespace TapPay\Payment\Enum;

enum Currency: string
{
    case TWD = 'TWD';
    case USD = 'USD';
    case JPY = 'JPY';
    case EUR = 'EUR';
    case GBP = 'GBP';
    case KRW = 'KRW';
    case VND = 'VND';

    public static function normalize(self|string $currency): string
    {
        if ($currency instanceof self) {
            return $currency->value;
        }

        return strtoupper($currency);
    }
}

