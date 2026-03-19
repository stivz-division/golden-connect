<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns mentor data for valid login', function () {
    User::factory()->create([
        'login' => 'mentor1',
        'name' => 'Ivan',
        'surname' => 'Petrov',
    ]);

    $response = $this->getJson('/api/mentor/mentor1');

    $response
        ->assertOk()
        ->assertJson([
            'login' => 'mentor1',
            'name' => 'Ivan',
            'surname' => 'Petrov',
        ]);
});

it('returns 404 for nonexistent mentor login', function () {
    $response = $this->getJson('/api/mentor/nonexistent');

    $response
        ->assertNotFound()
        ->assertJsonStructure(['message']);
});
