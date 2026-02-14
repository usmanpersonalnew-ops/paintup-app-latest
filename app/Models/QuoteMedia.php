<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_room_id',
        'photo_url',
        'tag',
    ];

    protected $casts = [
        'tag' => 'string',
    ];

    public function projectRoom()
    {
        return $this->belongsTo(ProjectRoom::class, 'project_room_id');
    }
}