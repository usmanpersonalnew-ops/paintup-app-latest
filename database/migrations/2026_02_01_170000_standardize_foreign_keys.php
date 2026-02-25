<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1. Handle QUOTE_ITEMS
        Schema::table('quote_items', function (Blueprint $table) {
            if (Schema::hasColumn('quote_items', 'quote_zone_id')) {
                $this->dropForeignQuietly('quote_items', 'quote_items_quote_zone_id_foreign');
                $table->renameColumn('quote_zone_id', 'project_zone_id');
            } elseif (Schema::hasColumn('quote_items', 'zone_id')) {
                $table->renameColumn('zone_id', 'project_zone_id');
            } elseif (!Schema::hasColumn('quote_items', 'project_zone_id')) {
                // If 160000 deleted it, we bring it back
                $table->foreignId('project_zone_id')->nullable()->after('id');
            }
        });

        // 2. Handle QUOTE_SERVICES
        Schema::table('quote_services', function (Blueprint $table) {
            if (Schema::hasColumn('quote_services', 'quote_zone_id')) {
                $this->dropForeignQuietly('quote_services', 'quote_services_quote_zone_id_foreign');
                $table->renameColumn('quote_zone_id', 'project_zone_id');
            } elseif (!Schema::hasColumn('quote_services', 'project_zone_id')) {
                // This is where your error was: column was missing. Re-add it.
                $table->foreignId('project_zone_id')->nullable()->after('id');
            }
        });

        // 3. Handle QUOTE_MEDIA
        Schema::table('quote_media', function (Blueprint $table) {
            if (Schema::hasColumn('quote_media', 'project_room_id')) {
                $table->renameColumn('project_room_id', 'project_zone_id');
            } elseif (!Schema::hasColumn('quote_media', 'project_zone_id')) {
                $table->foreignId('project_zone_id')->nullable()->after('id');
            }
        });

        // 4. ATTACH CONSTRAINTS (After all columns definitely exist)
        Schema::table('quote_items', function (Blueprint $table) {
            $table->foreign('project_zone_id')->references('id')->on('quote_zones')->onDelete('cascade');
        });

        Schema::table('quote_services', function (Blueprint $table) {
            $table->foreign('project_zone_id')->references('id')->on('quote_zones')->onDelete('cascade');
        });

        Schema::table('quote_media', function (Blueprint $table) {
            $table->foreign('project_zone_id')->references('id')->on('quote_zones')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('quote_items', function (Blueprint $table) {
            $table->dropForeign(['project_zone_id']);
            $table->renameColumn('project_zone_id', 'quote_zone_id');
        });

        Schema::table('quote_services', function (Blueprint $table) {
            $table->dropForeign(['project_zone_id']);
            $table->renameColumn('project_zone_id', 'quote_zone_id');
        });

        Schema::table('quote_media', function (Blueprint $table) {
            $table->dropForeign(['project_zone_id']);
            $table->renameColumn('project_zone_id', 'project_room_id');
        });
    }

    /**
     * Helper to drop foreign keys only if they exist to prevent SQL errors
     */
    private function dropForeignQuietly(string $tableName, string $foreignKeyName): void
    {
        $conn = Schema::getConnection()->getDoctrineSchemaManager();
        $keys = array_keys($conn->listTableForeignKeys($tableName));
        
        if (in_array($foreignKeyName, $keys)) {
            Schema::table($tableName, function (Blueprint $table) use ($foreignKeyName) {
                $table->dropForeign($foreignKeyName);
            });
        }
    }
};