<?php

namespace App\Models;

use Database\Factories\TransferFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Ramsey\Uuid\Uuid;

/**
 * Class Transfer
 *
 * Represents a transfer between two users: a payer and a payee.
 *
 * @property string $id UUID of the transfer.
 * @property int $payer ID of the user who made the payment.
 * @property int $payee ID of the user who received the payment.
 * @property float $value Amount of the transfer.
 * @property string $status Status of the transfer (e.g., pending, success, failed).
 * @property Carbon|null $created_at Timestamp when the transfer was created.
 * @property Carbon|null $updated_at Timestamp when the transfer was last updated.
 * @property Carbon|null $deleted_at Timestamp when the transfer was soft deleted.
 *
 * @property-read User $userPayer The user who initiated the transfer.
 * @property-read User $userPayee The user who received the transfer.
 */
class Transfer extends Model
{
    /** @use HasFactory<TransferFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Indicates that the primary key is a non-incrementing UUID string.
     *
     * @var string
     */
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'payer',
        'payee',
        'value',
        'status',
    ];

    /**
     * Booted model event to automatically generate UUID on creation.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::creating(function ($transfer) {
            $transfer->id = (string) Uuid::uuid4();
        });
    }

    /**
     * Get the user who made the payment (payer).
     *
     * @return BelongsTo
     */
    public function userPayer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payer');
    }

    /**
     * Get the user who received the payment (payee).
     *
     * @return BelongsTo
     */
    public function userPayee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payee');
    }
}
