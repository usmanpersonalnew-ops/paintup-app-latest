<?php

namespace App\Services\Documents;

use App\Models\Project;
use Illuminate\Support\Facades\DB;

class WarrantyGenerator
{
    /**
     * Generate warranty data for a project
     */
    public function generate(Project $project): array
    {
        // Load relationships
        $project->load([
            'rooms.items.surface',
            'rooms.items.product',
            'rooms.items.system',
        ]);

        // Generate certificate number
        $certificateNumber = $this->generateCertificateNumber($project);

        // Get completion date
        $completionDate = $project->work_completed_at;

        // Get warranty items grouped by room
        $warrantyItems = $this->getWarrantyItems($project);

        // Get settings
        $settings = $this->getSettings();

        // Calculate valid_till date (clone to avoid mutating original)
        $validTill = $completionDate ? (clone $completionDate)->addYears(1)->format('d M Y') : null;

        return [
            'certificate_number' => $certificateNumber,
            'issue_date' => $completionDate?->format('d M Y'),
            'valid_till' => $validTill,
            'project' => $project,
            'warranty_items' => $warrantyItems,
            'settings' => $settings,
        ];
    }

    /**
     * Get settings from database
     */
    protected function getSettings(): array
    {
        $settings = DB::table('settings')->first();

        return [
            'company_name' => $settings->company_name ?? 'PaintUp',
            'logo_path' => $settings->logo_path ?? '',
            'support_email' => $settings->support_email ?? '',
        ];
    }

    /**
     * Generate certificate number
     */
    protected function generateCertificateNumber(Project $project): string
    {
        return 'WRN-' . str_pad($project->id, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Get warranty items grouped by room, surface, product, and system
     */
    protected function getWarrantyItems(Project $project): array
    {
        $grouped = [];

        foreach ($project->rooms as $room) {
            foreach ($room->items as $item) {
                $surfaceName = $item->surface?->name ?? 'Unknown Surface';
                $productName = $item->product?->name ?? 'Unknown Product';

                // Get system with fallback
                $system = $item->system;

                // If system is missing but product exists, try to get first system from product
                if (!$system && $item->product) {
                    $item->product->load('systems');
                    $system = $item->product->systems->first();
                }

                $systemName = $system?->system_name ?? 'Unknown System';

                // Get warranty months from system
                $warrantyMonths = $system?->warranty_months ?? 0;
                $warrantyMonths = $warrantyMonths > 0 ? (int) $warrantyMonths : 0;

                $key = $room->name . '|' . $surfaceName . '|' . $productName . '|' . $systemName . '|' . $warrantyMonths;

                if (!isset($grouped[$key])) {
                    $grouped[$key] = [
                        'room' => $room->name,
                        'surface' => $surfaceName,
                        'product' => $productName,
                        'system' => $systemName,
                        'warranty_months' => $warrantyMonths,
                        'qty' => 0,
                    ];
                }

                $grouped[$key]['qty'] += $item->qty ?? 0;
            }
        }

        return array_values($grouped);
    }

}
