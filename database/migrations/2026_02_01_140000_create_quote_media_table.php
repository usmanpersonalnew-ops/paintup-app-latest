<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_zone_id')->constrained('quote_zones')->onDelete('cascade');
            $table->string('photo_url');
            $table->enum('tag', ['BEFORE', 'AFTER'])->default('BEFORE');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_media');
    }
};
