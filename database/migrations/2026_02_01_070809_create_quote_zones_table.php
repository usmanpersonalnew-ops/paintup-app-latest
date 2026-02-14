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
        Schema::create('quote_zones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quote_id');
            $table->string('name');
            $table->enum('zone_type', ['INTERIOR', 'EXTERIOR']);
            $table->decimal('default_length', 8, 2)->nullable();
            $table->decimal('default_breadth', 8, 2)->nullable();
            $table->decimal('default_height', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_zones');
    }
};
