<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_user_messages', function (Blueprint $table): void {
            $table->string('email_status', 24)->default('not_sent');
            $table->timestamp('email_sent_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('admin_user_messages', function (Blueprint $table): void {
            $table->dropColumn(['email_status', 'email_sent_at']);
        });
    }
};
