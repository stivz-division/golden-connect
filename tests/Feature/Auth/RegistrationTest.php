<?php

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders registration page', function () {
    $response = $this
        ->withSession(['locale' => 'ru'])
        ->get(route('register'));

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

it('registers user with valid ref and attaches to mentor', function () {
    $mentor = User::factory()->create(['login' => 'mentor1']);
    $mentor->saveAsRoot();

    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->withSession(['locale' => 'ru'])
        ->post(route('register'), [
            'login' => 'refuser',
            'name' => 'Ref',
            'surname' => 'User',
            'email' => 'ref@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'ref' => 'mentor1',
        ]);

    $this->assertAuthenticated();

    $newUser = User::where('login', 'refuser')->first();
    $mentor->refresh();

    expect($newUser)->not->toBeNull();
    expect($newUser->parent_id)->toBe($mentor->id);
    expect($newUser->isDescendantOf($mentor))->toBeTrue();
});

it('blocks registration with invalid ref', function () {
    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->withSession(['locale' => 'ru'])
        ->post(route('register'), [
            'login' => 'blockeduser',
            'name' => 'Blocked',
            'surname' => 'User',
            'email' => 'blocked@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'ref' => 'nonexistent_mentor',
        ]);

    $response->assertSessionHasErrors(['ref']);
    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['login' => 'blockeduser']);
});

it('registers user without ref and attaches to first user', function () {
    $firstUser = User::factory()->create(['login' => 'firstuser']);
    $firstUser->saveAsRoot();

    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->withSession(['locale' => 'ru'])
        ->post(route('register'), [
            'login' => 'norefuser',
            'name' => 'NoRef',
            'surname' => 'User',
            'email' => 'noref@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

    $this->assertAuthenticated();

    $newUser = User::where('login', 'norefuser')->first();

    expect($newUser)->not->toBeNull();
    expect($newUser->parent_id)->toBe($firstUser->id);
});

it('passes ref query parameter as inertia prop on register page', function () {
    $response = $this
        ->withSession(['locale' => 'ru'])
        ->get(route('register', ['ref' => 'testmentor']));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Auth/Register')
        ->where('ref', 'testmentor')
        ->where('mentorLogin', 'testmentor')
    );
});

it('passes first user login as mentorLogin when no ref provided', function () {
    $firstUser = User::factory()->create(['login' => 'defaultmentor']);

    $response = $this
        ->withSession(['locale' => 'ru'])
        ->get(route('register'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Auth/Register')
        ->where('ref', null)
        ->where('mentorLogin', 'defaultmentor')
    );
});
