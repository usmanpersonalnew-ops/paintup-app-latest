<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'value',
        'min_order_amount',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // Coupon types
    public const TYPE_FLAT = 'FLAT';
    public const TYPE_PERCENT = 'PERCENT';

    /**
     * Scope for active coupons
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for valid coupons (not expired and active)
     */
    public function scopeValid($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Get projects that have used this coupon
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Calculate discount amount for a given subtotal
     */
    public function calculateDiscount(float $subtotal): float
    {
        if ($this->type === self::TYPE_FLAT) {
            return min($this->value, $subtotal);
        }

        if ($this->type === self::TYPE_PERCENT) {
            return round($subtotal * ($this->value / 100), 2);
        }

        return 0;
    }

    /**
     * Check if coupon meets minimum order requirement
     */
    public function meetsMinOrderRequirement(float $subtotal): bool
    {
        if (is_null($this->min_order_amount)) {
            return true;
        }

        return $subtotal >= $this->min_order_amount;
    }

    /**
     * Get formatted type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            self::TYPE_FLAT => 'Flat',
            self::TYPE_PERCENT => 'Percent',
            default => 'Unknown',
        };
    }

    /**
     * Get formatted value display
     */
    public function getValueDisplayAttribute(): string
    {
        if ($this->type === self::TYPE_FLAT) {
            return '₹' . number_format($this->value, 2);
        }

        return $this->value . '%';
    }

    /**
     * Get expiration status
     */
    public function getIsExpiredAttribute(): bool
    {
        if (is_null($this->expires_at)) {
            return false;
        }

        return $this->expires_at->isPast();
    }
}