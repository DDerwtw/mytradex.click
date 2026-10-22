<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investment_accounts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('package_name', 32);
            $table->decimal('principal_usdt', 18, 8);
            $table->decimal('daily_rate', 6, 3);
            $table->unsignedSmallInteger('maturity_days');
            $table->decimal('estimated_reward_usdt', 18, 8);
            $table->decimal('minimum_withdrawal_usdt', 18, 8);
            $table->timestamp('started_at');
            $table->timestamp('matures_at');
            $table->string('status', 24)->default('preview');
            $table->timestamps();
            $table->index(['user_id', 'status', 'matures_at']);
        });

        Schema::create('withdrawal_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('investment_account_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->decimal('amount_usdt', 18, 8);
            $table->string('network', 16)->default('TRC20');
            $table->string('status', 24)->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('review_note', 1000)->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawal_requests');
        Schema::dropIfExists('investment_accounts');
    }
};
