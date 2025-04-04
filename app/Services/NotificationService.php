<?php

namespace App\Services;

use App\Interfaces\NotificationServiceInterface;
use Illuminate\Support\Facades\Http;
use App\Exceptions\TransferNotificationFailedException;

/**
 * Service responsible for sending notifications to an external system.
 */
class NotificationService implements NotificationServiceInterface
{
    /**
     * @throws TransferNotificationFailedException
     */
    public function send(array $data): bool
    {
        $response = Http::post('https://util.devi.tools/api/v1/notify', $data);

        if ($response->failed() || $response->json('status') === 'error') {
            throw new TransferNotificationFailedException();
        }

        return true;
    }
}
