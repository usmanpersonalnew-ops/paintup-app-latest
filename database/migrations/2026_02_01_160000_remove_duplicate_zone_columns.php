<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove duplicate project_zone_id from quote_services (keep project_room_id)
        Schema::table('quote_services', function (Blueprint $table) {
            $table->dropForeign(['project_zone_id']);
            $table->dropColumn('project_zone_id');
        });

        // Remove project_zone_id from quote_media (keep project_room_id)
        Schema::table('quote_media', function (Blueprint $table) {
            $table->dropForeign(['project_zone_id']);
            $table->dropColumn('project_zone_id');
        });
    }

    public function down(): void
    {
        // Add back project_zone_id to quote_services
        Schema::table('quote_services', function (Blueprint $table) {
            $table->foreignId('project_zone_id')->nullable()->constrained('quote_zones')->onDelete('set null');
        });

        // Add back project_zone_id to quote_media
        Schema::table('quote_media', function (Blueprint $table) {
            $table->foreignId('project_zone_id')->nullable()->constrained('quote_zones')->onDelete('set null');
        });
    }
};