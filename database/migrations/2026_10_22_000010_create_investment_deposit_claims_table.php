<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investment_deposit_claims', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('investment_account_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('transaction_id', 160);
            $table->string('source_address', 80);
            $table->decimal('amount_usdt', 18, 8);
            $table->string('network', 16)->default('TRC20');
            $table->string('status', 24)->default('pending');
            $table->foreignId('binance_deposit_observation_id')->nullable()->constrained('binance_deposit_observations')->restrictOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('review_note', 1000)->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
            $table->index(['transaction_id', 'status']);
            $table->unique('binance_deposit_observation_id', 'deposit_claims_observation_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investment_deposit_claims');
    }
};
