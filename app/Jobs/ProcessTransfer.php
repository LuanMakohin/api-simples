<?php

namespace App\Jobs;

use App\Exceptions\UnauthorizedTransferException;
use App\Models\Transfer;
use App\Models\User;
use App\Services\AuthorizationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

/**
 * Job responsible for processing a financial transfer between users.
 *
 * This job handles the entire transfer process, including:
 * - Verifying authorization
 * - Processing the transfer (debiting and crediting user balances)
 * - Sending notifications after the transfer is processed.
 */
class ProcessTransfer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Transfer The transfer object that contains all relevant transfer data.
     */
    protected Transfer $transfer;

    /**
     * Create a new job instance.
     *
     * @param Transfer $transfer The transfer to be processed.
     */
    public function __construct(Transfer $transfer)
    {
        $this->transfer = $transfer;
    }

    /**
     * Handle the transfer processing.
     *
     * This method performs the following steps:
     * 1. Verifies the transfer authorization.
     * 2. Processes the transfer by updating user balances and transfer status.
     * 3. Sends a notification about the transfer result.
     *
     * @param AuthorizationService $authorizationService The service responsible for verifying authorization.
     * @return void
     * @throws UnauthorizedTransferException If the transfer is not authorized.
     */
    public function handle(AuthorizationService $authorizationService): void
    {
        $this->authorizeTransfer($authorizationService);

        $this->processTransfer();

        $this->sendNotification();
    }

    /*
     * Return the transfer.
     *
     * @return Transfer
    */
    public function getTransfer(): Transfer
    {
        return $this->transfer;
    }


    /**
     * Authorize the transfer using the AuthorizationService.
     *
     *
     * @param AuthorizationService $authorizationService The service used to check transfer authorization.
     * @return void
     * @throws UnauthorizedTransferException If authorization fails.
     */
    protected function authorizeTransfer(AuthorizationService $authorizationService): void
    {
        if (!$authorizationService->authorize()) {
            throw new UnauthorizedTransferException();
        }
    }

    /**
     * Process the transfer by updating balances and the transfer status.
     *
     * This method runs within a database transfer to ensure consistency. If there is not enough balance,
     * the transfer status is set to 'failed'. Otherwise, it updates the balances and marks the transfer as 'success'.
     *
     * @return void
     */
    protected function processTransfer(): void
    {
        DB::transaction(function () {
            $transfer = $this->transfer;
            $sender = User::findOrFail($transfer->payer);
            $receiver = User::findOrFail($transfer->payee);

            if ($sender->balance < $transfer->value) {
                $transfer->update(['status' => 'failed']);
                return;
            }

            $sender->decrement('balance', $transfer->value);
            $receiver->increment('balance', $transfer->value);

            $transfer->update(['status' => 'completed']);
        });
    }

    /**
     * Send a notification about the transfer.
     *
     * This method attempts to send a notification regarding the transfer status.
     * If notification sending fails, it logs the error.
     *
     * @return void
     */
    protected function sendNotification(): void
    {
        SendTransferNotification::dispatch($this->transfer)->onQueue('notifications');
    }
}
