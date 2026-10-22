<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

#[Fillable(['ledger_transaction_id', 'ledger_account_id', 'direction', 'amount', 'created_at'])]
class LedgerEntry extends Model
{
    public $timestamps = false;

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Posted ledger entries are immutable.'));
        static::deleting(fn () => throw new LogicException('Posted ledger entries are immutable.'));
    }

    protected function casts(): array
    {
        return ['amount' => 'decimal:8', 'created_at' => 'datetime'];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(LedgerTransaction::class, 'ledger_transaction_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class, 'ledger_account_id');
    }
}
