<?php

namespace App\Interfaces;

/**
 * Interface for notification services.
 *
 * This interface defines a contract for services responsible for sending
 * notifications to external systems.
 */
interface NotificationServiceInterface
{
    /**
     * Sends a notification to an external service.
     *
     * @param array $data The notification payload to be sent.
     *
     * @return bool Returns true if the notification was successfully sent.
     */
    public function send(array $data): bool;
}
