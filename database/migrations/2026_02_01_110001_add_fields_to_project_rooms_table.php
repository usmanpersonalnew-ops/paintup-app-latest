<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_rooms', function (Blueprint $table) {
            $table->string('type')->default('INTERIOR')->after('name');
            $table->decimal('length', 10, 2)->nullable()->after('type');
            $table->decimal('breadth', 10, 2)->nullable()->after('length');
            $table->decimal('height', 10, 2)->nullable()->after('breadth');
        });
    }

    public function down(): void
    {
        Schema::table('project_rooms', function (Blueprint $table) {
            $table->dropColumn(['type', 'length', 'breadth', 'height']);
        });
    }
};