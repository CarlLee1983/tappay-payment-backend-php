<?php

declare(strict_types=1);

namespace TapPay\Payment\Dto;

/**
 * Response data for payment APIs.
 */
final class PaymentResponse
{
    /**
     * @param int $status Status code (0 for success).
     * @param string $msg Response message.
     * @param string|null $recTradeId Record trade ID (TapPay transaction ID).
     * @param string|null $bankTransactionId Bank transaction ID.
     * @param string|null $bankOrderNumber Bank order number.
     * @param string|null $authCode Authorization code.
     * @param array{card_key?:string, card_token?:string, ...}|array<string, mixed>|null $cardSecret Card secret data (for Pay by Token).
     * @param array<string, mixed>|null $cardInfo Card information.
     * @param int|null $amount Transaction amount.
     * @param string|null $currency Transaction currency.
     * @param array<string, mixed> $raw Raw response data.
     */
    public function __construct(
        public readonly int $status,
        public readonly string $msg,
        public readonly ?string $recTradeId,
        public readonly ?string $bankTransactionId,
        public readonly ?string $bankOrderNumber,
        public readonly ?string $authCode,
        public readonly ?array $cardSecret,
        public readonly ?array $cardInfo,
        public readonly ?int $amount,
        public readonly ?string $currency,
        public readonly array $raw
    ) {
    }

    /**
     * Create a new instance from an API response array.
     *
     * @param array<string, mixed> $data The raw response data from the API.
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $cardSecret = null;
        if (array_key_exists('card_secret', $data) && is_array($data['card_secret'])) {
            $cardSecret = $data['card_secret'];
        }

        $cardInfo = null;
        if (array_key_exists('card_info', $data) && is_array($data['card_info'])) {
            $cardInfo = $data['card_info'];
        }

        return new self(
            (int) ($data['status'] ?? -1),
            (string) ($data['msg'] ?? ''),
            $data['rec_trade_id'] ?? null,
            $data['bank_transaction_id'] ?? null,
            $data['bank_order_number'] ?? null,
            $data['auth_code'] ?? null,
            $cardSecret,
            $cardInfo,
            isset($data['amount']) ? (int) $data['amount'] : null,
            $data['currency'] ?? null,
            $data
        );
    }

    /**
     * Check if the payment was successful.
     *
     * @return bool True if status is 0, false otherwise.
     */
    public function isSuccess(): bool
    {
        return $this->status === 0;
    }
}
