<?php

namespace App\Services;

use App\Interfaces\UserServiceInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Service class for managing users.
 *
 * This class provides methods for handling user-related operations such as
 * creating, retrieving, updating, and deleting users. It interacts with the
 * User model to perform operations on the database.
 */
class UserService implements UserServiceInterface
{
    /**
     * Retrieve all users.
     *
     * This method fetches all the users from the database.
     *
     * @return Collection A collection of all users.
     */
    public function findAll(): Collection
    {
        return User::all();
    }

    /**
     * Retrieve a specific user by their ID.
     *
     * This method finds a user by their unique identifier. If the user is not found,
     * a ModelNotFoundException will be thrown.
     *
     * @param int $id The ID of the user.
     * @return User The user instance.
     * @throws ModelNotFoundException If the user with the specified ID is not found.
     */
    public function find(int $id): User
    {
        return User::findOrFail($id);
    }

    /**
     * Create a new user.
     *
     * This method creates a new user in the database using the provided data.
     * The password is hashed before storing it in the database.
     *
     * @param array $data The data required to create the user.
     * @return User The created user instance.
     */
    public function create(array $data): User
    {
        return User::create($data);
    }

    /**
     * Update an existing user.
     *
     * This method updates the specified user with new data. The password is also hashed
     * before updating it in the database.
     *
     * @param array $data The new data to update the user.
     * @param int $id The ID of the user to update.
     * @return User The updated user instance.
     * @throws ModelNotFoundException If the user with the specified ID is not found.
     */
    public function update(array $data, int $id): User
    {
        $user = User::findOrFail($id);
        $user->update($data);
        return $user;
    }

    /**
     * Delete a user.
     *
     * This method deletes the specified user from the database. If the user does not exist,
     * a ModelNotFoundException will be thrown.
     *
     * @param int $id The ID of the user to delete.
     * @return bool|null True if the user was deleted, false otherwise.
     * @throws ModelNotFoundException If the user with the specified ID is not found.
     */
    public function delete(int $id): ?bool
    {
        $user = User::findOrFail($id);
        return $user->delete();
    }
}
