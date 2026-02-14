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
            // Only add payment_method if it doesn't exist (from 2026_02_08_120000)
            if (!Schema::hasColumn('projects', 'payment_method')) {
                $table->enum('payment_method', ['ONLINE', 'CASH'])->nullable()->after('total_amount');
            }

            // Milestone amounts (40-40-20) - use consistent naming
            if (!Schema::hasColumn('projects', 'booking_amount')) {
                $table->decimal('booking_amount', 10, 2)->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('projects', 'mid_amount')) {
                $table->decimal('mid_amount', 10, 2)->nullable()->after('booking_amount');
            }
            if (!Schema::hasColumn('projects', 'final_amount')) {
                $table->decimal('final_amount', 10, 2)->nullable()->after('mid_amount');
            }

            // Milestone payment statuses - use consistent naming
            if (!Schema::hasColumn('projects', 'booking_status')) {
                $table->enum('booking_status', ['PENDING', 'PAID', 'CASH_PENDING'])->default('PENDING')->after('final_amount');
            }
            if (!Schema::hasColumn('projects', 'mid_status')) {
                $table->enum('mid_status', ['PENDING', 'PAID'])->default('PENDING')->after('booking_status');
            }
            if (!Schema::hasColumn('projects', 'final_status')) {
                $table->enum('final_status', ['PENDING', 'PAID'])->default('PENDING')->after('mid_status');
            }

            // Milestone paid timestamps
            if (!Schema::hasColumn('projects', 'booking_paid_at')) {
                $table->timestamp('booking_paid_at')->nullable()->after('final_status');
            }
            if (!Schema::hasColumn('projects', 'mid_paid_at')) {
                $table->timestamp('mid_paid_at')->nullable()->after('booking_paid_at');
            }
            if (!Schema::hasColumn('projects', 'final_paid_at')) {
                $table->timestamp('final_paid_at')->nullable()->after('mid_paid_at');
            }

            // Payment references
            if (!Schema::hasColumn('projects', 'booking_reference')) {
                $table->string('booking_reference', 100)->nullable()->after('final_paid_at');
            }
            if (!Schema::hasColumn('projects', 'mid_reference')) {
                $table->string('mid_reference', 100)->nullable()->after('booking_reference');
            }
            if (!Schema::hasColumn('projects', 'final_reference')) {
                $table->string('final_reference', 100)->nullable()->after('mid_reference');
            }

            // Cash confirmation fields
            if (!Schema::hasColumn('projects', 'cash_confirmed_by')) {
                $table->unsignedBigInteger('cash_confirmed_by')->nullable()->after('final_reference');
            }
            if (!Schema::hasColumn('projects', 'cash_confirmed_at')) {
                $table->timestamp('cash_confirmed_at')->nullable()->after('cash_confirmed_by');
            }

            // Drop old payment columns (if they exist from previous migration)
            $columnsToDrop = [];
            if (Schema::hasColumn('projects', 'token_amount')) {
                $columnsToDrop[] = 'token_amount';
            }
            if (Schema::hasColumn('projects', 'token_paid_amount')) {
                $columnsToDrop[] = 'token_paid_amount';
            }
            if (Schema::hasColumn('projects', 'payment_status')) {
                $columnsToDrop[] = 'payment_status';
            }
            if (Schema::hasColumn('projects', 'payment_reference')) {
                $columnsToDrop[] = 'payment_reference';
            }
            if (Schema::hasColumn('projects', 'paid_at')) {
                $columnsToDrop[] = 'paid_at';
            }
            // Drop old milestone columns from 2026_02_08_120000 if they exist
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
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Add back old columns
            $table->decimal('token_amount', 10, 2)->nullable();
            $table->decimal('token_paid_amount', 10, 2)->nullable();
            $table->enum('payment_status', ['PENDING', 'PAID'])->default('PENDING');
            $table->string('payment_reference', 100)->nullable();
            $table->timestamp('paid_at')->nullable();

            // Drop new columns
            $table->dropColumn([
                'payment_method',
                'booking_amount',
                'mid_payment_amount',
                'final_payment_amount',
                'booking_payment_status',
                'mid_payment_status',
                'final_payment_status',
                'booking_paid_at',
                'mid_paid_at',
                'final_paid_at',
                'cash_confirmed_by',
                'cash_confirmed_at',
            ]);
        });
    }
};