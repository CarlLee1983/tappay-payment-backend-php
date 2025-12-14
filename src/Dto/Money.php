<?php

declare(strict_types=1);

namespace TapPay\Payment\Dto;

use TapPay\Payment\Exception\ValidationException;

/**
 * Money value object for handling amounts with currency.
 *
 * Automatically handles currency-specific formatting for TapPay API:
 * - TWD: No conversion needed (integer amounts)
 * - Non-TWD (USD, JPY, etc.): Multiplied by 100 for API submission
 */
final class Money
{
    /**
     * Currencies that don't have decimal places.
     */
    private const ZERO_DECIMAL_CURRENCIES = ['JPY', 'KRW', 'VND'];

    /**
     * @param int|float $amount The amount in the currency's natural unit.
     * @param string $currency ISO 4217 currency code (default: TWD).
     */
    public function __construct(
        private readonly int|float $amount,
        private readonly string $currency = 'TWD'
    ) {
        if ($this->amount < 0) {
            throw new ValidationException('Amount cannot be negative.');
        }
    }

    /**
     * Create a Money instance in TWD.
     */
    public static function TWD(int $amount): self
    {
        return new self($amount, 'TWD');
    }

    /**
     * Create a Money instance in USD.
     *
     * @param float|int $amount Amount in dollars (e.g., 1.50 for $1.50).
     */
    public static function USD(float|int $amount): self
    {
        return new self($amount, 'USD');
    }

    /**
     * Create a Money instance in JPY (no decimal places).
     */
    public static function JPY(int $amount): self
    {
        return new self($amount, 'JPY');
    }

    /**
     * Create a Money instance in EUR.
     *
     * @param float|int $amount Amount in euros (e.g., 1.50 for €1.50).
     */
    public static function EUR(float|int $amount): self
    {
        return new self($amount, 'EUR');
    }

    /**
     * Create a Money instance in any currency.
     *
     * @param float|int $amount Amount in the currency's natural unit.
     * @param string $currency ISO 4217 currency code.
     */
    public static function of(float|int $amount, string $currency): self
    {
        return new self($amount, strtoupper($currency));
    }

    /**
     * Get the original amount.
     */
    public function getAmount(): int|float
    {
        return $this->amount;
    }

    /**
     * Get the currency code.
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Get the amount formatted for TapPay API.
     *
     * TapPay requires non-TWD currencies to be multiplied by 100.
     * Zero-decimal currencies (JPY, KRW, VND) are passed as-is.
     */
    public function toApiAmount(): int
    {
        // TWD and zero-decimal currencies don't need conversion
        if ($this->currency === 'TWD' || in_array($this->currency, self::ZERO_DECIMAL_CURRENCIES, true)) {
            return (int) $this->amount;
        }

        // Other currencies need to be multiplied by 100
        return (int) round($this->amount * 100);
    }

    /**
     * Check if this is a zero amount.
     */
    public function isZero(): bool
    {
        return $this->amount == 0;
    }

    /**
     * Check if this is a positive amount.
     */
    public function isPositive(): bool
    {
        return $this->amount > 0;
    }

    /**
     * Validate that the amount is greater than zero.
     *
     * @throws ValidationException If the amount is zero or negative.
     */
    public function ensurePositive(): void
    {
        if (!$this->isPositive()) {
            throw new ValidationException('Amount must be greater than zero.');
        }
    }

    /**
     * Format the amount for display.
     */
    public function format(): string
    {
        // TWD and zero-decimal currencies show as integers
        if ($this->currency === 'TWD' || in_array($this->currency, self::ZERO_DECIMAL_CURRENCIES, true)) {
            return sprintf('%s %d', $this->currency, (int) $this->amount);
        }

        return sprintf('%s %.2f', $this->currency, $this->amount);
    }

    /**
     * String representation.
     */
    public function __toString(): string
    {
        return $this->format();
    }
}
