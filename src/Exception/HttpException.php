<?php

declare(strict_types=1);

namespace TapPay\Payment\Exception;

use Throwable;

/**
 * Thrown for HTTP-level failures or invalid responses.
 */
class HttpException extends TapPayException
{
    public function __construct(
        string $message,
        private int $statusCode = 0,
        private ?string $responseBody = null,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getResponseBody(): ?string
    {
        return $this->responseBody;
    }
}
