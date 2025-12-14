<?php

declare(strict_types=1);

namespace TapPay\Payment\Dto;

use TapPay\Payment\Exception\ValidationException;

/**
 * Result URL configuration for 3D Secure and e-wallet payments.
 *
 * Required for 3D Secure verification and electronic payment methods.
 */
final class ResultUrl
{
    /**
     * @param string $frontendRedirectUrl URL to redirect the user after payment completion (HTTPS required).
     * @param string $backendNotifyUrl URL for backend notification after payment (HTTPS, port 443 required).
     * @param string|null $goBackUrl URL for the "Go Back" button on 3D Secure error pages (optional).
     */
    public function __construct(
        public readonly string $frontendRedirectUrl,
        public readonly string $backendNotifyUrl,
        public readonly ?string $goBackUrl = null
    ) {
    }

    /**
     * Create a ResultUrl instance from an associative array.
     *
     * @param array<string, mixed> $data
     * @throws ValidationException If required fields are missing.
     */
    public static function fromArray(array $data): self
    {
        $frontendUrl = $data['frontend_redirect_url'] ?? $data['frontendRedirectUrl'] ?? null;
        $backendUrl = $data['backend_notify_url'] ?? $data['backendNotifyUrl'] ?? null;
        $goBackUrl = $data['go_back_url'] ?? $data['goBackUrl'] ?? null;

        if ($frontendUrl === null || $frontendUrl === '') {
            throw new ValidationException('frontend_redirect_url is required for ResultUrl.');
        }

        if ($backendUrl === null || $backendUrl === '') {
            throw new ValidationException('backend_notify_url is required for ResultUrl.');
        }

        return new self(
            frontendRedirectUrl: $frontendUrl,
            backendNotifyUrl: $backendUrl,
            goBackUrl: $goBackUrl
        );
    }

    /**
     * Validate that URLs are using HTTPS.
     *
     * @throws ValidationException If URLs are not using HTTPS.
     */
    public function validate(): void
    {
        if (!str_starts_with($this->frontendRedirectUrl, 'https://')) {
            throw new ValidationException('frontend_redirect_url must use HTTPS.');
        }

        if (!str_starts_with($this->backendNotifyUrl, 'https://')) {
            throw new ValidationException('backend_notify_url must use HTTPS.');
        }

        if ($this->goBackUrl !== null && !str_starts_with($this->goBackUrl, 'https://')) {
            throw new ValidationException('go_back_url must use HTTPS.');
        }
    }

    /**
     * Convert to TapPay API payload format.
     *
     * @return array<string, string>
     */
    public function toPayload(): array
    {
        $payload = [
            'frontend_redirect_url' => $this->frontendRedirectUrl,
            'backend_notify_url' => $this->backendNotifyUrl,
        ];

        if ($this->goBackUrl !== null) {
            $payload['go_back_url'] = $this->goBackUrl;
        }

        return $payload;
    }
}
