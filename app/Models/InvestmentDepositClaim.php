<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['investment_account_id', 'user_id', 'transaction_id', 'source_address', 'receipt_path', 'amount_usdt', 'network', 'status', 'trc20_deposit_observation_id', 'reviewed_by', 'review_note', 'reviewed_at'])]
class InvestmentDepositClaim extends Model
{
    protected function casts(): array
    {
        return ['amount_usdt' => 'decimal:8', 'reviewed_at' => 'datetime'];
    }

    public function investmentAccount(): BelongsTo
    {
        return $this->belongsTo(InvestmentAccount::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function observation(): BelongsTo
    {
        return $this->belongsTo(Trc20DepositObservation::class, 'trc20_deposit_observation_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
