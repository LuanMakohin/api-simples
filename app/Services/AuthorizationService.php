<?php

namespace App\Services;

use App\Interfaces\AuthorizationServiceInterface;
use Illuminate\Support\Facades\Http;
use App\Exceptions\UnauthorizedTransferException;

/**
 * Service responsible for verifying authorization of a transfer
 * using an external authorization service.
 *
 * This service sends a request to an external API to verify if the transfer is authorized.
 * If the authorization check fails, an UnauthorizedTransferException is thrown.
 */
class AuthorizationService implements AuthorizationServiceInterface
{
    /**
     * Authorizes the transfer by sending a request to an external authorization service.
     *
     * This method sends an HTTP GET request to an external API to check if the transfer
     * is authorized. If the authorization fails, it throws an UnauthorizedTransferException.
     *
     * @throws UnauthorizedTransferException If the transfer is not authorized.
     * @return bool Returns true if the transfer is authorized, false otherwise.
     */
    public function authorize(): bool
    {
        $response = Http::get('https://util.devi.tools/api/v2/authorize');

        if ($response->failed() || !$response->json('data.authorization')) {
            throw new UnauthorizedTransferException();
        }

        return true;
    }
}
