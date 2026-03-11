<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_zone_id')->constrained()->onDelete('cascade');
            $table->foreignId('master_service_id')->nullable()->constrained()->onDelete('set null');
            $table->string('custom_name');
            $table->string('unit_type');
            $table->decimal('quantity', 10, 2);
            $table->decimal('rate', 10, 2);
            $table->decimal('amount', 10, 2);
            $table->string('photo_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_services');
    }
};