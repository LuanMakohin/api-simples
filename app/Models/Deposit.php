<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Ramsey\Uuid\Uuid;

/**
 * Class Deposit
 *
 * Represents a deposit made by a user.
 *
 * @property string $id UUID of the deposit.
 * @property int $user ID of the user who made the deposit.
 * @property float $value Amount of the deposit.
 * @property string $status Status of the deposit (e.g., pending, success, failed).
 * @property Carbon|null $created_at Timestamp when the deposit was created.
 * @property Carbon|null $updated_at Timestamp when the deposit was last updated.
 * @property Carbon|null $deleted_at Timestamp when the deposit was soft deleted.
 *
 * @property-read User $receiver The user who received the deposit.
 */
class Deposit extends Model
{
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
        'user',
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
        static::creating(function ($deposit) {
            $deposit->id = (string) Uuid::uuid4();
        });
    }

    /**
     * Get the user who received the deposit.
     *
     * @return BelongsTo
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user');
    }
}
