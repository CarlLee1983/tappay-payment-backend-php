<?php

declare(strict_types=1);

namespace TapPay\Payment\Dto;

use TapPay\Payment\ClientConfig;
use TapPay\Payment\Exception\ValidationException;

/**
 * Request payload for the "Pay by Prime" API.
 */
final class PrimePaymentRequest
{
    private readonly int $resolvedAmount;
    private readonly string $resolvedCurrency;

    /**
     * @param string $prime The prime string obtained from the frontend.
     * @param int|Money $amount Payment amount (int for TWD, or Money for other currencies).
     * @param string|null $currency Currency code (default: 'TWD'). Ignored if $amount is Money.
     * @param string|null $details Transaction details/description.
     * @param string|null $orderNumber Merchant's order number.
     * @param string|null $bankTransactionId Bank transaction ID (if any).
     * @param Cardholder|array<string, mixed>|null $cardholder Cardholder information.
     * @param bool|null $remember Whether to remember the card (create a card token).
     * @param int|null $instalment Number of installments (0 or null for one-time payment).
     * @param int|null $delayCaptureInDays Number of days to delay capture (0 for immediate).
     * @param bool|null $threeDomainSecure Whether to enable 3D Secure.
     * @param ResultUrl|array<string, mixed>|null $resultUrl Result URL for 3D Secure or other callbacks.
     * @param string|null $merchantId Optional merchant ID override.
     * @param string|null $partnerKey Optional partner key override.
     */
    public function __construct(
        public readonly string $prime,
        public readonly int|Money $amount,
        public readonly ?string $currency = null,
        public readonly ?string $details = null,
        public readonly ?string $orderNumber = null,
        public readonly ?string $bankTransactionId = null,
        public readonly Cardholder|array|null $cardholder = null,
        public readonly ?bool $remember = null,
        public readonly ?int $instalment = null,
        public readonly ?int $delayCaptureInDays = null,
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
     * @param string $prime The prime string obtained from the frontend.
     * @param Money $money The payment amount with currency.
     * @param string|null $details Transaction details/description.
     * @param string|null $orderNumber Merchant's order number.
     * @param Cardholder|null $cardholder Cardholder information.
     * @param bool|null $remember Whether to remember the card.
     * @param ResultUrl|null $resultUrl Result URL for 3D Secure.
     * @param bool|null $threeDomainSecure Whether to enable 3D Secure.
     */
    public static function withMoney(
        string $prime,
        Money $money,
        ?string $details = null,
        ?string $orderNumber = null,
        ?Cardholder $cardholder = null,
        ?bool $remember = null,
        ?ResultUrl $resultUrl = null,
        ?bool $threeDomainSecure = null
    ): self {
        return new self(
            prime: $prime,
            amount: $money,
            details: $details,
            orderNumber: $orderNumber,
            cardholder: $cardholder,
            remember: $remember,
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

        if ($this->prime === '') {
            throw new ValidationException('Prime is required.');
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

        // Resolve cardholder to payload format
        $cardholderPayload = $this->resolveCardholder();

        // Resolve result URL to payload format
        $resultUrlPayload = Payload::resolveResultUrl($this->resultUrl);
        Payload::validateThreeDomainSecure($this->threeDomainSecure, $resultUrlPayload);

        $payload = [
            'prime' => $this->prime,
            'partner_key' => $partnerKey,
            'merchant_id' => $merchantId,
            'amount' => $this->resolvedAmount,
            'currency' => $this->resolvedCurrency,
            'details' => $this->details,
            'order_number' => $this->orderNumber,
            'bank_transaction_id' => $this->bankTransactionId,
            'cardholder' => $cardholderPayload,
            'remember' => $this->remember,
            'instalment' => $this->instalment,
            'delay_capture_in_days' => $this->delayCaptureInDays,
            'three_domain_secure' => $this->threeDomainSecure,
            'result_url' => $resultUrlPayload,
        ];

        return Payload::filter($payload);
    }

    /**
     * Resolve cardholder to API payload format.
     *
     * @return array<string, string>|null
     */
    private function resolveCardholder(): ?array
    {
        if ($this->cardholder === null) {
            return null;
        }

        if ($this->cardholder instanceof Cardholder) {
            return $this->cardholder->toPayload();
        }

        // Legacy array format - convert to Cardholder and back
        return Cardholder::fromArray($this->cardholder)->toPayload();
    }

    // Result URL resolution lives in Payload::resolveResultUrl().
}
