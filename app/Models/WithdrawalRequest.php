<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['investment_account_id', 'user_id', 'amount_usdt', 'fee_rate', 'fee_amount_usdt', 'net_amount_usdt', 'network', 'status', 'reviewed_by', 'review_note', 'reviewed_at'])]
class WithdrawalRequest extends Model
{
    protected function casts(): array
    {
        return ['amount_usdt' => 'decimal:8', 'fee_rate' => 'decimal:2', 'fee_amount_usdt' => 'decimal:8', 'net_amount_usdt' => 'decimal:8', 'reviewed_at' => 'datetime'];
    }

    public function investmentAccount(): BelongsTo
    {
        return $this->belongsTo(InvestmentAccount::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
