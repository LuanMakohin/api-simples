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
 * including retrieval, processing, and validation of transfers.
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
     * Processes a transfer between users.
     *
     * @param array $data The data containing the transfer details, such as value, payee and payer information.
     * @return Transfer The created transfer instance.
     *
     * @throws SelfTransferException If the transfer is a self-transfer (payer and payee are the same).
     * @throws InsufficientBalanceException If the payer does not have enough balance.
     * @throws UnauthorizedPayerException If the payer is not authorized to make transfers.
     */
    public function transfer(array $data): Transfer;
}
