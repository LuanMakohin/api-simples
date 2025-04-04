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
 * @implements TransferServiceInterface
 */
class TransferService implements TransferServiceInterface
{
    public function findLastTransfers(): Collection
    {
        return Cache::remember('recent_transfers', now()->addSeconds(30), function () {
            return Transfer::where('updated_at', '>=', now()->subSeconds(60))->get();
        });
    }

    public function findAll(): Collection
    {
        return Transfer::all();
    }

    public function find(string $id): Transfer
    {
        return Transfer::findOrFail($id);
    }

    public function transfer(array $data): Transfer
    {
        $payer = $data['user_payer_id'];
        $payee = $data['user_payee_id'];
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
            'user_payer_id' => $sender->id,
            'user_payee_id' => $receiver->id,
            'value' => $value,
            'status' => 'pending',
        ]);

        ProcessTransfer::dispatch($transfer)->onQueue('transfers');

        return $transfer;
    }
}
