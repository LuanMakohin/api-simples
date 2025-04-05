<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepositRequest;
use App\Http\Requests\UpdateDepositRequest;
use App\Services\DepositService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

class DepositController extends Controller
{
    protected DepositService $depositService;

    public function __construct(DepositService $depositService)
    {
        $this->depositService = $depositService;
    }

    /**
     * Get a list of all deposits.
     *
     * @return JsonResponse
     *
     * @response 200 scenario="List of deposits"
     * [
     *   {
     *     "id": "c1f6d3c0-9d56-4e25-a0d9-f1791e4371aa",
     *     "user": 1,
     *     "value": 100
     *   },
     *   {
     *     "id": "a2e8e5cd-87f2-4cf0-a13c-339b1f8bb343",
     *     "user": 2,
     *     "value": 200
     *   }
     * ]
     */
    public function index(): JsonResponse
    {
        $deposits = $this->depositService->findAll();
        return response()->json($deposits, Response::HTTP_OK);
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
        $deposits = $this->depositService->findLastDeposits();
        return response()->json($deposits, Response::HTTP_OK);
    }

    /**
     * Create a new deposit.
     *
     * @param StoreDepositRequest $request
     * @return JsonResponse
     *
     * @response 201 scenario="Successful creation"
     * {
     *   "id": "c1f6d3c0-9d56-4e25-a0d9-f1791e4371aa",
     *   "user": 1,
     *   "value": 100
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
    public function store(StoreDepositRequest $request): JsonResponse
    {
        $deposit = $this->depositService->create($request->all());

        return response()->json($deposit, Response::HTTP_CREATED);
    }

    /**
     * Get a specific deposit by UUID.
     *
     * @param string $id
     * @return JsonResponse
     *
     * @response 200 scenario="Deposit found"
     * {
     *   "id": "c1f6d3c0-9d56-4e25-a0d9-f1791e4371aa",
     *   "user": 1,
     *   "value": 100
     * }
     * @response 400 scenario="Invalid UUID"
     * {
     *   "error": "Invalid UUID format."
     * }
     * @response 404 scenario="Not found"
     * {
     *   "error": "Deposit not found"
     * }
     */
    public function show(string $id): JsonResponse
    {
        if (!Uuid::isValid($id)) {
            return response()->json(['error' => 'Invalid UUID format.'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $deposit = $this->depositService->find($id);
            return response()->json($deposit, Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Deposit not found'], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update a deposit.
     *
     * @param UpdateDepositRequest $request
     * @param string $id
     * @return JsonResponse
     *
     * @response 200 scenario="Updated successfully"
     * {
     *   "id": "c1f6d3c0-9d56-4e25-a0d9-f1791e4371aa",
     *   "value": 150
     * }
     * @response 400 scenario="Invalid UUID"
     * {
     *   "error": "Invalid UUID format."
     * }
     * @response 404 scenario="Not found"
     * {
     *   "error": "Deposit not found"
     * }
     */
    public function update(UpdateDepositRequest $request, string $id): JsonResponse
    {
        if (!Uuid::isValid($id)) {
            return response()->json(['error' => 'Invalid UUID format.'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $deposit = $this->depositService->update($request->all(), $id);
            return response()->json($deposit, Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Deposit not found'], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Delete a deposit.
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
     *   "error": "Deposit not found"
     * }
     */
    public function destroy(string $id): JsonResponse
    {
        if (!Uuid::isValid($id)) {
            return response()->json(['error' => 'Invalid UUID format.'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->depositService->delete($id);
            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Deposit not found'], Response::HTTP_NOT_FOUND);
        }
    }
}
