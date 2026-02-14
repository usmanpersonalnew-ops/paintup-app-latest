<?php

namespace App\Services\Documents;

use App\Models\Project;
use App\Models\QuoteItem;
use App\Models\MasterPaintingSystem;
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

        return [
            'certificate_number' => $certificateNumber,
            'issue_date' => $completionDate?->format('d M Y'),
            'valid_till' => $completionDate?->addYears(1)->format('d M Y'),
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
                $surfaceName = $item->surface->name ?? 'Unknown Surface';
                $productName = $item->product->name ?? 'Unknown Product';
                $systemName = $item->system->system_name ?? 'Unknown System';

                // Get warranty months from system, fallback to product
                $warrantyMonths = $this->getWarrantyMonths($item);

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

    /**
     * Get warranty months for an item
     */
    protected function getWarrantyMonths(QuoteItem $item): int
    {
        // First try to get from painting system
        if ($item->system && $item->system instanceof MasterPaintingSystem) {
            return (int) ($item->system->warranty_months ?? 0);
        }

        // Fallback: check system relationship if loaded differently
        if ($item->system) {
            return (int) ($item->system->warranty_months ?? 0);
        }

        // Try to get from product
        if ($item->product) {
            return (int) ($item->product->warranty_months ?? 0);
        }

        return 0;
    }
}
