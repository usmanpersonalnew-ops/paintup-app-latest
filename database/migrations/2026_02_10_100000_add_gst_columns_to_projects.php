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
            // GST columns for proper milestone calculation
            if (!Schema::hasColumn('projects', 'subtotal')) {
                $table->decimal('subtotal', 12, 2)->default(0)->after('total_amount');
            }
            if (!Schema::hasColumn('projects', 'gst_rate')) {
                $table->decimal('gst_rate', 5, 2)->default(18)->after('subtotal');
            }
            if (!Schema::hasColumn('projects', 'gst_amount')) {
                $table->decimal('gst_amount', 12, 2)->default(0)->after('gst_rate');
            }
            if (!Schema::hasColumn('projects', 'grand_total')) {
                $table->decimal('grand_total', 12, 2)->default(0)->after('gst_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'gst_rate', 'gst_amount', 'grand_total']);
        });
    }
};