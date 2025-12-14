<?php

declare(strict_types=1);

namespace TapPay\Payment\Exception;

use Throwable;

/**
 * Thrown when request DTO validation fails.
 */
class ValidationException extends TapPayException
{
    public function __construct(
        string $message,
        private ?string $invalidField = null,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getInvalidField(): ?string
    {
        return $this->invalidField;
    }
}
