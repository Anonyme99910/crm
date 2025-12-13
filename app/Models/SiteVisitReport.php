<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteVisitReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_visit_id',
        'report_number',
        'summary',
        'recommendations',
        'scope_of_work',
        'estimated_cost',
        'pdf_path',
    ];

    protected $casts = [
        'scope_of_work' => 'array',
        'estimated_cost' => 'decimal:2',
    ];

    public function siteVisit()
    {
        return $this->belongsTo(SiteVisit::class);
    }
}
