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
            $table->date('home_visit_date')->nullable()->after('supervisor_id');
            $table->time('home_visit_time')->nullable()->after('home_visit_date');
            $table->text('home_visit_supervisors')->nullable()->after('home_visit_time'); // JSON array of supervisor IDs
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['home_visit_date', 'home_visit_time', 'home_visit_supervisors']);
        });
    }
};
