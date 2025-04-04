<?php

namespace App\Services;

use App\Interfaces\AuthorizationServiceInterface;
use Illuminate\Support\Facades\Http;
use App\Exceptions\UnauthorizedTransferException;

/**
 * Service responsible for verifying authorization of a transfer
 * using an external authorization service.
 */
class AuthorizationService implements AuthorizationServiceInterface
{
    /**
     * @throws UnauthorizedTransferException
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
