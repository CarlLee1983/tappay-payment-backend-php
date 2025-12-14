<?php

declare(strict_types=1);

namespace TapPay\Payment\Dto;

use TapPay\Payment\ClientConfig;
use TapPay\Payment\Enum\Currency;
use TapPay\Payment\Exception\ValidationException;

/**
 * Request payload for the "Pay by Token" API.
 */
final class TokenPaymentRequest
{
    private readonly int $resolvedAmount;
    private readonly string $resolvedCurrency;

    /**
     * @param string $cardKey The card key (obtained from Pay by Prime with remember: true).
     * @param string $cardToken The card token (obtained from Pay by Prime with remember: true).
     * @param int|Money $amount Payment amount (int for TWD, or Money for other currencies).
     * @param Currency|string|null $currency Currency code (default: 'TWD'). Ignored if $amount is Money.
     * @param string|null $details Transaction details/description.
     * @param string|null $orderNumber Merchant's order number.
     * @param string|null $bankTransactionId Bank transaction ID (if any).
     * @param bool|null $threeDomainSecure Whether to enable 3D Secure.
     * @param ResultUrl|array<string, mixed>|null $resultUrl Result URL for 3D Secure or other callbacks.
     * @param string|null $merchantId Optional merchant ID override.
     * @param string|null $partnerKey Optional partner key override.
     */
    public function __construct(
        public readonly string $cardKey,
        public readonly string $cardToken,
        public readonly int|Money $amount,
        public readonly Currency|string|null $currency = null,
        public readonly ?string $details = null,
        public readonly ?string $orderNumber = null,
        public readonly ?string $bankTransactionId = null,
        public readonly ?bool $threeDomainSecure = null,
        public readonly ResultUrl|array|null $resultUrl = null,
        public readonly ?string $merchantId = null,
        public readonly ?string $partnerKey = null
    ) {
        $resolved = Payload::resolveAmountAndCurrency($amount, $currency);
        $this->resolvedAmount = $resolved['amount'];
        $this->resolvedCurrency = $resolved['currency'];
    }

    /**
     * Create a request using the Money value object.
     *
     * @param string $cardKey The card key.
     * @param string $cardToken The card token.
     * @param Money $money The payment amount with currency.
     * @param string|null $details Transaction details/description.
     * @param string|null $orderNumber Merchant's order number.
     * @param ResultUrl|null $resultUrl Result URL for 3D Secure.
     * @param bool|null $threeDomainSecure Whether to enable 3D Secure.
     */
    public static function withMoney(
        string $cardKey,
        string $cardToken,
        Money $money,
        ?string $details = null,
        ?string $orderNumber = null,
        ?ResultUrl $resultUrl = null,
        ?bool $threeDomainSecure = null
    ): self {
        return new self(
            cardKey: $cardKey,
            cardToken: $cardToken,
            amount: $money,
            details: $details,
            orderNumber: $orderNumber,
            resultUrl: $resultUrl,
            threeDomainSecure: $threeDomainSecure
        );
    }

    /**
     * Convert the request to an API payload array.
     *
     * @param ClientConfig $config The client configuration to use for defaults.
     * @return array<string, mixed> The payload for the API request.
     *
     * @throws ValidationException If required fields are missing or invalid.
     */
    public function toPayload(ClientConfig $config): array
    {
        $partnerKey = $this->partnerKey ?? $config->partnerKey();
        $merchantId = $this->merchantId ?? $config->merchantId();

        if ($this->cardKey === '') {
            throw new ValidationException('Card key is required.');
        }

        if ($this->cardToken === '') {
            throw new ValidationException('Card token is required.');
        }

        if ($partnerKey === '') {
            throw new ValidationException('Partner key is required.');
        }

        if ($merchantId === '') {
            throw new ValidationException('Merchant ID is required.');
        }

        if ($this->resolvedAmount <= 0) {
            throw new ValidationException('Amount must be greater than zero.');
        }

        $resultUrlPayload = Payload::resolveResultUrl($this->resultUrl);
        Payload::validateThreeDomainSecure($this->threeDomainSecure, $resultUrlPayload);

        $payload = [
            'card_key' => $this->cardKey,
            'card_token' => $this->cardToken,
            'partner_key' => $partnerKey,
            'merchant_id' => $merchantId,
            'amount' => $this->resolvedAmount,
            'currency' => $this->resolvedCurrency,
            'details' => $this->details,
            'order_number' => $this->orderNumber,
            'bank_transaction_id' => $this->bankTransactionId,
            'three_domain_secure' => $this->threeDomainSecure,
            'result_url' => $resultUrlPayload,
        ];

        return Payload::filter($payload);
    }
    // Result URL resolution lives in Payload::resolveResultUrl().
}
