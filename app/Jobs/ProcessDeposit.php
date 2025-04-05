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

/**
 * Job responsible for processing a financial deposit for a user.
 *
 * This job handles the entire deposit process, including:
 * - Verifying authorization
 * - Crediting user balance
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
        $this->authorizeDeposit($authorizationService);
        $this->processDeposit();
        $this->sendNotification();
    }

    /*
     * Return the deposit.
     *
     * @return Deposit
     */
    public function getDeposit(): Deposit
    {
        return $this->deposit;
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
        if (!$authorizationService->authorize()) {
            throw new UnauthorizedTransferException();
        }
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
        DB::transaction(function () {
            $deposit = $this->deposit;

            $deposit->receiver->increment('balance', $deposit->value);

            $deposit->update(['status' => 'completed']);
        });
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
        SendDepositNotification::dispatch($this->deposit)->onQueue('notifications');
    }
}
