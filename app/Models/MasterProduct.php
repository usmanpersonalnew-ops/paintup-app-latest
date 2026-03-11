<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterProduct extends Model
{
    use HasFactory;

    // Allow these fields to be saved
    protected $fillable = ['name', 'brand', 'tier'];

    // RELATIONSHIP 1: Surfaces (Fixes the crash)
    public function surfaces()
    {
        return $this->belongsToMany(MasterSurface::class, 'product_surface_links', 'product_id', 'surface_id');
    }

    // RELATIONSHIP 2: Systems (Fixes the "0 Systems" issue)
    public function systems()
    {
        return $this->hasMany(MasterPaintingSystem::class, 'product_id');
    }
}