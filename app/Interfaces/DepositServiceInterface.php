<?php

namespace App\Interfaces;

use App\Models\Deposit;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

/**
 * Interface for the Deposit Service.
 *
 * This interface defines the contract for managing deposits,
 * including creation, retrieval, updating and deletion.
 */
interface DepositServiceInterface
{
    /**
     * Retrieve deposits from the last 60 seconds and cache the result.
     *
     * @return Collection A collection of recent deposits.
     */
    public function findLastDeposits(): Collection;

    /**
     * Retrieve all deposits.
     *
     * @return Collection A collection of all deposits.
     */
    public function findAll(): Collection;

    /**
     * Retrieve a specific deposit by ID.
     *
     * @param string $id The unique identifier of the deposit.
     * @return Deposit The deposit instance.
     *
     * @throws ModelNotFoundException If the deposit is not found.
     */
    public function find(string $id): Deposit;

    /**
     * Create a new deposit.
     *
     * @param array $data The validated request data for the deposit.
     * @return Deposit The created deposit instance.
     */
    public function create(array $data): Deposit;

    /**
     * Update an existing deposit.
     *
     * @param array $data The validated request data.
     * @param string $id The ID of the deposit to update.
     * @return Deposit The updated deposit instance.
     */
    public function update(array $data, string $id): Deposit;

    /**
     * Delete a deposit by ID.
     *
     * @param string $id The ID of the deposit to delete.
     */
    public function delete(string $id): void;
}
