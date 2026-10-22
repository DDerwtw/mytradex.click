<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['binance_id', 'coin', 'network', 'amount', 'provider_status', 'transaction_id', 'source_address', 'occurred_at', 'last_seen_at'])]
class BinanceDepositObservation extends Model
{
    protected function casts(): array
    {
        return ['amount' => 'decimal:8', 'occurred_at' => 'datetime', 'last_seen_at' => 'datetime'];
    }

    public function investmentDepositClaims(): HasMany
    {
        return $this->hasMany(InvestmentDepositClaim::class);
    }
}
