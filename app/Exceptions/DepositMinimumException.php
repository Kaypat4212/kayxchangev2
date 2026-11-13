<?php

namespace App\Exceptions;

use RuntimeException;

class DepositMinimumException extends RuntimeException
{
    public function __construct(public readonly float $minimumUsd)
    {
        parent::__construct('Deposit amount is below the configured minimum.');
    }
}
