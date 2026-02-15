<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('quote_services', 'remarks')) {
            Schema::table('quote_services', function (Blueprint $table) {
                $table->text('remarks')->nullable()->after('photo_url');
            });
        }
    }

    public function down(): void
    {
        Schema::table('quote_services', function (Blueprint $table) {
            $table->dropColumn('remarks');
        });
    }
};