<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add project_room_id to quote_services table
        Schema::table('quote_services', function (Blueprint $table) {
            $table->foreignId('project_room_id')->nullable()->constrained('project_rooms')->onDelete('set null');
        });

        // Add project_room_id to quote_media table
        Schema::table('quote_media', function (Blueprint $table) {
            $table->foreignId('project_room_id')->nullable()->constrained('project_rooms')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('quote_services', function (Blueprint $table) {
            $table->dropForeign(['project_room_id']);
            $table->dropColumn('project_room_id');
        });

        Schema::table('quote_media', function (Blueprint $table) {
            $table->dropForeign(['project_room_id']);
            $table->dropColumn('project_room_id');
        });
    }
};