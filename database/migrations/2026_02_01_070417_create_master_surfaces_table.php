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
        Schema::create('master_surfaces', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('category', ['INTERIOR', 'EXTERIOR', 'BOTH']);
            $table->enum('unit_type', ['AREA', 'LINEAR', 'COUNT', 'LUMPSUM']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_surfaces');
    }
};
