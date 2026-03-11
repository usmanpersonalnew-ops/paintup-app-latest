<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This migration fixes milestone amounts for existing projects.
     */
    public function up(): void
    {
        // Get GST rate from settings or use default 18%
        $gstRate = (float) Setting::get('gst_rate', 18);

        // Get all projects and fix their milestone amounts
        $projects = DB::table('projects')->get();

        foreach ($projects as $project) {
            // Calculate the correct base total (after discount)
            $baseTotal = ($project->total_amount ?? 0) - ($project->discount_amount ?? 0);

            // Skip if base total is invalid
            if ($baseTotal <= 0) {
                continue;
            }

            // Calculate milestone amounts (40-40-20 split) from BASE TOTAL
            $bookingBase = round($baseTotal * 0.40, 2);
            $midBase = round($baseTotal * 0.40, 2);
            $finalBase = round($baseTotal * 0.20, 2);

            // Calculate GST for each milestone
            $bookingGst = round($bookingBase * ($gstRate / 100), 2);
            $midGst = round($midBase * ($gstRate / 100), 2);
            $finalGst = round($finalBase * ($gstRate / 100), 2);

            // Calculate totals (base + GST)
            $bookingTotal = $bookingBase + $bookingGst;
            $midTotal = $midBase + $midGst;
            $finalTotal = $finalBase + $finalGst;

            // Update the project with correct milestone amounts
            DB::table('projects')
                ->where('id', $project->id)
                ->update([
                    'base_total' => $baseTotal,
                    'gst_rate' => $gstRate,
                    'booking_amount' => $bookingBase,
                    'booking_gst' => $bookingGst,
                    'booking_total' => $bookingTotal,
                    'mid_amount' => $midBase,
                    'mid_gst' => $midGst,
                    'mid_total' => $midTotal,
                    'final_amount' => $finalBase,
                    'final_gst' => $finalGst,
                    'final_total' => $finalTotal,
                ]);
        }

        // Update total_amount to include GST (grand total) for display purposes
        DB::statement("UPDATE projects SET total_amount = base_total + (base_total * gst_rate / 100) WHERE base_total > 0");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback needed for this fix migration
    }
};
