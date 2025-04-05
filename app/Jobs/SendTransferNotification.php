<?php

namespace App\Jobs;

use App\Exceptions\TransferNotificationFailedException;
use App\Models\Transfer;
use App\Services\TransferNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Job responsible for sending a notification regarding a transfer.
 *
 * This job uses the TransferNotificationService to notify external systems or users
 * about the status of a specific transfer.
 */
class SendTransferNotification implements ShouldQueue
{
    use Queueable;

    /**
     * The transfer instance containing the details of the transfer.
     *
     * @var Transfer
     */
    protected $transfer;

    /**
     * Create a new job instance.
     *
     * @param Transfer $transfer The transfer for which the notification will be sent.
     */
    public function __construct(Transfer $transfer)
    {
        $this->transfer = $transfer;
    }

    /**
     * Execute the job.
     *
     * This method attempts to send a notification about the transfer status using the
     * TransferNotificationService. If the notification fails, it logs the error.
     *
     * @param TransferNotificationService $notificationService The service used to send the transfer notification.
     * @return void
     */
    public function handle(TransferNotificationService $notificationService): void
    {
        try {
            $notificationService->send([
                'transfer' => $this->transfer->id,
                'status' => $this->transfer->status,
            ]);
        } catch (TransferNotificationFailedException $e) {
            Log::error('Failed to send notification for transfer ' . $this->transfer->id . ': ' . $e->getMessage());
        }
    }
}
