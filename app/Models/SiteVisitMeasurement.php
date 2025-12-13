<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteVisitMeasurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_visit_id',
        'room_name',
        'length',
        'width',
        'height',
        'area',
        'notes',
        'custom_measurements',
    ];

    protected $casts = [
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'area' => 'decimal:2',
        'custom_measurements' => 'array',
    ];

    public function siteVisit()
    {
        return $this->belongsTo(SiteVisit::class);
    }
}
