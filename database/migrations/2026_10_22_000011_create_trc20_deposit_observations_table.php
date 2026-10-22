<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trc20_deposit_observations', function (Blueprint $table): void {
            $table->id();
            $table->string('transaction_id', 160)->unique();
            $table->string('coin', 12)->default('USDT');
            $table->string('network', 24)->default('TRC20');
            $table->string('contract_address', 80);
            $table->string('source_address', 80);
            $table->string('destination_address', 80);
            $table->decimal('amount', 28, 8);
            $table->string('provider_status', 24)->default('confirmed');
            $table->timestamp('occurred_at')->nullable();
            $table->timestamp('last_seen_at');
            $table->timestamps();
            $table->index(['coin', 'network', 'occurred_at']);
        });

        if (! Schema::hasColumn('investment_deposit_claims', 'trc20_deposit_observation_id')) {
            Schema::table('investment_deposit_claims', function (Blueprint $table): void {
                $table->foreignId('trc20_deposit_observation_id')->nullable()
                    ->constrained('trc20_deposit_observations')->restrictOnDelete();
                $table->unique('trc20_deposit_observation_id', 'deposit_claims_trc20_observation_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('investment_deposit_claims', 'trc20_deposit_observation_id')) {
            Schema::table('investment_deposit_claims', function (Blueprint $table): void {
                $table->dropUnique('deposit_claims_trc20_observation_unique');
                $table->dropConstrainedForeignId('trc20_deposit_observation_id');
            });
        }

        Schema::dropIfExists('trc20_deposit_observations');
    }
};
