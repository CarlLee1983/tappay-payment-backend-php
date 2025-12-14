<?php

declare(strict_types=1);

namespace TapPay\Payment\Dto;

use TapPay\Payment\ClientConfig;
use TapPay\Payment\Exception\ValidationException;

/**
 * Request payload for the "Refund" API.
 */
final class RefundRequest
{
    /**
     * @param string $recTradeId The TapPay transaction ID (rec_trade_id) to refund.
     * @param int|null $amount Refund amount (null for full refund).
     * @param string|null $bankRefundId Bank refund ID (optional).
     * @param string|null $partnerKey Optional partner key override.
     */
    public function __construct(
        public readonly string $recTradeId,
        public readonly ?int $amount = null,
        public readonly ?string $bankRefundId = null,
        public readonly ?string $partnerKey = null
    ) {
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

        if ($this->recTradeId === '') {
            throw new ValidationException('rec_trade_id is required.');
        }

        if ($partnerKey === '') {
            throw new ValidationException('Partner key is required.');
        }

        if ($this->amount !== null && $this->amount <= 0) {
            throw new ValidationException('Refund amount must be greater than zero when provided.');
        }

        $payload = [
            'rec_trade_id' => $this->recTradeId,
            'partner_key' => $partnerKey,
            'amount' => $this->amount,
            'bank_refund_id' => $this->bankRefundId,
        ];

        return Payload::filter($payload);
    }
}
