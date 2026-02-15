<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    protected $fillable = [
        'project_id',
        'customer_name',
        'customer_phone',
        'status',
        'discount_amount',
        'tax_percent',
        'tax_amount',
        'grand_total',
        'notes',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    public function zones() {
        return $this->hasMany(QuoteZone::class);
    }

    public function project() {
        return $this->belongsTo(Project::class);
    }
}
