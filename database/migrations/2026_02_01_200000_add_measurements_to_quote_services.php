<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quote_services', function (Blueprint $table) {
            $table->decimal('length', 10, 2)->nullable()->after('quantity');
            $table->decimal('breadth', 10, 2)->nullable()->after('length');
            $table->decimal('count', 10, 2)->nullable()->after('breadth');
        });
    }

    public function down(): void
    {
        Schema::table('quote_services', function (Blueprint $table) {
            $table->dropColumn(['length', 'breadth', 'count']);
        });
    }
};
