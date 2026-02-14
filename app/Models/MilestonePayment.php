<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MilestonePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'milestone_name',
        'base_amount',
        'gst_amount',
        'total_amount',
        'payment_status',
        'payment_method',
        'payment_reference',
        'tracking_id',
        'bank_ref_no',
        'payment_mode',
        'card_name',
        'paid_at',
        'gst_invoice_generated_at',
        'gst_invoice_number',
    ];

    protected $casts = [
        'base_amount' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'gst_invoice_generated_at' => 'datetime',
    ];

    // Payment statuses
    public const STATUS_PENDING = 'PENDING';
    public const STATUS_PAID = 'PAID';
    public const STATUS_AWAITING_CONFIRMATION = 'AWAITING_CONFIRMATION';

    // Payment methods
    public const METHOD_ONLINE = 'ONLINE';
    public const METHOD_CASH = 'CASH';

    // Milestone names
    public const MILESTONE_BOOKING = 'booking';
    public const MILESTONE_MID = 'mid';
    public const MILESTONE_FINAL = 'final';

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Check if milestone is paid
     */
    public function isPaid(): bool
    {
        return $this->payment_status === self::STATUS_PAID;
    }

    /**
     * Check if milestone is awaiting confirmation
     */
    public function isAwaitingConfirmation(): bool
    {
        return $this->payment_status === self::STATUS_AWAITING_CONFIRMATION;
    }

    /**
     * Check if milestone is pending
     */
    public function isPending(): bool
    {
        return $this->payment_status === self::STATUS_PENDING;
    }

    /**
     * Check if GST invoice can be generated
     */
    public function canGenerateGstInvoice(): bool
    {
        return $this->isPaid() && !$this->gst_invoice_generated_at;
    }

    /**
     * Generate GST invoice number
     */
    public function generateGstInvoiceNumber(): string
    {
        return 'GST-' . date('Y') . '-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }
}