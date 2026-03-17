<?php

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders registration page', function () {
    $response = $this->get(route('register'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Auth/Register'));
});

it('registers a new user and redirects to dashboard', function () {
    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->withSession(['locale' => 'ru'])
        ->post(route('register'), [
            'login' => 'newuser',
            'name' => 'John',
            'surname' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard'));

    $this->assertDatabaseHas('users', [
        'login' => 'newuser',
        'email' => 'john@example.com',
        'name' => 'John',
        'surname' => 'Doe',
    ]);
});

it('creates user in nestedset tree as root when no users exist', function () {
    $this->withoutMiddleware(ValidateCsrfToken::class)
        ->withSession(['locale' => 'ru'])
        ->post(route('register'), [
            'login' => 'firstuser',
            'name' => 'First',
            'surname' => 'User',
            'email' => 'first@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

    $user = User::where('login', 'firstuser')->first();

    expect($user)->not->toBeNull();
    expect($user->parent_id)->toBeNull();
    expect($user->isRoot())->toBeTrue();
});

it('creates user in nestedset tree as child of first user', function () {
    $root = User::factory()->create();
    $root->saveAsRoot();

    $this->withoutMiddleware(ValidateCsrfToken::class)
        ->withSession(['locale' => 'ru'])
        ->post(route('register'), [
            'login' => 'childuser',
            'name' => 'Child',
            'surname' => 'User',
            'email' => 'child@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

    $root->refresh();

    $child = User::where('login', 'childuser')->first();

    expect($child)->not->toBeNull();
    expect($child->parent_id)->toBe($root->id);
    expect($child->isDescendantOf($root))->toBeTrue();
});

it('requires all registration fields', function () {
    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->withSession(['locale' => 'ru'])
        ->post(route('register'), []);

    $response->assertSessionHasErrors(['login', 'name', 'surname', 'email', 'password']);
    $this->assertGuest();
});

it('requires unique login', function () {
    User::factory()->create(['login' => 'taken']);

    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->withSession(['locale' => 'ru'])
        ->post(route('register'), [
            'login' => 'taken',
            'name' => 'John',
            'surname' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

    $response->assertSessionHasErrors(['login']);
    $this->assertGuest();
});

it('requires unique email', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->withSession(['locale' => 'ru'])
        ->post(route('register'), [
            'login' => 'newuser',
            'name' => 'John',
            'surname' => 'Doe',
            'email' => 'taken@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

    $response->assertSessionHasErrors(['email']);
    $this->assertGuest();
});

it('requires password confirmation', function () {
    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->withSession(['locale' => 'ru'])
        ->post(route('register'), [
            'login' => 'newuser',
            'name' => 'John',
            'surname' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'DifferentPassword!',
        ]);

    $response->assertSessionHasErrors(['password']);
    $this->assertGuest();
});

it('redirects authenticated user from register page to dashboard', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('register'));

    $response->assertRedirect(route('dashboard'));
});
