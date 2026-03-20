<?php

use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns mentor data for valid uuid', function () {
    $user = User::factory()->create([
        'name' => 'Ivan',
        'surname' => 'Petrov',
    ]);

    $response = $this->getJson("/api/mentor/{$user->uuid}");

    $response
        ->assertOk()
        ->assertJson([
            'uuid' => $user->uuid,
            'name' => 'Ivan',
            'surname' => 'Petrov',
        ]);
});

it('returns 404 for nonexistent mentor uuid', function () {
    $response = $this->getJson('/api/mentor/00000000-0000-0000-0000-000000000000');

    $response
        ->assertNotFound()
        ->assertJsonStructure(['message']);
});
