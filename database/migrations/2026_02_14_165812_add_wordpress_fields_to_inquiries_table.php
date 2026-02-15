<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->string('pincode')->nullable()->after('city');
            $table->boolean('whatsapp_enabled')->default(false)->after('pincode');
            $table->string('construction_ongoing')->nullable()->after('whatsapp_enabled');
            $table->string('property_type')->nullable()->after('construction_ongoing');
            $table->date('visit_date')->nullable()->after('property_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropColumn(['pincode', 'whatsapp_enabled', 'construction_ongoing', 'property_type', 'visit_date']);
        });
    }
};
