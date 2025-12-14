<?php

declare(strict_types=1);

namespace TapPay\Payment\Exception;

use Throwable;

/**
 * Thrown when TapPay rejects the API key signature (401/403).
 */
class SignatureException extends TapPayException
{
    public function __construct(
        string $message = 'Invalid x-api-key signature.',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
