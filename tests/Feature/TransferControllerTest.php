<?php

use App\Models\User;
use App\Models\Transfer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

uses(RefreshDatabase::class);

it('returns a list of transfers', function () {
    Transfer::factory()->count(2)->create();

    $response = $this->getJson('/api/transfer');

    $response->assertOk()
        ->assertJsonCount(2);
});

it('returns the latest transfers', function () {
    Transfer::factory()->count(3)->create();

    $response = $this->getJson('/api/transfer/lasts');

    $response->assertOk()
        ->assertJsonStructure([
            '*' => ['id', 'payer', 'payee', 'value', 'created_at']
        ]);
});

it('creates a transfer successfully', function () {
    Http::fake([
        'https://util.devi.tools/api/v2/authorize' => Http::response([
            'status' => 'success',
            'data' => ['authorization' => true]
        ], 200),
        'https://util.devi.tools/api/v1/notify' => Http::response([], 204),
    ]);

    $payer = User::factory()->create(['user_type' => 'PF']);
    $payee = User::factory()->create();

    $payer->update(['balance' => 1000]);

    $payload = [
        'payer' => $payer->id,
        'payee' => $payee->id,
        'value' => 100.00,
    ];

    $response = $this->postJson('/api/transfer', $payload);

    $response->assertCreated()
        ->assertJsonFragment([
            'payer' => $payer->id,
            'payee' => $payee->id,
            'value' => 100.00,
        ]);

    $this->assertDatabaseHas('transfers', [
        'payer' => $payer->id,
        'payee' => $payee->id,
        'value' => 100.00,
    ]);
});

it('returns a specific transfer by uuid', function () {
    $transfer = Transfer::factory()->create();

    $response = $this->getJson("/api/transfer/{$transfer->id}");

    $response->assertOk()
        ->assertJsonFragment(['id' => $transfer->id]);
});

it('returns 400 for invalid UUID in show', function () {
    $response = $this->getJson('/api/transfer/invalid-uuid');

    $response->assertStatus(Response::HTTP_BAD_REQUEST)
        ->assertJson(['error' => 'Invalid UUID format.']);
});

it('returns 404 if transfer not found', function () {
    $uuid = Str::uuid();

    $response = $this->getJson("/api/transfer/{$uuid}");

    $response->assertNotFound()
        ->assertJson(['error' => 'Transfer not found']);
});

it('updates a transfer successfully', function () {
    $payer = User::factory()->create(['user_type' => 'PF']);
    $transfer = Transfer::factory()->create(['payer' => $payer->id,'value' => 100]);

    $response = $this->putJson("/api/transfer/{$transfer->id}", [
        'payer' => $transfer->payer,
        'payee' => $transfer->payee,
        'value' => 200,
        'status' => $transfer->status,
    ]);

    $response->assertOk()
        ->assertJsonFragment(['value' => 200]);
});

it('returns 400 for invalid UUID in update', function () {
    $transfer = Transfer::factory()->create();

    $response = $this->putJson('/api/transfer/invalid-uuid', [
        'payer' => $transfer->payer,
        'payee' => $transfer->payee,
        'value' => 200,
        'status' => $transfer->status,
    ]);

    $response->assertStatus(Response::HTTP_BAD_REQUEST)
        ->assertJson(['error' => 'Invalid UUID format.']);
});

it('returns 404 if transfer not found during update', function () {
    $uuid = Str::uuid();
    $payer = User::factory()->create();
    $payee = User::factory()->create();

    $response = $this->putJson("/api/transfer/{$uuid}", [
        'payer' => $payer->id,
        'payee' => $payee->id,
        'value' => 200,
        'status' => 'completed',
    ]);

    $response->assertNotFound()
        ->assertJson(['error' => 'Transfer not found']);
});

it('deletes a transfer successfully', function () {
    $transfer = Transfer::factory()->create();

    $response = $this->deleteJson("/api/transfer/{$transfer->id}");

    $response->assertNoContent();

    $this->assertSoftDeleted('transfers', ['id' => $transfer->id]);
});

it('returns 400 for invalid UUID in destroy', function () {
    $response = $this->deleteJson('/api/transfer/invalid-uuid');

    $response->assertStatus(Response::HTTP_BAD_REQUEST)
        ->assertJson(['error' => 'Invalid UUID format.']);
});

it('returns 404 if transfer not found during delete', function () {
    $uuid = Str::uuid();

    $response = $this->deleteJson("/api/transfer/{$uuid}");

    $response->assertNotFound()
        ->assertJson(['error' => 'Transfer not found']);
});
