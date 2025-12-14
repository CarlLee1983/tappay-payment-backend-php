<?php

declare(strict_types=1);

namespace TapPay\Payment;

use TapPay\Payment\Exception\ValidationException;

/**
 * TapPay client configuration.
 *
 * Holds the necessary credentials and base URI for connecting to the TapPay API.
 */
final class ClientConfig
{
    private string $baseUri;

    /**
     * @param string $partnerKey The partner key provided by TapPay.
     * @param string $merchantId The merchant ID provided by TapPay.
     * @param string $baseUri The base URI for the API (default: Sandbox).
     *
     * @throws ValidationException If any required field is empty.
     */
    public function __construct(
        private string $partnerKey,
        private string $merchantId,
        string $baseUri = 'https://sandbox.tappaysdk.com'
    ) {
        $this->baseUri = rtrim($baseUri, '/');
        $this->validate();
    }

    /**
     * Get the Partner Key.
     */
    public function partnerKey(): string
    {
        return $this->partnerKey;
    }

    /**
     * Get the Merchant ID.
     */
    public function merchantId(): string
    {
        return $this->merchantId;
    }

    /**
     * Get the Base URI.
     */
    public function baseUri(): string
    {
        return $this->baseUri;
    }

    private function validate(): void
    {
        if ($this->partnerKey === '') {
            throw new ValidationException('Partner key is required.');
        }

        if ($this->merchantId === '') {
            throw new ValidationException('Merchant ID is required.');
        }

        if ($this->baseUri === '') {
            throw new ValidationException('Base URI is required.');
        }
    }
}
