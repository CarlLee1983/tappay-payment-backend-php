<?php

declare(strict_types=1);

namespace TapPay\Payment\Dto;

/**
 * Response data for the refund API.
 */
final class RefundResponse
{
    /**
     * @param int $status Status code.
     * @param string $msg Response message.
     * @param string|null $refundId Refund ID.
     * @param string|null $bankRefundOrderNumber Bank refund order number.
     * @param int|null $refundAmount Refunding amount.
     * @param string|null $currency Currency code.
     * @param bool|null $isCaptured Whether the transaction was captured.
     * @param array<string, mixed> $raw Raw response data.
     */
    public function __construct(
        public readonly int $status,
        public readonly string $msg,
        public readonly ?string $refundId,
        public readonly ?string $bankRefundOrderNumber,
        public readonly ?int $refundAmount,
        public readonly ?string $currency,
        public readonly ?bool $isCaptured,
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
        return new self(
            (int) ($data['status'] ?? -1),
            (string) ($data['msg'] ?? ''),
            $data['refund_id'] ?? null,
            $data['bank_refund_order_number'] ?? null,
            isset($data['refund_amount']) ? (int) $data['refund_amount'] : null,
            $data['currency'] ?? null,
            isset($data['is_captured']) ? (bool) $data['is_captured'] : null,
            $data
        );
    }

    /**
     * Check if the refund was successful.
     *
     * @return bool True if status is 0, false otherwise.
     */
    public function isSuccess(): bool
    {
        return $this->status === 0;
    }
}
