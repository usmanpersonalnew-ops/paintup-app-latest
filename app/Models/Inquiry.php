<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'city',
        'pincode',
        'whatsapp_enabled',
        'construction_ongoing',
        'property_type',
        'visit_date',
        'source',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
        'whatsapp_enabled' => 'boolean',
        'visit_date' => 'date',
    ];

    public const STATUS_NEW = 'NEW';
    public const STATUS_CALLED = 'CALLED';
    public const STATUS_VISIT_BOOKED = 'VISIT_BOOKED';
}
