<?php

namespace App\Application\Referral\Actions;

use App\Domain\Referral\Enums\ReferralSource;
use App\Domain\Referral\Models\ReferralStat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TrackReferralRegistrationAction
{
    public function execute(int $mentorId, ReferralSource $source): void
    {
        $column = match ($source) {
            ReferralSource::Web => 'web_registrations',
            ReferralSource::Telegram => 'telegram_registrations',
        };

        ReferralStat::query()->upsert(
            ['user_id' => $mentorId, $column => 1],
            ['user_id'],
            [$column => DB::raw("`{$column}` + 1")],
        );

        Log::info('Referral registration tracked', [
            'mentor_id' => $mentorId,
            'source' => $source->value,
            'column' => $column,
        ]);
    }
}
