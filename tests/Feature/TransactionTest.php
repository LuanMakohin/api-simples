<?php

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Ramsey\Uuid\Uuid;

uses(RefreshDatabase::class);

it('creates a transaction successfully', function () {
    $payer = User::factory()->create();
    $payee = User::factory()->create();

    $transaction = Transaction::factory()->create([
        'user_payer_id' => $payer->id,
        'user_payee_id' => $payee->id,
        'value' => 100.50,
        'status' => 'completed',
    ]);

    expect($transaction)->toBeInstanceOf(Transaction::class)
        ->and($transaction->user_payer_id)->toBe($payer->id)
        ->and($transaction->user_payee_id)->toBe($payee->id)
        ->and($transaction->value)->toBe(100.50)
        ->and($transaction->status)->toBe('completed');
});

it('ensures transaction ID is a valid UUID', function () {
    $transaction = Transaction::factory()->create();
    expect(Uuid::isValid($transaction->id))->toBeTrue();
});

it('checks transaction relationships', function () {
    $payer = User::factory()->create();
    $payee = User::factory()->create();

    $transaction = Transaction::factory()->create([
        'user_payer_id' => $payer->id,
        'user_payee_id' => $payee->id,
    ]);

    expect($transaction->payer->id)->toBe($payer->id)
        ->and($transaction->payee->id)->toBe($payee->id);
});

it('soft deletes a transaction', function () {
    $transaction = Transaction::factory()->create();
    $transaction->delete();

    expect(Transaction::find($transaction->id))->toBeNull()
        ->and(Transaction::withTrashed()->find($transaction->id))->not->toBeNull();
});
