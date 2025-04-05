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
    protected $deposit;

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
        try {
            $notificationService->send([
                'deposit' => $this->deposit->id,
                'status' => $this->deposit->status,
            ]);
        } catch (DepositNotificationFailedException $e) {
            Log::error('Failed to send notification for deposit ' . $this->deposit->id . ': ' . $e->getMessage());
        }
    }
}
