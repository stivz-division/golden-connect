<?php

namespace App\Application\Referral\Actions;

use App\Domain\Referral\Enums\ReferralSource;
use App\Domain\Referral\Models\ReferralStat;
use Illuminate\Support\Facades\Log;

class TrackReferralClickAction
{
    public function execute(int $mentorId, ReferralSource $source): void
    {
        $column = match ($source) {
            ReferralSource::Web => 'web_clicks',
            ReferralSource::Telegram => 'telegram_clicks',
        };

        $stat = ReferralStat::firstOrCreate(['user_id' => $mentorId]);
        $stat->increment($column);

        Log::debug('Referral click tracked', [
            'mentor_id' => $mentorId,
            'source' => $source->value,
            'column' => $column,
        ]);
    }
}
