<?php
// app/Models/Tier.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    // RELATIONSHIP: Tier has many MasterProducts
    public function products()
    {
        return $this->hasMany(MasterProduct::class, 'tier_id'); 
    }

}