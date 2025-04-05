<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * Class User
 *
 * Represents a user within the system, including their personal details and balance information.
 *
 * @property string $id Unique identifier for the user.
 * @property string $name User's full name.
 * @property string $email User's email address.
 * @property string $password Hashed password of the user.
 * @property string $document Document number (e.g., CPF or CNPJ).
 * @property string $user_type Type of user (e.g., 'customer', 'merchant').
 * @property float $balance Current balance of the user.
 * @property Carbon|null $email_verified_at Timestamp for email verification.
 *
 * @property-read Collection|Transfer[] $sentTransfers Transfers initiated by the user (payer).
 * @property-read Collection|Transfer[] $receivedTransfers Transfers received by the user (payee).
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'document',
        'user_type',
        'balance',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get all the transfers where the user is the payer.
     *
     * @return HasMany
     */
    public function sentTransfers(): HasMany
    {
        return $this->hasMany(Transfer::class, 'payer');
    }

    /**
     * Get all the transfers where the user is the payee.
     *
     * @return HasMany
     */
    public function receivedTransfers(): HasMany
    {
        return $this->hasMany(Transfer::class, 'payee');
    }

    /**
     * Get all the deposits where the user is the payee.
     *
     * @return HasMany
     */
    public function receivedDeposits(): HasMany
    {
        return $this->hasMany(Deposit::class, 'user');
    }
}
