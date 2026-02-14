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
        // Add all payment columns directly without conditions
        Schema::table('projects', function (Blueprint $table) {
            // Payment amounts (40-40-20)
            if (!Schema::hasColumn('projects', 'booking_amount')) {
                $table->decimal('booking_amount', 10, 2)->default(0)->after('total_amount');
            }
            if (!Schema::hasColumn('projects', 'mid_amount')) {
                $table->decimal('mid_amount', 10, 2)->default(0)->after('booking_amount');
            }
            if (!Schema::hasColumn('projects', 'final_amount')) {
                $table->decimal('final_amount', 10, 2)->default(0)->after('mid_amount');
            }

            // Payment statuses
            if (!Schema::hasColumn('projects', 'booking_status')) {
                $table->enum('booking_status', ['PENDING', 'PAID', 'CASH_PENDING'])->default('PENDING')->after('final_amount');
            }
            if (!Schema::hasColumn('projects', 'mid_status')) {
                $table->enum('mid_status', ['PENDING', 'PAID'])->default('PENDING')->after('booking_status');
            }
            if (!Schema::hasColumn('projects', 'final_status')) {
                $table->enum('final_status', ['PENDING', 'PAID'])->default('PENDING')->after('mid_status');
            }

            // Payment method
            if (!Schema::hasColumn('projects', 'payment_method')) {
                $table->enum('payment_method', ['ONLINE', 'CASH'])->nullable()->after('final_status');
            }

            // Payment references
            if (!Schema::hasColumn('projects', 'booking_reference')) {
                $table->string('booking_reference', 100)->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('projects', 'mid_reference')) {
                $table->string('mid_reference', 100)->nullable()->after('booking_reference');
            }
            if (!Schema::hasColumn('projects', 'final_reference')) {
                $table->string('final_reference', 100)->nullable()->after('mid_reference');
            }

            // Payment timestamps
            if (!Schema::hasColumn('projects', 'booking_paid_at')) {
                $table->timestamp('booking_paid_at')->nullable()->after('final_reference');
            }
            if (!Schema::hasColumn('projects', 'mid_paid_at')) {
                $table->timestamp('mid_paid_at')->nullable()->after('booking_paid_at');
            }
            if (!Schema::hasColumn('projects', 'final_paid_at')) {
                $table->timestamp('final_paid_at')->nullable()->after('mid_paid_at');
            }

            // Cash confirmation fields
            if (!Schema::hasColumn('projects', 'cash_confirmed_by')) {
                $table->unsignedBigInteger('cash_confirmed_by')->nullable()->after('final_paid_at');
            }
            if (!Schema::hasColumn('projects', 'cash_confirmed_at')) {
                $table->timestamp('cash_confirmed_at')->nullable()->after('cash_confirmed_by');
            }
        });

        // For SQLite, we need to manually add columns if they don't exist
        $this->addMissingColumnsForSQLite();
    }

    /**
     * Add missing columns for SQLite (it doesn't support all alter operations)
     */
    protected function addMissingColumnsForSQLite(): void
    {
        $columns = [
            'booking_amount' => 'decimal(10,2) default 0',
            'mid_amount' => 'decimal(10,2) default 0',
            'final_amount' => 'decimal(10,2) default 0',
            'booking_status' => "text default 'PENDING'",
            'mid_status' => "text default 'PENDING'",
            'final_status' => "text default 'PENDING'",
            'payment_method' => 'text',
            'booking_reference' => 'text',
            'mid_reference' => 'text',
            'final_reference' => 'text',
            'booking_paid_at' => 'text',
            'mid_paid_at' => 'text',
            'final_paid_at' => 'text',
            'cash_confirmed_by' => 'integer',
            'cash_confirmed_at' => 'text',
        ];

        foreach ($columns as $column => $definition) {
            try {
                DB::statement("ALTER TABLE projects ADD COLUMN {$column} {$definition}");
            } catch (\Exception $e) {
                // Column may already exist, ignore error
            }
        }
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
                'booking_reference',
                'mid_reference',
                'final_reference',
                'booking_paid_at',
                'mid_paid_at',
                'final_paid_at',
                'cash_confirmed_by',
                'cash_confirmed_at',
            ]);
        });
    }
};
