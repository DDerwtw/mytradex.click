<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'kyc_submission_id', 'reason', 'status', 'reviewed_by', 'admin_note', 'reviewed_at', 'fulfilled_at'])]
class KycChangeRequest extends Model
{
    protected function casts(): array
    {
        return ['reviewed_at' => 'datetime', 'fulfilled_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(KycSubmission::class, 'kyc_submission_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
