<?php

use App\Models\User;
use App\Models\Transfer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

it('has fillable attributes: name, email, password, document, user_type, balance', function () {
    $model = new User();

    expect($model->getFillable())->toMatchArray([
        'name',
        'email',
        'password',
        'document',
        'user_type',
        'balance',
    ]);
});

it('checks user transfers relationships', function () {
    $payer = User::factory()->create();
    $payee = User::factory()->create();

    Transfer::factory()->create([
        'payer' => $payer->id,
        'payee' => $payee->id,
    ]);

    expect($payer->sentTransfers)->toHaveCount(1)
        ->and($payee->receivedTransfers)->toHaveCount(1);
});

it('sentTransfers and receivedTransfers return HasMany relationships', function () {
    $user = new User();

    expect($user->sentTransfers())->toBeInstanceOf(HasMany::class)
        ->and($user->receivedTransfers())->toBeInstanceOf(HasMany::class);
});

it('hides password and remember_token from serialization', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password'),
    ]);

    $userArray = $user->toArray();

    expect($userArray)->not->toHaveKey('password')
        ->and($userArray)->not->toHaveKey('remember_token');
});

it('has timestamps after creation', function () {
    $user = User::factory()->create();

    expect($user->created_at)->not->toBeNull()
        ->and($user->updated_at)->not->toBeNull();
});
