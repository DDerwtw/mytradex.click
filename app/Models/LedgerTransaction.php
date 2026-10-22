<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

#[Fillable(['reference', 'idempotency_key', 'payload_hash', 'currency', 'description', 'posted_at'])]
class LedgerTransaction extends Model
{
    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Posted ledger transactions are immutable.'));
        static::deleting(fn () => throw new LogicException('Posted ledger transactions are immutable.'));
    }

    protected function casts(): array
    {
        return ['posted_at' => 'datetime'];
    }

    public function entries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class);
    }
}
