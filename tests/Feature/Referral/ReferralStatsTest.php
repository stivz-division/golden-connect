<?php

use App\Application\Referral\Actions\TrackReferralClickAction;
use App\Application\Referral\Actions\TrackReferralRegistrationAction;
use App\Domain\Referral\Enums\ReferralSource;
use App\Domain\Referral\Models\ReferralStat;
use App\Domain\User\Models\User;
use App\Domain\User\Models\VerificationCode;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// --- Модель ---

it('computes totalClicks accessor correctly', function () {
    $user = User::factory()->create();

    $stat = ReferralStat::create([
        'user_id' => $user->id,
        'web_clicks' => 5,
        'telegram_clicks' => 3,
    ]);

    expect($stat->total_clicks)->toBe(8);
});

it('computes totalRegistrations accessor correctly', function () {
    $user = User::factory()->create();

    $stat = ReferralStat::create([
        'user_id' => $user->id,
        'web_registrations' => 2,
        'telegram_registrations' => 4,
    ]);

    expect($stat->total_registrations)->toBe(6);
});

it('has user relationship', function () {
    $user = User::factory()->create();

    $stat = ReferralStat::create(['user_id' => $user->id]);

    expect($stat->user->id)->toBe($user->id);
});

// --- TrackReferralClickAction ---

it('increments web_clicks for web source', function () {
    $user = User::factory()->create();

    $action = app(TrackReferralClickAction::class);
    $action->execute($user->id, ReferralSource::Web);

    $stat = ReferralStat::where('user_id', $user->id)->first();

    expect($stat->web_clicks)->toBe(1);
    expect($stat->telegram_clicks)->toBe(0);
});

it('increments telegram_clicks for telegram source', function () {
    $user = User::factory()->create();

    $action = app(TrackReferralClickAction::class);
    $action->execute($user->id, ReferralSource::Telegram);

    $stat = ReferralStat::where('user_id', $user->id)->first();

    expect($stat->telegram_clicks)->toBe(1);
    expect($stat->web_clicks)->toBe(0);
});

it('increments clicks multiple times', function () {
    $user = User::factory()->create();

    $action = app(TrackReferralClickAction::class);
    $action->execute($user->id, ReferralSource::Web);
    $action->execute($user->id, ReferralSource::Web);
    $action->execute($user->id, ReferralSource::Telegram);

    $stat = ReferralStat::where('user_id', $user->id)->first();

    expect($stat->web_clicks)->toBe(2);
    expect($stat->telegram_clicks)->toBe(1);
});

// --- TrackReferralRegistrationAction ---

it('increments web_registrations for web source', function () {
    $user = User::factory()->create();

    $action = app(TrackReferralRegistrationAction::class);
    $action->execute($user->id, ReferralSource::Web);

    $stat = ReferralStat::where('user_id', $user->id)->first();

    expect($stat->web_registrations)->toBe(1);
    expect($stat->telegram_registrations)->toBe(0);
});

it('increments telegram_registrations for telegram source', function () {
    $user = User::factory()->create();

    $action = app(TrackReferralRegistrationAction::class);
    $action->execute($user->id, ReferralSource::Telegram);

    $stat = ReferralStat::where('user_id', $user->id)->first();

    expect($stat->telegram_registrations)->toBe(1);
    expect($stat->web_registrations)->toBe(0);
});

// --- InviteController ---

it('returns real referral stats on invite page', function () {
    $user = User::factory()->create();

    ReferralStat::create([
        'user_id' => $user->id,
        'web_clicks' => 10,
        'telegram_clicks' => 5,
        'web_registrations' => 3,
        'telegram_registrations' => 2,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('invite'));

    $response->assertInertia(fn ($page) => $page
        ->component('Invite/Index')
        ->where('stats.totalClicks', 15)
        ->where('stats.registrations', 5)
    );
});

it('returns zero stats when no referral_stat record exists', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('invite'));

    $response->assertInertia(fn ($page) => $page
        ->where('stats.totalClicks', 0)
        ->where('stats.registrations', 0)
    );
});

// --- Feature: переход по реферальной ссылке инкрементирует web_clicks ---

it('tracks web click when visiting register with ref param', function () {
    $mentor = User::factory()->create();
    $mentor->saveAsRoot();

    $this
        ->withSession(['locale' => 'ru'])
        ->get(route('register', ['ref' => $mentor->uuid]));

    $stat = ReferralStat::where('user_id', $mentor->id)->first();

    expect($stat)->not->toBeNull();
    expect($stat->web_clicks)->toBe(1);
});

it('does not double-count clicks on page refresh', function () {
    $mentor = User::factory()->create();
    $mentor->saveAsRoot();

    $this
        ->withSession(['locale' => 'ru'])
        ->get(route('register', ['ref' => $mentor->uuid]));

    $this
        ->withSession(['locale' => 'ru'])
        ->get(route('register', ['ref' => $mentor->uuid]));

    $stat = ReferralStat::where('user_id', $mentor->id)->first();

    expect($stat)->not->toBeNull();
    expect($stat->web_clicks)->toBe(1);
});

it('tracks registration via referral link', function () {
    $mentor = User::factory()->create();
    $mentor->saveAsRoot();

    VerificationCode::create([
        'identifier' => '+79997654321',
        'type' => 'phone',
        'code' => '111111',
        'expires_at' => now()->addMinutes(5),
    ]);

    $this->withoutMiddleware(ValidateCsrfToken::class)
        ->withSession(['locale' => 'ru'])
        ->post(route('register'), [
            'type' => 'phone',
            'identifier' => '+79997654321',
            'code' => '111111',
            'ref' => $mentor->uuid,
        ]);

    $stat = ReferralStat::where('user_id', $mentor->id)->first();

    expect($stat)->not->toBeNull();
    expect($stat->web_registrations)->toBe(1);
});
