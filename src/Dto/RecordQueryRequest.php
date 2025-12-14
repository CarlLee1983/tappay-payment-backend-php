<?php

declare(strict_types=1);

namespace TapPay\Payment\Dto;

use TapPay\Payment\ClientConfig;
use TapPay\Payment\Exception\ValidationException;

/**
 * Request payload for the "Record Query" API.
 */
final class RecordQueryRequest
{
    /**
     * @param string|null $partnerKey Optional partner key override.
     * @param int $recordsPerPage Number of records per page (1-200).
     * @param int $page Page number (0-based).
     * @param array<string, mixed> $filters Query filters (e.g., time range, status).
     * @param array{attribute?:string, is_descending?:bool, ...}|array<string, mixed> $orderBy Sorting options.
     * @param string|null $merchantId Optional merchant ID override.
     */
    public function __construct(
        public readonly ?string $partnerKey = null,
        public readonly int $recordsPerPage = 50,
        public readonly int $page = 0,
        public readonly array $filters = [],
        public readonly array $orderBy = [],
        public readonly ?string $merchantId = null
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

        if ($partnerKey === '') {
            throw new ValidationException('Partner key is required.');
        }

        if ($this->recordsPerPage < 1 || $this->recordsPerPage > 200) {
            throw new ValidationException('records_per_page must be between 1 and 200.');
        }

        if ($this->page < 0) {
            throw new ValidationException('page cannot be negative.');
        }

        $filters = $this->filters;
        $merchantId = $this->merchantId ?? $config->merchantId();
        if ($merchantId !== '' && !array_key_exists('merchant_id', $filters)) {
            $filters['merchant_id'] = $merchantId;
        }

        $payload = [
            'partner_key' => $partnerKey,
            'records_per_page' => $this->recordsPerPage,
            'page' => $this->page,
            'filters' => $filters,
            'order_by' => $this->orderBy,
        ];

        return array_filter(
            $payload,
            static fn($value) => $value !== null && $value !== []
        );
    }
}
