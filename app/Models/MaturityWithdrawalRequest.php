<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaturityWithdrawalRequest extends Model
{
    use HasFactory;

        protected $fillable = [
        'user_id',
        'amount_usdt',
        'wallet_address',
        'network',
        'status',
        'reviewed_by',
        'review_note',
        'payout_transaction_id',
        'reviewed_at',
        'paid_at',
    ];

    protected $casts = [
        'amount_usdt' => 'decimal:8',
        'reviewed_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}