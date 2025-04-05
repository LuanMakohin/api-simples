<?php

namespace App\Services;

use App\Interfaces\DepositServiceInterface;
use App\Jobs\ProcessDeposit;
use App\Models\Deposit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Service class for managing deposits.
 *
 * This service class provides methods for managing deposits, including
 * creating, updating, deleting, and retrieving deposit records. It also
 * handles caching recent deposits for performance optimization.
 *
 * @implements DepositServiceInterface
 */
class DepositService implements DepositServiceInterface
{
    /**
     * Retrieve recent deposits from the cache or database.
     *
     * This method retrieves deposits that have been updated within the last 60 seconds.
     * It utilizes caching to reduce database queries and improve performance.
     *
     * @return Collection A collection of recent deposits.
     */
    public function findLastDeposits(): Collection
    {
        return Cache::remember('recent_deposits', now()->addSeconds(30), function () {
            return Deposit::where('updated_at', '>=', now()->subSeconds(60))->get();
        });
    }

    /**
     * Retrieve all deposits.
     *
     * This method retrieves all deposits from the database.
     *
     * @return Collection A collection of all deposits.
     */
    public function findAll(): Collection
    {
        return Deposit::all();
    }

    /**
     * Retrieve a specific deposit by ID.
     *
     * This method retrieves a deposit by its ID. If the deposit is not found, it will throw a ModelNotFoundException.
     *
     * @param string $id The ID of the deposit.
     * @return Deposit The deposit record.
     */
    public function find(string $id): Deposit
    {
        return Deposit::findOrFail($id);
    }

    /**
     * Create a new deposit record.
     *
     * This method validates and creates a new deposit record, and dispatches a job to process the deposit.
     *
     * @param array $data The validated data for the deposit.
     * @return Deposit The newly created deposit record.
     */
    public function create(array $data): Deposit
    {
        $deposit = Deposit::create($data);

        ProcessDeposit::dispatch($deposit)->onQueue('deposits');

        return $deposit;
    }

    /**
     * Update an existing deposit record.
     *
     * This method validates and updates an existing deposit record by its ID.
     *
     * @param array $data The validated data for updating the deposit.
     * @param string $id The ID of the deposit to update.
     * @return Deposit The updated deposit record.
     */
    public function update(array $data, string $id): Deposit
    {
        $deposit = Deposit::findOrFail($id);

        $deposit->update($data);

        return $deposit;
    }

    /**
     * Delete a deposit record by ID.
     *
     * This method deletes a deposit record from the database by its ID.
     *
     * @param string $id The ID of the deposit to delete.
     * @return void
     */
    public function delete(string $id): void
    {
        $deposit = Deposit::findOrFail($id);
        $deposit->delete();
    }
}
