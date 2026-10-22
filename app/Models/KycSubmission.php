<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'document_type', 'document_path', 'address_proof_path', 'face_photo_path', 'withdrawal_wallet_address', 'document_disk', 'status', 'reviewed_by', 'rejection_reason', 'submitted_at', 'reviewed_at'])]
class KycSubmission extends Model
{
    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(KycReview::class);
    }

    public function changeRequests(): HasMany
    {
        return $this->hasMany(KycChangeRequest::class);
    }
}
