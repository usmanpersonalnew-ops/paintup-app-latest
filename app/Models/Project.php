<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_name',
        'location',
        'phone',
        'status',
        'public_token',
        'total_amount',
        'base_total',
        'gst_rate',
        'payment_method',
        'booking_amount',
        'booking_gst',
        'booking_total',
        'mid_amount',
        'mid_gst',
        'mid_total',
        'final_amount',
        'final_gst',
        'final_total',
        'booking_status',
        'mid_status',
        'final_status',
        'booking_reference',
        'mid_reference',
        'final_reference',
        'booking_paid_at',
        'mid_paid_at',
        'final_paid_at',
        'cash_confirmed_by',
        'cash_confirmed_at',
        'supervisor_id',
        'work_status',
        'work_started_at',
        'work_completed_at',
        'coupon_id',
        'coupon_code',
        'discount_amount',
    ];

    protected $casts = [
        'status' => 'string',
        'total_amount' => 'decimal:2',
        'base_total' => 'decimal:2',
        'gst_rate' => 'decimal:2',
        'booking_amount' => 'decimal:2',
        'booking_gst' => 'decimal:2',
        'booking_total' => 'decimal:2',
        'mid_amount' => 'decimal:2',
        'mid_gst' => 'decimal:2',
        'mid_total' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'final_gst' => 'decimal:2',
        'final_total' => 'decimal:2',
        'booking_paid_at' => 'datetime',
        'mid_paid_at' => 'datetime',
        'final_paid_at' => 'datetime',
        'cash_confirmed_at' => 'datetime',
        'work_status' => 'string',
        'work_started_at' => 'datetime',
        'work_completed_at' => 'datetime',
        'discount_amount' => 'decimal:2',
    ];

    protected $appends = [
        'painting_total',
        'services_total',
    ];

    // Project statuses
    public const STATUS_DRAFT = 'DRAFT';
    public const STATUS_AWAITING_CASH_CONFIRMATION = 'AWAITING_CASH_CONFIRMATION';
    public const STATUS_CONFIRMED = 'CONFIRMED';
    public const STATUS_IN_PROGRESS = 'IN_PROGRESS';
    public const STATUS_COMPLETED = 'COMPLETED';

    // Payment methods
    public const PAYMENT_METHOD_ONLINE = 'ONLINE';
    public const PAYMENT_METHOD_CASH = 'CASH';

    // Payment statuses
    public const PAYMENT_PENDING = 'PENDING';
    public const PAYMENT_PAID = 'PAID';

    // Work statuses
    public const WORK_STATUS_PENDING = 'PENDING';
    public const WORK_STATUS_ASSIGNED = 'ASSIGNED';
    public const WORK_STATUS_IN_PROGRESS = 'IN_PROGRESS';
    public const WORK_STATUS_ON_HOLD = 'ON_HOLD';
    public const WORK_STATUS_COMPLETED = 'COMPLETED';
    public const WORK_STATUS_CLOSED = 'CLOSED';

    public function rooms()
    {
        return $this->hasMany(ProjectRoom::class);
    }

    // Alias for zones (used in SummaryController)
    public function zones()
    {
        return $this->hasMany(ProjectRoom::class);
    }

    /**
     * Get all photos for this project
     */
    public function photos()
    {
        return $this->hasMany(ProjectPhoto::class);
    }

    /**
     * Get the customer associated with this project (by phone).
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'phone', 'phone');
    }

    /**
     * Calculate milestone amounts (40-40-20) from BASE TOTAL only (excluding GST)
     */
    public function calculateMilestoneAmounts(): array
    {
        $baseTotal = $this->base_total ?? $this->total_amount ?? 0;
        return [
            'booking_amount' => round($baseTotal * 0.40, 2),
            'mid_amount' => round($baseTotal * 0.40, 2),
            'final_amount' => round($baseTotal * 0.20, 2),
        ];
    }

    /**
     * Get the milestone payments for this project
     */
    public function milestonePayments()
    {
        return $this->hasMany(MilestonePayment::class);
    }

    /**
     * Get GST rate from settings or project
     */
    public function getGstRate(): float
    {
        return $this->gst_rate ?? (float) Setting::get('gst_rate', 18);
    }

    /**
     * Calculate milestone with GST breakdown
     */
    public function calculateMilestoneWithGst(string $milestoneType): array
    {
        $baseTotal = $this->base_total ?? $this->total_amount ?? 0;
        $gstRate = $this->getGstRate();
        
        $percentage = match($milestoneType) {
            'booking' => 0.40,
            'mid' => 0.40,
            'final' => 0.20,
            default => 0,
        };
        
        $baseAmount = round($baseTotal * $percentage, 2);
        $gstAmount = round($baseAmount * ($gstRate / 100), 2);
        $totalAmount = $baseAmount + $gstAmount;
        
        return [
            'base_amount' => $baseAmount,
            'gst_rate' => $gstRate,
            'gst_amount' => $gstAmount,
            'total_amount' => $totalAmount,
        ];
    }

    /**
     * Check if all milestones are paid
     */
    public function isFullyPaid(): bool
    {
        return $this->milestonePayments()
            ->where('payment_status', MilestonePayment::STATUS_PAID)
            ->count() === 3;
    }

    /**
     * Check if GST invoice can be generated
     */
    public function canGenerateGstInvoice(): bool
    {
        if (!$this->isFullyPaid()) {
            return false;
        }
        
        $totalBasePaid = $this->milestonePayments()
            ->where('payment_status', MilestonePayment::STATUS_PAID)
            ->sum('base_amount');
        
        return abs($totalBasePaid - ($this->base_total ?? $this->total_amount ?? 0)) < 0.01;
    }

    /**
     * Check if booking is pending
     */
    public function isBookingPending(): bool
    {
        return $this->booking_status === self::PAYMENT_PENDING;
    }

    /**
     * Check if booking is paid
     */
    public function isBookingPaid(): bool
    {
        return $this->booking_status === self::PAYMENT_PAID;
    }

    /**
     * Check if mid payment is pending
     */
    public function isMidPaymentPending(): bool
    {
        return $this->mid_status === self::PAYMENT_PENDING;
    }

    /**
     * Check if mid payment is paid
     */
    public function isMidPaymentPaid(): bool
    {
        return $this->mid_status === self::PAYMENT_PAID;
    }

    /**
     * Check if final payment is pending
     */
    public function isFinalPaymentPending(): bool
    {
        return $this->final_status === self::PAYMENT_PENDING;
    }

    /**
     * Check if final payment is paid
     */
    public function isFinalPaymentPaid(): bool
    {
        return $this->final_status === self::PAYMENT_PAID;
    }

    /**
     * Check if payment method is cash
     */
    public function isCashPayment(): bool
    {
        return $this->payment_method === self::PAYMENT_METHOD_CASH;
    }

    /**
     * Check if payment method is online
     */
    public function isOnlinePayment(): bool
    {
        return $this->payment_method === self::PAYMENT_METHOD_ONLINE;
    }

    /**
     * Check if awaiting cash confirmation
     */
    public function isAwaitingCashConfirmation(): bool
    {
        return $this->status === self::STATUS_AWAITING_CASH_CONFIRMATION;
    }

    /**
     * Check if project is confirmed
     */
    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    /**
     * Check if work can start (booking must be paid)
     */
    public function canStartWork(): bool
    {
        return $this->isBookingPaid();
    }

    /**
     * Check if mid payment can be collected
     */
    public function canCollectMidPayment(): bool
    {
        return $this->isBookingPaid() && $this->isMidPaymentPending();
    }

    /**
     * Check if final payment can be collected
     */
    public function canCollectFinalPayment(): bool
    {
        return $this->isMidPaymentPaid() && $this->isFinalPaymentPending();
    }

    /**
     * Get the user who confirmed cash payment
     */
    public function cashConfirmedByUser()
    {
        return $this->belongsTo(User::class, 'cash_confirmed_by');
    }

    /**
     * Get the supervisor assigned to this project
     */
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    // ==================== Work Status Helpers ====================

    /**
     * Check if work is pending
     */
    public function isWorkPending(): bool
    {
        return $this->work_status === self::WORK_STATUS_PENDING;
    }

    /**
     * Check if work is assigned
     */
    public function isWorkAssigned(): bool
    {
        return $this->work_status === self::WORK_STATUS_ASSIGNED;
    }

    /**
     * Check if work is in progress
     */
    public function isWorkInProgress(): bool
    {
        return $this->work_status === self::WORK_STATUS_IN_PROGRESS;
    }

    /**
     * Check if work is on hold
     */
    public function isWorkOnHold(): bool
    {
        return $this->work_status === self::WORK_STATUS_ON_HOLD;
    }

    /**
     * Check if work is completed
     */
    public function isWorkCompleted(): bool
    {
        return $this->work_status === self::WORK_STATUS_COMPLETED;
    }

    /**
     * Check if work is closed
     */
    public function isWorkClosed(): bool
    {
        return $this->work_status === self::WORK_STATUS_CLOSED;
    }

    /**
     * Get human readable work status label
     */
    public function getWorkStatusLabelAttribute(): string
    {
        return match($this->work_status) {
            self::WORK_STATUS_PENDING => 'Pending',
            self::WORK_STATUS_ASSIGNED => 'Assigned',
            self::WORK_STATUS_IN_PROGRESS => 'In Progress',
            self::WORK_STATUS_ON_HOLD => 'On Hold',
            self::WORK_STATUS_COMPLETED => 'Completed',
            self::WORK_STATUS_CLOSED => 'Closed',
            default => 'Unknown',
        };
    }

    // ==================== Coupon Helpers ====================

    /**
     * Get the coupon associated with this project
     */
    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Check if any payment has been made
     */
    public function hasPayment(): bool
    {
        return $this->booking_status === self::PAYMENT_PAID ||
               $this->mid_status === self::PAYMENT_PAID ||
               $this->final_status === self::PAYMENT_PAID;
    }

    /**
     * Check if coupon can be modified (no payments made)
     */
    public function canModifyCoupon(): bool
    {
        return !$this->hasPayment();
    }

    /**
     * Get the discounted subtotal (before GST)
     */
    public function getDiscountedSubtotalAttribute(): float
    {
        $subtotal = $this->total_amount ?? 0;
        return max(0, $subtotal - ($this->discount_amount ?? 0));
    }

    /**
     * Get the grand total (after discount + GST)
     * Assuming GST is 18% - adjust as needed
     */
    public function getGrandTotalAttribute(): float
    {
        $subtotal = $this->discounted_subtotal;
        $gstRate = 0.18; // 18% GST
        $gstAmount = round($subtotal * $gstRate, 2);
        return $subtotal + $gstAmount;
    }

    /**
     * Apply a coupon to this project
     */
    public function applyCoupon(Coupon $coupon, float $subtotal): bool
    {
        if (!$this->canModifyCoupon()) {
            return false;
        }

        $discount = $coupon->calculateDiscount($subtotal);

        $this->update([
            'coupon_id' => $coupon->id,
            'coupon_code' => $coupon->code,
            'discount_amount' => $discount,
        ]);

        return true;
    }

    /**
     * Remove coupon from this project
     */
    public function removeCoupon(): bool
    {
        if (!$this->canModifyCoupon()) {
            return false;
        }

        $this->update([
            'coupon_id' => null,
            'coupon_code' => null,
            'discount_amount' => 0,
        ]);

        return true;
    }

    /**
     * Recalculate and persist base_total and milestone amounts from room items/services
     * This is the SINGLE SOURCE OF TRUTH for all payment calculations
     */
    public function recalculateTotals(): self
    {
        // Load rooms with items and services if not already loaded
        if (!$this->relationLoaded('rooms')) {
            $this->load(['rooms.items', 'rooms.services']);
        }

        $totalPaintAmount = 0;
        $totalServiceAmount = 0;

        foreach ($this->rooms as $room) {
            foreach ($room->items as $item) {
                $totalPaintAmount += $item->amount ?? 0;
            }
            foreach ($room->services as $service) {
                $totalServiceAmount += $service->amount ?? 0;
            }
        }

        // Calculate base_total (excluding GST) - this is the contract value
        $discountAmount = $this->discount_amount ?? 0;
        $baseTotal = $totalPaintAmount + $totalServiceAmount - $discountAmount;

        // Calculate milestone amounts from base_total only (40-40-20)
        $bookingAmount = round($baseTotal * 0.40, 2);
        $midAmount = round($baseTotal * 0.40, 2);
        $finalAmount = round($baseTotal * 0.20, 2);

        // Persist all values to database
        $this->update([
            'total_amount' => $totalPaintAmount + $totalServiceAmount, // Raw total before discount
            'base_total' => $baseTotal, // After discount, excluding GST
            'booking_amount' => $bookingAmount,
            'mid_amount' => $midAmount,
            'final_amount' => $finalAmount,
            'gst_rate' => $this->getGstRate(),
        ]);

        return $this;
    }

    /**
     * Get painting total from rooms
     */
    public function getPaintingTotalAttribute(): float
    {
        if (!$this->relationLoaded('rooms')) {
            $this->load(['rooms.items']);
        }

        return $this->rooms->sum(function ($room) {
            return $room->items->sum('amount');
        });
    }

    /**
     * Get services total from rooms
     */
    public function getServicesTotalAttribute(): float
    {
        if (!$this->relationLoaded('rooms')) {
            $this->load(['rooms.services']);
        }

        return $this->rooms->sum(function ($room) {
            return $room->services->sum('amount');
        });
    }
}
