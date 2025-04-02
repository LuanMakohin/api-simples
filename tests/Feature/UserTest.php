<?php

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a user successfully', function () {
    $user = User::factory()->create([
        'name' => 'Pedro Paulo',
        'email' => 'ppa@example.com',
        'password' => bcrypt('password'),
        'document' => '65065182000',
        'user_type' => 'PF',
        'balance' => 500.00,
    ]);

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->name)->toBe('Pedro Paulo')
        ->and($user->email)->toBe('ppa@example.com')
        ->and($user->document)->toBe('65065182000')
        ->and($user->user_type)->toBe('PF')
        ->and($user->balance)->toBe(500.00);
});

it('checks user transactions relationships', function () {
    $payer = User::factory()->create();
    $payee = User::factory()->create();

    $transaction = Transaction::factory()->create([
        'user_payer_id' => $payer->id,
        'user_payee_id' => $payee->id,
    ]);

    expect($payer->sentTransactions)->toHaveCount(1)
        ->and($payee->receivedTransactions)->toHaveCount(1);
});

it('hides password and remember_token from serialization', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password'),
    ]);

    $userArray = $user->toArray();

    expect($userArray)->not->toHaveKey('password')
        ->and($userArray)->not->toHaveKey('remember_token');
});
