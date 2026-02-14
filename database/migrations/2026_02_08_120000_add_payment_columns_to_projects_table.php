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
        Schema::table('projects', function (Blueprint $table) {
            // Drop old columns if they exist
            if (Schema::hasColumn('projects', 'token_amount')) {
                $table->dropColumn(['token_amount', 'token_paid_amount', 'payment_status', 'payment_reference', 'paid_at']);
            }

            // 40-40-20 Payment Milestone Columns
            $table->decimal('booking_amount', 10, 2)->nullable()->after('total_amount');
            $table->decimal('mid_amount', 10, 2)->nullable()->after('booking_amount');
            $table->decimal('final_amount', 10, 2)->nullable()->after('mid_amount');

            // Payment Status Columns
            $table->enum('booking_status', ['PENDING', 'PAID', 'CASH_PENDING'])->default('PENDING')->after('final_amount');
            $table->enum('mid_status', ['PENDING', 'PAID'])->default('PENDING')->after('booking_status');
            $table->enum('final_status', ['PENDING', 'PAID'])->default('PENDING')->after('mid_status');

            // Payment Method
            $table->enum('payment_method', ['ONLINE', 'CASH'])->nullable()->after('final_status');

            // Timestamps for payments
            $table->timestamp('booking_paid_at')->nullable()->after('payment_method');
            $table->timestamp('mid_paid_at')->nullable()->after('booking_paid_at');
            $table->timestamp('final_paid_at')->nullable()->after('mid_paid_at');

            // Payment references
            $table->string('booking_reference', 100)->nullable()->after('final_paid_at');
            $table->string('mid_reference', 100)->nullable()->after('booking_reference');
            $table->string('final_reference', 100)->nullable()->after('mid_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'booking_amount',
                'mid_amount',
                'final_amount',
                'booking_status',
                'mid_status',
                'final_status',
                'payment_method',
                'booking_paid_at',
                'mid_paid_at',
                'final_paid_at',
                'booking_reference',
                'mid_reference',
                'final_reference',
            ]);
        });
    }
};
