<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteZone extends Model
{
    protected $fillable = ['quote_id', 'name', 'zone_type', 'default_length', 'default_breadth', 'default_height'];

    public function quote() {
        return $this->belongsTo(Quote::class);
    }

    public function items() {
        return $this->hasMany(QuoteItem::class);
    }

    public function services() {
        return $this->hasMany(QuoteService::class);
    }

    public function media() {
        return $this->hasMany(QuoteMedia::class);
    }
}
