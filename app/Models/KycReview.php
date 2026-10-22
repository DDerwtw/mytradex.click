<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['kyc_submission_id', 'reviewer_id', 'decision', 'reason', 'created_at'])]
class KycReview extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(KycSubmission::class, 'kyc_submission_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
