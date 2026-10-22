<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kyc_change_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('kyc_submission_id')->constrained()->restrictOnDelete();
            $table->text('reason');
            $table->string('status', 24)->default('pending')->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->text('admin_note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('fulfilled_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kyc_change_requests');
    }
};
