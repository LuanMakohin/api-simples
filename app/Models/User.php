<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * Class User
 *
 * @property string $id User's unique identifier
 * @property string $name User's full name
 * @property string $email User's email address
 * @property string $password Hashed password
 * @property string $document Document number (CPF/CNPJ)
 * @property string $user_type Type of user (e.g., 'customer', 'merchant')
 * @property float $balance Current user balance
 * @property Carbon|null $email_verified_at Email verification timestamp
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
        'balance'
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
     * Transactions where the user is the payer.
     */
    public function sentTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'user_payer_id');
    }

    /**
     * Transactions where the user is the payee.
     */
    public function receivedTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'user_payee_id');
    }
}
