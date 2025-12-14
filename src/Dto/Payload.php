<?php

declare(strict_types=1);

namespace TapPay\Payment\Dto;

use TapPay\Payment\Enum\Currency;
use TapPay\Payment\Exception\ValidationException;

/**
 * Internal helper for building TapPay API payloads.
 */
final class Payload
{
    /**
     * @return array{amount:int, currency:string}
     */
    public static function resolveAmountAndCurrency(int|Money $amount, Currency|string|null $currency): array
    {
        if ($amount instanceof Money) {
            return [
                'amount' => $amount->toApiAmount(),
                'currency' => $amount->getCurrency(),
            ];
        }

        return [
            'amount' => $amount,
            'currency' => $currency instanceof Currency ? $currency->value : ($currency ?? 'TWD'),
        ];
    }

    /**
     * @param ResultUrl|array<string, mixed>|null $resultUrl
     * @return array<string, string>|null
     *
     * @throws ValidationException
     */
    public static function resolveResultUrl(ResultUrl|array|null $resultUrl): ?array
    {
        if ($resultUrl === null) {
            return null;
        }

        if ($resultUrl instanceof ResultUrl) {
            $resultUrl->validate();
            return $resultUrl->toPayload();
        }

        $resolved = ResultUrl::fromArray($resultUrl);
        $resolved->validate();
        return $resolved->toPayload();
    }

    /**
     * @throws ValidationException
     */
    public static function validateThreeDomainSecure(?bool $threeDomainSecure, ?array $resultUrlPayload): void
    {
        if ($threeDomainSecure === true && $resultUrlPayload === null) {
            throw new ValidationException('result_url is required when three_domain_secure is enabled.');
        }
    }

    /**
     * Filters out nulls, empty strings, and empty arrays.
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public static function filter(array $payload): array
    {
        return array_filter($payload, static function ($value): bool {
            if ($value === null) {
                return false;
            }

            if (is_string($value)) {
                return trim($value) !== '';
            }

            if (is_array($value)) {
                return $value !== [];
            }

            return true;
        });
    }
}
