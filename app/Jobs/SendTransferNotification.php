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
    protected Transfer $transfer;

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
        Log::info('[TransferNotificationJob] Sending notification for transfer', [
            'transfer_id' => $this->transfer->id,
            'status' => $this->transfer->status,
        ]);

        try {
            $notificationService->send([
                'transfer' => $this->transfer->id,
                'status' => $this->transfer->status,
            ]);

            Log::info('[TransferNotificationJob] Notification sent successfully', [
                'transfer_id' => $this->transfer->id,
            ]);
        } catch (TransferNotificationFailedException $e) {
            Log::error('[TransferNotificationJob] Failed to send notification', [
                'transfer_id' => $this->transfer->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::critical('[TransferNotificationJob] Job failed with unhandled exception', [
            'transfer_id' => $this->transfer->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
