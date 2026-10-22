<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'referred_user_id', 'investment_deposit_claim_id', 'ledger_transaction_id', 'level', 'commission_rate', 'base_amount_usdt', 'amount_usdt'])]
class ReferralCommission extends Model
{
    protected function casts(): array
    {
        return ['commission_rate' => 'decimal:2', 'base_amount_usdt' => 'decimal:8', 'amount_usdt' => 'decimal:8'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function referredUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    public function depositClaim(): BelongsTo
    {
        return $this->belongsTo(InvestmentDepositClaim::class, 'investment_deposit_claim_id');
    }

    public function ledgerTransaction(): BelongsTo
    {
        return $this->belongsTo(LedgerTransaction::class);
    }
}
