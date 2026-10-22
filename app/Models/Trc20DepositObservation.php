<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['transaction_id', 'coin', 'network', 'contract_address', 'source_address', 'destination_address', 'amount', 'provider_status', 'occurred_at', 'last_seen_at'])]
class Trc20DepositObservation extends Model
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
