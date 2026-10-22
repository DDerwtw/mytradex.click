<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kyc_submissions', function (Blueprint $table): void {
            $table->string('address_proof_path', 512)->nullable()->after('document_path');
            $table->string('face_photo_path', 512)->nullable()->after('address_proof_path');
            $table->string('withdrawal_wallet_address', 64)->nullable()->after('face_photo_path');
        });
    }

    public function down(): void
    {
        Schema::table('kyc_submissions', function (Blueprint $table): void {
            $table->dropColumn(['address_proof_path', 'face_photo_path', 'withdrawal_wallet_address']);
        });
    }
};
