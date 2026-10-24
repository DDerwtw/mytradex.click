<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_interest_accruals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('investment_account_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount_usdt', 18, 8);
            $table->date('accrued_date');
            $table->timestamps();
            
            // එකම දවසේ එකම ඉන්වෙස්ට්මන්ට් එකට දෙපාරක් පොලිය එකතු වෙන එක නවත්තන්න
            $table->unique(['investment_account_id', 'accrued_date'], 'unique_daily_interest');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_interest_accruals');
    }
};