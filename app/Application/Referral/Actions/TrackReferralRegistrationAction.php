<?php

namespace App\Application\Referral\Actions;

use App\Domain\Referral\Enums\ReferralSource;
use App\Domain\Referral\Models\ReferralStat;
use Illuminate\Support\Facades\Log;

class TrackReferralRegistrationAction
{
    public function execute(int $mentorId, ReferralSource $source): void
    {
        $column = match ($source) {
            ReferralSource::Web => 'web_registrations',
            ReferralSource::Telegram => 'telegram_registrations',
        };

        $stat = ReferralStat::firstOrCreate(['user_id' => $mentorId]);
        $stat->increment($column);

        Log::info('Referral registration tracked', [
            'mentor_id' => $mentorId,
            'source' => $source->value,
            'column' => $column,
        ]);
    }
}
