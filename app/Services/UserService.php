<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;

/**
 * Service class for managing users.
 *
 * This class provides methods for handling user-related operations such as
 * creating, retrieving, updating, and deleting users.
 */
class UserService
{
    /**
     * Retrieve all users.
     *
     * @return Collection A collection of all users.
     */
    public function findAll(): Collection
    {
        return User::all();
    }

    /**
     * Retrieve a specific user by ID.
     *
     * @param string $id The unique identifier of the user.
     * @return User The user instance.
     *
     * @throws ModelNotFoundException If the user is not found.
     */
    public function find(string $id): User
    {
        return User::findOrFail($id);
    }

    /**
     * Create a new user in the database.
     *
     * @param array<string, mixed> $data An associative array containing user data.
     * - **name** (string, required) - The name of the user.
     * - **email** (string, required) - The email address of the user.
     * - **password** (string, required) - The user's password.
     * - **document** (string, required) - The user's document.
     * - **user_type** (string, required) - The type of user.
     * - **balance** (float, required) - The initial balance of the user.
     *
     * @return User The created user instance.
     */
    public function create(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'document' => $data['document'],
            'user_type' => $data['user_type'],
            'balance' => $data['balance'],
        ]);
    }

    /**
     * Update an existing user.
     *
     * @param string $id The ID of the user to update.
     * @param array<string, mixed> $data An associative array containing the updated user data.
     * - **name** (string, required) - The updated name of the user.
     * - **email** (string, required) - The updated email address of the user.
     * - **password** (string, required) - The updated password (hashed automatically).
     * - **document** (string, required) - The updated document.
     * - **user_type** (string, required) - The updated user type.
     * - **balance** (float, required) - The updated balance.
     *
     * @return User The updated user instance.
     *
     * @throws ModelNotFoundException If the user is not found.
     */
    public function update(string $id, array $data): User
    {
        $user = User::findOrFail($id);
        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'document' => $data['document'],
            'user_type' => $data['user_type'],
            'balance' => $data['balance'],
        ]);
        return $user;
    }

    /**
     * Delete a user from the database.
     *
     * If soft deletes are enabled, the user will be marked as deleted but not permanently removed.
     *
     * @param string $id The ID of the user to delete.
     * @return bool|null Returns `true` if the user was deleted successfully, or `null` if the deletion failed.
     *
     * @throws ModelNotFoundException If the user is not found.
     */
    public function delete(string $id): ?bool
    {
        $user = User::findOrFail($id);
        return $user->delete();
    }
}
