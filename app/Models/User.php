<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmailContract
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, MustVerifyEmailTrait, Notifiable;

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            if (! $user->referral_code) {
                do {
                    $code = Str::upper(Str::random(12));
                } while (static::query()->where('referral_code', $code)->exists());

                $user->referral_code = $code;
            }
        });
    }

    public function kycSubmissions(): HasMany
    {
        return $this->hasMany(KycSubmission::class);
    }

    public function kycChangeRequests(): HasMany
    {
        return $this->hasMany(KycChangeRequest::class);
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by_id');
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(User::class, 'referred_by_id');
    }

    public function referralCommissions(): HasMany
    {
        return $this->hasMany(ReferralCommission::class);
    }

    public function referralWithdrawalRequests(): HasMany
    {
        return $this->hasMany(ReferralWithdrawalRequest::class);
    }

    public function investmentAccounts(): HasMany
    {
        return $this->hasMany(InvestmentAccount::class);
    }

    public function withdrawalRequests(): HasMany
    {
        return $this->hasMany(WithdrawalRequest::class);
    }

    public function investmentDepositClaims(): HasMany
    {
        return $this->hasMany(InvestmentDepositClaim::class);
    }

    public function adminMessages(): HasMany
    {
        return $this->hasMany(AdminUserMessage::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
