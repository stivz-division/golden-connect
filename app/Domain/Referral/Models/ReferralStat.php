<?php

namespace App\Domain\Referral\Models;

use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralStat extends Model
{
    protected $fillable = [
        'user_id',
        'web_clicks',
        'telegram_clicks',
        'web_registrations',
        'telegram_registrations',
    ];

    protected function casts(): array
    {
        return [
            'web_clicks' => 'integer',
            'telegram_clicks' => 'integer',
            'web_registrations' => 'integer',
            'telegram_registrations' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTotalClicksAttribute(): int
    {
        return $this->web_clicks + $this->telegram_clicks;
    }

    public function getTotalRegistrationsAttribute(): int
    {
        return $this->web_registrations + $this->telegram_registrations;
    }
}
