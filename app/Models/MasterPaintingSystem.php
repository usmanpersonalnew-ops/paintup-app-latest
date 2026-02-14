<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterPaintingSystem extends Model
{
    use HasFactory;
    protected $fillable = ['product_id', 'system_name', 'process_remarks', 'base_rate', 'warranty_months'];

    public function product()
    {
        return $this->belongsTo(MasterProduct::class, 'product_id');
    }
}
