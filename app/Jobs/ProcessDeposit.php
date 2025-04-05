<?php

namespace App\Jobs;

use App\Exceptions\UnauthorizedTransferException;
use App\Models\Deposit;
use App\Services\AuthorizationService;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Job responsible for processing a financial deposit for a user.
 *
 * This job handles the entire deposit process, including:
 * - Verifying authorization
 * - Crediting the user's balance
 * - Sending a notification after the deposit is processed
 */
class ProcessDeposit implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The deposit object that contains all relevant deposit data.
     *
     * @var Deposit
     */
    protected Deposit $deposit;

    /**
     * Create a new job instance.
     *
     * @param Deposit $deposit The deposit to be processed.
     */
    public function __construct(Deposit $deposit)
    {
        $this->deposit = $deposit;
    }

    /**
     * Handle the deposit processing.
     *
     * This method performs the following steps:
     * 1. Verifies the deposit authorization.
     * 2. Processes the deposit by updating user balance and status.
     * 3. Sends a notification about the deposit result.
     *
     * @param AuthorizationService $authorizationService The service responsible for verifying authorization.
     * @return void
     * @throws UnauthorizedTransferException If the deposit is not authorized.
     */
    public function handle(AuthorizationService $authorizationService): void
    {
        Log::info('[DepositJob] Starting deposit processing', [
            'deposit_id' => $this->deposit->id,
        ]);

        try {
            $this->authorizeDeposit($authorizationService);
            $this->processDeposit();
            $this->sendNotification();

            Log::info('[DepositJob] Deposit successfully processed', [
                'deposit_id' => $this->deposit->id,
            ]);
        } catch (UnauthorizedTransferException $e) {
            Log::warning('[DepositJob] Deposit authorization failed', [
                'deposit_id' => $this->deposit->id,
            ]);

            $this->deposit->update(['status' => 'failed']);

            throw $e;
        }
    }
    /**
     * Get the deposit instance associated with the job.
     *
     * @return Deposit
     */
    public function getDeposit(): Deposit
    {
        return $this->deposit;
    }

    /**
     * Handle job failure.
     *
     * This method is automatically called when the job fails.
     *
     * @param \Throwable $exception The exception that caused the failure.
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('[DepositJob] Failed to process deposit', [
            'deposit_id' => $this->deposit->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }

    /**
     * Authorize the deposit using the AuthorizationService.
     *
     * @param AuthorizationService $authorizationService The service used to check deposit authorization.
     * @return void
     * @throws UnauthorizedTransferException If authorization fails.
     */
    protected function authorizeDeposit(AuthorizationService $authorizationService): void
    {
        Log::info('[DepositJob] Checking deposit authorization', [
            'deposit_id' => $this->deposit->id,
        ]);

        if (!$authorizationService->authorize()) {
            Log::warning('[DepositJob] Deposit authorization denied', [
                'deposit_id' => $this->deposit->id,
            ]);
            throw new UnauthorizedTransferException();
        }

        Log::info('[DepositJob] Deposit authorized', [
            'deposit_id' => $this->deposit->id,
        ]);
    }

    /**
     * Process the deposit by updating the user balance and deposit status.
     *
     * This method runs within a database transaction to ensure consistency.
     *
     * @return void
     */
    protected function processDeposit(): void
    {
        Log::info('[DepositJob] Processing deposit', [
            'deposit_id' => $this->deposit->id,
            'value' => $this->deposit->value,
        ]);

        DB::transaction(function () {
            $deposit = $this->deposit;

            $deposit->receiver->increment('balance', $deposit->value);
            $deposit->update(['status' => 'completed']);
        });

        Log::info('[DepositJob] Deposit completed and balance updated', [
            'deposit_id' => $this->deposit->id,
        ]);
    }

    /**
     * Send a notification about the deposit.
     *
     * This method dispatches a notification job for the processed deposit.
     *
     * @return void
     */
    protected function sendNotification(): void
    {
        Log::info('[DepositJob] Sending deposit notification', [
            'deposit_id' => $this->deposit->id,
        ]);

        SendDepositNotification::dispatch($this->deposit)->onQueue('notifications');
    }
}
