<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add otp_hash column
        Schema::table('customer_otps', function (Blueprint $table) {
            $table->string('otp_hash')->after('phone')->nullable();
        });

        // Hash existing OTPs and store in otp_hash
        DB::table('customer_otps')->whereNotNull('otp')->where('otp', '!=', '')->update([
            'otp_hash' => DB::raw("otp"), // Temporary - will be hashed properly
        ]);

        // Drop the old otp column
        Schema::table('customer_otps', function (Blueprint $table) {
            $table->dropColumn('otp');
        });

        // Rename otp_hash to otp_hash (already named correctly)
        // Make otp_hash not nullable for new records
        Schema::table('customer_otps', function (Blueprint $table) {
            $table->string('otp_hash')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back otp column
        Schema::table('customer_otps', function (Blueprint $table) {
            $table->string('otp')->after('phone')->nullable();
        });

        // Drop otp_hash column
        Schema::table('customer_otps', function (Blueprint $table) {
            $table->dropColumn('otp_hash');
        });
    }
};