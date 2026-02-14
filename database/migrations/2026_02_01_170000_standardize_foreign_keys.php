<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rename zone_id/quote_zone_id to project_zone_id in quote_items table
        if (Schema::hasColumn('quote_items', 'zone_id')) {
            Schema::table('quote_items', function (Blueprint $table) {
                $table->renameColumn('zone_id', 'project_zone_id');
            });
        } elseif (Schema::hasColumn('quote_items', 'quote_zone_id')) {
            Schema::table('quote_items', function (Blueprint $table) {
                $table->renameColumn('quote_zone_id', 'project_zone_id');
            });
        }

        // Rename quote_zone_id to project_zone_id in quote_services table
        if (Schema::hasColumn('quote_services', 'quote_zone_id')) {
            Schema::table('quote_services', function (Blueprint $table) {
                $table->renameColumn('quote_zone_id', 'project_zone_id');
            });
        } elseif (Schema::hasColumn('quote_services', 'zone_id')) {
            Schema::table('quote_services', function (Blueprint $table) {
                $table->renameColumn('zone_id', 'project_zone_id');
            });
        }

        // Rename project_room_id to project_zone_id in quote_items table
        if (Schema::hasColumn('quote_items', 'project_room_id')) {
            Schema::table('quote_items', function (Blueprint $table) {
                $table->renameColumn('project_room_id', 'project_zone_id');
            });
        }

        // Rename project_room_id to project_zone_id in quote_services table
        if (Schema::hasColumn('quote_services', 'project_room_id')) {
            Schema::table('quote_services', function (Blueprint $table) {
                $table->renameColumn('project_room_id', 'project_zone_id');
            });
        }

        // Rename project_room_id to project_zone_id in quote_media table
        if (Schema::hasColumn('quote_media', 'project_room_id')) {
            Schema::table('quote_media', function (Blueprint $table) {
                $table->renameColumn('project_room_id', 'project_zone_id');
            });
        }
    }

    public function down(): void
    {
        // Reverse the changes
        if (Schema::hasColumn('quote_items', 'project_zone_id')) {
            Schema::table('quote_items', function (Blueprint $table) {
                $table->renameColumn('project_zone_id', 'project_room_id');
            });
        }

        if (Schema::hasColumn('quote_services', 'project_zone_id')) {
            Schema::table('quote_services', function (Blueprint $table) {
                $table->renameColumn('project_zone_id', 'project_room_id');
            });
        }

        if (Schema::hasColumn('quote_media', 'project_zone_id')) {
            Schema::table('quote_media', function (Blueprint $table) {
                $table->renameColumn('project_zone_id', 'project_room_id');
            });
        }
    }
};
