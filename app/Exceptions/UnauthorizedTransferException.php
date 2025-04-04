<?php

namespace App\Exceptions;

use Exception;

/**
 * Exception thrown when the authorization API denies the transfer.
 */
class UnauthorizedTransferException extends Exception
{
    /**
     * Constructs a new UnauthorizedTransferException with a default message.
     */
    public function __construct()
    {
        parent::__construct('Transfer not authorized by external service.');
    }
}
