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
use Illuminate\Support\Facades\Log;

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
     * The transfer object that contains all relevant transfer data.
     *
     * @var Transfer
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
        Log::info('[TransferJob] Starting transfer processing', [
            'transfer_id' => $this->transfer->id,
        ]);

        try {
            $this->authorizeTransfer($authorizationService);
            $this->processTransfer();
            $this->sendNotification();

            Log::info('[TransferJob] Transfer successfully processed', [
                'transfer_id' => $this->transfer->id,
            ]);
        } catch (UnauthorizedTransferException $e) {
            Log::warning('[TransferJob] Transfer authorization failed', [
                'transfer_id' => $this->transfer->id,
            ]);

            $this->transfer->update(['status' => 'failed']);

            throw $e;
        }
    }

    /**
     * Handle job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('[TransferJob] Failed to process transfer', [
            'transfer_id' => $this->transfer->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }

    /**
     * Get the transfer instance associated with the job.
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
     * @param AuthorizationService $authorizationService The service used to check transfer authorization.
     * @return void
     * @throws UnauthorizedTransferException If authorization fails.
     */
    protected function authorizeTransfer(AuthorizationService $authorizationService): void
    {
        Log::info('[TransferJob] Checking transfer authorization', [
            'transfer_id' => $this->transfer->id,
        ]);

        if (!$authorizationService->authorize()) {
            Log::warning('[TransferJob] Transfer authorization denied', [
                'transfer_id' => $this->transfer->id,
            ]);
            throw new UnauthorizedTransferException();
        }

        Log::info('[TransferJob] Transfer authorized', [
            'transfer_id' => $this->transfer->id,
        ]);
    }

    /**
     * Process the transfer by updating balances and the transfer status.
     *
     * This method runs within a database transaction to ensure consistency.
     * If the sender does not have enough balance, the transfer is marked as 'failed'.
     * Otherwise, it debits and credits the balances and marks the transfer as 'completed'.
     *
     * @return void
     */
    protected function processTransfer(): void
    {
        Log::info('[TransferJob] Processing transfer', [
            'transfer_id' => $this->transfer->id,
            'value' => $this->transfer->value,
        ]);

        DB::transaction(function () {
            $transfer = $this->transfer;
            $sender = User::findOrFail($transfer->payer);
            $receiver = User::findOrFail($transfer->payee);

            if ($sender->balance < $transfer->value) {
                Log::warning('[TransferJob] Insufficient balance for transfer', [
                    'transfer_id' => $transfer->id,
                    'sender_id' => $sender->id,
                    'balance' => $sender->balance,
                    'attempted_value' => $transfer->value,
                ]);

                $transfer->update(['status' => 'failed']);
                return;
            }

            $sender->decrement('balance', $transfer->value);
            $receiver->increment('balance', $transfer->value);
            $transfer->update(['status' => 'completed']);

            Log::info('[TransferJob] Balances updated and transfer completed', [
                'transfer_id' => $transfer->id,
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
            ]);
        });
    }

    /**
     * Send a notification about the transfer.
     *
     * @return void
     */
    protected function sendNotification(): void
    {
        Log::info('[TransferJob] Sending transfer notification', [
            'transfer_id' => $this->transfer->id,
        ]);

        SendTransferNotification::dispatch($this->transfer)->onQueue('notifications');
    }
}
