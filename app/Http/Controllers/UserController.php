<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Services\UserService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * List all users.
     *
     * @return JsonResponse
     *
     * @response 200 scenario="Success" [{"id": 1, "name": "John Doe", "email": "john@example.com"}]
     */
    public function index(): JsonResponse
    {
        $users = $this->userService->findAll();
        return response()->json($users, Response::HTTP_OK);
    }

    /**
     * Create a new user.
     *
     * @param StoreUserRequest $request
     * @return JsonResponse
     *
     * @response 201 scenario="User created" {"id": 1, "name": "John Doe", "email": "john@example.com"}
     * @response 422 scenario="Validation error" {"errors": {"email": ["The email has already been taken."]}}
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->create($request->all());

        return response()->json($user, Response::HTTP_CREATED);
    }

    /**
     * Get a specific user by ID.
     *
     * @param int $id
     * @return JsonResponse
     *
     * @response 200 scenario="User found" {"id": 1, "name": "John Doe", "email": "john@example.com"}
     * @response 400 scenario="Invalid UUID" {"error": "Invalid UUID format."}
     * @response 404 scenario="User not found" {"error": "User not found"}
     */
    public function show(int $id): JsonResponse
    {
        try {
            $user = $this->userService->find($id);
            return response()->json($user, Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update a user.
     *
     * @param UpdateUserRequest $request
     * @param int $id
     * @return JsonResponse
     *
     * @response 200 scenario="User updated" {"id": 1, "name": "Updated Name", "email": "updated@example.com"}
     * @response 400 scenario="Invalid UUID" {"error": "Invalid UUID format."}
     * @response 404 scenario="User not found" {"error": "User not found"}
     * @response 422 scenario="Validation error" {"errors": {"email": ["The email must be valid."]}}
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        try {
            $user = $this->userService->update($request->all(), $id);
            return response()->json($user, Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Delete a user.
     *
     * @param int $id
     * @return JsonResponse
     *
     * @response 204 scenario="User deleted successfully"
     * @response 400 scenario="Invalid UUID" {"error": "Invalid UUID format."}
     * @response 404 scenario="User not found" {"error": "User not found"}
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->userService->delete($id);
            return response()->json([], Response::HTTP_NO_CONTENT);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
    }
}
