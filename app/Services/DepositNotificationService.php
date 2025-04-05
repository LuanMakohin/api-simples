<?php

namespace App\Services;

use App\Exceptions\DepositNotificationFailedException;
use App\Interfaces\NotificationServiceInterface;
use Illuminate\Support\Facades\Http;

/**
 * Service responsible for sending notifications to an external system.
 *
 * This service handles the process of sending notifications about a deposit
 * to an external notification service. If the notification fails, it throws
 * a DepositNotificationFailedException.
 */
class DepositNotificationService implements NotificationServiceInterface
{
    /**
     * Sends a notification about a deposit to an external system.
     *
     * This method sends an HTTP POST request to an external API to notify about
     * the deposit. If the notification fails or the response indicates an error,
     * it throws a DepositNotificationFailedException.
     *
     * @param array $data Data to be sent with the notification.
     * @throws DepositNotificationFailedException If the notification fails.
     * @return bool Returns true if the notification is successfully sent.
     */
    public function send(array $data): bool
    {
        $response = Http::post('https://util.devi.tools/api/v1/notify', $data);

        if ( $response->failed() || ($response->status() !== 204 && $response->json('status') !== 'success')) {
            throw new DepositNotificationFailedException();
        }

        return true;
    }
}
