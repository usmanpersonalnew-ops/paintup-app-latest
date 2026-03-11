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
        Schema::create('project_billing_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('milestone_type'); // booking, mid, final
            $table->enum('buying_type', ['INDIVIDUAL', 'BUSINESS'])->default('INDIVIDUAL');
            $table->string('gstin')->nullable();
            $table->string('business_name')->nullable();
            $table->text('business_address')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode')->nullable();
            $table->string('order_id')->nullable();
            $table->timestamps();
            
            // Unique constraint per project + milestone
            $table->unique(['project_id', 'milestone_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_billing_details');
    }
};