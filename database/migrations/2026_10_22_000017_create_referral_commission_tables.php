<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_commissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('referred_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('investment_deposit_claim_id')->constrained('investment_deposit_claims')->restrictOnDelete();
            $table->foreignId('ledger_transaction_id')->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('level');
            $table->decimal('commission_rate', 5, 2);
            $table->decimal('base_amount_usdt', 18, 8);
            $table->decimal('amount_usdt', 18, 8);
            $table->timestamps();
            $table->unique(['investment_deposit_claim_id', 'level'], 'referral_commission_claim_level_unique');
            $table->index(['user_id', 'level']);
        });

        Schema::create('referral_withdrawal_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->decimal('gross_amount_usdt', 18, 8);
            $table->decimal('fee_rate', 5, 2);
            $table->decimal('fee_amount_usdt', 18, 8);
            $table->decimal('net_amount_usdt', 18, 8);
            $table->string('network', 16)->default('TRC20');
            $table->string('wallet_address', 64);
            $table->date('scheduled_for');
            $table->string('status', 24)->default('pending')->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('review_note', 1000)->nullable();
            $table->string('payout_transaction_id', 128)->nullable()->unique();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
            $table->index(['status', 'scheduled_for']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_withdrawal_requests');
        Schema::dropIfExists('referral_commissions');
    }
};
