<?php

use App\Models\Transfer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Ramsey\Uuid\Uuid;

uses(RefreshDatabase::class);

it('creates a transfer successfully', function () {
    $payer = User::factory()->create();
    $payee = User::factory()->create();

    $transfer = Transfer::factory()->create([
        'user_payer_id' => $payer->id,
        'user_payee_id' => $payee->id,
        'value' => 100.50,
        'status' => 'completed',
    ]);

    expect($transfer)->toBeInstanceOf(Transfer::class)
        ->and($transfer->user_payer_id)->toBe($payer->id)
        ->and($transfer->user_payee_id)->toBe($payee->id)
        ->and($transfer->value)->toBe(100.50)
        ->and($transfer->status)->toBe('completed');
});

it('ensures transfer ID is a valid UUID', function () {
    $transfer = Transfer::factory()->create();
    expect(Uuid::isValid($transfer->id))->toBeTrue();
});

it('checks transfer relationships', function () {
    $payer = User::factory()->create();
    $payee = User::factory()->create();

    $transfer = Transfer::factory()->create([
        'user_payer_id' => $payer->id,
        'user_payee_id' => $payee->id,
    ]);

    expect($transfer->payer->id)->toBe($payer->id)
        ->and($transfer->payee->id)->toBe($payee->id);
});

it('soft deletes a transfer', function () {
    $transfer = Transfer::factory()->create();
    $transfer->delete();

    expect(Transfer::find($transfer->id))->toBeNull()
        ->and(Transfer::withTrashed()->find($transfer->id))->not->toBeNull();
});
