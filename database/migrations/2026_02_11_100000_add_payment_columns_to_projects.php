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
            // Add missing columns only (check if they exist first)
            if (!Schema::hasColumn('projects', 'base_total')) {
                $table->decimal('base_total', 12, 2)->default(0)->after('total_amount');
            }
            if (!Schema::hasColumn('projects', 'gst_rate')) {
                $table->decimal('gst_rate', 5, 2)->default(18)->after('base_total');
            }
            if (!Schema::hasColumn('projects', 'booking_amount')) {
                $table->decimal('booking_amount', 12, 2)->default(0)->after('gst_rate');
            }
            if (!Schema::hasColumn('projects', 'booking_gst')) {
                $table->decimal('booking_gst', 12, 2)->default(0)->after('booking_amount');
            }
            if (!Schema::hasColumn('projects', 'booking_total')) {
                $table->decimal('booking_total', 12, 2)->default(0)->after('booking_gst');
            }
            if (!Schema::hasColumn('projects', 'mid_amount')) {
                $table->decimal('mid_amount', 12, 2)->default(0)->after('booking_total');
            }
            if (!Schema::hasColumn('projects', 'mid_gst')) {
                $table->decimal('mid_gst', 12, 2)->default(0)->after('mid_amount');
            }
            if (!Schema::hasColumn('projects', 'mid_total')) {
                $table->decimal('mid_total', 12, 2)->default(0)->after('mid_gst');
            }
            if (!Schema::hasColumn('projects', 'final_amount')) {
                $table->decimal('final_amount', 12, 2)->default(0)->after('mid_total');
            }
            if (!Schema::hasColumn('projects', 'final_gst')) {
                $table->decimal('final_gst', 12, 2)->default(0)->after('final_amount');
            }
            if (!Schema::hasColumn('projects', 'final_total')) {
                $table->decimal('final_total', 12, 2)->default(0)->after('final_gst');
            }
            if (!Schema::hasColumn('projects', 'booking_status')) {
                $table->string('booking_status')->default('PENDING')->after('final_total');
            }
            if (!Schema::hasColumn('projects', 'mid_status')) {
                $table->string('mid_status')->default('PENDING')->after('booking_status');
            }
            if (!Schema::hasColumn('projects', 'final_status')) {
                $table->string('final_status')->default('PENDING')->after('mid_status');
            }
            if (!Schema::hasColumn('projects', 'booking_reference')) {
                $table->string('booking_reference')->nullable()->after('final_status');
            }
            if (!Schema::hasColumn('projects', 'mid_reference')) {
                $table->string('mid_reference')->nullable()->after('booking_reference');
            }
            if (!Schema::hasColumn('projects', 'final_reference')) {
                $table->string('final_reference')->nullable()->after('mid_reference');
            }
            if (!Schema::hasColumn('projects', 'booking_paid_at')) {
                $table->timestamp('booking_paid_at')->nullable()->after('final_reference');
            }
            if (!Schema::hasColumn('projects', 'mid_paid_at')) {
                $table->timestamp('mid_paid_at')->nullable()->after('booking_paid_at');
            }
            if (!Schema::hasColumn('projects', 'final_paid_at')) {
                $table->timestamp('final_paid_at')->nullable()->after('mid_paid_at');
            }
            if (!Schema::hasColumn('projects', 'coupon_id')) {
                $table->unsignedBigInteger('coupon_id')->nullable()->after('final_paid_at');
            }
            if (!Schema::hasColumn('projects', 'coupon_code')) {
                $table->string('coupon_code')->nullable()->after('coupon_id');
            }
            if (!Schema::hasColumn('projects', 'discount_amount')) {
                $table->decimal('discount_amount', 12, 2)->default(0)->after('coupon_code');
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
                'base_total',
                'gst_rate',
                'booking_amount',
                'booking_gst',
                'booking_total',
                'mid_amount',
                'mid_gst',
                'mid_total',
                'final_amount',
                'final_gst',
                'final_total',
                'booking_status',
                'mid_status',
                'final_status',
                'booking_reference',
                'mid_reference',
                'final_reference',
                'booking_paid_at',
                'mid_paid_at',
                'final_paid_at',
                'coupon_id',
                'coupon_code',
                'discount_amount',
            ]);
        });
    }
};
