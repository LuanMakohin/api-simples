<?php

use App\Models\Deposit;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a user with correct attributes', function () {
    $user = User::factory()->create([
        'name' => 'Lua',
        'email' => 'lua@example.com',
        'document' => '12345678900',
        'user_type' => 'PF',
        'balance' => 150.00,
    ]);

    expect($user)
        ->name->toBe('Lua')
        ->and($user->email)->toBe('lua@example.com')
        ->and($user->document)->toBe('12345678900')
        ->and($user->user_type)->toBe('PF')
        ->and($user->balance)->toEqual(150.00);
});

it('has many sent transfers', function () {
    $user = User::factory()->create();
    $transfers = Transfer::factory()->count(2)->create([
        'payer' => $user->id,
    ]);

    expect($user->sentTransfers)->toHaveCount(2);
});

it('has many received transfers', function () {
    $user = User::factory()->create();
    $transfers = Transfer::factory()->count(3)->create([
        'payee' => $user->id,
    ]);

    expect($user->receivedTransfers)->toHaveCount(3);
});

it('has many received deposits', function () {
    $user = User::factory()->create();
    $deposits = Deposit::factory()->count(2)->create([
        'user' => $user->id,
    ]);

    expect($user->receivedDeposits)->toHaveCount(2);
});

it('soft deletes a user', function () {
    $user = User::factory()->create();
    $user->delete();

    $this->assertSoftDeleted('users', [
        'id' => $user->id,
    ]);
});
