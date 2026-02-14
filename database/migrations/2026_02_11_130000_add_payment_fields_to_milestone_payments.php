<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('milestone_payments', function (Blueprint $table) {
            $table->string('tracking_id')->nullable()->after('payment_reference');
            $table->string('bank_ref_no')->nullable()->after('tracking_id');
            $table->string('payment_mode')->nullable()->after('bank_ref_no');
            $table->string('card_name')->nullable()->after('payment_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('milestone_payments', function (Blueprint $table) {
            $table->dropColumn(['tracking_id', 'bank_ref_no', 'payment_mode', 'card_name']);
        });
    }
};