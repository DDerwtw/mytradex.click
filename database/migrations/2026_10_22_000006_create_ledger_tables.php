<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_accounts', function (Blueprint $table): void {
            $table->id();
            $table->string('account_key', 96)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('currency', 12);
            $table->string('kind', 32);
            $table->timestamps();
            $table->index(['user_id', 'currency', 'kind']);
        });

        Schema::create('ledger_transactions', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 64)->unique();
            $table->string('idempotency_key', 128)->unique();
            $table->char('payload_hash', 64);
            $table->string('currency', 12);
            $table->string('description', 255);
            $table->timestamp('posted_at');
            $table->timestamps();
        });

        Schema::create('ledger_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ledger_transaction_id')->constrained()->restrictOnDelete();
            $table->foreignId('ledger_account_id')->constrained()->restrictOnDelete();
            $table->string('direction', 6);
            $table->decimal('amount', 28, 8);
            $table->timestamp('created_at');
            $table->index(['ledger_account_id', 'created_at']);
            $table->index(['ledger_transaction_id', 'direction']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
        Schema::dropIfExists('ledger_transactions');
        Schema::dropIfExists('ledger_accounts');
    }
};
