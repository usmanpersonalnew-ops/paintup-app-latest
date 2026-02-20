<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_room_id',
        'surface_id',
        'master_product_id',
        'master_system_id',
        'qty',
        'rate',
        'amount',
        'system_rate',
        'measurement_mode',
        'pricing_mode',
        'deductions',
        'color_code',
        'description',
        'remarks',
        'manual_price',
        'gross_qty',
        'net_qty',
    ];

    public function zone()
    {
        return $this->belongsTo(ProjectRoom::class, 'project_room_id');
    }

    public function surface()
    {
        return $this->belongsTo(MasterSurface::class, 'surface_id');
    }

    public function product()
    {
        return $this->belongsTo(MasterProduct::class, 'master_product_id');
    }

    public function system()
    {
        return $this->belongsTo(MasterPaintingSystem::class, 'master_system_id');
    }
}
