<?php

namespace App\Exceptions;

use RuntimeException;

class NowPaymentsApiException extends RuntimeException
{
    public function __construct(string $message, public readonly int $httpStatus = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    public function isTransient(): bool
    {
        return $this->httpStatus === 0 || $this->httpStatus === 429 || $this->httpStatus >= 500;
    }
}
