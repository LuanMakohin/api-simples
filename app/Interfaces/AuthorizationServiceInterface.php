<?php

namespace App\Interfaces;

/**
 * Interface for authorization services.
 *
 * This interface defines a contract for services that handle authorization
 * of financial transfers or other secure operations.
 */
interface AuthorizationServiceInterface
{
    /**
     * Authorizes a transfer or operation.
     *
     * @return bool Returns true if the authorization is successful, otherwise false.
     */
    public function authorize(): bool;
}
