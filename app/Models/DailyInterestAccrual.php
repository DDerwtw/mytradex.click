<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyInterestAccrual extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'investment_account_id',
        'amount_usdt',
        'accrued_date',
    ];

    protected $casts = [
        'amount_usdt' => 'decimal:8',
        'accrued_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function investment()
    {
        return $this->belongsTo(InvestmentAccount::class, 'investment_account_id');
    }
}