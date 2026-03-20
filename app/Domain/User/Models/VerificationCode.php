<?php

namespace App\Domain\User\Models;

use App\Domain\User\Enums\ContactType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationCode extends Model
{
    protected $fillable = [
        'user_id',
        'identifier',
        'type',
        'code',
        'expires_at',
        'verified_at',
        'attempts',
    ];

    protected function casts(): array
    {
        return [
            'type' => ContactType::class,
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForIdentifier(Builder $query, string $identifier): Builder
    {
        return $query->where('identifier', $identifier);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('verified_at')
            ->where('expires_at', '>', now());
    }

    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderByDesc('created_at');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    public function hasExceededAttempts(int $max = 5): bool
    {
        return $this->attempts >= $max;
    }
}
