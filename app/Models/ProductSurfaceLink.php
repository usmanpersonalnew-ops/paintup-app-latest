<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSurfaceLink extends Model
{
    protected $fillable = ['product_id', 'surface_id'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(MasterProduct::class);
    }

    public function surface(): BelongsTo
    {
        return $this->belongsTo(MasterSurface::class);
    }
}
