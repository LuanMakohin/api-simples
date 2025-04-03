<?php

namespace App\Exceptions;

use Exception;

/**
 * Exception thrown when a payer attempts to transfer money to themselves.
 */
class SelfTransferException extends Exception
{
    /**
     * Constructs a new SelfTransferException with a default message.
     */
    public function __construct()
    {
        parent::__construct('The payer cannot transfer to themselves.');
    }
}
