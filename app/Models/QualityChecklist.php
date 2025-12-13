<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QualityChecklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'description',
        'items',
        'is_active',
    ];

    protected $casts = [
        'items' => 'array',
        'is_active' => 'boolean',
    ];

    public function inspections()
    {
        return $this->hasMany(QualityInspection::class, 'checklist_id');
    }
}
