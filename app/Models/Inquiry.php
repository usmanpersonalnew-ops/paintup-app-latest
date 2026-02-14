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
        'source',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public const STATUS_NEW = 'NEW';
    public const STATUS_CALLED = 'CALLED';
    public const STATUS_VISIT_BOOKED = 'VISIT_BOOKED';
}