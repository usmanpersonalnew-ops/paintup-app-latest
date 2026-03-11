<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingDetail extends Model
{
    use HasFactory;

    protected $table = 'project_billing_details';

    protected $fillable = [
        'project_id',
        'milestone_type',
        'buying_type',
        'gstin',
        'business_name',
        'business_address',
        'state',
        'pincode',
    ];

    protected $casts = [
        'buying_type' => 'string',
    ];

    /**
     * Get the project that owns the billing details.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Check if buying as business.
     */
    public function isBusiness(): bool
    {
        return $this->buying_type === 'BUSINESS';
    }

    /**
     * Validate GSTIN format (15 characters alphanumeric).
     */
    public static function validateGstin(string $gstin): bool
    {
        return preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/', $gstin) === 1;
    }
}