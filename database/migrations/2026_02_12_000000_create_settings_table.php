<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->default('PaintUp');
            $table->string('logo_path')->nullable();
            $table->string('primary_color')->default('#2563eb'); // blue-600
            $table->string('secondary_color')->default('#1e293b'); // slate-800
            $table->string('support_whatsapp')->nullable();
            $table->string('support_email')->nullable();
            $table->string('footer_text')->nullable();
            $table->string('gst_number')->nullable();
            $table->text('address')->nullable();
            $table->string('invoice_prefix')->default('INV');
            $table->timestamps();
        });

        // Insert default settings row
        DB::table('settings')->insert([
            'company_name' => 'PaintUp',
            'primary_color' => '#2563eb',
            'secondary_color' => '#1e293b',
            'invoice_prefix' => 'INV',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
