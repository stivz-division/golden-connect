<?php

namespace App\Http\Controllers\Invite;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InviteController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $referralLink = config('app.url').'/register?ref='.$user->uuid;
        $telegramLink = config('telegram-webapp.mini_apps_url')
            ? config('telegram-webapp.mini_apps_url').'?startapp='.$user->uuid
            : '';

        return Inertia::render('Invite/Index', [
            'referralLink' => $referralLink,
            'telegramLink' => $telegramLink,
            'referralCode' => $user->uuid,
            'stats' => [
                'totalClicks' => 0,
                'registrations' => 0,
                'activeReferrals' => $user->getDescendantCount(),
                'totalEarned' => 0,
            ],
        ]);
    }
}
