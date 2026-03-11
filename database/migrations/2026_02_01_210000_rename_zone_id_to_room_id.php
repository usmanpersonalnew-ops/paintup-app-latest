<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rename project_zone_id to project_room_id in quote_services
        if (Schema::hasColumn('quote_services', 'project_zone_id')) {
            Schema::table('quote_services', function (Blueprint $table) {
                $table->renameColumn('project_zone_id', 'project_room_id');
            });
        }

        // Rename project_zone_id to project_room_id in quote_items
        if (Schema::hasColumn('quote_items', 'project_zone_id')) {
            Schema::table('quote_items', function (Blueprint $table) {
                $table->renameColumn('project_zone_id', 'project_room_id');
            });
        }

        // Rename project_zone_id to project_room_id in quote_media
        if (Schema::hasColumn('quote_media', 'project_zone_id')) {
            Schema::table('quote_media', function (Blueprint $table) {
                $table->renameColumn('project_zone_id', 'project_room_id');
            });
        }
    }

    public function down(): void
    {
        // Reverse the changes
        if (Schema::hasColumn('quote_services', 'project_room_id')) {
            Schema::table('quote_services', function (Blueprint $table) {
                $table->renameColumn('project_room_id', 'project_zone_id');
            });
        }

        if (Schema::hasColumn('quote_items', 'project_room_id')) {
            Schema::table('quote_items', function (Blueprint $table) {
                $table->renameColumn('project_room_id', 'project_zone_id');
            });
        }

        if (Schema::hasColumn('quote_media', 'project_room_id')) {
            Schema::table('quote_media', function (Blueprint $table) {
                $table->renameColumn('project_room_id', 'project_zone_id');
            });
        }
    }
};