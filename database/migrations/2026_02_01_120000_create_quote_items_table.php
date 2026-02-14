<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_items', function (Blueprint $table) {
            $table->id();

            // IDs (SQLite-safe: no FK constraints)
            $table->unsignedBigInteger('project_room_id');
            $table->unsignedBigInteger('surface_id');
            $table->unsignedBigInteger('master_product_id');
            $table->unsignedBigInteger('master_system_id');

            // Quantities & pricing
            $table->decimal('qty', 10, 2);
            $table->decimal('rate', 10, 2);
            $table->decimal('amount', 12, 2);
            $table->decimal('system_rate', 10, 2)->nullable();

            // Modes
            $table->string('measurement_mode')->nullable();
            $table->string('pricing_mode')->nullable();

            // Deductions
            $table->json('deductions')->nullable();

            // Extras
            $table->string('color_code')->nullable();
            $table->text('description')->nullable();

            // Manual / calculated values
            $table->decimal('manual_price', 12, 2)->default(0);
            $table->decimal('gross_qty', 10, 2)->default(0);
            $table->decimal('net_qty', 10, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_items');
    }
};
