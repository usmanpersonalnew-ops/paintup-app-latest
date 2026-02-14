<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteService extends Model
{
    use HasFactory;

    protected $table = 'quote_services';

    protected $fillable = [
        'project_room_id',
        'master_service_id',
        'custom_name',
        'unit_type',
        'quantity',
        'length',
        'breadth',
        'count',
        'rate',
        'amount',
        'photo_url',
        'remarks',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'length' => 'decimal:2',
        'breadth' => 'decimal:2',
        'count' => 'integer',
        'rate' => 'decimal:2',
        'amount' => 'decimal:2',
        'remarks' => 'string',
    ];

    public function zone()
    {
        return $this->belongsTo(ProjectRoom::class, 'project_room_id');
    }

    public function masterService()
    {
        return $this->belongsTo(MasterService::class);
    }
}
