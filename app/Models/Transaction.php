<?php

namespace App\Models;

use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Guid\Guid;


/**
 * Class Transaction
 * Represents a transaction between a payer and a payee.
 *
 * @property int $user_payer_id ID of the user who made the payment
 * @property int $user_payee_id ID of the user who received the payment
 * @property float $value Transaction amount (stored as decimal(10,2) in the database)
 * @property string $status Transaction status
 */
class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory, SoftDeletes;
    protected $keyType = 'string';
    public $incrementing = false;


    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_payer_id',
        'user_payee_id',
        'value',
        'status',
    ];

    protected static function booted(): void
    {
        static::creating(function ($transaction) {
            $transaction->id = (string) Guid::uuid4();
        });
    }

    /**
     * The payer user.
     */
    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_payer_id');
    }

    /**
     * The payee user.
     */
    public function payee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_payee_id');
    }
}
