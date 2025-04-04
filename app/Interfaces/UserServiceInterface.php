<?php

namespace App\Interfaces;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface for UserService.
 *
 * This interface defines the methods for user-related operations, including
 * creating, retrieving, updating, and deleting users.
 */
interface UserServiceInterface
{
    /**
     * Retrieve all users.
     *
     * @return Collection A collection of all users.
     */
    public function findAll(): Collection;

    /**
     * Retrieve a specific user by ID.
     *
     * @param string $id The unique identifier of the user.
     * @return User The user instance.
     */
    public function find(string $id): User;

    /**
     * Create a new user in the database.
     *
     * @param array<string, mixed> $data The user data.
     * @return User The created user instance.
     */
    public function create(array $data): User;

    /**
     * Update an existing user.
     *
     * @param string $id The unique identifier of the user.
     * @param array<string, mixed> $data The updated user data.
     * @return User The updated user instance.
     */
    public function update(string $id, array $data): User;

    /**
     * Delete a user from the database.
     *
     * @param string $id The unique identifier of the user.
     * @return bool|null Returns true if the user was deleted successfully, or null if the deletion failed.
     */
    public function delete(string $id): ?bool;
}
