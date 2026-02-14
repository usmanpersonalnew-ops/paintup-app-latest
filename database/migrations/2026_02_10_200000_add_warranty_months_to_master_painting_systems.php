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
        Schema::table('master_painting_systems', function (Blueprint $table) {
            $table->integer('warranty_months')->default(0)->after('base_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_painting_systems', function (Blueprint $table) {
            $table->dropColumn('warranty_months');
        });
    }
};
