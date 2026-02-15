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
        Schema::table('quotes', function (Blueprint $table) {
            // Add project_id foreign key if it doesn't exist
            if (!Schema::hasColumn('quotes', 'project_id')) {
                $table->foreignId('project_id')->nullable()->after('id')->constrained('projects')->onDelete('cascade');
            }

            // Add quote calculation fields if they don't exist
            if (!Schema::hasColumn('quotes', 'discount_amount')) {
                $table->decimal('discount_amount', 12, 2)->nullable()->after('status');
            }
            if (!Schema::hasColumn('quotes', 'tax_percent')) {
                $table->decimal('tax_percent', 5, 2)->nullable()->after('discount_amount');
            }
            if (!Schema::hasColumn('quotes', 'tax_amount')) {
                $table->decimal('tax_amount', 12, 2)->nullable()->after('tax_percent');
            }
            if (!Schema::hasColumn('quotes', 'grand_total')) {
                $table->decimal('grand_total', 12, 2)->nullable()->after('tax_amount');
            }

            // Add notes field if it doesn't exist
            if (!Schema::hasColumn('quotes', 'notes')) {
                $table->text('notes')->nullable()->after('grand_total');
            }
        });

        // Add foreign key constraint if project_id exists but constraint doesn't
        if (Schema::hasColumn('quotes', 'project_id')) {
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'quotes'
                AND COLUMN_NAME = 'project_id'
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");

            if (empty($foreignKeys)) {
                try {
                    DB::statement("ALTER TABLE quotes ADD CONSTRAINT quotes_project_id_foreign FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE");
                } catch (\Exception $e) {
                    // Foreign key constraint might fail if column types are incompatible
                    // This is okay - the relationship will still work at the application level
                }
            }
        }

        // Update status column to support FINALIZED status
        // For MySQL, we need to alter the enum type
        if (Schema::hasColumn('quotes', 'status')) {
            try {
                DB::statement("ALTER TABLE quotes MODIFY COLUMN status ENUM('DRAFT', 'SENT', 'APPROVED', 'FINALIZED') DEFAULT 'DRAFT'");
            } catch (\Exception $e) {
                // If status is not an enum (might be varchar), skip this
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn(['project_id', 'discount_amount', 'tax_percent', 'tax_amount', 'grand_total', 'notes']);
        });

        // Revert status enum
        if (Schema::hasColumn('quotes', 'status')) {
            DB::statement("ALTER TABLE quotes MODIFY COLUMN status ENUM('DRAFT', 'SENT', 'APPROVED') DEFAULT 'DRAFT'");
        }
    }
};
