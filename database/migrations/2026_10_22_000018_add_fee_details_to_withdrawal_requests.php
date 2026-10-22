<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('withdrawal_requests', function (Blueprint $table): void {
            $table->decimal('fee_rate', 5, 2)->nullable()->after('amount_usdt');
            $table->decimal('fee_amount_usdt', 18, 8)->nullable()->after('fee_rate');
            $table->decimal('net_amount_usdt', 18, 8)->nullable()->after('fee_amount_usdt');
        });
    }

    public function down(): void
    {
        Schema::table('withdrawal_requests', function (Blueprint $table): void {
            $table->dropColumn(['fee_rate', 'fee_amount_usdt', 'net_amount_usdt']);
        });
    }
};
