<?php

namespace App\Jobs;

use App\Exceptions\DepositNotificationFailedException;
use App\Models\Deposit;
use App\Services\DepositNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Job responsible for sending a notification regarding a deposit.
 *
 * This job uses the DepositNotificationService to notify external systems or users
 * about the status of a specific deposit.
 */
class SendDepositNotification implements ShouldQueue
{
    use Queueable;

    /**
     * The deposit instance containing the details of the deposit.
     *
     * @var Deposit
     */
    protected Deposit $deposit;

    /**
     * Create a new job instance.
     *
     * @param Deposit $deposit The deposit for which the notification will be sent.
     */
    public function __construct(Deposit $deposit)
    {
        $this->deposit = $deposit;
    }

    /**
     * Execute the job.
     *
     * This method attempts to send a notification about the deposit status using the
     * DepositNotificationService. If the notification fails, it logs the error.
     *
     * @param DepositNotificationService $notificationService The service used to send the deposit notification.
     * @return void
     */
    public function handle(DepositNotificationService $notificationService): void
    {
        Log::info('[DepositNotificationJob] Sending notification for deposit', [
            'deposit_id' => $this->deposit->id,
            'status' => $this->deposit->status,
        ]);

        try {
            $notificationService->send([
                'deposit' => $this->deposit->id,
                'status' => $this->deposit->status,
            ]);

            Log::info('[DepositNotificationJob] Notification sent successfully', [
                'deposit_id' => $this->deposit->id,
            ]);
        } catch (DepositNotificationFailedException $e) {
            Log::error('[DepositNotificationJob] Failed to send notification', [
                'deposit_id' => $this->deposit->id,
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
        Log::critical('[DepositNotificationJob] Job failed with unhandled exception', [
            'deposit_id' => $this->deposit->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
