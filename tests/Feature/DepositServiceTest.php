<?php

use App\Jobs\ProcessDeposit;
use App\Models\Deposit;
use App\Models\User;
use App\Services\DepositService;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    Queue::fake();
    $this->service = new DepositService();
    $this->user = User::factory()->create(['user_type' => 'PF']);
});

it('creates a deposit and dispatches job', function () {
    $data = [
        'user' => $this->user->id,
        'value' => 100.00,
    ];

    $deposit = $this->service->create($data);

    expect($deposit)->toBeInstanceOf(Deposit::class)
        ->and($deposit->user)->toBe($this->user->id)
        ->and($deposit->value)->toBe(100.00);

    Queue::assertPushed(ProcessDeposit::class, function ($job) use ($deposit) {
        return $job->getDeposit()->id === $deposit->id;
    });
});

it('updates a deposit by id', function () {
    $deposit = Deposit::factory()->create([
        'user' => $this->user->id,
        'value' => 50.00,
    ]);

    $data = [
        'user' => $this->user->id,
        'value' => 200.00,
    ];

    $updated = $this->service->update($data, $deposit->id);

    expect($updated->value)->toBe(200.00);
});

it('throws exception if deposit not found on update', function () {
    $nonExistentUuid = Str::uuid()->toString();

    $this->service->update([
        'user' => $this->user->id,
        'value' => 150,
    ], $nonExistentUuid);
})->throws(Illuminate\Database\Eloquent\ModelNotFoundException::class);

it('finds a deposit by id', function () {
    $deposit = Deposit::factory()->create([
        'user' => $this->user->id,
        'value' => 75,
    ]);

    $found = $this->service->find($deposit->id);

    expect($found)->toBeInstanceOf(Deposit::class)
        ->and($found->id)->toBe($deposit->id);
});

it('returns all deposits', function () {
    Deposit::factory()->count(3)->create([
        'user' => $this->user->id,
    ]);

    $deposits = $this->service->findAll();

    expect($deposits)->toHaveCount(3);
});

it('returns recent deposits from last 60 seconds', function () {
    Deposit::factory()->create([
        'user' => $this->user->id,
        'updated_at' => now()->subSeconds(30),
    ]);

    $recent = $this->service->findLastDeposits();

    expect($recent)->toHaveCount(1);
});


it('deletes a deposit by id', function () {
    $deposit = Deposit::factory()->create();

    $service = new DepositService();
    $service->delete($deposit->id);

    expect(Deposit::find($deposit->id))->toBeNull();
});
