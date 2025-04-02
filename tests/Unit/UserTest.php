<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Transaction;

uses(RefreshDatabase::class);

test('successfully transaction', function () {
    $payer = User::factory()->create();
    $payee = User::factory()->create();

    $transaction = Transaction::create([
        'user_payer_id' => $payer->id,
        'user_payee_id' => $payee->id,
        'value' => '100.00',
        'transaction_type' => 'transfer',
    ]);
});
