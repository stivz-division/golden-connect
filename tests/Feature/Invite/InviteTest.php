<?php

use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects guests to login', function () {
    $response = $this->get(route('invite'));

    $response->assertRedirect(route('login'));
});

it('renders invite page for authenticated user', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('invite'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Invite/Index')
        ->has('referralLink')
        ->has('telegramLink')
        ->has('referralCode')
        ->has('stats')
    );
});

it('passes correct referral code', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('invite'));

    $response->assertInertia(fn ($page) => $page
        ->where('referralCode', $user->uuid)
    );
});

it('builds correct referral link', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('invite'));

    $expectedLink = config('app.url').'/register?ref='.$user->uuid;

    $response->assertInertia(fn ($page) => $page
        ->where('referralLink', $expectedLink)
    );
});
