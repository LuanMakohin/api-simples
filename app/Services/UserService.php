<?php

namespace App\Services;

use App\Interfaces\UserServiceInterface;
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
class UserService implements UserServiceInterface
{
    public function findAll(): Collection
    {
        return User::all();
    }

    public function find(string $id): User
    {
        return User::findOrFail($id);
    }

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

    public function delete(string $id): ?bool
    {
        $user = User::findOrFail($id);
        return $user->delete();
    }
}
