<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterService extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'unit_type',
        'default_rate',
        'is_repair',
        'remarks',
    ];

    protected $casts = [
        'default_rate' => 'decimal:2',
        'is_repair' => 'boolean',
        'remarks' => 'string',
    ];
}