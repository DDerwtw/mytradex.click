<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'admin_id', 'category', 'title', 'body', 'read_at', 'email_status', 'email_sent_at'])]
class AdminUserMessage extends Model
{
    protected function casts(): array
    {
        return ['read_at' => 'datetime', 'email_sent_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
