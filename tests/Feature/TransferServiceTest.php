<?php

use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\SelfTransferException;
use App\Exceptions\UnauthorizedPayerException;
use App\Jobs\ProcessTransfer;
use App\Models\Transfer;
use App\Models\User;
use App\Services\TransferService;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new TransferService();

    $this->payer = User::factory()->create([
        'balance' => 1000,
        'user_type' => 'PF',
    ]);

    $this->payee = User::factory()->create([
        'balance' => 500,
        'user_type' => 'PF',
    ]);
});

it('creates a transfer and dispatches job', function () {
    Queue::fake();

    $transfer = $this->service->create([
        'payer' => $this->payer->id,
        'payee' => $this->payee->id,
        'value' => 200,
    ]);

    expect($transfer)
        ->payer->toBe($this->payer->id)
        ->and($transfer->payee)->toBe($this->payee->id)
        ->and($transfer->value)->toEqual(200.00)
        ->and($transfer->status)->toBe('pending');

    Queue::assertPushed(ProcessTransfer::class, function ($job) use ($transfer) {
        return $job->getTransfer()->id === $transfer->id;
    });
});

it('throws exception for self transfer', function () {
    $this->service->create([
        'payer' => $this->payer->id,
        'payee' => $this->payer->id,
        'value' => 100,
    ]);
})->throws(SelfTransferException::class);

it('throws exception for unauthorized payer (PJ)', function () {
    $pj = User::factory()->create([
        'user_type' => 'PJ',
        'balance' => 500,
    ]);

    $this->service->create([
        'payer' => $pj->id,
        'payee' => $this->payee->id,
        'value' => 100,
    ]);
})->throws(UnauthorizedPayerException::class);

it('throws exception for insufficient balance', function () {
    $this->payer->update(['balance' => 50]);

    $this->service->create([
        'payer' => $this->payer->id,
        'payee' => $this->payee->id,
        'value' => 200,
    ]);
})->throws(InsufficientBalanceException::class);

it('updates a transfer by id', function () {
    $payer = User::factory()->create(['user_type' => 'PF']);
    $transfer = Transfer::factory()->create(['payer' => $payer->id,'value' => 100]);

    $updated = $this->service->update([
        'payer' => $transfer->userPayer->id,
        'payee' => $transfer->userPayee->id,
        'value' => 999.99,
    ], $transfer->id);

    expect($updated->value)->toBe(999.99);
});

it('throws exception if transfer not found on update', function () {
    $nonExistentUuid = Str::uuid()->toString();

    $this->service->update([
        'value' => 999.99,
    ], $nonExistentUuid);
})->throws(Illuminate\Database\Eloquent\ModelNotFoundException::class);

it('deletes a transfer', function () {
    $transfer = Transfer::factory()->create();

    $this->service->delete($transfer->id);

    $this->assertSoftDeleted('transfers', [
        'id' => $transfer->id,
    ]);
});
