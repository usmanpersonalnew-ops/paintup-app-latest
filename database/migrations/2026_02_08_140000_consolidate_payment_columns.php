<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Consolidates payment columns to consistent naming convention
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // First drop the old/duplicate columns from previous migrations
            $columnsToDrop = [];
            
            // Drop from 2026_02_08_120000 if exists
            if (Schema::hasColumn('projects', 'mid_amount')) {
                $columnsToDrop[] = 'mid_amount';
            }
            if (Schema::hasColumn('projects', 'final_amount')) {
                $columnsToDrop[] = 'final_amount';
            }
            if (Schema::hasColumn('projects', 'booking_status')) {
                $columnsToDrop[] = 'booking_status';
            }
            if (Schema::hasColumn('projects', 'mid_status')) {
                $columnsToDrop[] = 'mid_status';
            }
            if (Schema::hasColumn('projects', 'final_status')) {
                $columnsToDrop[] = 'final_status';
            }
            
            // Drop from 2026_02_08_130000 if exists
            if (Schema::hasColumn('projects', 'mid_payment_amount')) {
                $columnsToDrop[] = 'mid_payment_amount';
            }
            if (Schema::hasColumn('projects', 'final_payment_amount')) {
                $columnsToDrop[] = 'final_payment_amount';
            }
            if (Schema::hasColumn('projects', 'booking_payment_status')) {
                $columnsToDrop[] = 'booking_payment_status';
            }
            if (Schema::hasColumn('projects', 'mid_payment_status')) {
                $columnsToDrop[] = 'mid_payment_status';
            }
            if (Schema::hasColumn('projects', 'final_payment_status')) {
                $columnsToDrop[] = 'final_payment_status';
            }
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
            
            // Add consolidated columns with correct naming
            if (!Schema::hasColumn('projects', 'payment_method')) {
                $table->enum('payment_method', ['ONLINE', 'CASH'])->nullable()->after('total_amount');
            }
            
            if (!Schema::hasColumn('projects', 'booking_amount')) {
                $table->decimal('booking_amount', 10, 2)->nullable()->after('payment_method');
            }
            
            if (!Schema::hasColumn('projects', 'mid_amount')) {
                $table->decimal('mid_amount', 10, 2)->nullable()->after('booking_amount');
            }
            
            if (!Schema::hasColumn('projects', 'final_amount')) {
                $table->decimal('final_amount', 10, 2)->nullable()->after('mid_amount');
            }
            
            if (!Schema::hasColumn('projects', 'booking_status')) {
                $table->enum('booking_status', ['PENDING', 'PAID', 'CASH_PENDING'])->default('PENDING')->after('final_amount');
            }
            
            if (!Schema::hasColumn('projects', 'mid_status')) {
                $table->enum('mid_status', ['LOCKED', 'PENDING', 'PAID'])->default('LOCKED')->after('booking_status');
            }
            
            if (!Schema::hasColumn('projects', 'final_status')) {
                $table->enum('final_status', ['LOCKED', 'PENDING', 'PAID'])->default('LOCKED')->after('mid_status');
            }
            
            if (!Schema::hasColumn('projects', 'booking_paid_at')) {
                $table->timestamp('booking_paid_at')->nullable()->after('final_status');
            }
            
            if (!Schema::hasColumn('projects', 'mid_paid_at')) {
                $table->timestamp('mid_paid_at')->nullable()->after('booking_paid_at');
            }
            
            if (!Schema::hasColumn('projects', 'final_paid_at')) {
                $table->timestamp('final_paid_at')->nullable()->after('mid_paid_at');
            }
            
            if (!Schema::hasColumn('projects', 'booking_reference')) {
                $table->string('booking_reference', 100)->nullable()->after('final_paid_at');
            }
            
            if (!Schema::hasColumn('projects', 'mid_reference')) {
                $table->string('mid_reference', 100)->nullable()->after('booking_reference');
            }
            
            if (!Schema::hasColumn('projects', 'final_reference')) {
                $table->string('final_reference', 100)->nullable()->after('mid_reference');
            }
            
            if (!Schema::hasColumn('projects', 'cash_confirmed_by')) {
                $table->unsignedBigInteger('cash_confirmed_by')->nullable()->after('final_reference');
            }
            
            if (!Schema::hasColumn('projects', 'cash_confirmed_at')) {
                $table->timestamp('cash_confirmed_at')->nullable()->after('cash_confirmed_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method',
                'booking_amount',
                'mid_amount',
                'final_amount',
                'booking_status',
                'mid_status',
                'final_status',
                'booking_paid_at',
                'mid_paid_at',
                'final_paid_at',
                'booking_reference',
                'mid_reference',
                'final_reference',
                'cash_confirmed_by',
                'cash_confirmed_at',
            ]);
        });
    }
};
