<?php

use App\Models\Deposit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

uses(RefreshDatabase::class);

it('creates a deposit successfully', function () {
    $user = User::factory()->create();

    $deposit = Deposit::factory()->create([
        'user' => $user->id,
        'value' => 150.00,
        'status' => 'pending',
    ]);

    expect($deposit)->toBeInstanceOf(Deposit::class)
        ->and($deposit->user)->toBe($user->id)
        ->and($deposit->value)->toBe(150.00)
        ->and($deposit->status)->toBe('pending');
});

it('has fillable attributes: user, value, status', function () {
    $model = new Deposit();

    expect($model->getFillable())->toMatchArray(['user', 'value', 'status']);
});

it('ensures deposit ID is a valid UUID', function () {
    $deposit = Deposit::factory()->create();

    expect(Uuid::isValid($deposit->id))->toBeTrue();
});

it('checks deposit relationship with user', function () {
    $user = User::factory()->create();

    $deposit = Deposit::factory()->create([
        'user' => $user->id,
    ]);

    expect($deposit->receiver->id)->toBe($user->id);
});

it('receiver relationship returns BelongsTo instance', function () {
    $deposit = new Deposit();

    expect($deposit->receiver())->toBeInstanceOf(BelongsTo::class);
});

it('soft deletes a deposit', function () {
    $deposit = Deposit::factory()->create();
    $deposit->delete();

    expect(Deposit::find($deposit->id))->toBeNull()
        ->and(Deposit::withTrashed()->find($deposit->id))->not->toBeNull();
});

it('marks deposit as trashed after soft delete', function () {
    $deposit = Deposit::factory()->create();
    $deposit->delete();

    expect($deposit->fresh()->trashed())->toBeTrue();
});

it('has timestamps after creation', function () {
    $deposit = Deposit::factory()->create();

    expect($deposit->created_at)->not->toBeNull()
        ->and($deposit->updated_at)->not->toBeNull();
});

it('allows only specific status values', function () {
    $allowedStatuses = ['pending', 'completed', 'failed'];

    $deposit = Deposit::factory()->create([
        'status' => 'completed',
    ]);

    expect(in_array($deposit->status, $allowedStatuses))->toBeTrue();
});
