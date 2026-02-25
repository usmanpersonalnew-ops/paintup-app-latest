<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Get GST rate safely. 
        // We use DB::table and a check to avoid "Table not found" errors.
        $gstRate = 18.0; 

        if (Schema::hasTable('settings')) {
            // Adjust 'key' or 'name' based on your actual settings table columns
            $setting = DB::table('settings')->where('key', 'gst_rate')->first();
            if ($setting) {
                $gstRate = (float) ($setting->value ?? 18.0);
            }
        }

        // 2. Ensure projects table exists before attempting updates
        if (!Schema::hasTable('projects')) {
            return;
        }

        // 3. Get all projects to fix amounts
        $projects = DB::table('projects')->get();

        foreach ($projects as $project) {
            // Calculate the correct base total (after discount)
            $totalAmount = $project->total_amount ?? 0;
            $discountAmount = $project->discount_amount ?? 0;
            $baseTotal = $totalAmount - $discountAmount;

            // Skip if base total is invalid
            if ($baseTotal <= 0) {
                continue;
            }

            // Calculate milestone amounts (40-40-20 split) from BASE TOTAL
            $bookingBase = round($baseTotal * 0.40, 2);
            $midBase     = round($baseTotal * 0.40, 2);
            $finalBase   = round($baseTotal * 0.20, 2);

            // Calculate GST for each milestone
            $bookingGst = round($bookingBase * ($gstRate / 100), 2);
            $midGst     = round($midBase * ($gstRate / 100), 2);
            $finalGst   = round($finalBase * ($gstRate / 100), 2);

            // Calculate totals (base + GST)
            $bookingTotal = $bookingBase + $bookingGst;
            $midTotal     = $midBase + $midGst;
            $finalTotal   = $finalBase + $finalGst;

            // Update the project using Query Builder (not Eloquent)
            DB::table('projects')
                ->where('id', $project->id)
                ->update([
                    'base_total'     => $baseTotal,
                    'gst_rate'       => $gstRate,
                    'booking_amount' => $bookingBase,
                    'booking_gst'    => $bookingGst,
                    'booking_total'  => $bookingTotal,
                    'mid_amount'     => $midBase,
                    'mid_gst'        => $midGst,
                    'mid_total'      => $midTotal,
                    'final_amount'   => $finalBase,
                    'final_gst'      => $finalGst,
                    'final_total'    => $finalTotal,
                ]);
        }

        // 4. Update total_amount to include GST (grand total) 
        // using raw SQL for efficiency on any remaining rows
        DB::statement("UPDATE projects SET total_amount = base_total + (base_total * gst_rate / 100) WHERE base_total > 0");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback usually needed for data correction migrations, 
        // but you could null out the fields if required.
    }
};