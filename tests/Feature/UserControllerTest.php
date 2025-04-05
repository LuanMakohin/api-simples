<?php

use App\Models\User;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('lists all users', function () {
    User::factory()->count(3)->create();

    $response = $this->getJson('/api/user');

    $response->assertOk()
        ->assertJsonStructure([['id', 'name', 'email']]);
});

it('creates a new user', function () {
    $data = [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'document' => '00000000000',
        'user_type' => 'PF',
        'balance' => 200.0
    ];

    $response = $this->postJson('/api/user', $data);

    $response->assertCreated()
        ->assertJsonFragment(['email' => 'jane@example.com']);
});

it('fails to create a user with invalid data', function () {
    $response = $this->postJson('/api/user', [
        'email' => 'invalid-email',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});

it('shows a specific user', function () {
    $response = $this->getJson("/api/user/{$this->user->id}");

    $response->assertOk()
        ->assertJsonFragment(['email' => $this->user->email]);
});

it('returns 404 if user not found', function () {
    $id = 999999;

    $response = $this->getJson("/api/user/{$id}");

    $response->assertStatus(404)
        ->assertJsonFragment(['error' => 'User not found']);
});

it('updates a user', function () {
    $response = $this->putJson("/api/user/{$this->user->id}", [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
        'password' => 'newpassword',
        'password_confirmation' => 'newpassword',
    ]);

    $response->assertOk()
        ->assertJsonFragment(['email' => 'updated@example.com']);
});

it('returns 404 if user not found on update', function () {
    $id = 999999;

    $response = $this->putJson("/api/user/{$id}", [
        'name' => 'Name',
        'email' => 'email@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertStatus(404)
        ->assertJsonFragment(['error' => 'User not found']);
});

it('deletes a user', function () {
    $response = $this->deleteJson("/api/user/{$this->user->id}");

    $response->assertNoContent();
});

it('returns 404 if user not found on delete', function () {
    $id = 999999;

    $response = $this->deleteJson("/api/user/{$id}");

    $response->assertStatus(404)
        ->assertJsonFragment(['error' => 'User not found']);
});
