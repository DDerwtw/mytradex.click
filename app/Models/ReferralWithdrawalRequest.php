<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'gross_amount_usdt', 'fee_rate', 'fee_amount_usdt', 'net_amount_usdt', 'network', 'wallet_address', 'scheduled_for', 'status', 'reviewed_by', 'review_note', 'payout_transaction_id', 'reviewed_at', 'paid_at'])]
class ReferralWithdrawalRequest extends Model
{
    protected function casts(): array
    {
        return ['gross_amount_usdt' => 'decimal:8', 'fee_rate' => 'decimal:2', 'fee_amount_usdt' => 'decimal:8', 'net_amount_usdt' => 'decimal:8', 'scheduled_for' => 'date', 'reviewed_at' => 'datetime', 'paid_at' => 'datetime'];
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
