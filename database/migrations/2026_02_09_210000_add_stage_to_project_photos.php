<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_photos', function (Blueprint $table) {
            $table->string('stage')->default('before')->after('description'); // before, in-progress, after
        });
    }

    public function down(): void
    {
        Schema::table('project_photos', function (Blueprint $table) {
            $table->dropColumn('stage');
        });
    }
};
