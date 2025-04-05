<?php

namespace App\Services;

use App\Interfaces\NotificationServiceInterface;
use Illuminate\Support\Facades\Http;
use App\Exceptions\TransferNotificationFailedException;

/**
 * Service responsible for sending notifications to an external system.
 *
 * This service is responsible for sending transfer notifications to an external
 * system. It communicates with the external API to notify about the transfer
 * status and handles failure scenarios by throwing an exception.
 */
class TransferNotificationService implements NotificationServiceInterface
{
    /**
     * Send a notification about a transfer.
     *
     * This method sends a POST request to an external system to notify about
     * the transfer status. If the request fails or the response indicates an error,
     * it throws a TransferNotificationFailedException.
     *
     * @param array $data The data to be sent in the notification.
     * @return bool Returns true if the notification was successfully sent.
     * @throws TransferNotificationFailedException If the notification fails.
     */
    public function send(array $data): bool
    {
        $response = Http::post('https://util.devi.tools/api/v1/notify', $data);

        if ( $response->failed() || ($response->status() !== 204 && $response->json('status') !== 'success')) {
            throw new TransferNotificationFailedException();
        }

        return true;
    }
}
