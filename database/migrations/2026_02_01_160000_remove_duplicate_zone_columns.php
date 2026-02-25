<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Fix for quote_services
        Schema::table('quote_services', function (Blueprint $table) {
            if (Schema::hasColumn('quote_services', 'project_zone_id')) {
                // We use an array ['project_zone_id'] which tells Laravel 
                // to figure out the index name automatically.
                // Wrap in try-catch or check to prevent migration crash.
                try {
                    $table->dropForeign(['project_zone_id']);
                } catch (\Exception $e) {
                    // Ignore if foreign key doesn't exist
                }
                $table->dropColumn('project_zone_id');
            }
        });

        // Fix for quote_media
        Schema::table('quote_media', function (Blueprint $table) {
            if (Schema::hasColumn('quote_media', 'project_zone_id')) {
                try {
                    $table->dropForeign(['project_zone_id']);
                } catch (\Exception $e) {
                    // Ignore if foreign key doesn't exist
                }
                $table->dropColumn('project_zone_id');
            }
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