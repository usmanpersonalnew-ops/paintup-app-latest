<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'uploaded_by_type',
        'uploaded_by_id',
        'google_drive_file_id',
        'google_drive_link',
        'file_name',
        'description',
        'stage',
    ];

    public const STAGE_BEFORE = 'before';
    public const STAGE_IN_PROGRESS = 'in-progress';
    public const STAGE_AFTER = 'after';

    public const STAGES = [
        self::STAGE_BEFORE => 'Before',
        self::STAGE_IN_PROGRESS => 'In Progress',
        self::STAGE_AFTER => 'After',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the project that owns the photo
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user who uploaded the photo
     */
    public function uploadedBy()
    {
        return $this->morphTo();
    }
}
