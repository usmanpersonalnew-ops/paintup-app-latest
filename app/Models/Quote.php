<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    public function zones() {
        return $this->hasMany(QuoteZone::class);
    }
}
