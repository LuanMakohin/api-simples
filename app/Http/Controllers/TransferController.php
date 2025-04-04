<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\SelfTransferException;
use App\Exceptions\UnauthorizedPayerException;
use App\Http\Requests\TransferRequest;
use App\Services\TransferService;
use Ramsey\Uuid\Uuid;

class TransferController extends Controller
{
    protected TransferService $transferService;

    public function __construct(TransferService $transferService)
    {
        $this->transferService = $transferService;
    }

    /**
     * Display a listing of the transfers.
     */
    public function index()
    {
        return $this->transferService->findAll();
    }

    /**
     * Store a newly created transfer in storage.
     * @throws SelfTransferException
     * @throws UnauthorizedPayerException
     * @throws InsufficientBalanceException
     */
    public function store(TransferRequest $request)
    {
        return $this->transferService->transfer($request->all());
    }

    /**
     * Display the transfer resource.
     */
    public function show(string $id)
    {
        if (!Uuid::isValid($id)) {
            return response()->json(['error' => 'Invalid UUID format.'], 400);
        }

        return $this->transferService->find($id);
    }
}
