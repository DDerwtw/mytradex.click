<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('binance_deposit_observations', function (Blueprint $table): void {
            $table->id();
            $table->string('binance_id', 80)->unique();
            $table->string('coin', 12);
            $table->string('network', 24);
            $table->decimal('amount', 28, 8);
            $table->string('provider_status', 32)->nullable();
            $table->string('transaction_id', 160)->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamp('last_seen_at');
            $table->timestamps();
            $table->index(['coin', 'network', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('binance_deposit_observations');
    }
};
