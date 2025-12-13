<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'material_id',
        'phase_id',
        'required_quantity',
        'used_quantity',
        'unit_price',
        'status',
    ];

    protected $casts = [
        'required_quantity' => 'decimal:2',
        'used_quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function phase()
    {
        return $this->belongsTo(ProjectPhase::class, 'phase_id');
    }

    public function getRemainingQuantityAttribute()
    {
        return $this->required_quantity - $this->used_quantity;
    }

    public function getTotalCostAttribute()
    {
        return $this->used_quantity * $this->unit_price;
    }
}
