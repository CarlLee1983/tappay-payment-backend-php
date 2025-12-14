<?php

declare(strict_types=1);

namespace TapPay\Payment\Dto;

/**
 * Response data for the record query API.
 */
final class RecordQueryResponse
{
    /**
     * @param int $status Status code.
     * @param string|null $msg Response message.
     * @param int $recordsPerPage Records per page used in the query.
     * @param int $page Current page number.
     * @param int|null $totalPageCount Total number of pages.
     * @param int|null $numberOfTransactions Total number of transactions found.
     * @param array<int, array<string, mixed>> $tradeRecords List of transaction records.
     * @param array<string, mixed> $raw Raw response data.
     */
    public function __construct(
        public readonly int $status,
        public readonly ?string $msg,
        public readonly int $recordsPerPage,
        public readonly int $page,
        public readonly ?int $totalPageCount,
        public readonly ?int $numberOfTransactions,
        public readonly array $tradeRecords,
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
            isset($data['msg']) ? (string) $data['msg'] : null,
            (int) ($data['records_per_page'] ?? 0),
            (int) ($data['page'] ?? 0),
            isset($data['total_page_count']) ? (int) $data['total_page_count'] : null,
            isset($data['number_of_transactions']) ? (int) $data['number_of_transactions'] : null,
            $data['trade_records'] ?? [],
            $data
        );
    }

    /**
     * Check if the query request was successful.
     *
     * @return bool True if status is 0, false otherwise.
     */
    public function isSuccess(): bool
    {
        return $this->status === 0;
    }

    /**
     * Check if there are more pages of results.
     *
     * @return bool True if the status indicates more records might be available.
     */
    public function hasMore(): bool
    {
        return $this->status !== 2;
    }
}
