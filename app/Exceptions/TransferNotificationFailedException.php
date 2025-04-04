<?php

namespace App\Exceptions;

use Exception;

/**
 * Exception thrown when a merchant attempts to make a transfer, which is not allowed.
 */
class TransferNotificationFailedException extends Exception
{
    /**
     * Constructs a new UnauthorizedPayerException with a default message.
     */
    public function __construct()
    {
        parent::__construct('Failed to send a notification after completing the transfer.');
    }
}
