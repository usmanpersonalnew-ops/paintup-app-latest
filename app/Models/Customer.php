<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'city',
        'state',
        'pincode',
        'whatsapp_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'whatsapp_verified_at' => 'datetime',
    ];

    /**
     * Get the projects associated with this customer (by phone).
     */
    public function projects()
    {
        return $this->hasMany(Project::class, 'phone', 'phone');
    }

    /**
     * Check if customer can access a specific project.
     */
    public function canAccessProject($projectId): bool
    {
        return Project::where('id', $projectId)
            ->where('phone', $this->phone)
            ->exists();
    }
}