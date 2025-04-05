<?php

use App\Models\Deposit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

uses(RefreshDatabase::class);

it('returns a list of deposits', function () {
    Deposit::factory()->count(2)->create();

    $response = $this->getJson('/api/deposit');

    $response->assertOk()
        ->assertJsonCount(2);
});

it('returns the latest deposits', function () {
    Deposit::factory()->count(3)->create();

    $response = $this->getJson('/api/deposit/lasts');
    $response->assertOk()
        ->assertJsonStructure([
            '*' => ['id', 'user', 'value', 'created_at']
        ]);
});

use Illuminate\Support\Facades\Http;

it('creates a deposit successfully', function () {
    Http::fake([
        'https://util.devi.tools/api/v2/authorize' => Http::response([
            'status' => 'success',
            'data' => [
                'authorization' => true
            ]
        ], 200),

        'https://util.devi.tools/api/v1/notify' => Http::response([

        ], 204),
    ]);

    $user = User::factory()->create(['user_type' => 'PF']);

    $payload = [
        'user' => $user->id,
        'value' => 100.00,
    ];

    $response = $this->postJson('/api/deposit', $payload);

    $response->assertCreated()
        ->assertJsonFragment([
            'user' => $user->id,
            'value' => 100.00,
        ]);

    $this->assertDatabaseHas('deposits', [
        'user' => $user->id,
        'value' => 100.00,
    ]);
});

it('returns a specific deposit by uuid', function () {
    $deposit = Deposit::factory()->create();

    $response = $this->getJson("/api/deposit/{$deposit->id}");

    $response->assertOk()
        ->assertJsonFragment([
            'id' => $deposit->id,
            'user' => $deposit->user,
        ]);
});

it('returns 400 for invalid UUID in show', function () {
    $response = $this->getJson('/api/deposit/invalid-uuid');

    $response->assertStatus(Response::HTTP_BAD_REQUEST)
        ->assertJson(['error' => 'Invalid UUID format.']);
});

it('returns 404 if deposit not found', function () {
    $uuid = Str::uuid();

    $response = $this->getJson("/api/deposit/{$uuid}");

    $response->assertNotFound()
        ->assertJson(['error' => 'Deposit not found']);
});

it('updates a deposit successfully', function () {
    $user = User::factory()->create(['user_type' => 'PF']);

    $deposit = Deposit::factory()->create([
        'user' => $user->id,
        'value' => 100.00,
        'status' => 'pending',
    ]);

    $response = $this->putJson("/api/deposit/{$deposit->id}", [
        'user' => $user->id,
        'value' => 200.00,
        'status' => 'completed',
    ]);

    $response->assertOk()
        ->assertJsonFragment(['value' => 200.00]);
});

it('returns 400 for invalid UUID in update', function () {
    $user = User::factory()->create();

    $response = $this->putJson('/api/deposit/invalid-uuid', [
        'user' => $user->id,
        'value' => 200.00,
        'status' => 'completed'
    ]);

    $response->assertStatus(Response::HTTP_BAD_REQUEST)
        ->assertJson(['error' => 'Invalid UUID format.']);
});

it('returns 404 if deposit not found during update', function () {
    $uuid = Str::uuid();
    $user = User::factory()->create(['user_type' => 'PF']);


    $response = $this->putJson("/api/deposit/{$uuid}", [
        'user' => $user->id,
        'value' => 200.00,
        'status' => 'completed'
    ]);

    $response->assertNotFound()
        ->assertJson(['error' => 'Deposit or receiver not found']);
});

it('deletes a deposit successfully', function () {
    $deposit = Deposit::factory()->create();

    $response = $this->deleteJson("/api/deposit/{$deposit->id}");

    $response->assertNoContent();

    $this->assertSoftDeleted('deposits', ['id' => $deposit->id]);
});

it('returns 400 for invalid UUID in destroy', function () {
    $response = $this->deleteJson('/api/deposit/invalid-uuid');

    $response->assertStatus(Response::HTTP_BAD_REQUEST)
        ->assertJson(['error' => 'Invalid UUID format.']);
});

it('returns 404 if deposit not found during delete', function () {
    $uuid = Str::uuid();

    $response = $this->deleteJson("/api/deposit/{$uuid}");

    $response->assertNotFound()
        ->assertJson(['error' => 'Deposit not found']);
});
