<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\SelfTransferException;
use App\Exceptions\UnauthorizedPayerException;
use App\Http\Requests\StoreTransferRequest;
use App\Http\Requests\UpdateTransferRequest;
use App\Services\TransferService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

class TransferController extends Controller
{
    protected TransferService $transferService;

    public function __construct(TransferService $transferService)
    {
        $this->transferService = $transferService;
    }

    /**
     * Get a list of all transfers.
     *
     * @return JsonResponse
     *
     * @response 200 scenario="List of transfers"
     * [
     *   {
     *     "id": "d1e7d2f0-8c68-42f9-a9e6-05ad81f20c59",
     *     "payer_id": 1,
     *     "payee_id": 2,
     *     "amount": 100
     *   },
     *   {
     *     "id": "f2a9ed33-7a58-41cc-a121-bbbd10a1aeb3",
     *     "payer_id": 3,
     *     "payee_id": 4,
     *     "amount": 250
     *   }
     * ]
     */
    public function index(): JsonResponse
    {
        $transfers = $this->transferService->findAll();
        return response()->json($transfers, Response::HTTP_OK);
    }

    /**
     * Get the latest deposits in the last 60 seconds.
     *
     * @return JsonResponse
     *
     * @response 200 scenario="Success" [{"id": "uuid", "user": 1, "value": 100, "created_at": "2025-04-04T12:00:00Z"}]
     */
    public function lasts()
    {
        $transfers = $this->transferService->findLastTransfers();
        return response()->json($transfers, Response::HTTP_OK);
    }

    /**
     * Create a new transfer.
     *
     * @param StoreTransferRequest $request
     * @return JsonResponse
     *
     *
     * @response 201 scenario="Successful creation"
     * {
     *   "id": "d1e7d2f0-8c68-42f9-a9e6-05ad81f20c59",
     *   "payer_id": 1,
     *   "payee_id": 2,
     *   "amount": 100
     * }
     * @response 403 scenario="Unauthorized payer"
     * {
     *   "error": "Unauthorized payer"
     * }
     * @response 422 scenario="Insufficient balance"
     * {
     *   "error": "Insufficient balance"
     * }
     */
    public function store(StoreTransferRequest $request): JsonResponse
    {
        try {
            $transfer = $this->transferService->create($request->all());

            return response()->json($transfer, Response::HTTP_CREATED);
        }
        catch (SelfTransferException $e) {
            return response()->json([
                'error' => 'Self transfer is not allowed.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        catch (UnauthorizedPayerException $e) {
            return response()->json([
                'error' => 'Payer is not authorized to make transfers.',
            ], Response::HTTP_FORBIDDEN);
        }
        catch (InsufficientBalanceException $e) {
            return response()->json([
                'error' => 'Payer does not have sufficient balance.',
            ], Response::HTTP_PAYMENT_REQUIRED);
        }
    }

    /**
     * Get a specific transfer by UUID.
     *
     * @param string $id
     * @return JsonResponse
     *
     * @response 200 scenario="Transfer found"
     * {
     *   "id": "d1e7d2f0-8c68-42f9-a9e6-05ad81f20c59",
     *   "payer_id": 1,
     *   "payee_id": 2,
     *   "amount": 100
     * }
     * @response 400 scenario="Invalid UUID"
     * {
     *   "error": "Invalid UUID format."
     * }
     * @response 404 scenario="Not found"
     * {
     *   "error": "Transfer not found"
     * }
     */
    public function show(string $id): JsonResponse
    {
        if (!Uuid::isValid($id)) {
            return response()->json(['error' => 'Invalid UUID format.'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $transfer = $this->transferService->find($id);
            return response()->json($transfer, Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Transfer not found'], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update a transfer.
     *
     * @param UpdateTransferRequest $request
     * @param string $id
     * @return JsonResponse
     *
     * @response 200 scenario="Updated successfully"
     * {
     *   "id": "d1e7d2f0-8c68-42f9-a9e6-05ad81f20c59",
     *   "amount": 150
     * }
     * @response 400 scenario="Invalid UUID"
     * {
     *   "error": "Invalid UUID format."
     * }
     * @response 404 scenario="Not found"
     * {
     *   "error": "Transfer not found"
     * }
     */
    public function update(UpdateTransferRequest $request, string $id): JsonResponse
    {
        if (!Uuid::isValid($id)) {
            return response()->json(['error' => 'Invalid UUID format.'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $transfer = $this->transferService->update($request->all(), $id);

            return response()->json($transfer, Response::HTTP_OK);
        }
        catch (SelfTransferException $e) {
            return response()->json([
                'error' => 'Self transfer is not allowed.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        catch (UnauthorizedPayerException $e) {
            return response()->json([
                'error' => 'Payer is not authorized to make transfers.',
            ], Response::HTTP_FORBIDDEN);
        }
        catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Transfer not found'], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Delete a transfer.
     *
     * @param string $id
     * @return JsonResponse
     *
     * @response 204 scenario="Deleted successfully" ""
     * @response 400 scenario="Invalid UUID"
     * {
     *   "error": "Invalid UUID format."
     * }
     * @response 404 scenario="Not found"
     * {
     *   "error": "Transfer not found"
     * }
     */
    public function destroy(string $id): JsonResponse
    {
        if (!Uuid::isValid($id)) {
            return response()->json(['error' => 'Invalid UUID format.'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->transferService->delete($id);
            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Transfer not found'], Response::HTTP_NOT_FOUND);
        }
    }
}
