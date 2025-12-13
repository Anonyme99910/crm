<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'material_id',
        'unit_price',
        'minimum_order',
        'lead_time_days',
        'valid_from',
        'valid_until',
        'is_preferred',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'minimum_order' => 'decimal:2',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'is_preferred' => 'boolean',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function isValid()
    {
        $now = now();
        return (!$this->valid_from || $this->valid_from <= $now) 
            && (!$this->valid_until || $this->valid_until >= $now);
    }
}
