<?php

namespace App\Exceptions;

use Exception;

/**
 * Exception thrown when an account has insufficient balance to complete a transaction.
 */
class InsufficientBalanceException extends Exception
{
    /**
     * Constructs a new InsufficientBalanceException with a default message.
     */
    public function __construct()
    {
        parent::__construct('Insufficient balance.');
    }
}
