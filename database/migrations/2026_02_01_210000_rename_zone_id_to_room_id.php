<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['quote_services', 'quote_items', 'quote_media'];

        foreach ($tables as $tableName) {
            if (Schema::hasColumn($tableName, 'project_zone_id')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    
                    // 1. Drop foreign key safely using the array syntax 
                    // Laravel will correctly resolve this to '[table]_[column]_foreign'
                    try {
                        $table->dropForeign([ 'project_zone_id' ]);
                    } catch (\Exception $e) {
                        // If it doesn't exist, just keep going
                    }

                    // 2. Handle the column rename/drop
                    if (Schema::hasColumn($tableName, 'project_room_id')) {
                        // If project_room_id already exists, we just remove the old zone_id
                        $table->dropColumn('project_zone_id');
                    } else {
                        // If room_id doesn't exist, we rename zone_id to room_id
                        $table->renameColumn('project_zone_id', 'project_room_id');
                    }
                });
            }
        }
    }

    public function down(): void
    {
        $tables = ['quote_services', 'quote_items', 'quote_media'];

        foreach ($tables as $tableName) {
            if (Schema::hasColumn($tableName, 'project_room_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->renameColumn('project_room_id', 'project_zone_id');
                });
            }
        }
    }
};