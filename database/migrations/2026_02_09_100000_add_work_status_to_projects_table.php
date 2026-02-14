<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Check if columns already exist
        $hasWorkStatus = Schema::hasColumn('projects', 'work_status');
        $hasWorkStartedAt = Schema::hasColumn('projects', 'work_started_at');
        $hasWorkCompletedAt = Schema::hasColumn('projects', 'work_completed_at');

        if (!$hasWorkStatus || !$hasWorkStartedAt || !$hasWorkCompletedAt) {
            Schema::table('projects', function (Blueprint $table) use ($hasWorkStatus, $hasWorkStartedAt, $hasWorkCompletedAt) {
                if (!$hasWorkStatus) {
                    $table->enum('work_status', [
                        'PENDING',
                        'ASSIGNED',
                        'IN_PROGRESS',
                        'ON_HOLD',
                        'COMPLETED',
                        'CLOSED'
                    ])->default('PENDING')->after('status');
                }

                if (!$hasWorkStartedAt) {
                    $table->timestamp('work_started_at')->nullable()->after('work_status');
                }

                if (!$hasWorkCompletedAt) {
                    $table->timestamp('work_completed_at')->nullable()->after('work_started_at');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['work_status', 'work_started_at', 'work_completed_at']);
        });
    }
};