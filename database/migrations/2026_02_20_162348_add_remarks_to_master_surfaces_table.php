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
        Schema::table('master_surfaces', function (Blueprint $table) {
            if (!Schema::hasColumn('master_surfaces', 'remarks')) {
                $table->text('remarks')->nullable()->after('unit_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_surfaces', function (Blueprint $table) {
            if (Schema::hasColumn('master_surfaces', 'remarks')) {
                $table->dropColumn('remarks');
            }
        });
    }
};
