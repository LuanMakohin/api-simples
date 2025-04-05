<?php

namespace App\Services;

use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\SelfTransferException;
use App\Exceptions\UnauthorizedPayerException;
use App\Interfaces\TransferServiceInterface;
use App\Jobs\ProcessTransfer;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Service class for managing transfers.
 *
 * This service is responsible for handling all transfer operations, including
 * creating, updating, deleting, and retrieving transfers. It also validates
 * the transfer conditions, such as ensuring the payer and payee are not the same,
 * the payer has sufficient balance, and the payer is authorized.
 *
 * @implements TransferServiceInterface
 */
class TransferService implements TransferServiceInterface
{
    /**
     * Retrieve the most recent transfers.
     *
     * This method fetches the transfers that were updated in the last 60 seconds
     * and caches the result for 30 seconds to improve performance.
     *
     * @return Collection A collection of recent transfers.
     */
    public function findLastTransfers(): Collection
    {
        return Cache::remember('recent_transfers', now()->addSeconds(30), function () {
            return Transfer::where('updated_at', '>=', now()->subSeconds(60))->get();
        });
    }

    /**
     * Retrieve all transfers.
     *
     * This method retrieves all the transfers from the database.
     *
     * @return Collection A collection of all transfers.
     */
    public function findAll(): Collection
    {
        return Transfer::all();
    }

    /**
     * Retrieve a specific transfer by its ID.
     *
     * This method finds a transfer by its unique identifier.
     *
     * @param string $id The ID of the transfer.
     * @return Transfer The transfer instance.
     */
    public function find(string $id): Transfer
    {
        return Transfer::findOrFail($id);
    }

    /**
     * Create a new transfer.
     *
     * This method creates a new transfer after validating the conditions:
     * - The payer and payee cannot be the same.
     * - The payer must not be a legal entity (PJ).
     * - The payer must have sufficient balance to complete the transfer.
     * If all conditions are met, the transfer is created and dispatched for processing.
     *
     * @param array $data The data required to create the transfer.
     * @return Transfer The created transfer instance.
     * @throws SelfTransferException If the payer and payee are the same.
     * @throws UnauthorizedPayerException If the payer is a legal entity.
     * @throws InsufficientBalanceException If the payer does not have enough balance.
     */
    public function create(array $data): Transfer
    {
        $payer = $data['payer'];
        $payee = $data['payee'];
        $value = $data['value'];

        if ($payer === $payee) {
            throw new SelfTransferException();
        }

        $sender = User::findOrFail($payer);
        $receiver = User::findOrFail($payee);

        if ($sender->user_type === 'PJ') {
            throw new UnauthorizedPayerException();
        }

        if ($sender->balance < $value) {
            throw new InsufficientBalanceException();
        }

        $transfer = Transfer::create([
            'payer' => $sender->id,
            'payee' => $receiver->id,
            'value' => $value,
            'status' => 'pending',
        ]);

        ProcessTransfer::dispatch($transfer)->onQueue('transfers');

        return $transfer;
    }

    /**
     * Update an existing transfer.
     *
     * This method updates the specified transfer with new data.
     *
     * @param array $data The new data to update the transfer.
     * @param string $id The ID of the transfer to update.
     * @return Transfer The updated transfer instance.
     * @throws UnauthorizedPayerException
     * @throws SelfTransferException
     */
    public function update(array $data, string $id): Transfer
    {
        $transfer = Transfer::findOrFail($id);
        $payer = $data['payer'];
        $payee = $data['payee'];

        if ($payer === $payee) {
            throw new SelfTransferException();
        }

        $sender = User::findOrFail($payer);

        if ($sender->user_type === 'PJ') {
            throw new UnauthorizedPayerException();
        }

        $transfer->update($data);

        return $transfer;
    }

    /**
     * Delete a transfer.
     *
     * This method deletes the specified transfer from the database.
     *
     * @param string $id The ID of the transfer to delete.
     * @return void
     */
    public function delete(string $id): void
    {
        $transfer = Transfer::findOrFail($id);
        $transfer->delete();
    }
}
