<?php

declare(strict_types=1);

namespace TapPay\Payment\Dto;

/**
 * Cardholder information for TapPay payment requests.
 *
 * Used for fraud detection and 3D Secure risk verification.
 */
final class Cardholder
{
    /**
     * @param string|null $phoneNumber Phone number with country code (e.g., +886912345678).
     * @param string|null $name Cardholder's name.
     * @param string|null $email Cardholder's email address.
     * @param string|null $zipCode Postal/ZIP code.
     * @param string|null $address Billing address.
     * @param string|null $nationalId National ID or passport number.
     */
    public function __construct(
        public readonly ?string $phoneNumber = null,
        public readonly ?string $name = null,
        public readonly ?string $email = null,
        public readonly ?string $zipCode = null,
        public readonly ?string $address = null,
        public readonly ?string $nationalId = null
    ) {
    }

    /**
     * Create a Cardholder instance from an associative array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            phoneNumber: $data['phone_number'] ?? $data['phoneNumber'] ?? null,
            name: $data['name'] ?? null,
            email: $data['email'] ?? null,
            zipCode: $data['zip_code'] ?? $data['zipCode'] ?? null,
            address: $data['address'] ?? null,
            nationalId: $data['national_id'] ?? $data['nationalId'] ?? null
        );
    }

    /**
     * Convert to TapPay API payload format.
     *
     * @return array<string, string>|null Returns null if all fields are empty.
     */
    public function toPayload(): ?array
    {
        $payload = array_filter([
            'phone_number' => $this->phoneNumber,
            'name' => $this->name,
            'email' => $this->email,
            'zip_code' => $this->zipCode,
            'address' => $this->address,
            'national_id' => $this->nationalId,
        ], static fn($value) => $value !== null && $value !== '');

        return empty($payload) ? null : $payload;
    }

    /**
     * Check if any cardholder information is provided.
     */
    public function isEmpty(): bool
    {
        return $this->toPayload() === null;
    }
}
