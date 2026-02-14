<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ProjectZone extends Model {
    protected $guarded = [];
    public function items() { return $this->hasMany(QuoteItem::class); }
    public function services() { return $this->hasMany(QuoteService::class); }
    public function project() { return $this->belongsTo(Project::class); }
}
