<?php

namespace App\Interfaces;

use App\Models\Transfer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use App\Exceptions\SelfTransferException;
use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\UnauthorizedPayerException;

/**
 * Interface for the Transfer Service.
 *
 * This interface defines the contract for managing transfers,
 * including creation, retrieval, updating, and deletion.
 */
interface TransferServiceInterface
{
    /**
     * Retrieve transfers from the last 60 seconds and cache the result.
     *
     * @return Collection A collection of recent transfers.
     */
    public function findLastTransfers(): Collection;

    /**
     * Retrieve all transfers.
     *
     * @return Collection A collection of all transfers.
     */
    public function findAll(): Collection;

    /**
     * Retrieve a specific transfer by ID.
     *
     * @param string $id The unique identifier of the transfer.
     * @return Transfer The transfer instance.
     *
     * @throws ModelNotFoundException If the transfer is not found.
     */
    public function find(string $id): Transfer;

    /**
     * Create a new transfer between users.
     *
     * @param array $data Transfer details including value, payer, and payee.
     * @return Transfer The created transfer instance.
     *
     * @throws SelfTransferException If payer and payee are the same.
     * @throws InsufficientBalanceException If payer lacks sufficient balance.
     * @throws UnauthorizedPayerException If payer is unauthorized.
     */
    public function create(array $data): Transfer;

    /**
     * Update an existing transfer.
     *
     * @param string $id The transfer ID.
     * @param array $data Fields to update.
     * @return Transfer The updated transfer instance.
     *
     * @throws ModelNotFoundException If the transfer is not found.
     */
    public function update(array $data, string $id): Transfer;

    /**
     * Delete a transfer.
     *
     * @param string $id The transfer ID.
     * @return void
     *
     * @throws ModelNotFoundException If the transfer is not found.
     */
    public function delete(string $id): void;
}
