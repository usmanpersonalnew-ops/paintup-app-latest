<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('quote_items', function (Blueprint $table) {
            if (Schema::hasColumn('quote_items', 'manual_height')) {
                $table->decimal('manual_area', 10, 2)->nullable()->after('manual_height');
            } else {
                $table->decimal('manual_area', 10, 2)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('quote_items', function (Blueprint $table) {
            $table->dropColumn('manual_area');
        });
    }
};
