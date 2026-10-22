<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'package_name', 'principal_usdt', 'daily_rate', 'maturity_days', 'estimated_reward_usdt', 'minimum_withdrawal_usdt', 'started_at', 'matures_at', 'status'])]
class InvestmentAccount extends Model
{
    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'matures_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function withdrawalRequests(): HasMany
    {
        return $this->hasMany(WithdrawalRequest::class);
    }

    public function depositClaims(): HasMany
    {
        return $this->hasMany(InvestmentDepositClaim::class);
    }

    public function estimatedTotal(): float
    {
        return (float) $this->principal_usdt + (float) $this->estimated_reward_usdt;
    }
}
