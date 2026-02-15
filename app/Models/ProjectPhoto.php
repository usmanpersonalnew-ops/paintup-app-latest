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

    /**
     * Convert Google Drive link to direct image URL
     * Handles various Google Drive URL formats
     */
    public function getImageUrlAttribute(): string
    {
        $link = $this->google_drive_link;
        
        // If already in the correct format, return as is
        if (strpos($link, 'drive.google.com/uc?export=view&id=') !== false) {
            return $link;
        }
        
        // Priority 1: Use stored file ID if available (most reliable)
        $fileId = $this->google_drive_file_id;
        if (!empty($fileId)) {
            // Don't trim - preserve the exact file ID as it may be needed
            return "https://drive.google.com/uc?export=view&id=" . urlencode($fileId);
        }
        
        // Priority 2: Try to extract file ID from various Google Drive URL formats
        // Handle: drive.usercontent.google.com/download?id=... (download URLs)
        if (preg_match('/drive\.usercontent\.google\.com\/download[?&]id=([a-zA-Z0-9_-]+)/', $link, $matches)) {
            $fileId = $matches[1];
            return "https://drive.google.com/uc?export=view&id=" . urlencode($fileId);
        }
        
        // Handle: drive.usercontent.google.com/download?id=... (with query params)
        if (preg_match('/[?&]id=([a-zA-Z0-9_-]+)/', $link, $matches)) {
            $fileId = $matches[1];
            return "https://drive.google.com/uc?export=view&id=" . urlencode($fileId);
        }
        
        // Handle: drive.google.com/file/d/FILE_ID/view
        if (preg_match('/drive\.google\.com\/file\/d\/([a-zA-Z0-9_-]+)/', $link, $matches)) {
            $fileId = $matches[1];
            return "https://drive.google.com/uc?export=view&id=" . urlencode($fileId);
        }
        
        // Handle: drive.google.com/uc?id=...
        if (preg_match('/drive\.google\.com\/uc\?id=([a-zA-Z0-9_-]+)/', $link, $matches)) {
            $fileId = $matches[1];
            return "https://drive.google.com/uc?export=view&id=" . urlencode($fileId);
        }
        
        // Fallback: return original link
        return $link;
    }
}
