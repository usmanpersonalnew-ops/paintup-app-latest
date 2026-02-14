<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectRoom extends Model
{
    protected $table = 'project_rooms';
    protected $fillable = ['project_id', 'name', 'type', 'length', 'breadth', 'height'];

    /**
     * Get the route key name for route model binding.
     * Ensures {projectRoom} parameter binds to this model by 'id'.
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }

    // Link to Parent Project
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Link to Children
    public function items()
    {
        return $this->hasMany(QuoteItem::class, 'project_room_id');
    }

    public function services()
    {
        return $this->hasMany(QuoteService::class, 'project_room_id');
    }

    public function media()
    {
        return $this->hasMany(QuoteMedia::class, 'project_room_id');
    }
}
