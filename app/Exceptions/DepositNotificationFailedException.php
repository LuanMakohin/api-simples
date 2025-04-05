<?php

namespace App\Exceptions;

use Exception;

/**
 * Exception thrown when a user attempts to make a deposit, which is not allowed.
 */
class DepositNotificationFailedException extends Exception
{
    /**
     * Constructs a new UnauthorizedPayerException with a default message.
     */
    public function __construct()
    {
        parent::__construct('Failed to send a notification after completing the deposit.');
    }
}
