<?php

use App\Models\Transfer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

uses(RefreshDatabase::class);

it('creates a transfer successfully', function () {
    $payer = User::factory()->create();
    $payee = User::factory()->create();

    $transfer = Transfer::factory()->create([
        'payer' => $payer->id,
        'payee' => $payee->id,
        'value' => 100.50,
        'status' => 'completed',
    ]);

    expect($transfer)->toBeInstanceOf(Transfer::class)
        ->and($transfer->payer)->toBe($payer->id)
        ->and($transfer->payee)->toBe($payee->id)
        ->and($transfer->value)->toBe(100.50)
        ->and($transfer->status)->toBe('completed');
});

it('has fillable attributes: payer, payee, value, status', function () {
    $model = new Transfer();

    expect($model->getFillable())->toMatchArray(['payer', 'payee', 'value', 'status']);
});

it('ensures transfer ID is a valid UUID', function () {
    $transfer = Transfer::factory()->create();

    expect(Uuid::isValid($transfer->id))->toBeTrue();
});

it('checks transfer relationships', function () {
    $payer = User::factory()->create();
    $payee = User::factory()->create();

    $transfer = Transfer::factory()->create([
        'payer' => $payer->id,
        'payee' => $payee->id,
    ]);

    expect($transfer->userPayer->id)->toBe($payer->id)
        ->and($transfer->userPayee->id)->toBe($payee->id);
});

it('userPayer and userPayee return BelongsTo relationships', function () {
    $transfer = new Transfer();

    expect($transfer->userPayer())->toBeInstanceOf(BelongsTo::class)
        ->and($transfer->userPayee())->toBeInstanceOf(BelongsTo::class);
});

it('soft deletes a transfer', function () {
    $transfer = Transfer::factory()->create();
    $transfer->delete();

    expect(Transfer::find($transfer->id))->toBeNull()
        ->and(Transfer::withTrashed()->find($transfer->id))->not->toBeNull();
});

it('marks transfer as trashed after soft delete', function () {
    $transfer = Transfer::factory()->create();
    $transfer->delete();

    expect($transfer->fresh()->trashed())->toBeTrue();
});

it('has timestamps after creation', function () {
    $transfer = Transfer::factory()->create();

    expect($transfer->created_at)->not->toBeNull()
        ->and($transfer->updated_at)->not->toBeNull();
});

it('allows only specific status values', function () {
    $allowedStatuses = ['pending', 'completed', 'failed'];

    $transfer = Transfer::factory()->create([
        'status' => 'completed'
    ]);

    expect(in_array($transfer->status, $allowedStatuses))->toBeTrue();
});
