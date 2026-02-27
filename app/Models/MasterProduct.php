<?php
// app/Models/MasterProduct.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterProduct extends Model
{
    use HasFactory;

    // Allow these fields to be saved
    protected $fillable = ['name', 'brand', 'tier_id'];

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

    // RELATIONSHIP 3: Tier (New relationship)
    public function tier()
    {
        return $this->belongsTo(Tier::class, 'tier_id');
    }

    // Accessor for tier name
    public function getTierNameAttribute()
    {
        return $this->tier ? $this->tier->name : 'No Tier';
    }
}