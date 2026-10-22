<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kyc_submissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('document_type', 32);
            $table->string('document_path', 512);
            $table->string('document_disk', 32)->default('private');
            $table->string('status', 24)->default('pending')->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('submitted_at');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });

        Schema::create('kyc_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('kyc_submission_id')->constrained()->restrictOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->restrictOnDelete();
            $table->string('decision', 24);
            $table->text('reason')->nullable();
            $table->timestamp('created_at');
            $table->index(['kyc_submission_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kyc_reviews');
        Schema::dropIfExists('kyc_submissions');
    }
};
