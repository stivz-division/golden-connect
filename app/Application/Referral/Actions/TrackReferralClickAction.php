<?php

namespace App\Application\Referral\Actions;

use App\Domain\Referral\Enums\ReferralSource;
use App\Domain\Referral\Models\ReferralStat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TrackReferralClickAction
{
    public function execute(int $mentorId, ReferralSource $source): void
    {
        $column = match ($source) {
            ReferralSource::Web => 'web_clicks',
            ReferralSource::Telegram => 'telegram_clicks',
        };

        ReferralStat::query()->upsert(
            ['user_id' => $mentorId, $column => 1],
            ['user_id'],
            [$column => DB::raw("`{$column}` + 1")],
        );

        Log::debug('Referral click tracked', [
            'mentor_id' => $mentorId,
            'source' => $source->value,
            'column' => $column,
        ]);
    }
}
