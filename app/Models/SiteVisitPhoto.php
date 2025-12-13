<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteVisitPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_visit_id',
        'photo_path',
        'caption',
        'category',
    ];

    public function siteVisit()
    {
        return $this->belongsTo(SiteVisit::class);
    }
}
