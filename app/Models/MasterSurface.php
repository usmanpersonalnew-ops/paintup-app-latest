<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MasterSurface extends Model
{
    protected $fillable = [
        'name',
        'category',
        'unit_type',
        'remarks',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(MasterProduct::class, 'product_surface_links', 'surface_id', 'product_id')->with('systems');
    }
}
