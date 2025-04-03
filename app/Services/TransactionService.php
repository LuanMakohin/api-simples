<?php

namespace App\Services;

use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\SelfTransferException;
use App\Exceptions\UnauthorizedPayerException;
use App\Jobs\ProcessTransfer;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Service class for managing transactions.
 *
 * This class provides methods for handling transaction-related operations such as
 * creating, retrieving, updating, and deleting transactions.
 */
class TransactionService
{
    /**
     * Retrieve transactions from the last 60 seconds and cache the result.
     *
     * @return Collection A collection of recent transactions.
     */
    public function findLastTransactions(): Collection
    {
        return Cache::remember('recent_transactions', now()->addSeconds(30), function () {
            return Transaction::where('updated_at', '>=', now()->subSeconds(60))->get();
        });
    }

    /**
     * Retrieve all transactions.
     *
     * @return Collection A collection of all transactions.
     */
    public function findAll(): Collection
    {
        return Transaction::all();
    }

    /**
     * Retrieve a specific transaction by ID.
     *
     * @param string $id The unique identifier of the transaction.
     * @return Transaction The transaction instance.
     *
     * @throws ModelNotFoundException If the transaction is not found.
     */
    public function find(string $id): Transaction
    {
        return Transaction::findOrFail($id);
    }

    /**
     * Processes a transfer between users.
     *
     * @param float $value Amount to be transferred.
     * @param int $payer ID of the user sending the money.
     * @param int $payee ID of the user receiving the money.
     * @return Transaction The created transaction.
     * @throws SelfTransferException If the transfer is not valid.
     * @throws InsufficientBalanceException If the payer does not have enough balance.
     * @throws UnauthorizedPayerException If the payer is not authorized to make transfers.
     */
    public function transfer(float $value, int $payer, int $payee): Transaction
    {
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

        //TODO - logica de consulta  https://util.devi.tools/api/v2/authorize

        return DB::transaction(function () use ($value, $sender, $receiver) {
            $sender->decrement('balance', $value);
            $receiver->increment('balance', $value);

            $transaction = Transaction::create([
                'user_payer_id' => $sender->id,
                'user_payee_id' => $receiver->id,
                'value' => $value,
                'status' => 'pending',
            ]);

            ProcessTransfer::dispatch($transaction);

            return $transaction;
        });
    }
}
